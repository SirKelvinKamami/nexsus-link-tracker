<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\ClickController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\LandingPageController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes — Nexsus Link Tracker
|--------------------------------------------------------------------------
|
| All routes require Bearer token auth (API_TOKEN in .env).
| Base URL: /api/v1
|
*/

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
});