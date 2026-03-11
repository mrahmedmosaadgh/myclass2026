<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\SchoolLoginController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\ClassroomSubjectTeacherController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PeriodActivityController;
use App\Http\Controllers\puzzle1Controller;
use App\Http\Controllers\PageViewController;  // Add page view controller import
use App\Http\Controllers\ScheduleAdminNewController;
use App\Http\Controllers\ScheduleTimingController;
use App\Http\Controllers\SchoolBrandingController;
use App\Models\User;
use App\Notifications\WebPushNotification;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

// Domain-based Routing for QudratPro
Route::domain('qudratpro.com')->group(function () {
    require base_path('routes/qudrat/web.php');
});

Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);

// Domain-based Routing for Local Development (Testing)
Route::domain('qudratpro.test')->name('test.')->group(function () {
    require base_path('routes/qudrat/web.php');
});


// School-specific login routes (public)


// School-specific login routes (public)
Route::get('/login/{school_slug}', [SchoolLoginController::class, 'show'])
    ->name('school.login');
Route::post('/login/{school_slug}', [SchoolLoginController::class, 'authenticate'])
    ->name('school.login.authenticate');
Route::get('/api/school-branding/{school_slug}', [SchoolLoginController::class, 'getBranding'])
    ->name('school.branding');

// Chatbot - User Routes
Route::post('/api/chatbot/start', [App\Http\Controllers\ChatbotController::class, 'start'])->name('chatbot.start');
Route::post('/api/chatbot/send', [App\Http\Controllers\ChatbotController::class, 'send'])->name('chatbot.send');
Route::get('/api/chatbot/history', [App\Http\Controllers\ChatbotController::class, 'history'])->name('chatbot.history');

// Detect user's school for redirect (public)
Route::post('/api/detect-school', [App\Http\Controllers\Auth\LoginRedirectController::class, 'detectSchool'])
    ->name('detect.school');

// Validate school slug from localStorage (public)
Route::post('/api/validate-school', [App\Http\Controllers\Auth\LoginRedirectController::class, 'getSchoolBySlug'])
    ->name('validate.school');

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

    // Realtime System Test Route
    Route::get('/realtime-test', function () {
        return Inertia::render('Realtime/TestPage');
    })->name('realtime.test');

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


    Route::get('/page-test', function () {
        return Inertia::render('my_class/page_test/page_test');
    })->name('page.test');

    // Micro component testing page (science namespace)
    Route::get('/science/micro-component-test', function () {
        return Inertia::render('MicroComponentTest/Index');
    })->name('science.micro-component-test');
// Micro Component Test (Public)
Route::get('/micro-component-test', function () {
    return Inertia::render('MicroComponentTest/Index');
})->name('micro-component-test.public');

