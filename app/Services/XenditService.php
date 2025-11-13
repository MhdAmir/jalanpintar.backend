<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Submission;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class XenditService
{
    protected InvoiceApi $invoiceApi;

    public function __construct()
    {
        Configuration::setXenditKey(config('xendit.api_key'));
        $this->invoiceApi = new InvoiceApi();
    }

    /**
     * Create Xendit invoice for submission payment
     */
    public function createInvoice(Submission $submission, array $data): Payment
    {
        $form = $submission->form;
        $pricingTier = $form->pricingTiers()->findOrFail($data['pricing_tier_id']);

        // Generate external ID
        $externalId = 'SUBMISSION-' . $submission->id . '-' . time();

        // Prepare customer data
        $customerData = $submission->data;
        $customerName = $customerData['nama_lengkap'] ?? 'Customer';
        $customerEmail = $customerData['email'] ?? null;
        $customerPhone = $customerData['telepon'] ?? null;

        // Create invoice request
        $invoiceData = [
            'external_id' => $externalId,
            'amount' => (float) $pricingTier->price,
            'payer_email' => $customerEmail,
            'description' => "{$form->title} - {$pricingTier->name}",
            'invoice_duration' => 86400, // 24 hours
            'currency' => $pricingTier->currency,
            'success_redirect_url' => config('xendit.success_redirect_url') . '?external_id=' . $externalId,
            'failure_redirect_url' => config('xendit.failure_redirect_url') . '?external_id=' . $externalId,
        ];

        // Add customer data if available
        if ($customerName) {
            $invoiceData['customer'] = [
                'given_names' => $customerName,
                'email' => $customerEmail,
                'mobile_number' => $customerPhone,
            ];
        }

        // Add items
        $invoiceData['items'] = [
            [
                'name' => $pricingTier->name,
                'quantity' => 1,
                'price' => (float) $pricingTier->price,
            ]
        ];

        // Create invoice via Xendit API
        $createInvoiceRequest = new CreateInvoiceRequest($invoiceData);
        $xenditInvoice = $this->invoiceApi->createInvoice($createInvoiceRequest);

        // Store payment record in database
        $payment = Payment::create([
            'submission_id' => $submission->id,
            'form_id' => $form->id,
            'pricing_tier_id' => $pricingTier->id,
            'xendit_invoice_id' => $xenditInvoice['id'],
            'xendit_invoice_url' => $xenditInvoice['invoice_url'],
            'external_id' => $externalId,
            'amount' => $pricingTier->price,
            'currency' => $pricingTier->currency,
            'status' => 'pending',
            'expired_at' => now()->addSeconds($invoiceData['invoice_duration']),
        ]);

        // Update submission status to pending_payment
        $submission->update(['status' => 'pending_payment']);

        return $payment;
    }

    /**
     * Handle webhook from Xendit
     */
    public function handleWebhook(array $data): ?Payment
    {
        $externalId = $data['external_id'] ?? null;
        if (!$externalId) {
            return null;
        }

        $payment = Payment::where('external_id', $externalId)->first();
        if (!$payment) {
            return null;
        }

        // Update payment status based on webhook event
        $status = strtolower($data['status'] ?? '');
        
        switch ($status) {
            case 'paid':
            case 'settled':
                $payment->update([
                    'status' => 'paid',
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_channel' => $data['payment_channel'] ?? null,
                    'paid_at' => now(),
                ]);
                
                // Update submission status to paid
                $payment->submission->update(['status' => 'paid']);
                break;

            case 'expired':
                $payment->update(['status' => 'expired']);
                $payment->submission->update(['status' => 'draft']);
                break;

            case 'failed':
                $payment->update(['status' => 'failed']);
                $payment->submission->update(['status' => 'draft']);
                break;
        }

        return $payment;
    }

    /**
     * Get invoice details
     */
    public function getInvoice(string $invoiceId): array
    {
        return $this->invoiceApi->getInvoiceById($invoiceId);
    }

    /**
     * Expire an invoice
     */
    public function expireInvoice(string $invoiceId): array
    {
        return $this->invoiceApi->expireInvoice($invoiceId);
    }
}
