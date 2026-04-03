<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Schedule App V5 Routes
|--------------------------------------------------------------------------
|
| Offline-first schedule app with IndexedDB storage and menu-driven settings.
| All routes are public (no auth required).
|
*/

// Main application route
Route::get('/my-fly-schedule-app/ver5', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/v5/ScheduleAppV5');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->name('schedule.app.v5');

// PWA Manifest
Route::get('/my-fly-schedule-app/v5/manifest.webmanifest', function () {
    $manifestPath = public_path('my-fly-schedule-app/v5/manifest.webmanifest');
    if (!file_exists($manifestPath)) {
        abort(404);
    }
    
    $manifest = json_decode(file_get_contents($manifestPath), true);
    return response()->json($manifest)
        ->header('Content-Type', 'application/manifest+json')
        ->header('Cache-Control', 'public, max-age=86400'); // Cache for 1 day
});

// App Icon
Route::get('/my-fly-schedule-app/v5/icon.png', function () {
    $iconPath = public_path('my-fly-schedule-app/v5/icon.png');
    if (!file_exists($iconPath)) {
        abort(404);
    }
    
    return response()->file($iconPath)
        ->header('Cache-Control', 'public, max-age=86400'); // Cache for 1 day
});

// Service Worker
Route::get('/my-fly-schedule-app/v5-sw.js', function () {
    $swPath = public_path('my-fly-schedule-app-v5-sw.js');
    if (!file_exists($swPath)) {
        abort(404);
    }
    
    return response()->file($swPath)
        ->header('Content-Type', 'application/javascript')
        ->header('Cache-Control', 'no-cache'); // Don't cache service worker
});

// API Routes (Public - No Auth Required)
Route::prefix('schedule-app-v5')->group(function () {
    
    // Health check
    Route::get('health', function () {
        return response()->json([
            'status' => 'ok',
            'version' => '5.0',
            'features' => [
                'offline_first',
                'indexeddb_storage',
                'cloud_sync',
                'pwa_installable',
                'menu_driven_settings',
                'view_only_main_app'
            ],
            'timestamp' => now()->toISOString()
        ]);
    });

    // Save data to server
    Route::post('save-data', function (Request $request) {
        $data = $request->all();
        $userId = $request->header('X-User-ID');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'error' => 'User ID required'
            ], 400);
        }

        try {
            // Create user directory if it doesn't exist
            $userDir = storage_path("app/public/schedule-app-v5/users/" . $userId);
            if (!is_dir($userDir)) {
                mkdir($userDir, 0755, true);
            }

            // Save main data file
            $dataFile = $userDir . '/schedule-data.json';
            file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

            // Create backup
            $backupFile = $userDir . '/backup-' . date('Y-m-d-H-i-s') . '.json';
            copy($dataFile, $backupFile);

            // Clean old backups (keep only last 10)
            $backupFiles = glob($userDir . '/backup-*.json');
            if (count($backupFiles) > 10) {
                rsort($backupFiles);
                $toDelete = array_slice($backupFiles, 10);
                foreach ($toDelete as $file) {
                    unlink($file);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data saved successfully',
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Save failed: ' . $e->getMessage()
            ], 500);
        }
    });

    // Load data from server
    Route::get('load-data', function (Request $request) {
        $userId = $request->header('X-User-ID');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'error' => 'User ID required'
            ], 400);
        }

        try {
            $dataFile = storage_path("app/public/schedule-app-v5/users/" . $userId . "/schedule-data.json");
            
            if (!file_exists($dataFile)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No data found'
                ], 404);
            }

            $data = json_decode(file_get_contents($dataFile), true);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Load failed: ' . $e->getMessage()
            ], 500);
        }
    });

    // List backups
    Route::get('backups', function (Request $request) {
        $userId = $request->header('X-User-ID');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'error' => 'User ID required'
            ], 400);
        }

        try {
            $backupDir = storage_path("app/public/schedule-app-v5/users/" . $userId);
            if (!is_dir($backupDir)) {
                return response()->json([
                    'success' => true,
                    'backups' => []
                ]);
            }

            $backupFiles = glob($backupDir . '/backup-*.json');
            $backups = [];
            
            foreach ($backupFiles as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'modified' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }

            rsort($backups); // Most recent first

            return response()->json([
                'success' => true,
                'backups' => $backups
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to list backups: ' . $e->getMessage()
            ], 500);
        }
    });

    // Download backup
    Route::get('download-backup/{filename}', function (Request $request, $filename) {
        $userId = $request->header('X-User-ID');
        
        if (!$userId) {
            abort(400, 'User ID required');
        }

        // Security: validate filename
        if (!preg_match('/^backup-\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.json$/', $filename)) {
            abort(400, 'Invalid filename');
        }

        $filePath = storage_path("app/public/schedule-app-v5/users/" . $userId . "/" . $filename);
        
        if (!file_exists($filePath)) {
            abort(404, 'Backup not found');
        }

        return response()->download($filePath, "schedule-v5-backup-" . date('Y-m-d') . ".json");
    });
});
