<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Exam Ready To Print Routes
|--------------------------------------------------------------------------
|
| Routes for the exam builder and print-ready workflow.
|
*/

Route::prefix('exam/ready-to-print')->name('exam.ready-to-print.')->group(function () {
    
    // Main Ready-To-Print builder page
    Route::get('/builder', function () {
        return Inertia::render('myclass2026/features/Exam/ReadyToPrint/TestBuilder');
    })->name('builder');
    
    // Test builder page for question display testing
    Route::get('/test-builder', function () {
        return Inertia::render('myclass2026/features/Exam/ReadyToPrint/Builder_tetst');
    })->name('test-builder');
    
});
