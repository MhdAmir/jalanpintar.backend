<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormRequest;
use App\Http\Requests\UpdateFormRequest;
use App\Http\Resources\FormResource;
use App\Models\Form;
use App\Services\FormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function __construct(
        private FormService $formService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Form::with(['category', 'sections.fields'])
            ->withCount('submissions');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $forms = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => FormResource::collection($forms),
            'meta' => [
                'current_page' => $forms->currentPage(),
                'last_page' => $forms->lastPage(),
                'per_page' => $forms->perPage(),
                'total' => $forms->total(),
            ],
        ]);
    }

    public function store(StoreFormRequest $request): JsonResponse
    {
        $form = $this->formService->createFormWithSections($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Form created successfully',
            'data' => new FormResource($form),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $form = Form::with([
            'category',
            'sections.fields',
            'pricingTiers',
            'upsells',
            'affiliateRewards'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new FormResource($form),
        ]);
    }

    public function update(UpdateFormRequest $request, string $id): JsonResponse
    {
        $form = Form::findOrFail($id);
        $form = $this->formService->updateFormWithSections($form, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Form updated successfully',
            'data' => new FormResource($form),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $form = Form::findOrFail($id);
        $form->delete();

        return response()->json([
            'success' => true,
            'message' => 'Form deleted successfully',
        ]);
    }

    public function duplicate(string $id): JsonResponse
    {
        $newForm = $this->formService->duplicateForm($id);

        return response()->json([
            'success' => true,
            'message' => 'Form duplicated successfully',
            'data' => new FormResource($newForm),
        ], 201);
    }

    public function getBySlug(string $slug): JsonResponse
    {
        $form = $this->formService->getPublicForm($slug);

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found or inactive',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new FormResource($form),
        ]);
    }

    public function getAdminBySlug(string $slug): JsonResponse
    {
        $form = $this->formService->getAdminForm($slug);

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found or inactive',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new FormResource($form),
        ]);
    }

    /**
     * Reorder sections within a form
     * POST /api/sections/reorder
     * Body: { "items": [{"id": "uuid", "order": 1}, {"id": "uuid", "order": 2}] }
     */
    public function reorderSections(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|uuid|exists:sections,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->items as $item) {
            \App\Models\Section::where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sections reordered successfully',
        ]);
    }

    /**
     * Reorder fields within a section
     * POST /api/fields/reorder
     * Body: { "items": [{"id": "uuid", "order": 1}, {"id": "uuid", "order": 2}] }
     */
    public function reorderFields(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|uuid|exists:fields,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->items as $item) {
            \App\Models\Field::where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fields reordered successfully',
        ]);
    }

    /**
     * Reorder pricing tiers within a form
     * POST /api/pricing-tiers/reorder
     * Body: { "items": [{"id": "uuid", "order": 1}, {"id": "uuid", "order": 2}] }
     */
    public function reorderPricingTiers(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|uuid|exists:pricing_tiers,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->items as $item) {
            \App\Models\PricingTier::where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pricing tiers reordered successfully',
        ]);
    }

    /**
     * Reorder upsells within a form
     * POST /api/upsells/reorder
     * Body: { "items": [{"id": "uuid", "order": 1}, {"id": "uuid", "order": 2}] }
     */
    public function reorderUpsells(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|uuid|exists:upsells,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->items as $item) {
            \App\Models\Upsell::where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Upsells reordered successfully',
        ]);
    }

    /**
     * Get all forms with user submission status for authenticated user
     */
    public function getUserForms(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Form::with(['category', 'sections.fields'])
            ->where('is_active', true)
            ->withCount('submissions');

        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by title if provided
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $forms = $query->get();

        // Get user's submissions for these forms
        $userSubmissions = \App\Models\Submission::where('email', $user->email)
            ->whereIn('form_id', $forms->pluck('id'))
            ->get()
            ->keyBy('form_id');

        // Map forms with submission status
        $formsWithStatus = $forms->map(function ($form) use ($userSubmissions) {
            $userSubmission = $userSubmissions->get($form->id);

            return [
                'id' => $form->id,
                'title' => $form->title,
                'slug' => $form->slug,
                'description' => $form->description,
                'category' => $form->category,
                'sections' => $form->sections,
                'submissions_count' => $form->submissions_count,
                'enable_payment' => $form->enable_payment,
                'user_has_submitted' => $userSubmission !== null,
                'user_submission_status' => $userSubmission?->status,
                'user_submission_id' => $userSubmission?->id,
                'user_submitted_at' => $userSubmission?->created_at,
                'created_at' => $form->created_at,
                'updated_at' => $form->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formsWithStatus,
            'total' => $formsWithStatus->count(),
        ]);
    }
}
