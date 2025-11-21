<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\Payment;
use App\Models\PricingTier;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Form $form;
    protected PricingTier $pricingTier;
    protected Submission $submission;
    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        // Create form
        $this->form = Form::create([
            'title' => 'Test Payment Form',
            'slug' => 'test-payment-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => true,
        ]);

        // Create pricing tier
        $this->pricingTier = PricingTier::create([
            'form_id' => $this->form->id,
            'name' => 'Standard',
            'price' => 50000,
            'currency' => 'IDR',
            'description' => 'Standard pricing',
            'order' => 1,
        ]);

        // Create submission
        $this->submission = Submission::create([
            'form_id' => $this->form->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => '081234567890',
            'data' => ['name' => $this->user->name],
            'pricing_tier_id' => $this->pricingTier->id,
            'tier_amount' => $this->pricingTier->price,
            'total_amount' => $this->pricingTier->price,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        // Create payment
        $this->payment = Payment::create([
            'submission_id' => $this->submission->id,
            'form_id' => $this->form->id,
            'pricing_tier_id' => $this->pricingTier->id,
            'external_id' => 'TEST-' . $this->submission->id . '-' . time(),
            'xendit_invoice_id' => 'xendit-invoice-' . Str::random(10),
            'xendit_invoice_url' => 'https://checkout.xendit.co/test',
            'amount' => $this->pricingTier->price,
            'currency' => 'IDR',
            'status' => 'pending',
            'expired_at' => now()->addHours(24),
        ]);
    }

    public function test_webhook_updates_payment_and_submission_to_paid(): void
    {
        // Simulate Xendit webhook for paid invoice
        $webhookData = [
            'external_id' => $this->payment->external_id,
            'status' => 'PAID',
            'paid_at' => now()->toISOString(),
            'payment_method' => 'BANK_TRANSFER',
            'payment_channel' => 'BCA',
            'amount' => $this->payment->amount,
        ];

        $response = $this->postJson('/api/public/payments/webhook', $webhookData, [
            'x-callback-token' => config('xendit.webhook_token')
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ]);

        // Verify payment was updated
        $this->payment->refresh();
        $this->assertEquals('paid', $this->payment->status);
        $this->assertEquals('BANK_TRANSFER', $this->payment->payment_method);
        $this->assertEquals('BCA', $this->payment->payment_channel);
        $this->assertNotNull($this->payment->paid_at);

        // Verify submission was updated
        $this->submission->refresh();
        $this->assertEquals('paid', $this->submission->payment_status);
        $this->assertEquals('approved', $this->submission->status);
        $this->assertNotNull($this->submission->paid_at);
    }

    public function test_webhook_updates_payment_to_expired(): void
    {
        // Debug payment before webhook
        dump('Payment before webhook:', [
            'id' => $this->payment->id,
            'external_id' => $this->payment->external_id,
            'status' => $this->payment->status
        ]);

        // Simulate Xendit webhook for expired invoice
        $webhookData = [
            'external_id' => $this->payment->external_id,
            'status' => 'EXPIRED',
        ];

        $response = $this->postJson('/api/public/payments/webhook', $webhookData, [
            'x-callback-token' => config('xendit.webhook_token')
        ]);

        dump('Response:', $response->json());

        // Check if payment exists in database
        $paymentInDb = \App\Models\Payment::where('external_id', $this->payment->external_id)->first();
        dump('Payment in DB:', $paymentInDb ? $paymentInDb->toArray() : 'NOT FOUND');

        $response->assertOk();

        // Verify payment was updated - get fresh from database
        $updatedPayment = \App\Models\Payment::find($this->payment->id);
        $this->assertEquals('expired', $updatedPayment->status);

        // Verify submission was updated - get fresh from database
        $updatedSubmission = \App\Models\Submission::find($this->submission->id);
        dump('Updated submission:', $updatedSubmission ? $updatedSubmission->toArray() : 'NOT FOUND');
        $this->assertEquals('expired', $updatedSubmission->payment_status);
        $this->assertEquals('pending', $updatedSubmission->status);
    }

    public function test_webhook_updates_payment_to_failed(): void
    {
        // Simulate Xendit webhook for failed payment
        $webhookData = [
            'external_id' => $this->payment->external_id,
            'status' => 'FAILED',
        ];

        $response = $this->postJson('/api/public/payments/webhook', $webhookData, [
            'x-callback-token' => config('xendit.webhook_token')
        ]);

        $response->assertOk();

        // Verify payment was updated
        $this->payment->refresh();
        $this->assertEquals('failed', $this->payment->status);

        // Verify submission was updated
        $this->submission->refresh();
        $this->assertEquals('failed', $this->submission->payment_status);
        $this->assertEquals('pending', $this->submission->status);
    }

    public function test_webhook_with_invalid_external_id_returns_success(): void
    {
        // Simulate webhook with non-existent external_id
        $webhookData = [
            'external_id' => 'NON_EXISTENT_ID',
            'status' => 'PAID',
        ];

        $response = $this->postJson('/api/public/payments/webhook', $webhookData, [
            'x-callback-token' => config('xendit.webhook_token')
        ]);

        // Should still return success to prevent Xendit retries
        $response->assertOk()
            ->assertJson(['success' => true]);

        // Original payment should remain unchanged
        $this->payment->refresh();
        $this->assertEquals('pending', $this->payment->status);
    }

    public function test_webhook_always_returns_success_even_on_error(): void
    {
        // Test dengan data yang bisa menyebabkan error
        $webhookData = [
            // Missing external_id to potentially cause an error
            'status' => 'PAID',
        ];

        $response = $this->postJson('/api/public/payments/webhook', $webhookData, [
            'x-callback-token' => config('xendit.webhook_token')
        ]);

        // Should always return success to prevent Xendit retries
        $response->assertOk()
            ->assertJson(['success' => true]);
    }
}