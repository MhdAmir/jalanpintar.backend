<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AffiliateController;
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

    // Verify affiliate code (public)
    Route::post('affiliates/verify', [AffiliateController::class, 'verify']);

    // Payment webhook (public - called by Xendit)
    Route::post('payments/webhook', [PaymentController::class, 'webhook']);

    // Get payment by external ID (for redirect pages)
    Route::get('payments/external/{externalId}', [PaymentController::class, 'getByExternalId']);
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

    // Affiliates - User routes
    Route::post('affiliates', [AffiliateController::class, 'store']);
    Route::get('affiliates/my/statistics', [AffiliateController::class, 'myStatistics']);
    Route::get('affiliates/{id}', [AffiliateController::class, 'show']);
    Route::put('affiliates/{id}', [AffiliateController::class, 'update']);
    Route::delete('affiliates/{id}', [AffiliateController::class, 'destroy']);
    Route::get('affiliates/{id}/statistics', [AffiliateController::class, 'statistics']);

    // Payments - User routes
    Route::post('submissions/{submissionId}/payment', [PaymentController::class, 'createInvoice']);
    Route::get('payments/{paymentId}', [PaymentController::class, 'show']);
});

// Admin only routes
Route::middleware(['jwt', 'admin'])->group(function () {

    // Admin - Manage all affiliates
    Route::get('admin/affiliates', [AffiliateController::class, 'index']);
    Route::get('admin/affiliates/pending', [AffiliateController::class, 'pendingAffiliates']);
    Route::post('admin/affiliates/{id}/approve', [AffiliateController::class, 'approve']);
    Route::post('admin/affiliates/{id}/reject', [AffiliateController::class, 'reject']);

    // Admin - Categories CRUD
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

    // Admin - Forms CRUD
    Route::apiResource('forms', FormController::class)->except(['index', 'show']);
    Route::post('forms/{id}/duplicate', [FormController::class, 'duplicate']);

    // Admin - Sections Management
    Route::post('sections/reorder', [FormController::class, 'reorderSections']);

    // Admin - Fields Management
    Route::post('fields/reorder', [FormController::class, 'reorderFields']);

    // Admin - Pricing Tiers Management
    Route::post('pricing-tiers/reorder', [FormController::class, 'reorderPricingTiers']);

    // Admin - Upsells Management
    Route::post('upsells/reorder', [FormController::class, 'reorderUpsells']);

    // Admin - Submissions Management
    Route::get('submissions', [SubmissionController::class, 'index']);
    Route::get('submissions/statistics', [SubmissionController::class, 'statistics']);
    Route::get('submissions/{id}', [SubmissionController::class, 'show']);
    Route::patch('submissions/{id}/payment-status', [SubmissionController::class, 'updatePaymentStatus']);

    // Admin - Get submissions by form ID
    Route::get('forms/{formId}/submissions', [SubmissionController::class, 'getByFormId']);

    // Admin - Announcements CRUD
    Route::apiResource('announcements', AnnouncementController::class);
    Route::post('announcements/import', [AnnouncementController::class, 'import']);
    Route::get('announcements/statistics', [AnnouncementController::class, 'statistics']);
});

// Public read-only routes (no auth required)
Route::middleware('jwt')->group(function () {
    // Categories - Public read
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);

    // Forms - Public read
    Route::get('forms', [FormController::class, 'index']);
    Route::get('forms/{id}', [FormController::class, 'show']);
});
