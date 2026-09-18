<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\ClickController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\LandingPageController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MetricsController;

/*
|--------------------------------------------------------------------------
| API Routes — Nexsus Link Tracker
|--------------------------------------------------------------------------
|
| All routes require Bearer token auth (API_TOKEN in .env).
| Base URL: /api/v1
|
*/

// Health checks (no auth required)
Route::prefix('v1')->group(function () {
    Route::get('/health', [HealthController::class, 'check']);
    Route::get('/health/live', [HealthController::class, 'live']);
    Route::get('/health/ready', [HealthController::class, 'ready']);
});

Route::prefix('v1')->middleware('api.token')->group(function () {

    // Links
    Route::get('/links', [LinkController::class, 'index']);
    Route::get('/links/{id}', [LinkController::class, 'show']);

    // Click events
    Route::get('/clicks', [ClickController::class, 'index']);

    // Documents
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::get('/documents/{id}/stats', [DocumentController::class, 'stats']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);

    // Forms
    Route::get('/forms', [FormController::class, 'index']);
    Route::get('/forms/{id}', [FormController::class, 'show']);
    Route::get('/forms/{id}/responses', [FormController::class, 'responses']);
    Route::get('/forms/{id}/stats', [FormController::class, 'stats']);

    // Landing Pages
    Route::get('/landing-pages', [LandingPageController::class, 'index']);
    Route::get('/landing-pages/{id}', [LandingPageController::class, 'show']);
    Route::get('/landing-pages/{id}/stats', [LandingPageController::class, 'stats']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::get('/projects/{id}/stats', [ProjectController::class, 'stats']);

    // Webhooks
    Route::get('/webhooks', [WebhookController::class, 'index']);
    Route::get('/webhooks/{id}', [WebhookController::class, 'show']);
    Route::post('/webhooks', [WebhookController::class, 'store']);
    Route::put('/webhooks/{id}', [WebhookController::class, 'update']);
    Route::delete('/webhooks/{id}', [WebhookController::class, 'destroy']);
    Route::post('/webhooks/{id}/test', [WebhookController::class, 'test']);

    // API Tokens
    Route::get('/tokens', [TokenController::class, 'index']);
    Route::post('/tokens', [TokenController::class, 'store']);
    Route::delete('/tokens/{id}', [TokenController::class, 'destroy']);
    Route::post('/tokens/{id}/revoke', [TokenController::class, 'revoke']);
    Route::post('/tokens/{id}/activate', [TokenController::class, 'activate']);

    // Metrics (requires auth)
    Route::get('/metrics', [MetricsController::class, 'index']);

    // Export
    Route::get('/export/clicks', [AnalyticsController::class, 'exportClicks']);
    Route::get('/export/links', [AnalyticsController::class, 'exportLinks']);
    Route::get('/export/forms', [FormController::class, 'exportAll']);

    // Analytics
    Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
    Route::get('/analytics/top-links', [AnalyticsController::class, 'topLinks']);
    Route::get('/analytics/by-device', [AnalyticsController::class, 'byDevice']);
    Route::get('/analytics/by-browser', [AnalyticsController::class, 'byBrowser']);
    Route::get('/analytics/by-os', [AnalyticsController::class, 'byOs']);
    Route::get('/analytics/by-referrer', [AnalyticsController::class, 'byReferrer']);
    Route::get('/analytics/by-utm', [AnalyticsController::class, 'byUtm']);
    Route::get('/analytics/daily', [AnalyticsController::class, 'daily']);

    // Page views
    Route::get('/page-views', [AnalyticsController::class, 'pageViews']);

    // Tasks (Phase 4.0 + 4.1 + 4.2)
    Route::prefix('tasks')->group(function () {
        // Task CRUD (Phase 4.0)
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);

        // AI Parse (Phase 4.1) — before /{id}
        Route::post('/parse', [TaskController::class, 'parse']);

        // Smart Scheduling (Phase 4.2) — before /{id}
        Route::get('/schedule', [TaskController::class, 'schedule']);
        Route::get('/daily-digest', [TaskController::class, 'dailyDigest']);
        Route::post('/recalculate', [TaskController::class, 'recalculate']);
        Route::get('/rules', [TaskController::class, 'getRules']);
        Route::post('/rules', [TaskController::class, 'createRule']);

        // Task Analytics (Phase 4.4)
        Route::prefix('analytics')->group(function () {
            Route::get('/overview', [TaskController::class, 'analyticsOverview']);
            Route::get('/completion-trend', [TaskController::class, 'completionTrend']);
            Route::get('/priority-distribution', [TaskController::class, 'priorityDistribution']);
            Route::get('/productivity-score', [TaskController::class, 'productivityScore']);
            Route::get('/top-completed', [TaskController::class, 'topCompleted']);
        });

        // Task detail (must be last for GET to avoid catching /daily-digest, /schedule)
        Route::get('/{id}', [TaskController::class, 'show']);
        Route::put('/{id}', [TaskController::class, 'update']);
        Route::delete('/{id}', [TaskController::class, 'destroy']);
        Route::post('/{id}/complete', [TaskController::class, 'complete']);
        Route::post('/{id}/schedule', [TaskController::class, 'scheduleTask']);
    });
});
