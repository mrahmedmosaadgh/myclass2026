<?php

use App\Http\Controllers\Student\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Schedule Routes
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/schedule/current-week', [ScheduleController::class, 'currentWeek'])->name('schedule.current-week');
    Route::get('/schedule/next-week', [ScheduleController::class, 'nextWeek'])->name('schedule.next-week');

    // API endpoint for schedule data
    Route::get('/schedule/data', [ScheduleController::class, 'getScheduleData'])->name('schedule.data');

    // Optional: Print schedule route
    Route::get('/schedule/print', [ScheduleController::class, 'print'])->name('schedule.print');
});













