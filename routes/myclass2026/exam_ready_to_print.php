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

    // V2 test builder page
    Route::get('/test-builder-v2', function () {
        return Inertia::render('myclass2026/features/Exam/ReadyToPrint_ver2/Builder_test');
    })->name('test-builder-v2');
    
    // API routes for user-specific data storage
    Route::prefix('api')->group(function () {
        // Load user-specific questions and settings
        Route::get('/load-data', function () {
            $userId = auth()->id() ?? session()->getId();
            $filePath = public_path("data/exam-ready-to-print-v2-{$userId}.json");
            
            if (!file_exists($filePath)) {
                // Return default structure if file doesn't exist
                return response()->json([
                    'questions' => [],
                    'settings' => [
                        'examTitle' => '',
                        'printHeader' => [
                            'enabled' => false,
                            'autoFit' => true,
                            'mode' => 'html',
                            'height' => 60,
                            'extraMarginBottom' => 0,
                            'html' => '',
                            'imageUrl' => '',
                            'imageFit' => 'contain'
                        ],
                        'printFooter' => [
                            'enabled' => false,
                            'autoFit' => true,
                            'mode' => 'html',
                            'height' => 40,
                            'extraMarginBottom' => 0,
                            'html' => '',
                            'imageUrl' => '',
                            'imageFit' => 'contain'
                        ]
                    ]
                ]);
            }
            
            $data = json_decode(file_get_contents($filePath), true);
            return response()->json($data);
        })->name('api.load-data');
        
        // Save user-specific questions and settings
        Route::post('/save-data', function () {
            $userId = auth()->id() ?? session()->getId();
            $filePath = public_path("data/exam-ready-to-print-v2-{$userId}.json");
            
            // Ensure directory exists
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $data = request()->json()->all();
            file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
            
            return response()->json(['success' => true]);
        })->name('api.save-data');
    });
    
});
