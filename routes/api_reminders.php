<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\ScheduleRuleController;

/*
|--------------------------------------------------------------------------
| API Routes — Task Scheduler (Phase 4.0+)
|--------------------------------------------------------------------------
|
| All routes require Bearer token auth (API_TOKEN in .env).
| Base URL: /api/v1
| Extends existing /api/v1 routes in routes/api.php
|
*/

// Task Reminders (Phase 4.3)
Route::prefix('v1')->middleware('api.token')->group(function () {
    Route::prefix('reminders')->group(function () {
        Route::get('/', [ReminderController::class, 'index']);
        Route::post('/', [ReminderController::class, 'store']);
        Route::post('/{id}/send', [ReminderController::class, 'send']);
        Route::post('/{id}/respond', [ReminderController::class, 'respond']);
    });

    // Schedule Rules (Phase 4.2)
    Route::prefix('scheduler')->group(function () {
        Route::get('/rules', [ScheduleRuleController::class, 'index']);
        Route::post('/rules', [ScheduleRuleController::class, 'store']);
        Route::put('/rules/{id}', [ScheduleRuleController::class, 'update']);
    });
});
