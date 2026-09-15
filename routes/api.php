<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\ClickController;
use App\Http\Controllers\Api\AnalyticsController;

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