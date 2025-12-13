<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClassroomSubjectTeacherController;
use App\Http\Controllers\puzzle1Controller;
use App\Http\Controllers\ScheduleAdminNewController;
use App\Http\Controllers\ScheduleTimingController;
use App\Models\User;
use App\Notifications\WebPushNotification;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\PeriodActivityController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\CalendarEventController;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Quick link to Course Management
    Route::get('/courses', function () {
        return redirect()->route('course-management.courses.index');
    })->name('courses');

    // Firebase Test Route
    Route::get('/firebase-test', function () {
        return Inertia::render('Firebase/Test');
    })->name('firebase.test');

    Route::get('/print_html', function () {
        return Inertia::render('print_html/Index');
    })->name('print_html.index');

    Route::get('/qr-code-generator', function () {
        return Inertia::render('QrCodeGenerator');
    })->name('qr-code-generator');

    Route::get('/student-qr-codes', function () {
        return Inertia::render('my_class/admin/qr/test_qr/Index');
    })->name('student-qr-codes');

    Route::get('/ocr-test', function () {
        return Inertia::render('OcrTest');
    })->name('ocr-test');

    Route::get('/ocr-comparison', function () {
        return Inertia::render('OcrComparison');
    })->name('ocr-comparison');

    Route::get('/barcode-scanner', function () {
        return Inertia::render('BarcodeScanner');
    })->name('barcode-scanner');

    // Vocabulary Flashcards Routes
    Route::get('/vocabulary-flashcards', [App\Http\Controllers\VocabularyFlashcardsController::class, 'index'])->name('vocabulary-flashcards');
    Route::get('/vocabulary-flashcards/practice', function () {
        return Inertia::render('VocabularyFlashcards/Index', ['mode' => 'practice']);
    })->name('vocabulary-flashcards.practice');
    Route::get('/vocabulary-flashcards/quiz', function () {
        return Inertia::render('VocabularyFlashcards/Index', ['mode' => 'quiz']);
    })->name('vocabulary-flashcards.quiz');
    Route::post('/vocabulary-flashcards', [App\Http\Controllers\VocabularyFlashcardsController::class, 'store'])->name('vocabulary-flashcards.store');

    Route::get('/page-test', function () {
        return Inertia::render('my_class/page_test/page_test');
    })->name('page.test');

    // Offline System Test Route
    Route::get('/offline-test', function () {
        return Inertia::render('OfflineTest');
    })->name('offline.test');


        // Route to assign random colors to ClassroomSubjectTeachers for a school
        Route::post('/admin/schedules/assign-random-colors', [ScheduleAdminNewController::class, 'create_rand_color'])->name('schedules.assign_colors');
        Route::patch('/admin/schedules/{schedule}/update-period-code', [ScheduleAdminNewController::class, 'updatePeriodCode'])
        ->name('admin.schedules.update_period_code');

        Route::get('schedule/get_data/{school_id}', [ScheduleAdminNewController::class, 'getScheduleData'])->name('admin.schedules.get_data');

    Route::get('classroom-subject-teacher/import-page', [\App\Http\Controllers\ClassroomSubjectTeacherImportController::class, 'index'])
        ->name('classroom-subject-teacher.import-page');
    Route::post('classroom-subject-teacher/import', [\App\Http\Controllers\ClassroomSubjectTeacherImportController::class, 'store'])
        ->name('classroom-subject-teacher.import');
    Route::post('/classroom-subject-teacher/validate', [\App\Http\Controllers\ClassroomSubjectTeacherImportController::class, 'validate'])
        ->name('classroom-subject-teacher.validate');

    // Web Push Notification Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/vapid/public-key', [App\Http\Controllers\PushSubscriptionController::class, 'getVapidPublicKey']);
        Route::post('/push/subscribe', [App\Http\Controllers\PushSubscriptionController::class, 'store']);
        Route::post('/push/unsubscribe', [App\Http\Controllers\PushSubscriptionController::class, 'destroy']);

        // Notification Routes
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        Route::post('/notifications/send-test', [NotificationController::class, 'sendTestNotification']);
        Route::post('/notifications/send-to-users', [NotificationController::class, 'sendToUsers']);






        Route::resource('teacher/period-activities', PeriodActivityController::class);

        // Teacher presentation / demo lesson editor route
        Route::get('/teacher/presentation', function () {
            return Inertia::render('my_class/teacher/peresntation_2/peresentation_2');
        })->name('teacher.presentation');
    });
});

// Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/users', [UserController::class, 'index'])->name('users.index');
//     Route::post('/users', [UserController::class, 'store'])->name('users.store');
//     Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
//     Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
//     Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
// });


// D:\my_projects\2025\laravel12\myclass5\resources\js\Pages\LandingPage.vue
Route::get('/', function () {
    return Inertia::render('LandingPage');
})->name('LandingPage');

// Public Offline System Test Route (no authentication required)
Route::get('/offline-test-public', function () {
    return Inertia::render('OfflineTest');
})->name('offline.test.public');

// Simple test route for network indicator
Route::get('/network-test', function () {
    return Inertia::render('NetworkTest');
})->name('network.test');

// Test route to check CSRF cookie and session
Route::get('/sanctum-test', function () {
    return response()->json([
        'message' => 'CSRF cookie is set',
        'session_id' => session()->getId(),
        'user' => auth()->check() ? auth()->user()->only(['id', 'name', 'email']) : null,
    ]);
});

// Test page for Sanctum authentication
Route::get('/sanctum-test-page', function () {
    return Inertia::render('SanctumTest');
})->name('sanctum.test');

// Sanctum CSRF cookie route
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

// Auth status check route
Route::get('/auth/status', [App\Http\Controllers\AuthStatusController::class, 'check']);

include dirname(__DIR__) . '/routes/admin.php';
include dirname(__DIR__) . '/routes/r_hr.php';
include dirname(__DIR__) . '/routes/r_teacher.php';
include dirname(__DIR__) . '/routes/r_student.php';
include dirname(__DIR__) . '/routes/r_out.php';
include dirname(__DIR__) . '/routes/lessons.php';
include dirname(__DIR__) . '/routes/weekly_plans.php';
include dirname(__DIR__) . '/routes/acadimy.php';
include dirname(__DIR__) . '/routes/qudrat_routes.php';
include dirname(__DIR__) . '/routes/course_management.php';
include dirname(__DIR__) . '/routes/dp.php';
// include dirname(__DIR__) . '/routes/r_api.php';

// TickTick Task Management Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Task routes
    Route::get('/developer/ticktick', [App\Http\Controllers\Developer\TaskController::class, 'index'])->name('developer.ticktick');
    Route::get('/developer/tasks', [App\Http\Controllers\Developer\TaskController::class, 'getTasks'])->name('developer.tasks.get');
    Route::post('/developer/tasks', [App\Http\Controllers\Developer\TaskController::class, 'store'])->name('developer.tasks.store');
    Route::post('/developer/tasks/batch', [App\Http\Controllers\Developer\TaskController::class, 'batchStore'])->name('developer.tasks.batch-store');
    Route::post('/developer/tasks/bulk-delete', [App\Http\Controllers\Developer\TaskController::class, 'bulkDelete'])->name('developer.tasks.bulk-delete');
    Route::put('/developer/tasks/{task}', [App\Http\Controllers\Developer\TaskController::class, 'update'])->name('developer.tasks.update');
    Route::delete('/developer/tasks/{task}', [App\Http\Controllers\Developer\TaskController::class, 'destroy'])->name('developer.tasks.destroy');
    Route::post('/developer/tasks/{task}/toggle-complete', [App\Http\Controllers\Developer\TaskController::class, 'toggleComplete'])->name('developer.tasks.toggle-complete');
    Route::post('/developer/tasks/reorder', [App\Http\Controllers\Developer\TaskController::class, 'reorder'])->name('developer.tasks.reorder');

    // Pomodoro routes
    Route::post('/developer/pomodoro/start', [App\Http\Controllers\Developer\PomodoroController::class, 'start'])->name('developer.pomodoro.start');
    Route::post('/developer/pomodoro/{session}/end', [App\Http\Controllers\Developer\PomodoroController::class, 'end'])->name('developer.pomodoro.end');
    Route::get('/developer/pomodoro/recent', [App\Http\Controllers\Developer\PomodoroController::class, 'recent'])->name('developer.pomodoro.recent');
    Route::get('/developer/pomodoro/stats', [App\Http\Controllers\Developer\PomodoroController::class, 'stats'])->name('developer.pomodoro.stats');
});

