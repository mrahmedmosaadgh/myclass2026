<?php

use App\Http\Controllers\LessonController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Lesson management UI
    Route::get('/curriculum/{curriculumId}/lessons', [LessonController::class, 'manage'])
        ->name('lessons.manage');

    // API endpoints for lessons
    Route::prefix('api')->group(function () {
        Route::get('/curriculum/{curriculumId}/lessons', [LessonController::class, 'index'])
            ->name('api.lessons.index');
        Route::post('/lessons', [LessonController::class, 'store'])
            ->name('api.lessons.store');
        Route::put('/lessons/{lesson}', [LessonController::class, 'update'])
            ->name('api.lessons.update');
        Route::patch('/lessons/{lesson}/lesson-number', [LessonController::class, 'updateLessonNumber'])
            ->name('api.lessons.update-lesson-number');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])
            ->name('api.lessons.destroy');
        Route::post('/lessons/reorder', [LessonController::class, 'reorder'])
            ->name('api.lessons.reorder');
    });
});
