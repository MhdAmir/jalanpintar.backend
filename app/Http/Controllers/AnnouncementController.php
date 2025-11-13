<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportAnnouncementRequest;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(
        private AnnouncementService $announcementService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with(['form', 'submission']);

        // Filter by form
        if ($request->has('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $announcements = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => AnnouncementResource::collection($announcements),
            'meta' => [
                'current_page' => $announcements->currentPage(),
                'last_page' => $announcements->lastPage(),
                'per_page' => $announcements->perPage(),
                'total' => $announcements->total(),
            ],
        ]);
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $announcement = Announcement::create(array_merge(
            $request->validated(),
            ['announced_at' => now()]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Announcement created successfully',
            'data' => new AnnouncementResource($announcement),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $announcement = Announcement::with(['form', 'submission'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AnnouncementResource($announcement),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'identifier' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'status' => 'sometimes|required|in:lolos,tidak_lolos,pending',
            'notes' => 'nullable|string',
            'result_data' => 'nullable|array',
        ]);

        $announcement = Announcement::findOrFail($id);
        $announcement->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully',
            'data' => new AnnouncementResource($announcement),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully',
        ]);
    }

    public function import(ImportAnnouncementRequest $request): JsonResponse
    {
        $result = $this->announcementService->importFromCsv(
            $request->validated('form_id'),
            $request->file('file')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] ? 'Import completed successfully' : 'Import failed',
            'data' => [
                'imported' => $result['imported'],
                'errors' => $result['errors'],
            ],
        ], $result['success'] ? 200 : 422);
    }

    public function publicCheck(Request $request): JsonResponse
    {
        $request->validate([
            'form_slug' => 'required|string',
            'identifier' => 'required|string',
        ]);

        $announcement = Announcement::whereHas('form', function ($query) use ($request) {
            $query->where('slug', $request->form_slug);
        })
            ->where('identifier', $request->identifier)
            ->first();

        if (!$announcement) {
            return response()->json([
                'success' => false,
                'message' => 'No announcement found for this phone number',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AnnouncementResource($announcement),
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $formId = $request->query('form_id');

        if (!$formId) {
            return response()->json([
                'success' => false,
                'message' => 'form_id is required',
            ], 422);
        }

        $stats = $this->announcementService->getStatistics($formId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
