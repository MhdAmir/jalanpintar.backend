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

        // Validate and format phone number (must start with +62)
        if ($customerPhone) {
            // Remove leading 0 and add +62
            $customerPhone = preg_replace('/^0/', '+62', $customerPhone);
            // If doesn't start with +, add +62
            if (!str_starts_with($customerPhone, '+')) {
                $customerPhone = '+62' . $customerPhone;
            }
        }

        // Basic invoice data (required fields only)
        $invoiceData = [
            'external_id' => $externalId,
            'amount' => (float) $pricingTier->price,
            'description' => substr("{$form->title} - {$pricingTier->name}", 0, 255), // Max 255 chars
            'invoice_duration' => 86400, // 24 hours
            'currency' => $pricingTier->currency ?? 'IDR',
        ];

        // Add payer email if valid
        if ($customerEmail && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $invoiceData['payer_email'] = $customerEmail;
        }

        // Add redirect URLs
        $queryParams = [
            'form' => $form->title,
            'tier' => $pricingTier->name,
            'timestamp' => now()->format('d/m/Y, H.i.s'),
            'external_id' => $externalId,
        ];

        $invoiceData['success_redirect_url'] = config('xendit.success_redirect_url') . '?' . http_build_query($queryParams);
        $invoiceData['failure_redirect_url'] = config('xendit.failure_redirect_url') . '?external_id=' . $externalId;

        // Add customer data if name is available
        if ($customerName && $customerName !== 'Customer') {
            $customer = ['given_names' => substr($customerName, 0, 255)];

            if ($customerEmail && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                $customer['email'] = $customerEmail;
            }

            if ($customerPhone) {
                $customer['mobile_number'] = $customerPhone;
            }

            $invoiceData['customer'] = $customer;
        }

        // Add items
        $invoiceData['items'] = [
            [
                'name' => substr($pricingTier->name, 0, 255),
                'quantity' => 1,
                'price' => (float) $pricingTier->price,
            ]
        ];

        try {
            // Log request for debugging
            \Log::info('Creating Xendit invoice', [
                'external_id' => $externalId,
                'amount' => $invoiceData['amount'],
                'email' => $invoiceData['payer_email'] ?? null,
            ]);

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

            // Update submission status to pending
            $submission->update(['status' => 'pending']);

            \Log::info('Xendit invoice created successfully', [
                'payment_id' => $payment->id,
                'invoice_id' => $xenditInvoice['id'],
            ]);

            return $payment;

        } catch (\Exception $e) {
            \Log::error('Failed to create Xendit invoice', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id,
                'data' => $invoiceData ?? [],
            ]);
            throw $e;
        }
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
