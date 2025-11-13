<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Form Builder API v1.0']);
});

// Public routes - Form submissions
Route::prefix('public')->group(function () {
    // Get form by slug (for public submission)
    Route::get('forms/{slug}', [FormController::class, 'getBySlug']);

    // Submit form (public)
    Route::post('submissions', [SubmissionController::class, 'store']);

    // Check announcement status (public)
    Route::post('announcements/check', [AnnouncementController::class, 'publicCheck']);
});

// Authentication routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);

// Protected routes (require JWT authentication)
Route::middleware('jwt')->group(function () {

    // Auth user routes
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user', [AuthController::class, 'updateUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Categories CRUD
    Route::apiResource('categories', CategoryController::class);

    // Forms CRUD
    Route::apiResource('forms', FormController::class);
    Route::post('forms/{id}/duplicate', [FormController::class, 'duplicate']);

    // Sections Management
    Route::post('sections/reorder', [FormController::class, 'reorderSections']);

    // Fields Management
    Route::post('fields/reorder', [FormController::class, 'reorderFields']);

    // Pricing Tiers Management
    Route::post('pricing-tiers/reorder', [FormController::class, 'reorderPricingTiers']);

    // Upsells Management
    Route::post('upsells/reorder', [FormController::class, 'reorderUpsells']);

    // Submissions Management
    Route::get('submissions', [SubmissionController::class, 'index']);
    Route::get('submissions/statistics', [SubmissionController::class, 'statistics']);
    Route::get('submissions/{id}', [SubmissionController::class, 'show']);
    Route::patch('submissions/{id}/payment-status', [SubmissionController::class, 'updatePaymentStatus']);

    // Announcements CRUD
    Route::apiResource('announcements', AnnouncementController::class);
    Route::post('announcements/import', [AnnouncementController::class, 'import']);
    Route::get('announcements/statistics', [AnnouncementController::class, 'statistics']);
});
