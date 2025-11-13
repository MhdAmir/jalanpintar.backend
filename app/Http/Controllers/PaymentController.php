<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Submission;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected XenditService $xenditService
    ) {}

    /**
     * Create payment invoice for submission
     */
    public function createInvoice(Request $request, string $submissionId): JsonResponse
    {
        try {
            $submission = Submission::with('form.pricingTiers')->findOrFail($submissionId);

            // Validate that form requires payment
            if (!$submission->form->enable_payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'This form does not require payment'
                ], 400);
            }

            // Check if payment already exists and is pending/paid
            $existingPayment = Payment::where('submission_id', $submission->id)
                ->whereIn('status', ['pending', 'paid'])
                ->first();

            if ($existingPayment) {
                if ($existingPayment->isPaid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment already completed',
                        'data' => $existingPayment
                    ], 400);
                }

                // Return existing pending payment
                return response()->json([
                    'success' => true,
                    'message' => 'Payment invoice already exists',
                    'data' => [
                        'payment' => $existingPayment,
                        'invoice_url' => $existingPayment->xendit_invoice_url,
                    ]
                ]);
            }

            // Create new invoice
            $payment = $this->xenditService->createInvoice($submission, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Payment invoice created successfully',
                'data' => [
                    'payment' => $payment,
                    'invoice_url' => $payment->xendit_invoice_url,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Payment creation failed', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment details
     */
    public function show(string $paymentId): JsonResponse
    {
        try {
            $payment = Payment::with(['submission', 'form', 'pricingTier'])->findOrFail($paymentId);

            return response()->json([
                'success' => true,
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }
    }

    /**
     * Get payment by external ID
     */
    public function getByExternalId(string $externalId): JsonResponse
    {
        try {
            $payment = Payment::with(['submission', 'form', 'pricingTier'])
                ->where('external_id', $externalId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }
    }

    /**
     * Xendit webhook handler
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // Verify webhook token
            $callbackToken = $request->header('x-callback-token');
            
            if ($callbackToken !== config('xendit.webhook_token')) {
                Log::warning('Invalid webhook token', [
                    'received_token' => $callbackToken
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook token'
                ], 401);
            }

            // Log webhook data
            Log::info('Xendit webhook received', $request->all());

            // Handle webhook
            $payment = $this->xenditService->handleWebhook($request->all());

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
