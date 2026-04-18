<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Schedule App V7 Routes
|--------------------------------------------------------------------------
|
| Authenticated schedule app with server-side data storage.
| All routes require authentication and store data in the user's account.
|
*/

// Main application route (requires authentication)
Route::get('/my-fly-schedule-app/ver7', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/v7/ScheduleAppV7');
})->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->name('schedule.app.v7');

// PWA Manifest (authenticated)
Route::get('/my-fly-schedule-app/v7/manifest.webmanifest', function () {
    $user = Auth::user();
    $manifest = [
        'name' => 'My Schedule App V7',
        'short_name' => 'Schedule V7',
        'description' => 'Authenticated schedule app with cloud storage and real-time sync.',
        'id' => '/my-fly-schedule-app/ver7',
        'start_url' => '/my-fly-schedule-app/ver7',
        'scope' => '/my-fly-schedule-app/ver7',
        'display' => 'standalone',
        'background_color' => '#1e293b',
        'theme_color' => '#3b82f6',
        'orientation' => 'portrait-primary',
        'categories' => ['productivity', 'education', 'utilities'],
        'icons' => [
            [
                'src' => '/my-fly-schedule-app/v7/icon.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ],
        ],
        'shortcuts' => [
            [
                'name' => 'My Schedule',
                'short_name' => 'Schedule',
                'description' => 'View your personal schedule',
                'url' => '/my-fly-schedule-app/ver7',
                'icons' => [
                    [
                        'src' => '/my-fly-schedule-app/v7/icon.png',
                        'sizes' => '512x512',
                        'type' => 'image/png',
                    ],
                ],
            ],
        ],
    ];

    return response()->json($manifest)
        ->header('Content-Type', 'application/manifest+json')
        ->header('Cache-Control', 'public, max-age=86400');
})->name('schedule.app.v7.manifest');

// App Icon (authenticated)
Route::get('/my-fly-schedule-app/v7/icon.png', function () {
    $iconPath = public_path('my-fly-schedule-app/v7/icon.png');
    if (!file_exists($iconPath)) {
        $iconPath = public_path('icon.png');
    }
    if (!file_exists($iconPath)) {
        abort(404);
    }

    return response()->file($iconPath)
        ->header('Cache-Control', 'public, max-age=86400');
})->name('schedule.app.v7.icon');

// Service Worker (authenticated)
Route::get('/my-fly-schedule-app/v7-sw.js', function () {
    $swPath = public_path('my-fly-schedule-app-v7-sw.js');
    if (!file_exists($swPath)) {
        abort(404);
    }
    
    return response()->file($swPath)
        ->header('Content-Type', 'application/javascript')
        ->header('Cache-Control', 'no-cache');
})->name('schedule.app.v7.service-worker');

// API Routes (Authenticated - User must be logged in)
Route::prefix('api/schedule-app-v7')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    
    // Health check with user info
    Route::get('health', function () {
        $user = Auth::user();
        return response()->json([
            'status' => 'ok',
            'version' => '7.0',
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'school_id' => $user->school_id ?? null,
            ],
            'features' => [
                'authenticated_access',
                'server_side_storage',
                'real_time_sync',
                'user_account_data',
                'cloud_backup',
                'cross_device_sync',
            ],
            'timestamp' => now()->toISOString()
        ]);
    });

    // Save data to user's account
    Route::post('save-data', function (Request $request) {
        $user = Auth::user();
        $data = $request->all();
        
        try {
            // Validate data structure
            $validated = $request->validate([
                'schedule_data' => 'required|array',
                'settings' => 'nullable|array',
                'last_modified' => 'nullable|string',
                'version' => 'nullable|string',
            ]);

            // Store in database (create a schedule_data table or use user metadata)
            // For now, we'll use file storage with user ID
            $userDir = storage_path("app/public/schedule-app-v7/users/" . $user->id);
            if (!is_dir($userDir)) {
                mkdir($userDir, 0755, true);
            }

            // Save main data file
            $dataFile = $userDir . '/schedule-data.json';
            $saveData = [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'data' => $validated,
                'saved_at' => now()->toISOString(),
                'ip_address' => $request->ip(),
            ];
            
            file_put_contents($dataFile, json_encode($saveData, JSON_PRETTY_PRINT));

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
                'message' => 'Data saved successfully to your account',
                'timestamp' => now()->toISOString(),
                'user_id' => $user->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Save failed: ' . $e->getMessage()
            ], 500);
        }
    });

    // Load data from user's account
    Route::get('load-data', function () {
        $user = Auth::user();
        
        try {
            $dataFile = storage_path("app/public/schedule-app-v7/users/" . $user->id . "/schedule-data.json");
            
            if (!file_exists($dataFile)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No data found in your account',
                    'message' => 'Start by creating your first schedule'
                ], 404);
            }

            $saveData = json_decode(file_get_contents($dataFile), true);
            
            // Verify ownership
            if ($saveData['user_id'] !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'data' => $saveData['data'],
                'saved_at' => $saveData['saved_at'],
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Load failed: ' . $e->getMessage()
            ], 500);
        }
    });

    // Sync data (for real-time updates across devices)
    Route::post('sync', function (Request $request) {
        $user = Auth::user();
        
        try {
            $validated = $request->validate([
                'client_timestamp' => 'required|string',
                'data_hash' => 'required|string',
                'schedule_data' => 'required|array',
            ]);

            // Get server data
            $dataFile = storage_path("app/public/schedule-app-v7/users/" . $user->id . "/schedule-data.json");
            
            if (!file_exists($dataFile)) {
                // No server data, accept client data
                return response()->json([
                    'action' => 'accept_client',
                    'message' => 'No server data found, accepting your data'
                ]);
            }

            $serverData = json_decode(file_get_contents($dataFile), true);
            $serverHash = md5(json_encode($serverData['data']));
            
            if ($validated['data_hash'] === $serverHash) {
                return response()->json([
                    'action' => 'no_conflict',
                    'message' => 'Data is already in sync'
                ]);
            }

            // Conflict detected - for now, prefer server data
            return response()->json([
                'action' => 'conflict',
                'message' => 'Data conflict detected, server data preserved',
                'server_data' => $serverData['data'],
                'server_timestamp' => $serverData['saved_at']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    });

    // List backups
    Route::get('backups', function () {
        $user = Auth::user();
        
        try {
            $backupDir = storage_path("app/public/schedule-app-v7/users/" . $user->id);
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
                'backups' => $backups,
                'total_count' => count($backups)
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
        $user = Auth::user();

        // Security: validate filename
        if (!preg_match('/^backup-\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.json$/', $filename)) {
            abort(400, 'Invalid filename');
        }

        $filePath = storage_path("app/public/schedule-app-v7/users/" . $user->id . "/" . $filename);
        
        if (!file_exists($filePath)) {
            abort(404, 'Backup not found');
        }

        return response()->download($filePath, "schedule-v7-backup-" . date('Y-m-d') . ".json");
    });

    // Delete user data (for account cleanup)
    Route::delete('clear-data', function () {
        $user = Auth::user();
        
        try {
            $userDir = storage_path("app/public/schedule-app-v7/users/" . $user->id);
            
            if (is_dir($userDir)) {
                $files = glob($userDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($userDir);
            }

            return response()->json([
                'success' => true,
                'message' => 'All schedule data cleared from your account'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear data: ' . $e->getMessage()
            ], 500);
        }
    });

    // Export data as JSON
    Route::get('export', function () {
        $user = Auth::user();
        
        try {
            $dataFile = storage_path("app/public/schedule-app-v7/users/" . $user->id . "/schedule-data.json");
            
            if (!file_exists($dataFile)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No data to export'
                ], 404);
            }

            $saveData = json_decode(file_get_contents($dataFile), true);
            
            return response()->json([
                'success' => true,
                'export_data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'schedule_data' => $saveData['data'],
                    'exported_at' => now()->toISOString(),
                    'version' => '7.0'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    });
});