// Micro Component Test (Public)
Route::get('/ct', function () {
    return Inertia::render('MicroComponentTest/Index');
})->name('ct');



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
        Route::post('/notifications/Mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        Route::post('/notifications/send-test', [NotificationController::class, 'sendTestNotification']);
        Route::post('/notifications/send-to-users', [NotificationController::class, 'sendToUsers']);

        Route::resource('teacher/period-activities', PeriodActivityController::class);


    });

    // Teacher Import Routes
    Route::get('teachers/import', [\App\Http\Controllers\TeacherImportController::class, 'index'])
        ->name('myteachers.import');
    Route::get('teachers/import/schools', [\App\Http\Controllers\TeacherImportController::class, 'getSchools'])
        ->name('myteachers.import.schools');
    Route::get('teachers/import/academic-year/{schoolId}', [\App\Http\Controllers\TeacherImportController::class, 'getActiveAcademicYear'])
        ->name('myteachers.import.academic-year');
    Route::post('teachers/import/validate', [\App\Http\Controllers\TeacherImportController::class, 'validateImport'])
        ->name('myteachers.import.validate');
    Route::post('teachers/import/process', [\App\Http\Controllers\TeacherImportController::class, 'processImport'])
        ->name('myteachers.import.process');





    // School branding settings (admin only)
    Route::prefix('admin/school-branding')
        ->name('admin.school-branding.')
        ->group(function () {
            Route::get('/', [SchoolBrandingController::class, 'index'])->name('admin.school-branding.index');
            Route::put('/{school}', [SchoolBrandingController::class, 'update'])->name('admin.school-branding.update');
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
Route::get('/offline-test', function () {
    return Inertia::render('OfflineTest');
})->name('offline.test.public');

// Simple test route for network indicator
Route::get('/network-test', function () {
    return Inertia::render('NetworkTest');
})->name('network.test');


// Test route to check CSRF cookie and session
Route::get('/sanctum-test', function () {
    $user = Auth::user();

    return response()->json([
        'message' => 'CSRF cookie is set',
        'session_id' => session()->getId(),
        'user' => $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email] : null,
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

// Student & Teacher Schedule Viewing Routes (Read-only)
Route::middleware(['auth'])->group(function () {
    // Teacher self-schedule view (no ID required)
    Route::get('/schedules/my-schedule', [App\Http\Controllers\ScheduleController::class, 'showMySchedule'])
        ->name('schedules.teacher.my-schedule');

    // Student schedule view - read-only classroom timetable
    Route::get('/schedules/classroom/{classroom_id}/{classroom_name?}', [App\Http\Controllers\ScheduleController::class, 'showClassroomSchedule'])
        ->name('schedules.classroom.view')
        ->where(['classroom_id' => '[0-9]+']);

    // Teacher schedule view - read-only teacher assignments
    Route::get('/schedules/teacher/{teacher_id}/{teacher_name?}', [App\Http\Controllers\ScheduleController::class, 'showTeacherSchedule'])
        ->name('schedules.teacher.view')
        ->where(['teacher_id' => '[0-9]+']);
});

include dirname(__DIR__).'/routes/weekly_system.php';
include dirname(__DIR__).'/routes/admin.php';
include dirname(__DIR__).'/routes/r_hr.php';
include dirname(__DIR__).'/routes/r_teacher.php';
include dirname(__DIR__).'/routes/r_student.php';
include dirname(__DIR__).'/routes/r_out.php';
include dirname(__DIR__).'/routes/lessons.php';
// Legacy features route consolidation
include dirname(__DIR__).'/routes/old_features.php';

// MyClass2026 Role-Based Routes
include dirname(__DIR__).'/routes/myclass2026/roles/teacher.php';
include dirname(__DIR__).'/routes/myclass2026/roles/school-admin.php';
include dirname(__DIR__).'/routes/myclass2026/roles/student.php';
include dirname(__DIR__).'/routes/myclass2026/roles/parent.php';

// include dirname(__DIR__).'/routes/weekly_plans.php';
include dirname(__DIR__).'/routes/acadimy.php';
include dirname(__DIR__).'/routes/qudrat_routes.php';
// include dirname(__DIR__).'/routes/dp.php';
// include dirname(__DIR__) . '/routes/weekly_system.php';

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

Route::get('/storage/{path}', function ($path) {
    if (Storage::disk('public')->exists($path)) {
        return response()->file(Storage::disk('public')->path($path));
    }

    return response()->json(['error' => 'File not found'], 404);
})->where('path', '.*');



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
    $user = User::where('id', Auth::user()->id)->first();
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
        'vapidPublicKey' => config('webpush.vapid.public_key'),
    ]);
    // } catch (\Exception $e) {
    report($e);

    return Inertia::render('Notifications/Settings', [
        'error' => 'Failed to process notification: '.$e->getMessage(),
        'hasSubscription' => false,
        'vapidPublicKey' => config('webpush.vapid.public_key'),
    ]);
}
    // }
)->name('notifications.settings');

// Developer Routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('developer')->name('developer.')->group(function () {
    Route::get('/resume-system', function () {
        return Inertia::render('modules/resumes/Index');
    })->name('resume-system');
    
    // Page view counter routes
    Route::post('/page-views', [PageViewController::class, 'increment'])->name('page-views.increment');
    Route::get('/page-views/count', [PageViewController::class, 'getCount'])->name('page-views.count');
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
            'routeData' => $routes,
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

Route::get('/my_data', function () {
    return User::where('id', Auth::user()->id)->get();
});
Route::get('/my_classes', [ClassroomSubjectTeacherController::class, 'my_classes']);
Route::get('/my_classes_with_students', [ClassroomSubjectTeacherController::class, 'my_classes_with_students']);

Route::get('/all_classes', [ClassroomSubjectTeacherController::class, 'all_classes']);
Route::get('/all_subjects', [ClassroomSubjectTeacherController::class, 'all_subjects']);
Route::get('/all_teachers', [ClassroomSubjectTeacherController::class, 'all_teachers']);
Route::get('/teacher_classes', [ClassroomSubjectTeacherController::class, 'teacher_classes']);
Route::get('/all_teachers_with_classroom_subject', [ClassroomSubjectTeacherController::class, 'all_teachers_with_classroom_subject']);

// app\Models\Classroom.php

// resources\js\Pages\my_table_mnger/JsonDataTable.vue

// Developer Routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])
// ->prefix('developer')
// ->name('developer.')
    ->group(function () {



        // Quiz Management Routes
        Route::prefix('quizzes')->name('quizzes.')->group(function () {
            // Main dashboard
            Route::get('/', function () {
                return Inertia::render('QuizManagement/QuizDashboard');
            })->name('index');

            // Debug route to check if quiz exists
            Route::get('/{id}/debug', function ($id) {
                $quiz = \App\Models\Quiz::find($id);
                if (! $quiz) {
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
                return Inertia::render('QuizManagement/QuizPreview', ['quizId' => (int) $id]);
            })->name('preview');

            // Test/Take quiz
            Route::get('/{id}/test', function ($id) {
                return Inertia::render('QuizManagement/QuizTest', ['quizId' => (int) $id]);
            })->name('test');

            // Results/Review
            Route::get('/{id}/results', function ($id) {
                return Inertia::render('QuizManagement/QuizResults', ['quizId' => (int) $id]);
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
            })->name('index1');

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

// School HR Admin Registration
Route::get('/register-school-admin', [App\Http\Controllers\SchoolHrAdminRegistrationController::class, 'create'])->name('register.school_admin');
Route::post('/register-school-admin', [App\Http\Controllers\SchoolHrAdminRegistrationController::class, 'store'])->name('register.school_admin.store');

// Add route alias for /questions to redirect to the question bank system
Route::get('/questions', function () {
    return redirect()->route('qu.questions.index');
});

Route::get('/questions/create', function () {
    return redirect()->route('qu.questions.create');
});

Route::get('/questions/{question}/edit', function (\App\Models\QuQuestion $question) {
    return redirect()->route('qu.questions.edit', $question);
});

Route::get('/questions/{question}', function (\App\Models\QuQuestion $question) {
    return redirect()->route('qu.questions.show', $question);
});

// Load Feature Routes (Modules)
$modulesPath = base_path('routes/modules');
if (file_exists($modulesPath)) {
    $modules = scandir($modulesPath);
    foreach ($modules as $module) {
        if ($module === '.' || $module === '..') {
            continue;
        }

        $moduleWebRoute = $modulesPath.'/'.$module.'/web.php';
        if (file_exists($moduleWebRoute)) {
            require $moduleWebRoute;
        }
    }
}

// Load Course Modules Routes (e.g. Courses/bm)
$coursesPath = base_path('routes/Courses');
if (file_exists($coursesPath)) {
    $courses = scandir($coursesPath);
    foreach ($courses as $course) {
        if ($course === '.' || $course === '..') {
            continue;
        }

        $courseWebRoute = $coursesPath.'/'.$course.'/web.php';
        if (file_exists($courseWebRoute)) {
            require $courseWebRoute;
        }
    }
}

// BM2 Basic Math Platform Web Routes
Route::middleware(['auth'])->prefix('bm2')->group(function () {
    // Assessment Pages
    Route::get('/assessment/start', function () {
        return Inertia::render('Courses/bm2/Assessment/Start');
    })->name('bm2.assessment.start');

    Route::get('/assessment/{id}', function () {
        return Inertia::render('Courses/bm2/Assessment/Take');
    })->name('bm2.assessment.take');

    Route::get('/assessment/{id}/results', function () {
        return Inertia::render('Courses/bm2/Assessment/Results');
    })->name('bm2.assessment.results');

    // Student Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Courses/bm2/Dashboard');
    })->name('bm2.dashboard');

    Route::get('/learning-paths', function () {
        return Inertia::render('Courses/bm2/LearningPaths');
    })->name('bm2.learning-paths');

    Route::get('/badges', function () {
        return Inertia::render('Courses/bm2/Badges');
    })->name('bm2.badges');
});