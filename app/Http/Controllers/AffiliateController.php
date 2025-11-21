<?php

namespace App\Http\Controllers;

use App\Http\Resources\AffiliateRewardResource;
use App\Models\AffiliateReward;
use App\Models\Form;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function leaderboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id' => 'nullable|uuid|exists:forms,id',
            'metric' => 'nullable|in:total_earned,total_referrals',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        [$formId, $metric, $perPage, $metricColumn] = $this->resolveLeaderboardParams($validated);

        $baseAggregateQuery = $this->buildAggregateQuery($formId);

        $leaderboardPaginator = $this->paginateLeaderboard(
            clone $baseAggregateQuery,
            $metricColumn,
            $perPage
        );

        $leaderboardPaginator->setCollection(
            $this->formatLeaderboardItems($leaderboardPaginator)
        );

        [$myRank, $mySummary] = $this->calculateUserRankAndSummary(
            $request->user(),
            clone $baseAggregateQuery,
            $formId,
            $metric
        );

        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboardPaginator->items(),
                'my_rank' => $myRank,
                'my_summary' => $mySummary,
            ],
            'meta' => [
                'current_page' => $leaderboardPaginator->currentPage(),
                'last_page' => $leaderboardPaginator->lastPage(),
                'per_page' => $leaderboardPaginator->perPage(),
                'total_participants' => $leaderboardPaginator->total(),
            ],
        ]);
    }

    private function resolveLeaderboardParams(array $validated): array
    {
        $formId = $validated['form_id'] ?? null;
        $metric = $validated['metric'] ?? 'total_earned';
        $perPage = max(1, min((int) ($validated['per_page'] ?? 15), 100));
        $metricColumn = $metric === 'total_referrals' ? 'total_referrals_sum' : 'total_earned_sum';

        return [$formId, $metric, $perPage, $metricColumn];
    }

    private function buildAggregateQuery(?string $formId)
    {
        $query = DB::table('affiliate_rewards')
            ->selectRaw('user_id, SUM(total_earned) as total_earned_sum, SUM(total_referrals) as total_referrals_sum, COUNT(*) as affiliate_count')
            ->where('is_active', true)
            ->where('status', 'approved');

        if ($formId) {
            $query->where('form_id', $formId);
        }

        return $query->groupBy('user_id');
    }

    private function paginateLeaderboard($aggregateQuery, string $metricColumn, int $perPage)
    {
        return DB::query()
            ->fromSub($aggregateQuery, 'aggregates')
            ->join('users', 'users.id', '=', 'aggregates.user_id')
            ->select('aggregates.*', 'users.name as user_name', 'users.email as user_email', 'users.id as user_id')
            ->orderByDesc($metricColumn)
            ->orderByDesc('aggregates.total_earned_sum')
            ->orderByDesc('aggregates.total_referrals_sum')
            ->orderBy('users.name')
            ->paginate($perPage);
    }

    private function formatLeaderboardItems($paginator)
    {
        $startRank = ($paginator->currentPage() - 1) * $paginator->perPage();

        return collect($paginator->items())->map(function ($item, $index) use ($startRank) {
            return [
                'rank' => $startRank + $index + 1,
                'user' => [
                    'id' => $item->user_id,
                    'name' => $item->user_name,
                    'email' => $item->user_email,
                ],
                'total_earned' => number_format((float) $item->total_earned_sum, 2, '.', ''),
                'total_referrals' => (int) $item->total_referrals_sum,
                'affiliate_count' => (int) $item->affiliate_count,
            ];
        });
    }

    private function calculateUserRankAndSummary($user, $aggregateQuery, ?string $formId, string $metric): array
    {
        if (!$user) {
            return [null, null];
        }

        $myStatsQuery = DB::table('affiliate_rewards')
            ->selectRaw('COALESCE(SUM(total_earned), 0) as total_earned_sum, COALESCE(SUM(total_referrals), 0) as total_referrals_sum, COUNT(*) as affiliate_count')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('status', 'approved');

        if ($formId) {
            $myStatsQuery->where('form_id', $formId);
        }

        $myStats = $myStatsQuery->first();

        if (!$myStats || $myStats->affiliate_count === 0) {
            return [null, null];
        }

        $mySummary = [
            'total_earned' => number_format((float) $myStats->total_earned_sum, 2, '.', ''),
            'total_referrals' => (int) $myStats->total_referrals_sum,
            'affiliate_count' => (int) $myStats->affiliate_count,
        ];

        $comparisonColumn = $metric === 'total_referrals' ? 'total_referrals_sum' : 'total_earned_sum';
        $comparisonValue = $metric === 'total_referrals'
            ? (int) $myStats->total_referrals_sum
            : (float) $myStats->total_earned_sum;

        $tieValue = $metric === 'total_referrals'
            ? (float) $myStats->total_earned_sum
            : (int) $myStats->total_referrals_sum;

        $rankQuery = DB::query()->fromSub($aggregateQuery, 'aggregates');

        $betterCount = $rankQuery
            ->where(function ($query) use ($comparisonColumn, $comparisonValue, $tieValue, $metric, $user) {
                $query->where($comparisonColumn, '>', $comparisonValue)
                    ->orWhere(function ($tieQuery) use ($comparisonColumn, $comparisonValue, $tieValue, $metric, $user) {
                        $tieQuery->where($comparisonColumn, '=', $comparisonValue)
                            ->where(function ($secondaryTie) use ($metric, $tieValue, $user) {
                                if ($metric === 'total_referrals') {
                                    $secondaryTie->where('total_earned_sum', '>', $tieValue)
                                        ->orWhere(function ($finalTie) use ($tieValue, $user) {
                                            $finalTie->where('total_earned_sum', '=', $tieValue)
                                                ->where('user_id', '<', $user->id);
                                        });
                                } else {
                                    $secondaryTie->where('total_referrals_sum', '>', $tieValue)
                                        ->orWhere(function ($finalTie) use ($tieValue, $user) {
                                            $finalTie->where('total_referrals_sum', '=', $tieValue)
                                                ->where('user_id', '<', $user->id);
                                        });
                                }
                            });
                    });
            })
            ->count();

        return [$betterCount + 1, $mySummary];
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
