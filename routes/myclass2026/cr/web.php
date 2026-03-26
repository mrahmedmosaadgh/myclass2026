<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyClass2026\Cr\ClassroomRecordsPageController;
use App\Http\Controllers\Api\Cr\CrSessionController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Classroom Records v1 Routes
|--------------------------------------------------------------------------
|
| All routes related to the Classroom Records system (Phase 1 & 2)
| 
| Features:
| - Student score tracking across categories (Book, Homework, Behavior)
| - Attendance management with absent lock behavior
| - Real-time auto-save functionality
| - Teacher and Admin role-based access
|
*/

// Wrap all CR routes in authentication middleware
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Main Page Route (Teacher Access)
    Route::get('/classroom-records', [ClassroomRecordsPageController::class, '__invoke'])
        ->name('classroom-records.index');

    // Presentation Builder Routes
    Route::prefix('classroom-records/presentation')->name('classroom-records.presentation.')->group(function () {
        // Main presentation builder page
        Route::get('/builder', function () {
            return Inertia::render('myclass2026/features/cr/classroom_records_v1/peresentation/PresentationBuilder');
        })->name('builder');
        
        // V2 Presentation Builder
        Route::get('/builder-v2', function () {
            return Inertia::render('myclass2026/features/cr/classroom_records_v1/peresentation/v2/PresentationBuilderV2');
        })->name('builder-v2');
        
        // V3 Presentation Builder
        Route::get('/builder-v3', function () {
            return Inertia::render(
                'myclass2026/features/cr/classroom_records_v1/peresentation/v3/PresentationBuilderV3',
                [
                    'title' => 'Presentation Builder V3 - MyClass2026'
                ]
            );
        })->name('builder-v3');
        
        // V4 Presentation Builder
        Route::get('/builder-v4', function () {
            return Inertia::render(
                'myclass2026/features/cr/classroom_records_v1/peresentation/v4/Index',
                [
                    'title' => 'Presentation Builder V4 - MyClass2026'
                ]
            );
        })->name('builder-v4');
        
        // V5 Presentation Builder
        Route::get('/builder-v5', function () {
            return Inertia::render(
                'myclass2026/features/cr/classroom_records_v1/peresentation/v5/Index',
                [
                    'title' => 'Presentation Builder V5 - MyClass2026'
                ]
            );
        })->name('builder-v5');
        
        // Slide editor
        Route::get('/slide-editor', function () {
            return Inertia::render('myclass2026/features/cr/classroom_records_v1/peresentation/SlideEditor');
        })->name('slide-editor');
        
        // Animation editor
        Route::get('/animation-editor', function () {
            return Inertia::render('myclass2026/features/cr/classroom_records_v1/peresentation/AnimationEditor');
        })->name('animation-editor');
        
        // Slide presenter view
        Route::get('/presenter', function () {
            return Inertia::render('myclass2026/features/cr/classroom_records_v1/peresentation/SlidePresenter');
        })->name('presenter');

        // Remote V5 routes
        Route::prefix('remote')->name('remote.')->group(function () {
            Route::get('/teacher', [\App\Http\Controllers\QuizSessionController::class, 'teacherRemote'])->name('teacher');
            Route::get('/student', [\App\Http\Controllers\QuizSessionController::class, 'studentJoin'])->name('student');
            Route::get('/test', function () {
                return Inertia::render('myclass2026/features/cr/classroom_records_v1/peresentation/v5/remote/RemoteDiagnostics', [
                    'title' => 'Remote Diagnostics - MyClass2026',
                ]);
            })->name('test');
        });
    });

    // API Routes for Session Management
    Route::prefix('api/cr')->name('cr.')->group(function () {
        // Initialize or load a classroom records session
        Route::post('/init-session', [CrSessionController::class, 'initSession'])->name('init-session');
        
        // Batch update student scores (Main CR)
        Route::patch('/batch', [CrSessionController::class, 'batchUpdate'])->name('batch');

        // V5 Remote Teacher Controls (Auth Required)
        Route::middleware(['auth:sanctum', 'verified'])->group(function() {
            Route::post('/sessions/{session}/sync-slide', [\App\Http\Controllers\QuizSessionController::class, 'syncSlide'])->name('sessions.sync-slide');
            Route::post('/sessions/{session}/launch-quiz', [\App\Http\Controllers\QuizSessionController::class, 'launchQuiz'])->name('sessions.launch-quiz');
            Route::get('/sessions/{session}/stats', [\App\Http\Controllers\QuizSessionController::class, 'getStats'])->name('sessions.stats');
            Route::post('/sessions/{session}/end', [\App\Http\Controllers\QuizSessionController::class, 'endSession'])->name('sessions.end');
            Route::post('/sessions/{session}/mark-answer', [\App\Http\Controllers\QuizSessionController::class, 'markAnswer'])->name('sessions.mark-answer');
            Route::post('/debug-firebase', [\App\Http\Controllers\QuizSessionController::class, 'debugFirebase'])->name('debug-firebase');
        });
    });
});

// Student & Public API Routes
Route::prefix('api/cr')->name('cr.')->group(function () {
    Route::post('/sessions/join', [\App\Http\Controllers\QuizSessionController::class, 'join'])->name('sessions.join');
    Route::post('/sessions/{session}/submit-answer', [\App\Http\Controllers\QuizSessionController::class, 'submitAnswer'])->name('sessions.submit-answer');
    Route::get('/questions/{question}', [\App\Http\Controllers\QuestionController::class, 'show'])->name('questions.show');
});
