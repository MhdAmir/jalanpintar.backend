<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicSubmissionRequest;
use App\Http\Resources\SubmissionResource;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function __construct(
        private SubmissionService $submissionService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Submission::with(['form', 'pricingTier', 'affiliateReward']);

        // Filter by form
        if ($request->has('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by affiliate code
        if ($request->has('affiliate_code')) {
            $query->where('affiliate_code', $request->affiliate_code);
        }

        // Search by submission number or contact info
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('submission_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $submissions = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => SubmissionResource::collection($submissions),
            'meta' => [
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
                'per_page' => $submissions->perPage(),
                'total' => $submissions->total(),
            ],
        ]);
    }

    public function store(PublicSubmissionRequest $request): JsonResponse
    {
        $submission = $this->submissionService->submitForm($request->validated(), $request->user());

        // Prepare response data
        $responseData = [
            'submission' => new SubmissionResource($submission),
        ];

        // If requires payment, include payment info and redirect to Xendit
        if ($submission->status === 'pending' && $submission->payment) {
            $responseData['payment'] = [
                'id' => $submission->payment->id,
                'invoice_url' => $submission->payment->xendit_invoice_url,
                'amount' => $submission->payment->amount,
                'expired_at' => $submission->payment->expired_at,
            ];
            $responseData['redirect_url'] = $submission->payment->xendit_invoice_url;
            $message = 'Form submitted successfully. Please complete payment.';
        } else {
            // Free submission - redirect to success page with query parameters
            $frontendUrl = config('xendit.success_redirect_url', config('app.frontend_url') . '/payment/success');

            // Build custom success URL with query parameters
            $queryParams = [
                'form' => $submission->form->title,
                'tier' => $submission->pricingTier ? $submission->pricingTier->name : 'Gratis',
                'timestamp' => now()->format('d/m/Y, H.i.s'),
            ];

            $responseData['redirect_url'] = $frontendUrl . '?' . http_build_query($queryParams);
            $message = 'Form submitted successfully.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $responseData,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $submission = Submission::with(['form', 'pricingTier', 'affiliateReward', 'announcement'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new SubmissionResource($submission),
        ]);
    }

    public function updatePaymentStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'payment_status' => 'required|in:unpaid,pending,paid,failed,refunded',
            'payment_reference' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        $submission = $this->submissionService->updatePaymentStatus(
            $id,
            $request->payment_status,
            $request->only(['payment_reference', 'payment_method'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'data' => new SubmissionResource($submission),
        ]);
    }

    public function getByFormId(Request $request, string $formId): JsonResponse
    {
        $query = Submission::with(['form', 'pricingTier', 'affiliateReward', 'payment'])
            ->where('form_id', $formId);

        // Filter by tier (Free or Paid)
        if ($request->has('tier')) {
            $tier = strtolower($request->tier);
            if ($tier === 'free') {
                $query->where('payment_status', 'paid')->where('total_amount', 0);
            } elseif ($tier === 'paid') {
                $query->where('payment_status', 'paid')->where('total_amount', '>', 0);
            }
        }

        // Filter by status (Pending, Paid)
        if ($request->has('status')) {
            $status = strtolower($request->status);
            if ($status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($status === 'paid') {
                $query->where('payment_status', 'paid');
            }
        }

        // Search by name, email, or other data
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('submission_number', 'like', "%{$search}%");
            });
        }

        // Sort by latest
        $submissions = $query->latest()->paginate($request->get('per_page', 15));

        // Calculate statistics
        $stats = [
            'total_submissions' => Submission::where('form_id', $formId)->count(),
            'free_tier' => Submission::where('form_id', $formId)
                ->where('payment_status', 'paid')
                ->where('total_amount', 0)
                ->count(),
            'paid' => Submission::where('form_id', $formId)
                ->where('payment_status', 'paid')
                ->where('total_amount', '>', 0)
                ->count(),
            'total_revenue' => Submission::where('form_id', $formId)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => SubmissionResource::collection($submissions),
            'statistics' => $stats,
            'meta' => [
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
                'per_page' => $submissions->perPage(),
                'total' => $submissions->total(),
            ],
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $formId = $request->query('form_id');

        $query = Submission::query();

        if ($formId) {
            $query->where('form_id', $formId);
        }

        $stats = [
            'total_submissions' => $query->count(),
            'payment_stats' => [
                'paid' => (clone $query)->where('payment_status', 'paid')->count(),
                'unpaid' => (clone $query)->where('payment_status', 'unpaid')->count(),
                'pending' => (clone $query)->where('payment_status', 'pending')->count(),
                'failed' => (clone $query)->where('payment_status', 'failed')->count(),
            ],
            'total_revenue' => (clone $query)->where('payment_status', 'paid')->sum('total_amount'),
            'affiliate_referrals' => (clone $query)->whereNotNull('affiliate_code')->count(),
            'total_commissions' => (clone $query)->sum('affiliate_commission'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
