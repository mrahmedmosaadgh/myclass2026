<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Laravel Cache Management Routes
|--------------------------------------------------------------------------
|
| These routes provide web-based access to common Laravel cache commands
| for development and maintenance purposes.
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('developer')->name('developer.')->group(function () {

    // Cache Manager UI Page
    Route::get('/cache-manager', function () {
        return view('cache_manager');
    })->name('cache-manager');

    // Artisan cache commands route
    Route::post('/run-cache-commands', function () {
        $commands = [
            'config:cache',
            'route:cache',
            'view:cache',
            'optimize',
        ];

        $results = [];

        foreach ($commands as $command) {
            try {
                Artisan::call($command);
                $results[$command] = '✅ Success';
            } catch (\Exception $e) {
                $results[$command] = '❌ Error: ' . $e->getMessage();
            }
        }

        return response()->json([
            'status' => 'completed',
            'results' => $results,
            'timestamp' => now()->toDateTimeString(),
        ]);
    })->name('run-cache-commands');

    // Individual cache clear commands
    Route::post('/clear-cache', function () {
        try {
            Artisan::call('cache:clear');
            return response()->json(['status' => 'success', 'message' => 'Application cache cleared']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    })->name('clear-cache');

    Route::post('/clear-config', function () {
        try {
            Artisan::call('config:clear');
            return response()->json(['status' => 'success', 'message' => 'Config cache cleared']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    })->name('clear-config');

    Route::post('/clear-route', function () {
        try {
            Artisan::call('route:clear');
            return response()->json(['status' => 'success', 'message' => 'Route cache cleared']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    })->name('clear-route');

    Route::post('/clear-view', function () {
        try {
            Artisan::call('view:clear');
            return response()->json(['status' => 'success', 'message' => 'View cache cleared']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    })->name('clear-view');

    // Run all clears
    Route::match(['get', 'post'], '/clear-all', function () {
        $commands = [
            'cache:clear',
            'config:clear',
            'route:clear',
            'view:clear',
        ];

        $results = [];

        foreach ($commands as $command) {
            try {
                Artisan::call($command);
                $results[$command] = '✅ Cleared';
            } catch (\Exception $e) {
                $results[$command] = '❌ Error: ' . $e->getMessage();
            }
        }

        return response()->json([
            'status' => 'completed',
            'results' => $results,
            'timestamp' => now()->toDateTimeString(),
        ]);
    })->name('clear-all-cache');
});
