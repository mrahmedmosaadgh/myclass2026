<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyClass2026\Cr\ClassroomRecordsPageController;
use App\Http\Controllers\Api\Cr\CrSessionController;

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
