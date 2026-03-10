<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\PageView;

// QudratPro Domain Routes
// These routes are loaded via Route::domain('qudratpro.com') in web.php

Route::middleware(['web'])->group(function () {
    // Dedicated Landing Page
    Route::get('/', function () {
        // Get the view count for the landing page
        $viewCount = PageView::where('page_name', 'qudrat_landing_page')->count();
        
        return Inertia::render('Qudrat/LandingPage', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'viewCount' => $viewCount,
        ]);
    })->name('qudrat.landing');

    // Add specific QudratPro routes here
    // e.g., Pricing, Course Details, etc.
});