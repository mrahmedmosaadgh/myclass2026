<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DpMasterScheduleController;
use App\Http\Controllers\DpDailyPlannerController;
use App\Http\Controllers\DpFocusController;
use App\Http\Controllers\DpGamificationController;
use App\Http\Controllers\DpReportController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Master Schedule
    Route::get('/master-schedule', [DpMasterScheduleController::class, 'index'])->name('dp.master.index');
    Route::post('/master-schedule', [DpMasterScheduleController::class, 'store'])->name('dp.master.store');
    Route::put('/master-schedule/{dpTask}', [DpMasterScheduleController::class, 'update'])->name('dp.master.update');
    Route::delete('/master-schedule/{dpTask}', [DpMasterScheduleController::class, 'destroy'])->name('dp.master.destroy');

    // Daily Planner
    Route::get('/daily-planner', [DpDailyPlannerController::class, 'index'])->name('dp.daily.index');
    Route::put('/daily-planner/{dpDailyTask}', [DpDailyPlannerController::class, 'update'])->name('dp.daily.update');

    // Focus
    Route::get('/live-focus', [DpFocusController::class, 'index'])->name('dp.focus.index');
    Route::post('/live-focus', [DpFocusController::class, 'store'])->name('dp.focus.store');
    Route::put('/live-focus/{dpFocusLog}', [DpFocusController::class, 'update'])->name('dp.focus.update');
    Route::post('/live-focus/{dpFocusLog}/distraction', [DpFocusController::class, 'logDistraction'])->name('dp.focus.distraction');

    // Gamification
    Route::get('/gamification', [DpGamificationController::class, 'index'])->name('dp.gamification.index');
    Route::post('/gamification', [DpGamificationController::class, 'store'])->name('dp.gamification.store');

    // Reports
    Route::get('/reports', [DpReportController::class, 'index'])->name('dp.reports.index');
});
