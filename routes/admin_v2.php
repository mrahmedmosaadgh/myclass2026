<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminV2;

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
        // Placeholders until controllers are created
        Route::get('/dashboard', function() { return 'SuperSystem Dashboard'; })->name('dashboard');
        Route::get('/config', function() { return 'Configuration'; })->name('config');
        Route::get('/jobs', function() { return 'Jobs Monitor'; })->name('jobs');
        Route::get('/logs', function() { return 'Logs Viewer'; })->name('logs');
    });

// 2. SystemAdmin (Platform Management)
Route::prefix('v2/system-admin')
    ->middleware(['auth', 'role:SystemAdmin'])
    ->name('v2.system-admin.')
    ->group(function () {
        Route::get('/dashboard', function() { return 'SystemAdmin Dashboard'; })->name('dashboard');
        // Schools, Users, Roles resources will go here
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