Route::get('/admin/classroom-subject-teachers/import', [ClassroomSubjectTeacherController::class, 'index'])->name('admin.classroom-subject-teachers.import-page');
Route::post('/admin/classroom-subject-teachers/import', [ClassroomSubjectTeacherController::class, 'import'])->name('admin.classroom-subject-teachers.import');
Route::post('/admin/classroom-subject-teachers/validate', [ClassroomSubjectTeacherController::class, 'validateImport'])->name('admin.classroom-subject-teachers.validate');

Route::get('/storage/{path}', function($path) {
    if (Storage::exists($path)) {
        return response()->file(Storage::path($path));
    }
    return response()->json(['error' => 'File not found'], 404);
})->where('path', '.*');

// Chat Routes
Route::middleware(['auth'])->group(function () {
    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/create', [ConversationController::class, 'create'])->name('conversations.create');
    Route::post('/conversations', [ConversationController::class, 'store'])->name('conversations.store');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');

    // Messages
    Route::post('/conversations/{conversation}/messages', [ChatMessageController::class, 'store'])->name('messages.store');
    Route::post('/conversations/{conversation}/typing', [ChatMessageController::class, 'typing'])->name('messages.typing');
    Route::post('/conversations/{conversation}/mark-seen', [ChatMessageController::class, 'markAsSeen'])->name('messages.mark-seen');
});

// User Messages Routes (moved from API)
Route::middleware(['auth'])->group(function () {
    Route::get('/user-messages', [App\Http\Controllers\UserMessageController::class, 'index'])->name('user-messages.index');
    Route::post('/user-messages', [App\Http\Controllers\UserMessageController::class, 'store'])->name('user-messages.store');
    Route::post('/user-messages/{user_message}/read', [App\Http\Controllers\UserMessageController::class, 'markAsRead'])->name('user-messages.mark-as-read');
    Route::get('/user-messages/users', [App\Http\Controllers\UserMessageController::class, 'getUsers'])->name('user-messages.users');
});

// Private Chat Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/private-chat', [App\Http\Controllers\PrivateChatController::class, 'index'])->name('private-chat.index');
    Route::get('/private-chat/{userId}', [App\Http\Controllers\PrivateChatController::class, 'chat'])->name('private-chat.chat');
    Route::post('/private-chat/{conversationId}/send', [App\Http\Controllers\PrivateChatController::class, 'sendMessage'])->name('private-chat.send-message');
    Route::get('/private-chat/{conversationId}/messages', [App\Http\Controllers\PrivateChatController::class, 'getMessages'])->name('private-chat.get-messages');
});

Route::group(['prefix' => 'admin/schedules', 'as' => 'admin.schedules.'], function () {
    // ... existing routes ...

    Route::get('timings_show_data/{school_id}', [ScheduleTimingController::class, 'show_data'])
        ->name('timings.show_data');
      Route::post('timings_show_data2', [ScheduleTimingController::class, 'show_data2'])
        ->name('timings.timings_show_data2');
    Route::post('timings', [ScheduleTimingController::class, 'store'])
        ->name('timings.store');
});


    Route::get('puzzle1', [puzzle1Controller::class, 'index'])
        ->name('puzzle1');




    Route::get('/notifications/settings', function () {
        $user = User::where('id', Auth::user()->id)->first()   ;
        // try {
            // Only send test notification if the user has subscriptions
            if ($user->pushSubscriptions()->exists()) {
                $user->notify(new WebPushNotification(
                    'Web Push Test',
                    'Your web push notifications are working!',
                    route('dashboard')
                ));
            }

            return Inertia::render('Notifications/Settings', [
                'hasSubscription' => $user->pushSubscriptions()->exists(),
                'vapidPublicKey' => config('webpush.vapid.public_key')
            ]);
        // } catch (\Exception $e) {
            report($e);
            return Inertia::render('Notifications/Settings', [
                'error' => 'Failed to process notification: ' . $e->getMessage(),
                'hasSubscription' => false,
                'vapidPublicKey' => config('webpush.vapid.public_key')
            ]);
        }
    // }
    )->name('notifications.settings');

