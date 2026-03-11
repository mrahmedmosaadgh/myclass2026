<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V2 API Routes
|--------------------------------------------------------------------------
|
| API routes for V2, loaded by bootstrap/app.php with 'api' middleware group.
|
*/

Route::prefix('v2')->group(function () {
    // Public V2 APIs can go here
    
    // Authenticated V2 APIs
    Route::middleware(['auth:sanctum'])->group(function () {
        // BM2 Basic Math Platform Routes
        Route::prefix('bm2')->group(function () {
            // Assessment Routes
            Route::post('/assessment/start', [App\Http\Controllers\Bm2AssessmentController::class, 'start']);
            Route::post('/assessment/{assessmentId}/submit', [App\Http\Controllers\Bm2AssessmentController::class, 'submitAnswer']);
            Route::get('/assessment/{assessmentId}/next', [App\Http\Controllers\Bm2AssessmentController::class, 'getNextQuestion']);
            Route::post('/assessment/{assessmentId}/complete', [App\Http\Controllers\Bm2AssessmentController::class, 'complete']);
            Route::get('/assessment/{assessmentId}/results', [App\Http\Controllers\Bm2AssessmentController::class, 'getResults']);

            // Question Bank Routes (Teacher/Admin only)
            Route::apiResource('questions', App\Http\Controllers\Bm2QuestionController::class)->names([
                'index' => 'bm2.questions.index',
                'store' => 'bm2.questions.store',
                'show' => 'bm2.questions.show',
                'update' => 'bm2.questions.update',
                'destroy' => 'bm2.questions.destroy',
            ]);
            Route::post('/questions/random', [App\Http\Controllers\Bm2QuestionController::class, 'getRandom']);

            // Student Dashboard Routes
            Route::get('/student/dashboard', [App\Http\Controllers\Bm2StudentController::class, 'dashboard']);
            Route::get('/student/assessments', [App\Http\Controllers\Bm2StudentController::class, 'assessmentHistory']);
            Route::get('/student/learning-paths', [App\Http\Controllers\Bm2StudentController::class, 'learningPaths']);
            Route::get('/student/badges', [App\Http\Controllers\Bm2StudentController::class, 'badges']);
            Route::get('/student/assessment-results/{assessmentId}', [App\Http\Controllers\Bm2StudentController::class, 'assessmentResults']);
            Route::post('/student/learning-path/{pathId}/progress', [App\Http\Controllers\Bm2StudentController::class, 'updateLearningPathProgress']);
            Route::get('/student/statistics', [App\Http\Controllers\Bm2StudentController::class, 'statistics']);
        });
    });
});
