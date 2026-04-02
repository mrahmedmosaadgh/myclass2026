<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\HandleInertiaRequests;

/*
|--------------------------------------------------------------------------
| Schedule App V3 Routes
|--------------------------------------------------------------------------
|
| Routes for the advanced schedule application with timing settings,
| offline capabilities, and PWA installation features.
|
| Domain: https://qudratpro.com
| Base Path: /my-fly-schedule-app/v3
|
*/

// Main Schedule App V3 (Public, No-Auth, PWA)
Route::get('/my-fly-schedule-app/v3', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/v3/StandaloneScheduleAppV3');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->name('schedule.app.v3');

// PWA Manifest for V3
Route::get('/my-fly-schedule-app/v3/manifest.webmanifest', function () {
    $manifest = [
        'name' => 'My Schedule App V3',
        'short_name' => 'Schedule V3',
        'description' => 'Advanced schedule app with timing settings, offline capabilities, and PWA installation.',
        'id' => '/my-fly-schedule-app/v3',
        'start_url' => '/my-fly-schedule-app/v3',
        'scope' => '/my-fly-schedule-app/v3',
        'display' => 'standalone',
        'background_color' => '#0f172a',
        'theme_color' => '#1e293b',
        'orientation' => 'portrait-primary',
        'categories' => ['productivity', 'education', 'utilities'],
        'lang' => 'en',
        'dir' => 'ltr',
        'icons' => [
            [
                'src' => '/my-fly-schedule-app/v3/icon.svg',
                'sizes' => 'any',
                'type' => 'image/svg+xml',
                'purpose' => 'any maskable'
            ]
        ],
        'shortcuts' => [
            [
                'name' => 'Open Schedule V3',
                'short_name' => 'Schedule V3',
                'description' => 'View your advanced schedule with timing settings',
                'url' => '/my-fly-schedule-app/v3',
                'icons' => [
                    [
                        'src' => '/my-fly-schedule-app/v3/icon.svg',
                        'sizes' => 'any',
                        'type' => 'image/svg+xml'
                    ]
                ]
            ],
            [
                'name' => 'Timing Settings',
                'short_name' => 'Settings',
                'description' => 'Configure schedule timing settings',
                'url' => '/my-fly-schedule-app/v3#timing',
                'icons' => [
                    [
                        'src' => '/my-fly-schedule-app/v3/icon.svg',
                        'sizes' => 'any',
                        'type' => 'image/svg+xml'
                    ]
                ]
            ]
        ],
        'screenshots' => [
            [
                'src' => '/my-fly-schedule-app/v3/screenshots/main.png',
                'sizes' => '1280x720',
                'type' => 'image/png',
                'form_factor' => 'wide',
                'label' => 'Main schedule view'
            ],
            [
                'src' => '/my-fly-schedule-app/v3/screenshots/mobile.png',
                'sizes' => '375x812',
                'type' => 'image/png',
                'form_factor' => 'narrow',
                'label' => 'Mobile schedule view'
            ]
        ]
    ];

    return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 200)
        ->header('Content-Type', 'application/manifest+json');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v3.manifest');

// App Icon for V3
Route::get('/my-fly-schedule-app/v3/icon.svg', function () {
    $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="My Schedule V3">
  <rect width="512" height="512" rx="96" fill="#1e293b"/>
  <rect x="36" y="36" width="440" height="440" rx="72" fill="none" stroke="#3b82f6" stroke-width="12"/>
  
  <!-- Settings gear icon -->
  <circle cx="256" cy="180" r="48" fill="none" stroke="#60a5fa" stroke-width="8"/>
  <circle cx="256" cy="180" r="32" fill="none" stroke="#60a5fa" stroke-width="6"/>
  <circle cx="256" cy="180" r="16" fill="#60a5fa"/>
  
  <!-- Gear teeth -->
  <rect x="244" y="120" width="24" height="20" rx="4" fill="#60a5fa"/>
  <rect x="244" y="220" width="24" height="20" rx="4" fill="#60a5fa"/>
  <rect x="196" y="168" width="20" height="24" rx="4" fill="#60a5fa"/>
  <rect x="296" y="168" width="20" height="24" rx="4" fill="#60a5fa"/>
  
  <!-- Rotated gear teeth -->
  <rect x="210" y="134" width="20" height="24" rx="4" fill="#60a5fa" transform="rotate(45 220 146)"/>
  <rect x="282" y="134" width="20" height="24" rx="4" fill="#60a5fa" transform="rotate(45 292 146)"/>
  <rect x="210" y="202" width="20" height="24" rx="4" fill="#60a5fa" transform="rotate(45 220 214)"/>
  <rect x="282" y="202" width="20" height="24" rx="4" fill="#60a5fa" transform="rotate(45 292 214)"/>
  
  <!-- Schedule grid below -->
  <rect x="80" y="280" width="352" height="160" rx="8" fill="none" stroke="#60a5fa" stroke-width="6"/>
  <line x1="80" y1="310" x2="432" y2="310" stroke="#60a5fa" stroke-width="4"/>
  <line x1="80" y1="340" x2="432" y2="340" stroke="#60a5fa" stroke-width="4"/>
  <line x1="80" y1="370" x2="432" y2="370" stroke="#60a5fa" stroke-width="4"/>
  <line x1="80" y1="400" x2="432" y2="400" stroke="#60a5fa" stroke-width="4"/>
  
  <!-- Vertical lines -->
  <line x1="168" y1="280" x2="168" y2="440" stroke="#60a5fa" stroke-width="3" opacity="0.5"/>
  <line x1="256" y1="280" x2="256" y2="440" stroke="#60a5fa" stroke-width="3" opacity="0.5"/>
  <line x1="344" y1="280" x2="344" y2="440" stroke="#60a5fa" stroke-width="3" opacity="0.5"/>
  
  <!-- Version indicator dots -->
  <circle cx="120" cy="460" r="6" fill="#10b981"/>
  <circle cx="140" cy="460" r="6" fill="#10b981"/>
  <circle cx="160" cy="460" r="6" fill="#10b981"/>
  
  <!-- Status indicators -->
  <circle cx="400" cy="100" r="20" fill="#10b981" opacity="0.8"/>
  <circle cx="420" cy="120" r="12" fill="#f59e0b" opacity="0.8"/>
  <circle cx="100" cy="120" r="16" fill="#ef4444" opacity="0.8"/>
</svg>
SVG;

    return response($svg, 200)->header('Content-Type', 'image/svg+xml');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v3.icon');

// Service Worker for V3
Route::get('/my-fly-schedule-app/v3/sw.js', function () {
    $serviceWorkerPath = public_path('my-schedule-app/v3/sw.js');

    abort_unless(file_exists($serviceWorkerPath), 404);

    return response(file_get_contents($serviceWorkerPath), 200)
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/my-fly-schedule-app/v3');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v3.service-worker');

// Service Worker Alias for V3
Route::get('/my-fly-schedule-app-v3-sw.js', function () {
    $serviceWorkerPath = public_path('my-schedule-app/v3/sw.js');

    abort_unless(file_exists($serviceWorkerPath), 404);

    return response(file_get_contents($serviceWorkerPath), 200)
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/my-fly-schedule-app/v3');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v3.service-worker.alias');

// Test Route for V3 Development
Route::get('/my-fly-schedule-app/v3/test', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/v3/test_v3');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->name('schedule.app.v3.test');

// API Routes for V3 (Future Expansion)
Route::prefix('api/v3')->group(function () {
    // Timing Data API
    Route::get('/timing-data', function () {
        // Return default timing data
        return response()->json([
            'default' => [
                ['id' => 1, 'title' => 'Period 1', 'type' => 'lesson', 'start' => '09:00', 'end' => '09:30'],
                ['id' => 2, 'title' => 'Period 2', 'type' => 'lesson', 'start' => '09:30', 'end' => '10:00'],
                ['id' => 'b1', 'title' => 'First Break', 'type' => 'break', 'start' => '10:00', 'end' => '10:30'],
                ['id' => 3, 'title' => 'Period 3', 'type' => 'lesson', 'start' => '10:30', 'end' => '11:00'],
                ['id' => 4, 'title' => 'Period 4', 'type' => 'lesson', 'start' => '11:00', 'end' => '11:30'],
                ['id' => 'b2', 'title' => 'Second Break', 'type' => 'break', 'start' => '11:30', 'end' => '12:00'],
                ['id' => 5, 'title' => 'Period 5', 'type' => 'lesson', 'start' => '12:00', 'end' => '12:25'],
                ['id' => 6, 'title' => 'Period 6', 'type' => 'lesson', 'start' => '12:25', 'end' => '12:50']
            ],
            'overrides' => []
        ]);
    })->name('schedule.app.v3.api.timing-data');

    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'version' => '3.0',
            'timestamp' => now()->toISOString(),
            'features' => [
                'timing_settings' => true,
                'offline_support' => true,
                'pwa_installable' => true,
                'service_worker' => true
            ]
        ]);
    })->name('schedule.app.v3.api.health');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified']);

// Cache-busting Route for V3
Route::get('/my-fly-schedule-app/v3', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/v3/StandaloneScheduleAppV3');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->name('schedule.app.v3.refresh');
