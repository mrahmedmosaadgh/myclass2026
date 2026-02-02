<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// QudratPro Domain Routes
// These routes are loaded via Route::domain('qudratpro.com') in web.php

Route::middleware(['web'])->group(function () {
    // Dedicated Landing Page
    Route::get('/', function () {
        return Inertia::render('Qudrat/LandingPage', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    })->name('qudrat.landing');

    // Add specific QudratPro routes here
    // e.g., Pricing, Course Details, etc.
});
