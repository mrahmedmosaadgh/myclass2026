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
    
    // V3 builder page with login requirement
    Route::get('/builder-v3', function () {
        return Inertia::render('myclass2026/features/Exam/ReadyToPrint_ver3/Builder');
    })->middleware(['auth'])->name('builder-v3');
    
    // V3 test builder page with login requirement
    Route::get('/test-builder-v3', function () {
        return Inertia::render('myclass2026/features/Exam/ReadyToPrint_ver3/Builder_test');
    })->middleware(['auth'])->name('test-builder-v3');

    // V4 main page with login requirement
    Route::get('/builder-v4', function () {
        return Inertia::render('myclass2026/features/Exam/ReadyToPrint_ver4/Main');
    })->middleware(['auth'])->name('builder-v4');

    // V4 test builder page with login requirement (alias to the main v4 page)
    Route::get('/test-builder-v4', function () {
        return Inertia::render('myclass2026/features/Exam/ReadyToPrint_ver4/Main');
    })->middleware(['auth'])->name('test-builder-v4');
    
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
                        'examTitle' => [
                            'enabled' => true,
                            'text' => 'Math Questions Test'
                        ],
                        'showMarksPerQuestion' => true,
                        'printHeader' => [
                            'enabled' => true,
                            'autoFit' => true,
                            'heightPt' => 120,
                            'pageMarginTopMm' => 0,
                            'mode' => 'html',
                            'templateId' => 'custom',
                            'html' => '',
                            'imageUrl' => '',
                            'imageFit' => 'contain',
                            'template1' => [
                                'schoolName' => 'AL-MUTAQADIMAH SCHOOLS (Al-Tadamon International School)',
                                'period' => 'first Academic period 2025 - 2026',
                                'grade' => '4',
                                'subject' => 'Math',
                                'examType' => 'V1',
                                'gender' => 'Boys'
                            ]
                        ],
                        'printFooter' => [
                            'enabled' => true,
                            'autoFit' => true,
                            'heightPt' => 90,
                            'pageMarginBottomMm' => 0,
                            'mode' => 'html',
                            'html' => '',
                            'imageUrl' => '',
                            'imageFit' => 'contain',
                            'textFontSizePt' => 12,
                            'textColor' => '#000000',
                            'reserveSpace' => true,
                            'singleLine' => false,
                            'showTopBorder' => false,
                            'showPageNumbers' => true,
                            'pageNumberPosition' => 'bottom-center',
                            'pageNumberFormat' => 'page',
                            'pageNumberFontSize' => 10,
                            'pageNumberColor' => '#000000'
                        ],
                        'firstPage' => [
                            'enabled' => true,
                            'type' => 'title',
                            'title' => '',
                            'subtitle' => '',
                            'titleAlignment' => 'center',
                            'coverTitle' => '',
                            'coverDescription' => '',
                            'coverImage' => '',
                            'customContent' => '',
                            'skipPageNumber' => true,
                            'pageBreakAfter' => true
                        ],
                        'lastPage' => [
                            'enabled' => true,
                            'type' => 'message',
                            'title' => 'End of Exam',
                            'message' => 'Thank you for completing the exam.',
                            'alignment' => 'center',
                            'showTotalMarks' => false,
                            'showCompletionTime' => false,
                            'customContent' => '',
                            'skipPageNumber' => false,
                            'pageBreakBefore' => true
                        ],
                        'questionSeparator' => [
                            'enabled' => true,
                            'lineStyle' => 'solid',
                            'color' => '#1f3a5a',
                            'thicknessPt' => 1,
                            'spaceBeforePt' => 8,
                            'spaceAfterPt' => 12
                        ],
                        'mcqOptions' => [
                            'columns' => 1,
                            'optionGapPt' => 6,
                            'labelGapPt' => 8,
                            'labelStyle' => 'letter',
                            'customLabelTemplate' => '{letter})',
                            'checkboxStyle' => 'box',
                            'checkboxShowLabel' => false,
                            'checkboxLabelType' => 'letter',
                            'labelFontSizePt' => 0,
                            'optionFontSizePt' => 0,
                            'labelBold' => false,
                            'optionBold' => false
                        ],
                        'sectionTotal' => [
                            'template' => 'text',
                            'prefix' => 'Total:',
                            'suffix' => 'marks',
                            'placement' => 'normal',
                            'offsetXPt' => 0,
                            'offsetYPt' => 0,
                            'boxTopHeightPt' => 22
                        ],
                        'questionNumbering' => [
                            'style' => 'question',
                            'startAt' => 1,
                            'prefix' => '',
                            'suffix' => '',
                            'customTemplate' => '{n}',
                            'inlineWithText' => false,
                            'inlineGap' => 8,
                            'pageBreaksBefore' => []
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
        
        // Load user-specific questions and settings for V3
        Route::get('/load-data-v3', function () {
            $userId = auth()->id(); // Require authentication
            $filePath = public_path("data/exam-ready-to-print-v3-{$userId}.json");
            
            if (!file_exists($filePath)) {
                // Return default structure if file doesn't exist
                return response()->json([
                    'questions' => [],
                    'settings' => [
                        'examTitle' => [
                            'enabled' => true,
                            'text' => 'Math Questions Test'
                        ],
                        'showMarksPerQuestion' => true,
                        'printHeader' => [
                            'enabled' => true,
                            'autoFit' => true,
                            'heightPt' => 120,
                            'pageMarginTopMm' => 0,
                            'mode' => 'html',
                            'templateId' => 'custom',
                            'html' => '',
                            'imageUrl' => '',
                            'imageFit' => 'contain',
                            'template1' => [
                                'schoolName' => 'AL-MUTAQADIMAH SCHOOLS (Al-Tadamon International School)',
                                'period' => 'first Academic period 2025 - 2026',
                                'grade' => '4',
                                'subject' => 'Math',
                                'examType' => 'V1',
                                'gender' => 'Boys'
                            ]
                        ],
                        'printFooter' => [
                            'enabled' => true,
                            'autoFit' => true,
                            'heightPt' => 90,
                            'pageMarginBottomMm' => 0,
                            'bottomOffsetMm' => 0,
                            'mode' => 'html',
                            'html' => '',
                            'imageUrl' => '',
                            'imageFit' => 'contain',
                            'textFontSizePt' => 12,
                            'textColor' => '#000000',
                            'reserveSpace' => true,
                            'singleLine' => false,
                            'showTopBorder' => false,
                            'showPageNumbers' => true,
                            'pageNumberPosition' => 'bottom-center',
                            'pageNumberFormat' => 'page',
                            'pageNumberFontSize' => 10,
                            'pageNumberColor' => '#000000',
                            'applyOffsetToPageNumbers' => false
                        ],
                        'firstPage' => [
                            'enabled' => true,
                            'type' => 'title',
                            'title' => '',
                            'subtitle' => '',
                            'titleAlignment' => 'center',
                            'coverTitle' => '',
                            'coverDescription' => '',
                            'coverImage' => '',
                            'customContent' => '',
                            'skipPageNumber' => true,
                            'pageBreakAfter' => true
                        ],
                        'lastPage' => [
                            'enabled' => true,
                            'type' => 'message',
                            'title' => 'End of Exam',
                            'message' => 'Thank you for completing the exam.',
                            'alignment' => 'center',
                            'showTotalMarks' => false,
                            'showCompletionTime' => false,
                            'customContent' => '',
                            'skipPageNumber' => false,
                            'pageBreakBefore' => true
                        ],
                        'questionSeparator' => [
                            'enabled' => true,
                            'lineStyle' => 'solid',
                            'color' => '#1f3a5a',
                            'thicknessPt' => 1,
                            'spaceBeforePt' => 8,
                            'spaceAfterPt' => 12
                        ],
                        'mcqOptions' => [
                            'columns' => 1,
                            'optionGapPt' => 6,
                            'labelGapPt' => 8,
                            'labelStyle' => 'letter',
                            'customLabelTemplate' => '{letter})',
                            'checkboxStyle' => 'box',
                            'checkboxShowLabel' => false,
                            'checkboxLabelType' => 'letter',
                            'labelFontSizePt' => 0,
                            'optionFontSizePt' => 0,
                            'labelBold' => false,
                            'optionBold' => false
                        ],
                        'sectionTotal' => [
                            'template' => 'text',
                            'prefix' => 'Total:',
                            'suffix' => 'marks',
                            'placement' => 'normal',
                            'offsetXPt' => 0,
                            'offsetYPt' => 0,
                            'boxTopHeightPt' => 22
                        ],
                        'questionNumbering' => [
                            'style' => 'question',
                            'startAt' => 1,
                            'prefix' => '',
                            'suffix' => '',
                            'customTemplate' => '{n}',
                            'inlineWithText' => false,
                            'inlineGap' => 8,
                            'pageBreaksBefore' => []
                        ]
                    ]
                ]);
            }
            
            $data = json_decode(file_get_contents($filePath), true);
            return response()->json($data);
        })->middleware(['auth'])->name('api.load-data-v3');
        
        // Save user-specific questions and settings for V3
        Route::post('/save-data-v3', function () {
            $userId = auth()->id(); // Require authentication
            $filePath = public_path("data/exam-ready-to-print-v3-{$userId}.json");
            
            // Ensure directory exists
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $data = request()->json()->all();
            file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
            
            return response()->json(['success' => true]);
        })->middleware(['auth'])->name('api.save-data-v3');
    });
    
});
