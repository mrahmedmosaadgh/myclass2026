<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminV2;
use App\Http\Controllers\AdminV2\SuperSystem\DashboardController;
use App\Http\Controllers\AdminV2\SuperSystem\ConfigController;
use App\Http\Controllers\AdminV2\SuperSystem\JobsController;
use App\Http\Controllers\AdminV2\SuperSystem\LogsController;

/*
|--------------------------------------------------------------------------
| V2 Admin Routes
|--------------------------------------------------------------------------
|
| Role-based routes for the V2 system architecture.
| These are loaded by bootstrap/app.php with the 'web' middleware group.
|
*/

// 1. SuperSystem (Developer Tools)
Route::prefix('v2/super-system')
    ->middleware(['auth', 'role:SuperSystem'])
    ->name('v2.super-system.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Configuration Management
        Route::get('/config', [ConfigController::class, 'index'])->name('config');
        Route::post('/config/clear-cache', [ConfigController::class, 'clearCache'])->name('config.clear-cache');
        Route::post('/config/cache', [ConfigController::class, 'cacheConfig'])->name('config.cache');
        Route::post('/config/maintenance', [ConfigController::class, 'toggleMaintenance'])->name('config.maintenance');
        
        // Jobs Monitor
        Route::get('/jobs', [JobsController::class, 'index'])->name('jobs');
        Route::post('/jobs/{id}/retry', [JobsController::class, 'retry'])->name('jobs.retry');
        Route::post('/jobs/retry-all', [JobsController::class, 'retryAll'])->name('jobs.retry-all');
        Route::delete('/jobs/{id}', [JobsController::class, 'forget'])->name('jobs.forget');
        Route::delete('/jobs/flush', [JobsController::class, 'flush'])->name('jobs.flush');
        
        // Logs Viewer
        Route::get('/logs', [LogsController::class, 'index'])->name('logs');
        Route::get('/logs/download', [LogsController::class, 'download'])->name('logs.download');
        Route::post('/logs/clear', [LogsController::class, 'clear'])->name('logs.clear');
    });

// 2. SystemAdmin (Platform Management)
Route::prefix('v2/system-admin')
    ->middleware(['auth', 'role:admin|SystemAdmin|super_admin'])
    ->name('v2.system-admin.')
    ->group(function () {
        Route::get('/dashboard', function() { return 'SystemAdmin Dashboard'; })->name('dashboard');
        
        Route::prefix('schools')->name('schools.')->group(function () {
             Route::get('/', function() { return 'Schools Index'; })->name('index');
        });

        Route::prefix('users')->name('users.')->group(function () {
             Route::get('/', function() { return 'Users Index'; })->name('index');
        });
    });

// 3. SchoolAdmin (School Management)
Route::prefix('v2/school/{school_slug}/{school_id}/admin')
    ->middleware(['auth', 'role:SchoolAdmin', 'school.context.v2'])
    ->name('v2.school-admin.')
    ->group(function () {
        Route::get('/dashboard', function() { return 'SchoolAdmin Dashboard'; })->name('dashboard');
        
        // Modules
        Route::prefix('academics')->name('academics.')->group(function () {
             Route::get('/', function() { return 'Academics Index'; })->name('index');
        });
        
        Route::prefix('people')->name('people.')->group(function () {
             Route::get('/', function() { return 'People Index'; })->name('index');
             Route::get('/teachers', function() { return 'Teachers Index'; })->name('teachers.index');
             Route::get('/students', function() { return 'Students Index'; })->name('students.index');
        });


    });

// 4. Teacher
Route::prefix('v2/teacher')
    ->middleware(['auth', 'role:Teacher'])
    ->name('v2.teacher.')
    ->group(function () {
        Route::get('/dashboard', function() { return 'Teacher Dashboard'; })->name('dashboard');
    });

// 5. Student
Route::prefix('v2/student')
    ->middleware(['auth', 'role:Student'])
    ->name('v2.student.')
    ->group(function () {
        Route::get('/dashboard', function() { return 'Student Dashboard'; })->name('dashboard');
    });

// 6. Parent
Route::prefix('v2/parent')
    ->middleware(['auth', 'role:Parent'])
    ->name('v2.parent.')
    ->group(function () {
        Route::get('/dashboard', function() { return 'Parent Dashboard'; })->name('dashboard');
    });
