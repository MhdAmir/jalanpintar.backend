<?php

namespace App\Http\Controllers;

use App\Http\Resources\AffiliateRewardResource;
use App\Models\AffiliateReward;
use App\Models\Form;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AffiliateReward::with(['form', 'user']);

        // Filter by form
        if ($request->has('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by code
        if ($request->has('search')) {
            $query->where('affiliate_code', 'like', '%' . $request->search . '%');
        }

        $affiliates = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => AffiliateRewardResource::collection($affiliates),
            'meta' => [
                'current_page' => $affiliates->currentPage(),
                'last_page' => $affiliates->lastPage(),
                'per_page' => $affiliates->perPage(),
                'total' => $affiliates->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id' => 'required|uuid|exists:forms,id',
            'user_id' => 'required|uuid|exists:users,id',
            'affiliate_code' => 'nullable|string|max:50',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        // Generate unique code if not provided
        if (empty($validated['affiliate_code'])) {
            $validated['affiliate_code'] = $this->generateUniqueCode($validated['form_id']);
        }

        // Check if code already exists for this form
        $exists = AffiliateReward::where('form_id', $validated['form_id'])
            ->where('affiliate_code', $validated['affiliate_code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Affiliate code already exists for this form',
            ], 422);
        }

        // Set default status to pending
        $validated['status'] = 'pending';

        $affiliate = AffiliateReward::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate created successfully. Waiting for admin approval.',
            'data' => new AffiliateRewardResource($affiliate->load(['form', 'user'])),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $affiliate = AffiliateReward::with(['form', 'user', 'submissions'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AffiliateRewardResource($affiliate),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $affiliate = AffiliateReward::findOrFail($id);

        $validated = $request->validate([
            'commission_type' => 'sometimes|in:percentage,fixed',
            'commission_value' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $affiliate->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate updated successfully',
            'data' => new AffiliateRewardResource($affiliate->load(['form', 'user'])),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $affiliate = AffiliateReward::findOrFail($id);
        $affiliate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Affiliate deleted successfully',
        ]);
    }

    public function statistics(string $id): JsonResponse
    {
        $affiliate = AffiliateReward::findOrFail($id);

        $submissions = Submission::where('affiliate_reward_id', $id)->get();

        $stats = [
            'affiliate_code' => $affiliate->affiliate_code,
            'commission_type' => $affiliate->commission_type,
            'commission_value' => $affiliate->commission_value,
            'total_referrals' => $affiliate->total_referrals,
            'total_earned' => $affiliate->total_earned,
            'pending_earned' => $submissions->where('payment_status', 'pending')->sum('affiliate_amount'),
            'paid_earned' => $submissions->where('payment_status', 'paid')->sum('affiliate_amount'),
            'failed_earned' => $submissions->where('payment_status', 'failed')->sum('affiliate_amount'),
            'conversion_rate' => $affiliate->total_referrals > 0
                ? ($submissions->where('payment_status', 'paid')->count() / $affiliate->total_referrals * 100)
                : 0,
            'recent_submissions' => $submissions->sortByDesc('created_at')->take(10)->values(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function myStatistics(Request $request): JsonResponse
    {
        $user = $request->user();

        $affiliates = AffiliateReward::where('user_id', $user->id)
            ->with(['form', 'submissions'])
            ->get();

        $totalEarned = $affiliates->sum('total_earned');
        $totalReferrals = $affiliates->sum('total_referrals');

        $allSubmissions = collect();
        foreach ($affiliates as $affiliate) {
            $allSubmissions = $allSubmissions->merge($affiliate->submissions);
        }

        $stats = [
            'total_affiliates' => $affiliates->count(),
            'total_earned' => $totalEarned,
            'total_referrals' => $totalReferrals,
            'pending_earned' => $allSubmissions->where('payment_status', 'pending')->sum('affiliate_amount'),
            'paid_earned' => $allSubmissions->where('payment_status', 'paid')->sum('affiliate_amount'),
            'affiliates' => $affiliates->map(function ($affiliate) {
                return [
                    'id' => $affiliate->id,
                    'affiliate_code' => $affiliate->affiliate_code,
                    'form' => [
                        'id' => $affiliate->form->id,
                        'title' => $affiliate->form->title,
                    ],
                    'total_earned' => $affiliate->total_earned,
                    'total_referrals' => $affiliate->total_referrals,
                    'is_active' => $affiliate->is_active,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'affiliate_code' => 'required|string',
            'form_id' => 'required|uuid|exists:forms,id',
        ]);

        $affiliate = AffiliateReward::where('affiliate_code', $request->affiliate_code)
            ->where('form_id', $request->form_id)
            ->where('is_active', true)
            ->where('status', 'approved')
            ->with(['user', 'form'])
            ->first();

        if (!$affiliate) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid affiliate code',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'affiliate_code' => $affiliate->affiliate_code,
                'affiliate_name' => $affiliate->user->name ?? 'Partner',
                'commission_type' => $affiliate->commission_type,
                'commission_value' => $affiliate->commission_value,
                'form' => [
                    'id' => $affiliate->form->id,
                    'title' => $affiliate->form->title,
                ],
            ],
        ]);
    }

    public function approve(string $id): JsonResponse
    {
        $affiliate = AffiliateReward::findOrFail($id);

        if ($affiliate->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Affiliate already approved',
            ], 422);
        }

        $affiliate->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate approved successfully',
            'data' => new AffiliateRewardResource($affiliate->load(['form', 'user', 'approvedBy'])),
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $affiliate = AffiliateReward::findOrFail($id);

        if ($affiliate->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Affiliate already rejected',
            ], 422);
        }

        $affiliate->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => auth()->id(),
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate rejected successfully',
            'data' => new AffiliateRewardResource($affiliate->load(['form', 'user', 'approvedBy'])),
        ]);
    }

    public function pendingAffiliates(): JsonResponse
    {
        $affiliates = AffiliateReward::with(['form', 'user'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => AffiliateRewardResource::collection($affiliates),
        ]);
    }

    private function generateUniqueCode(string $formId): string
    {
        do {
            $code = strtoupper(Str::random(8));
            $exists = AffiliateReward::where('form_id', $formId)
                ->where('affiliate_code', $code)
                ->exists();
        } while ($exists);

        return $code;
    }
}