// Calendar Events Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarEventController::class, 'getEvents'])->name('calendar.events');
    Route::get('/calendar/event-types', [CalendarEventController::class, 'getEventTypes'])->name('calendar.event-types');
    Route::get('/calendar/export', [CalendarEventController::class, 'exportEvents'])->name('calendar.export');

    Route::post('/calendar/events', [CalendarEventController::class, 'store'])->name('calendar.events.store');
    Route::put('/calendar/events/{calendarEvent}', [CalendarEventController::class, 'update'])->name('calendar.events.update');
    Route::delete('/calendar/events/{calendarEvent}', [CalendarEventController::class, 'destroy'])->name('calendar.events.destroy');


    Route::get('/project-manager', function () {
    return Inertia::render('project_manager/ProjectTracker');
});
});

// Developer Routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('developer')->name('developer.')->group(function () {
    Route::get('/resume-system', function () {
        return Inertia::render('modules/resumes/Index');
    })->name('resume-system');
    // Optionally, keep the old route or remove:
    // Route::get('/resume-themes', ...);
    Route::get('/project-tasks', function () {
        return Inertia::render('project_manager/ProjectTracker');
    })->name('project-tasks');
    Route::get('/', function () {
        return Inertia::render('developer/DeveloperMenu');
    })->name('menu');
    
    // System routes viewer
    Route::get('/system-routes', function () {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'methods' => $route->methods(),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
            ];
        })->values();
        
        return Inertia::render('developer/sys_links/Index', [
            'routeData' => $routes
        ]);
    })->name('system-routes');

    Route::get('/resume-questions-manager', function () {
    return Inertia::render('modules/resumes/qbank2/ResumeQuestionsManager');
    // resources\js\Pages\modules\resumes\qbank2\ResumeQuestionsManager.vue
});

    // MyProject Tasks Route
    Route::get('/myproject-tasks', function () {
        return Inertia::render('developer/myproject_tasks/TaskManager');
    })->name('myproject-tasks');
        Route::get('/SpeechRecognition', function () {
        return Inertia::render('quiz_system/add_students/add_students');
    })->name('SpeechRecognition');
});

        Route::get('/JsonTableBuilder', function () {
        return Inertia::render('my_table_mnger/JsonTableBuilder');
    })->name('JsonTableBuilder');
     
           Route::get('/TableManager', function () {
        return Inertia::render('my_table_mnger/TableManager');
    })->name('TableManager'); 

               Route::get('/get_json_test', function () {
        return Inertia::render('my_table_mnger/get_json_test');
        // resources\js\Pages\my_table_mnger\get_json_test.vue
    })->name('get_json_test'); 


               Route::get('/my_data', function () {return User::where('id', Auth::user()->id)->get();}) ; 
               Route::get('/my_classes'  , [ClassroomSubjectTeacherController::class, 'my_classes']   ) ; 
               Route::get('/my_classes_with_students'  , [ClassroomSubjectTeacherController::class, 'my_classes_with_students']   ) ; 
               
               Route::get('/all_classes'  , [ClassroomSubjectTeacherController::class, 'all_classes']   ) ; 
               Route::get('/all_subjects'  , [ClassroomSubjectTeacherController::class, 'all_subjects']   ) ; 
               Route::get('/all_teachers'  , [ClassroomSubjectTeacherController::class, 'all_teachers']   ) ; 
               Route::get('/teacher_classes'  , [ClassroomSubjectTeacherController::class, 'teacher_classes']   ) ; 
               Route::get('/all_teachers_with_classroom_subject'  , [ClassroomSubjectTeacherController::class, 'all_teachers_with_classroom_subject']   ) ; 
        
        // app\Models\Classroom.php
    
    // resources\js\Pages/my_table_mnger/JsonDataTable.vue

