<?php

namespace Tests\Feature;

use App\Http\Middleware\JwtMiddleware;
use App\Models\Form;
use App\Models\PricingTier;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GetMySubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Form $form;
    private PricingTier $pricingTier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        $this->form = Form::create([
            'title' => 'Test Form',
            'slug' => 'test-form-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => true,
        ]);

        $this->pricingTier = PricingTier::create([
            'form_id' => $this->form->id,
            'name' => 'Standard',
            'price' => 50000,
            'currency' => 'IDR',
            'description' => 'Standard pricing',
            'order' => 1,
        ]);
    }

    public function test_user_can_get_their_submission_by_form_id(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);
        
        // Create a submission for the user
        $submission = Submission::create([
            'form_id' => $this->form->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => '081234567890',
            'data' => [
                'name' => $this->user->name,
                'message' => 'Test submission'
            ],
            'pricing_tier_id' => $this->pricingTier->id,
            'tier_amount' => $this->pricingTier->price,
            'total_amount' => $this->pricingTier->price,
            'payment_status' => 'paid',
            'status' => 'approved',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/forms/{$this->form->id}/my-submission");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'data',
                    'payment_status',
                    'status',
                    'total_amount',
                    'form' => [
                        'id',
                        'title',
                        'slug',
                    ],
                    'pricing_tier' => [
                        'id',
                        'name',
                        'price',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $submission->id,
                    'email' => $this->user->email,
                    'payment_status' => 'paid',
                    'status' => 'approved',
                ]
            ]);
    }

    public function test_user_gets_404_when_no_submission_exists(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/forms/{$this->form->id}/my-submission");

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'You have not submitted this form yet',
            ]);
    }

    public function test_user_cannot_see_other_users_submission(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);
        
        // Create another user and their submission
        $otherUser = User::factory()->create([
            'email' => 'other@example.com'
        ]);

        Submission::create([
            'form_id' => $this->form->id,
            'name' => $otherUser->name,
            'email' => $otherUser->email,
            'phone' => '081234567890',
            'data' => ['name' => $otherUser->name],
            'pricing_tier_id' => $this->pricingTier->id,
            'tier_amount' => $this->pricingTier->price,
            'total_amount' => $this->pricingTier->price,
            'payment_status' => 'paid',
            'status' => 'approved',
        ]);

        // Current user should not see other user's submission
        $response = $this->actingAs($this->user)
            ->getJson("/api/forms/{$this->form->id}/my-submission");

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'You have not submitted this form yet',
            ]);
    }

    public function test_endpoint_requires_authentication(): void
    {
        $response = $this->getJson("/api/forms/{$this->form->id}/my-submission");

        $response->assertUnauthorized();
    }

    public function test_user_gets_submission_with_payment_info(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);
        
        // Create submission with payment
        $submission = Submission::create([
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

        $response = $this->actingAs($this->user)
            ->getJson("/api/forms/{$this->form->id}/my-submission");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $submission->id,
                    'payment_status' => 'pending',
                    'status' => 'pending',
                    'total_amount' => '50000.00',
                ]
            ]);
    }
}