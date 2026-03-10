<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DpDailyPlannerController;
use App\Models\DpDailyTask;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Teacher Role Routes (MyClass2026)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'web', 'role:teacher|admin|super_admin'])->prefix('myclass2026/teacher')->name('teacher.')->group(function () {
    
    // --- Planning ---
    Route::prefix('planning')->name('planning.')->group(function () {
        
        // Weekly Plans
        Route::get('/weekly-plans', function () {
            return Inertia::render('myclass2026/roles/teacher/planning/WeeklyPlans/Index');
        })->name('weekly-plans.index');
        
        Route::get('/weekly-plans/{weeklyPlanId}/edit', function ($weeklyPlanId) {
            return Inertia::render('myclass2026/roles/teacher/planning/WeeklyPlans/Edit', [
                'weeklyPlanId' => $weeklyPlanId
            ]);
        })->name('weekly-plans.edit');

        // Daily Tasks (Wrapping the original logic for the new component path)
        Route::get('/daily-tasks', function () {
            // Replicating DpDailyPlannerController@index logic for the new view
            $today = Carbon::today();
            $tasks = DpDailyTask::where('user_id', auth()->id())
                ->whereDate('date', $today)
                ->orderBy('start_time')
                ->get();

            if ($tasks->isEmpty()) {
                // We shouldn't blindly duplicate private methods, but for the MVP route:
                // The API will still hit the legacy DpDailyPlannerController for updates
            }

            return Inertia::render('myclass2026/roles/teacher/planning/DailyTasks/dp_DailyPlanner', [
                'tasks' => $tasks
            ]);
        })->name('daily-tasks.index');
        
    });

    // --- Presentation ---
    Route::prefix('presentation')->name('presentation.')->group(function () {
        
        // Lessons Manager
        Route::get('/lessons', function () {
            // Equivalent to LessonPresentationController@dashboard for teachers
            return Inertia::render('myclass2026/roles/teacher/presentation/lesson_presentation/LessonList');
        })->name('lessons.index');

    });
    
    // --- Standalone Features (Teacher View) ---
    Route::prefix('tools')->name('tools.')->group(function () {
        
        // Vocabulary Flashcards
        Route::get('/vocabulary', function () {
            return Inertia::render('myclass2026/features/VocabularyFlashcards/Index', ['mode' => 'practice']);
        })->name('vocabulary.index');

    });

    // --- Communication ---
    Route::prefix('communication')->name('communication.')->group(function () {
        
        // Messages / Chat
        Route::get('/messages', function () {
            // Replicating ConversationController@index
            return Inertia::render('myclass2026/features/Communication/Conversations/Index');
        })->name('messages.index');

    });
    Route::prefix('tools')->name('tools.')->group(function () {
        
        // Vocabulary Flashcards
        Route::get('/vocabulary', function () {
            return Inertia::render('myclass2026/features/VocabularyFlashcards/Index', ['mode' => 'practice']);
        })->name('vocabulary.index');

    });

});