// Developer Routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])
// ->prefix('developer')
// ->name('developer.')
->group(function () {

    Route::get('/reward_sys', function () {
        return Inertia::render('my_table_mnger/reward_sys/reward_sys');
        // resources\js\Pages\my_table_mnger/reward_sys/reward_sys.vue
        // my_table_mnger/reward_sys/reward_sys
    })->name('reward_sys'); 

    // Lesson Presentation Routes (moved to separate file)
    require __DIR__.'/web_lesson_presentation.php';

    // Quiz Management Routes
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        // Main dashboard
        Route::get('/', function () {
            return Inertia::render('QuizManagement/QuizDashboard');
        })->name('index');
        
        // Debug route to check if quiz exists
        Route::get('/{id}/debug', function ($id) {
            $quiz = \App\Models\Quiz::find($id);
            if (!$quiz) {
                return response()->json(['error' => 'Quiz not found', 'id' => $id], 404);
            }
            return response()->json(['quiz' => $quiz, 'message' => 'Quiz exists']);
        });
        
        // Create new quiz
        Route::get('/create', function () {
            return Inertia::render('QuizManagement/QuizBuilder');
        })->name('create');
        
        // Edit quiz
        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('QuizManagement/QuizBuilder', ['quizId' => $id]);
        })->name('edit');
        
        // Preview quiz
        Route::get('/{id}/preview', function ($id) {
            return Inertia::render('QuizManagement/QuizPreview', ['quizId' => (int)$id]);
        })->name('preview');
        
        // Test/Take quiz
        Route::get('/{id}/test', function ($id) {
            return Inertia::render('QuizManagement/QuizTest', ['quizId' => (int)$id]);
        })->name('test');
        
        // Results/Review
        Route::get('/{id}/results', function ($id) {
            return Inertia::render('QuizManagement/QuizResults', ['quizId' => (int)$id]);
        })->name('results');
        
        // Analytics
        Route::get('/{id}/analytics', function ($id) {
            return Inertia::render('QuizManagement/QuizAnalytics', ['quizId' => $id]);
        })->name('analytics');
    });
    
    // Legacy route (redirect to new dashboard)
    Route::get('/quiz-management', function () {
        return redirect()->route('quizzes.index');
    })->name('quiz.management');
    
    // Question Bank Management Routes
    Route::prefix('questions')->name('questions.')->group(function () {
        // Main question bank listing
        Route::get('/', function () {
            return Inertia::render('QuestionManagement/QuestionBank');
        })->name('index');
        
        // Create new question
        Route::get('/create', function () {
            return Inertia::render('QuestionManagement/QuestionEditor');
        })->name('create');
        
        // Edit question
        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('QuestionManagement/QuestionEditor', ['questionId' => $id]);
        })->name('edit');
        
        // Import questions
        Route::get('/import', function () {
            return Inertia::render('QuestionManagement/QuestionImport');
        })->name('import');
    });
    
    // Live Quiz Session Routes
    Route::prefix('quiz/live')->name('quiz.live.')->group(function () {
        // Teacher control page
        Route::get('/test', [App\Http\Controllers\QuizSessionController::class, 'teacherControl'])
            ->name('test');
        
        // Student join page
        Route::get('/join', [App\Http\Controllers\QuizSessionController::class, 'studentJoin'])
            ->name('join');
    });
});

// Add route for LessonTemplateManager
Route::get('/lesson-template-manager', function () {
     return Inertia::render('LessonTemplateManager/index');
    // return view('LessonTemplateManager.index');
    // /Users/ahmedmosaad/Herd/myclass2026-main/resources/js/Pages/LessonTemplateManager/index.vue
})->name('lesson-template-manager');