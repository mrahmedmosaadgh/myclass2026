<?php

use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
    // Qu Exam Management API Routes
    Route::prefix('qu-exams')->group(function () {
        Route::get('/filter-options', [App\Http\Controllers\QuExamController::class, 'filterOptions']);
        Route::get('/', [App\Http\Controllers\QuExamController::class, 'index']);
        Route::post('/', [App\Http\Controllers\QuExamController::class, 'store']);
        Route::get('/{quExam}', [App\Http\Controllers\QuExamController::class, 'show']);
        Route::put('/{quExam}', [App\Http\Controllers\QuExamController::class, 'update']);
        Route::delete('/{quExam}', [App\Http\Controllers\QuExamController::class, 'destroy']);
        
        // Additional exam operations
        Route::post('/{quExam}/duplicate', [App\Http\Controllers\QuExamController::class, 'duplicate']);
        Route::get('/{quExam}/export', [App\Http\Controllers\QuExamController::class, 'export']);
        Route::get('/{quExam}/analytics', [App\Http\Controllers\QuExamController::class, 'analytics']);
        
        // Grading endpoints
        Route::get('/{quExam}/grading-attempts', [App\Http\Controllers\QuExamController::class, 'getGradingAttempts']);
        Route::get('/grading/{attempt}', [App\Http\Controllers\QuExamController::class, 'getAttemptGradingData']);
        Route::post('/grading/{attempt}/save-grades', [App\Http\Controllers\QuExamController::class, 'saveGrades']);
    });
});