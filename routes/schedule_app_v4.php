<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\HandleInertiaRequests;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Schedule App V4 Routes
|--------------------------------------------------------------------------
|
| Routes for the advanced schedule application with offline data persistence,
| user folder auto-save, and enhanced PWA features.
|
| Domain: https://qudratpro.com
| Base Path: /my-fly-schedule-app/v4
|
*/

// Main Schedule App V4 (Public, No-Auth, PWA with Offline Auto-Save)
Route::get('/my-fly-schedule-app/v4', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/v4/StandaloneScheduleAppV4');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->name('schedule.app.v4');

// PWA Manifest for V4
Route::get('/my-fly-schedule-app/v4/manifest.webmanifest', function () {
    $manifest = [
        'name' => 'My Schedule App V4',
        'short_name' => 'Schedule V4',
        'description' => 'Advanced schedule app with offline auto-save, user folder persistence, and enhanced PWA features.',
        'id' => '/my-fly-schedule-app/v4',
        'start_url' => '/my-fly-schedule-app/v4',
        'scope' => '/my-fly-schedule-app/v4',
        'display' => 'standalone',
        'background_color' => '#0f172a',
        'theme_color' => '#1e293b',
        'orientation' => 'portrait-primary',
        'categories' => ['productivity', 'education', 'utilities'],
        'lang' => 'en',
        'dir' => 'ltr',
        'icons' => [
            [
                'src' => '/my-fly-schedule-app/v4/icon.svg',
                'sizes' => 'any',
                'type' => 'image/svg+xml',
                'purpose' => 'any maskable'
            ]
        ],
        'shortcuts' => [
            [
                'name' => 'Open Schedule V4',
                'short_name' => 'Schedule V4',
                'description' => 'View your schedule with offline auto-save',
                'url' => '/my-fly-schedule-app/v4',
                'icons' => [
                    [
                        'src' => '/my-fly-schedule-app/v4/icon.svg',
                        'sizes' => 'any',
                        'type' => 'image/svg+xml'
                    ]
                ]
            ],
            [
                'name' => 'Data Manager',
                'short_name' => 'Data',
                'description' => 'Manage your offline data and backups',
                'url' => '/my-fly-schedule-app/v4#data',
                'icons' => [
                    [
                        'src' => '/my-fly-schedule-app/v4/icon.svg',
                        'sizes' => 'any',
                        'type' => 'image/svg+xml'
                    ]
                ]
            ]
        ],
        'screenshots' => [
            [
                'src' => '/my-fly-schedule-app/v4/screenshots/main.png',
                'sizes' => '1280x720',
                'type' => 'image/png',
                'form_factor' => 'wide',
                'label' => 'Main schedule view with offline features'
            ],
            [
                'src' => '/my-fly-schedule-app/v4/screenshots/mobile.png',
                'sizes' => '375x812',
                'type' => 'image/png',
                'form_factor' => 'narrow',
                'label' => 'Mobile schedule with auto-save indicator'
            ]
        ]
    ];

    return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 200)
        ->header('Content-Type', 'application/manifest+json');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v4.manifest');

// App Icon for V4
Route::get('/my-fly-schedule-app/v4/icon.svg', function () {
    $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="My Schedule V4">
  <rect width="512" height="512" rx="96" fill="#1e293b"/>
  <rect x="36" y="36" width="440" height="440" rx="72" fill="none" stroke="#3b82f6" stroke-width="12"/>
  
  <!-- Save/floppy disk icon for V4 -->
  <rect x="180" y="140" width="152" height="120" rx="8" fill="none" stroke="#60a5fa" stroke-width="8"/>
  <rect x="200" y="160" width="112" height="80" rx="4" fill="#60a5fa" opacity="0.3"/>
  <circle cx="256" cy="200" r="16" fill="#1e293b"/>
  <rect x="210" y="210" width="92" height="8" rx="2" fill="#1e293b"/>
  
  <!-- Save label -->
  <rect x="220" y="120" width="72" height="20" rx="4" fill="#60a5fa"/>
  <rect x="240" y="125" width="32" height="10" rx="2" fill="#1e293b"/>
  
  <!-- Auto-save indicator dots -->
  <circle cx="220" cy="280" r="6" fill="#10b981"/>
  <circle cx="256" cy="280" r="6" fill="#10b981"/>
  <circle cx="292" cy="280" r="6" fill="#10b981"/>
  
  <!-- Schedule grid below -->
  <rect x="80" y="320" width="352" height="120" rx="8" fill="none" stroke="#60a5fa" stroke-width="6"/>
  <line x1="80" y1="350" x2="432" y2="350" stroke="#60a5fa" stroke-width="4"/>
  <line x1="80" y1="380" x2="432" y2="380" stroke="#60a5fa" stroke-width="4"/>
  <line x1="80" y1="410" x2="432" y2="410" stroke="#60a5fa" stroke-width="4"/>
  
  <!-- Vertical lines -->
  <line x1="168" y1="320" x2="168" y2="440" stroke="#60a5fa" stroke-width="3" opacity="0.5"/>
  <line x1="256" y1="320" x2="256" y2="440" stroke="#60a5fa" stroke-width="3" opacity="0.5"/>
  <line x1="344" y1="320" x2="344" y2="440" stroke="#60a5fa" stroke-width="3" opacity="0.5"/>
  
  <!-- Version indicator -->
  <text x="256" y="470" text-anchor="middle" fill="#10b981" font-family="monospace" font-size="20" font-weight="bold">V4</text>
  
  <!-- Offline sync indicators -->
  <circle cx="100" cy="100" r="16" fill="#10b981" opacity="0.8"/>
  <path d="M 92 100 L 98 106 L 108 94" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
  
  <circle cx="412" cy="100" r="16" fill="#f59e0b" opacity="0.8"/>
  <path d="M 404 100 L 420 100 M 412 92 L 412 108" stroke="white" stroke-width="3" stroke-linecap="round"/>
</svg>
SVG;

    return response($svg, 200)->header('Content-Type', 'image/svg+xml');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v4.icon');

// Service Worker for V4
Route::get('/my-fly-schedule-app/v4/sw.js', function () {
    $serviceWorkerPath = public_path('my-schedule-app/v4/sw.js');

    abort_unless(file_exists($serviceWorkerPath), 404);

    return response(file_get_contents($serviceWorkerPath), 200)
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/my-fly-schedule-app/v4');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v4.service-worker');

// Service Worker Alias for V4
Route::get('/my-fly-schedule-app-v4-sw.js', function () {
    $serviceWorkerPath = public_path('my-schedule-app/v4/sw.js');

    abort_unless(file_exists($serviceWorkerPath), 404);

    return response(file_get_contents($serviceWorkerPath), 200)
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/my-fly-schedule-app/v4');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v4.service-worker.alias');

// API Routes for V4 - Enhanced with offline data management
Route::prefix('api/v4')->group(function () {
    // User Data Storage API
    Route::post('/save-data', function (\Illuminate\Http\Request $request) {
        try {
            $data = $request->all();
            $userId = $request->header('X-User-ID', 'anonymous');
            $timestamp = now()->toISOString();
            
            // Create user-specific data directory
            $userDir = storage_path("app/public/schedule-app-v4/users/{$userId}");
            if (!is_dir($userDir)) {
                mkdir($userDir, 0755, true);
            }
            
            // Save main data file
            $dataFile = "{$userDir}/schedule-data.json";
            file_put_contents($dataFile, json_encode([
                'data' => $data,
                'timestamp' => $timestamp,
                'version' => '4.0'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // Create backup with timestamp
            $backupFile = "{$userDir}/backup-" . date('Y-m-d-H-i-s') . ".json";
            copy($dataFile, $backupFile);
            
            // Keep only last 10 backups
            $backups = glob("{$userDir}/backup-*.json");
            rsort($backups);
            if (count($backups) > 10) {
                array_map('unlink', array_slice($backups, 10));
            }
            
            return response()->json([
                'success' => true,
                'timestamp' => $timestamp,
                'backups_count' => count(glob("{$userDir}/backup-*.json"))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    })->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'web'])->name('schedule.app.v4.api.save-data');
    
    // Load User Data API
    Route::get('/load-data', function (\Illuminate\Http\Request $request) {
        try {
            $userId = $request->header('X-User-ID', 'anonymous');
            $dataFile = storage_path("app/public/schedule-app-v4/users/{$userId}/schedule-data.json");
            
            if (file_exists($dataFile)) {
                $data = json_decode(file_get_contents($dataFile), true);
                return response()->json([
                    'success' => true,
                    'data' => $data['data'] ?? [],
                    'timestamp' => $data['timestamp'] ?? null,
                    'version' => $data['version'] ?? 'unknown'
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'timestamp' => null,
                    'message' => 'No existing data found'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    })->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'web'])->name('schedule.app.v4.api.load-data');
    
    // List Backups API
    Route::get('/backups', function (\Illuminate\Http\Request $request) {
        try {
            $userId = $request->header('X-User-ID', 'anonymous');
            $backupDir = storage_path("app/public/schedule-app-v4/users/{$userId}");
            
            if (!is_dir($backupDir)) {
                return response()->json(['success' => true, 'backups' => []]);
            }
            
            $backups = glob("{$backupDir}/backup-*.json");
            $backupList = [];
            
            foreach ($backups as $backup) {
                $filename = basename($backup);
                $timestamp = preg_replace('/backup-(.+)\.json/', '$1', $filename);
                $backupList[] = [
                    'filename' => $filename,
                    'timestamp' => $timestamp,
                    'size' => filesize($backup),
                    'url' => "/api/v4/download-backup/{$filename}"
                ];
            }
            
            rsort($backupList);
            
            return response()->json([
                'success' => true,
                'backups' => $backupList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    })->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'web'])->name('schedule.app.v4.api.backups');
    
    // Download Backup API
    Route::get('/download-backup/{filename}', function ($filename) {
        try {
            $userId = request()->header('X-User-ID', 'anonymous');
            $backupPath = storage_path("app/public/schedule-app-v4/users/{$userId}/{$filename}");
            
            abort_unless(file_exists($backupPath), 404);
            abort_unless(str_starts_with($filename, 'backup-') && str_ends_with($filename, '.json'), 400);
            
            return response()->download($backupPath);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    })->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'web'])->name('schedule.app.v4.api.download-backup');

    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'version' => '4.0',
            'timestamp' => now()->toISOString(),
            'features' => [
                'offline_auto_save' => true,
                'user_folder_storage' => true,
                'backup_management' => true,
                'pwa_installable' => true,
                'service_worker' => true
            ]
        ]);
    })->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'web'])->name('schedule.app.v4.api.health');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified']);

// Public Menu API for Schedule App V4
Route::get('/api/menu', function (\Illuminate\Http\Request $request) {
    try {
        // Return a minimal menu structure for the schedule app
        return response()->json([
            'success' => true,
            'menu' => [
                [
                    'name' => 'Schedule App V4',
                    'icon' => 'calendar',
                    'url' => '/my-fly-schedule-app/v4',
                    'active' => true
                ],
                [
                    'name' => 'Data Manager',
                    'icon' => 'database',
                    'url' => '/my-fly-schedule-app/v4#data-manager'
                ],
                [
                    'name' => 'Settings',
                    'icon' => 'cog',
                    'url' => '/my-fly-schedule-app/v4#settings'
                ]
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'web'])->name('schedule.app.v4.api.menu');

// Cache-busting Route for V4
Route::get('/my-fly-schedule-app/v4', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/v4/StandaloneScheduleAppV4');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->name('schedule.app.v4.refresh');
