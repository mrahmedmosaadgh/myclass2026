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
    });

    // API Routes for Session Management
    Route::prefix('api/cr')->name('cr.')->group(function () {
        // Initialize or load a classroom records session
        Route::post('/init-session', [CrSessionController::class, 'initSession'])
            ->name('init-session');
        
        // Batch update student scores
        Route::patch('/batch', [CrSessionController::class, 'batchUpdate'])
            ->name('batch');
    });
});
