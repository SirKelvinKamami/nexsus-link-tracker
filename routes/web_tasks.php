/*
|--------------------------------------------------------------------------
| Task Scheduler Web Routes (Phase 4.0+)
|--------------------------------------------------------------------------
|
| These routes extend the existing web.php routes within the auth middleware.
| Add these routes inside the existing auth middleware group in routes/web.php.
|
| Usage: Add these route definitions inside the existing auth group in routes/web.php
|
| // Task Scheduler Routes
| Route::get('/studio/tasks', [TaskController::class, 'index'])->name('tasks.index');
| Route::post('/studio/tasks', [TaskController::class, 'store'])->name('tasks.store');
| Route::put('/studio/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
| Route::delete('/studio/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
| Route::post('/studio/tasks/{id}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
|
*/
