<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\SchoolLoginController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\ClassroomSubjectTeacherController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PeriodActivityController;
use App\Http\Controllers\Puzzle1Controller;
use App\Http\Controllers\PageViewController;  // Add page view controller import
use App\Http\Controllers\ScheduleAdminNewController;
use App\Http\Controllers\ScheduleTimingController;
use App\Http\Controllers\SchoolBrandingController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Controllers\MyClass2026\Cr\ClassroomRecordsPageController;
use App\Models\User;
use App\Notifications\WebPushNotification;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

// Include Classroom Records v1 Routes
include dirname(__DIR__).'/routes/myclass2026/cr/web.php';

// Domain-based Routing for QudratPro
Route::domain('qudratpro.com')->group(function () {
    require base_path('routes/qudrat/web.php');
    
    // Include Remote Control System Routes (inside domain)
    include dirname(__DIR__).'/routes/myclass2026/remote_control.php';

    // Diagnostic route (Inside domain)
    Route::get('/debug-controller', function() {
        try {
            $class = 'App\Http\Controllers\QuizSessionController';
            $exists = class_exists($class);
            $path = $exists ? (new \ReflectionClass($class))->getFileName() : 'not found';
            
            // Also check the route file content
            $routeFile = base_path('routes/myclass2026/cr/web.php');
            $routeContent = file_exists($routeFile) ? file_get_contents($routeFile) : 'not found';
            $snippet = str_contains($routeContent, '\App\Http\Controllers\QuizSessionController::class') 
                ? 'Hardened' : 'Legacy (Unprotected)';

            return response()->json([
                'class' => $class,
                'exists' => $exists,
                'path' => $path,
                'route_file' => $routeFile,
                'route_snippet' => $snippet,
                'composer_autoload' => file_exists(base_path('vendor/autoload.php')),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    });

    // Public Page View Routes (Inside domain)
    Route::post('/page-views', [\App\Http\Controllers\PageViewController::class, 'increment'])->name('page-views.increment');
    Route::get('/page-views/count', [\App\Http\Controllers\PageViewController::class, 'getCount'])->name('page-views.count');
});

Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);

// Domain-based Routing for Local Development (Testing)
Route::domain('qudratpro.test')->name('test.')->group(function () {
    require base_path('routes/qudrat/web.php');
});


// Login v1.2 Safe Preview
use App\Http\Controllers\Auth\LoginController;

Route::get('/login-v12', [LoginController::class, 'showForm'])->name('login.v12');
Route::post('/login-v12', [LoginController::class, 'authenticate'])->name('login.v12.post');

// School-specific login routes (public)
Route::get('/login/{school_slug}', [SchoolLoginController::class, 'show'])
    ->name('school.login');
Route::post('/login/{school_slug}', [SchoolLoginController::class, 'authenticate'])
    ->name('school.login.authenticate');
Route::get('/api/school-branding/{school_slug}', [SchoolLoginController::class, 'getBranding'])
    ->name('school.branding');

// Simple login route - user-friendly, no complex redirects
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

// Handle login POST - authenticate user
Route::post('/login', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|string',
        'password' => 'required|string',
    ]);

    // Attempt to authenticate the user using Laravel's built-in authentication
    $credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        $user = Auth::user();
        // Update last login
        $user->last_login = now();
        $user->save();

        // Redirect to intended URL or dashboard
        return redirect()->intended(route('dashboard'));
    }

    throw \Illuminate\Validation\ValidationException::withMessages([
        'email' => ['The provided credentials are incorrect.'],
    ]);
});

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

    // Focus Grid 
    Route::get('/focus-grid', function () {
        return Inertia::render('myclass2026/features/fg/FgDashboard');
    })->name('focus-grid.index');



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
        return Inertia::render('OcrComparison', [
            'title' => 'OCR Comparison - MyClass2026'
        ]);
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

// Standalone Schedule App (Installable PWA)
Route::get('/my-schedule-app', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/StandaloneScheduleApp');
})->name('schedule.app.standalone');

Route::get('/my-schedule-app/manifest.webmanifest', function () {
    $manifest = [
        'name' => 'My Schedule App',
        'short_name' => 'Schedule',
        'description' => 'Offline-first schedule app with live tracking and notifications.',
        'id' => '/my-schedule-app',
        'start_url' => '/my-schedule-app',
        'scope' => '/my-schedule-app',
        'display' => 'standalone',
        'background_color' => '#0f172a',
        'theme_color' => '#1e293b',
        'orientation' => 'portrait-primary',
        'categories' => ['productivity', 'education', 'utilities'],
        'icons' => [
            [
                'src' => '/my-schedule-app/icon.svg',
                'sizes' => 'any',
                'type' => 'image/svg+xml',
                'purpose' => 'any maskable',
            ],
        ],
        'shortcuts' => [
            [
                'name' => 'Open Schedule',
                'short_name' => 'Schedule',
                'description' => 'View your class schedule',
                'url' => '/my-schedule-app',
                'icons' => [
                    [
                        'src' => '/my-schedule-app/icon.svg',
                        'sizes' => 'any',
                        'type' => 'image/svg+xml',
                    ],
                ],
            ],
        ],
    ];

    return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 200)
        ->header('Content-Type', 'application/manifest+json');
})->withoutMiddleware([HandleInertiaRequests::class])->name('schedule.app.manifest');

Route::get('/my-schedule-app/icon.svg', function () {
    $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="My Schedule">
  <rect width="512" height="512" rx="96" fill="#1e293b"/>
  <rect x="36" y="36" width="440" height="440" rx="72" fill="none" stroke="#3b82f6" stroke-width="12"/>
  
  <!-- Calendar grid -->
  <rect x="80" y="120" width="352" height="280" rx="8" fill="none" stroke="#60a5fa" stroke-width="8"/>
  <line x1="80" y1="160" x2="432" y2="160" stroke="#60a5fa" stroke-width="8"/>
  
  <!-- Grid cells -->
  <line x1="168" y1="160" x2="168" y2="400" stroke="#60a5fa" stroke-width="4" opacity="0.5"/>
  <line x1="256" y1="160" x2="256" y2="400" stroke="#60a5fa" stroke-width="4" opacity="0.5"/>
  <line x1="344" y1="160" x2="344" y2="400" stroke="#60a5fa" stroke-width="4" opacity="0.5"/>
  
  <line x1="80" y1="220" x2="432" y2="220" stroke="#60a5fa" stroke-width="4" opacity="0.5"/>
  <line x1="80" y1="280" x2="432" y2="280" stroke="#60a5fa" stroke-width="4" opacity="0.5"/>
  <line x1="80" y1="340" x2="432" y2="340" stroke="#60a5fa" stroke-width="4" opacity="0.5"/>
  
  <!-- Clock icon -->
  <circle cx="400" cy="100" r="36" fill="#3b82f6"/>
  <circle cx="400" cy="100" r="24" fill="none" stroke="#0f172a" stroke-width="4"/>
  <line x1="400" y1="100" x2="400" y2="88" stroke="#0f172a" stroke-width="4" stroke-linecap="round"/>
  <line x1="400" y1="100" x2="410" y2="100" stroke="#0f172a" stroke-width="4" stroke-linecap="round"/>
  
  <!-- Notification bell -->
  <path d="M112 80 L112 68 Q112 56 124 56 Q136 56 136 68 L136 80" fill="none" stroke="#f59e0b" stroke-width="6" stroke-linecap="round"/>
  <path d="M100 80 Q100 96 112 104 L112 108 Q112 112 118 112 Q124 112 130 112 Q136 112 136 108 L136 104 Q148 96 148 80 Z" fill="#f59e0b"/>
  <circle cx="124" cy="116" r="4" fill="#f59e0b"/>
</svg>
SVG;

    return response($svg, 200)->header('Content-Type', 'image/svg+xml');
})->withoutMiddleware([HandleInertiaRequests::class])->name('schedule.app.icon');

// Schedule App V2 Routes
Route::get('/my-schedule-app/v2', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/v2/StandaloneScheduleAppV2');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->name('schedule.app.v2');

Route::get('/my-schedule-app/v2/manifest.webmanifest', function () {
    $manifest = [
        'name' => 'My Schedule App V2',
        'short_name' => 'Schedule V2',
        'description' => 'Mobile-first schedule app with multiple view modes, school timetable, and offline capabilities.',
        'id' => '/my-schedule-app/v2',
        'start_url' => '/my-schedule-app/v2',
        'scope' => '/my-schedule-app/v2',
        'display' => 'standalone',
        'background_color' => '#0f172a',
        'theme_color' => '#1e293b',
        'orientation' => 'portrait-primary',
        'categories' => ['productivity', 'education', 'utilities'],
        'icons' => [
            [
                'src' => '/my-schedule-app/v2/icon.svg',
                'sizes' => 'any',
                'type' => 'image/svg+xml',
                'purpose' => 'any maskable',
            ],
        ],
        'shortcuts' => [
            [
                'name' => 'Open Schedule V2',
                'short_name' => 'Schedule V2',
                'description' => 'View your schedule with mobile-optimized views',
                'url' => '/my-schedule-app/v2',
                'icons' => [
                    [
                        'src' => '/my-schedule-app/v2/icon.svg',
                        'sizes' => 'any',
                        'type' => 'image/svg+xml',
                    ],
                ],
            ],
            [
                'name' => 'School Timetable',
                'short_name' => 'School',
                'description' => 'View complete school timetable',
                'url' => '/my-schedule-app/v2#master',
                'icons' => [
                    [
                        'src' => '/my-schedule-app/v2/icon.svg',
                        'sizes' => 'any',
                        'type' => 'image/svg+xml',
                    ],
                ],
            ],
        ],
    ];

    return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 200)
        ->header('Content-Type', 'application/manifest+json');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v2.manifest');

Route::get('/my-schedule-app/v2/icon.svg', function () {
    $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="My Schedule V2">
  <rect width="512" height="512" rx="96" fill="#1e293b"/>
  <rect x="36" y="36" width="440" height="440" rx="72" fill="none" stroke="#3b82f6" stroke-width="12"/>
  
  <!-- Mobile phone frame -->
  <rect x="140" y="80" width="232" height="352" rx="24" fill="none" stroke="#60a5fa" stroke-width="8"/>
  <rect x="150" y="90" width="212" height="332" rx="16" fill="#0f172a"/>
  
  <!-- Schedule grid inside phone -->
  <rect x="160" y="110" width="192" height="252" rx="4" fill="none" stroke="#3b82f6" stroke-width="4"/>
  <line x1="160" y1="140" x2="352" y2="140" stroke="#3b82f6" stroke-width="2"/>
  <line x1="160" y1="170" x2="352" y2="170" stroke="#3b82f6" stroke-width="2"/>
  <line x1="160" y1="200" x2="352" y2="200" stroke="#3b82f6" stroke-width="2"/>
  <line x1="160" y1="230" x2="352" y2="230" stroke="#3b82f6" stroke-width="2"/>
  <line x1="160" y1="260" x2="352" y2="260" stroke="#3b82f6" stroke-width="2"/>
  <line x1="160" y1="290" x2="352" y2="290" stroke="#3b82f6" stroke-width="2"/>
  <line x1="160" y1="320" x2="352" y2="320" stroke="#3b82f6" stroke-width="2"/>
  
  <!-- Vertical lines -->
  <line x1="200" y1="110" x2="200" y2="362" stroke="#3b82f6" stroke-width="2"/>
  <line x1="240" y1="110" x2="240" y2="362" stroke="#3b82f6" stroke-width="2"/>
  <line x1="280" y1="110" x2="280" y2="362" stroke="#3b82f6" stroke-width="2"/>
  <line x1="320" y1="110" x2="320" y2="362" stroke="#3b82f6" stroke-width="2"/>
  
  <!-- View mode indicators -->
  <circle cx="180" cy="130" r="4" fill="#60a5fa"/>
  <circle cx="220" cy="130" r="4" fill="#60a5fa"/>
  <circle cx="260" cy="130" r="4" fill="#60a5fa"/>
  <circle cx="300" cy="130" r="4" fill="#60a5fa"/>
  <circle cx="340" cy="130" r="4" fill="#60a5fa"/>
  
  <!-- Status indicators -->
  <circle cx="256" cy="380" r="8" fill="#10b981"/>
  <rect x="180" y="440" width="152" height="8" rx="4" fill="#475569"/>
  
  <!-- Floating elements -->
  <circle cx="400" cy="120" r="24" fill="#3b82f6" opacity="0.8"/>
  <circle cx="420" cy="140" r="16" fill="#10b981" opacity="0.8"/>
  <circle cx="110" cy="160" r="20" fill="#f59e0b" opacity="0.8"/>
  
  <!-- Notification bell -->
  <path d="M380 80 L380 68 Q380 56 392 56 Q404 56 404 68 L404 80" fill="none" stroke="#f59e0b" stroke-width="4" stroke-linecap="round"/>
  <path d="M368 80 Q368 96 380 104 L380 108 Q380 112 386 112 Q392 112 398 112 Q404 112 404 108 L404 104 Q416 96 416 80 Z" fill="#f59e0b"/>
  <circle cx="392" cy="116" r="3" fill="#f59e0b"/>
</svg>
SVG;

    return response($svg, 200)->header('Content-Type', 'image/svg+xml');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v2.icon');

Route::get('/my-schedule-app/v2/sw.js', function () {
    $serviceWorkerPath = public_path('my-schedule-app/v2/sw.js');

    abort_unless(file_exists($serviceWorkerPath), 404);

    return response(file_get_contents($serviceWorkerPath), 200)
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/my-schedule-app/v2');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v2.service-worker');

Route::get('/my-schedule-app-v2-sw.js', function () {
    $serviceWorkerPath = public_path('my-schedule-app/v2/sw.js');

    abort_unless(file_exists($serviceWorkerPath), 404);

    return response(file_get_contents($serviceWorkerPath), 200)
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/my-schedule-app/v2');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', HandleInertiaRequests::class])->name('schedule.app.v2.service-worker.alias');

    // Offline System Test Route
    Route::get('/offline-test', function () {
        return Inertia::render('OfflineTest');
    })->name('offline.test');

    // Presentation Offline Page
    Route::get('/presentation-offline', function () {
        return view('presentation-offline');
    })->name('presentation.offline');

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

// Simple Focus App Offline v1 (public, no-auth standalone app)
Route::redirect('/simple-focus-app-offline', '/simple-focus-app-offline/v1');

Route::get('/simple-focus-app-offline/v1', function () {
    return Inertia::render('myclass2026/features/simple_focus_app_offline/ver1/Index');
})->name('simple-focus-app-offline.v1');

// Simple Focus App Offline v2 (public, no-auth standalone app)
Route::get('/simple-focus-app-offline/v2', function () {
    return Inertia::render('myclass2026/features/simple_focus_app_offline/ver2/Index');
})->name('simple-focus-app-offline.v2');

// Test route for v2
Route::get('/simple-focus-app-offline/v2/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'v2 route is working',
        'timestamp' => now()
    ]);
})->name('simple-focus-app-offline.v2.test');

Route::get('/simple-focus-app-offline/v1/manifest.webmanifest', function () {
    $manifest = [
        'name' => 'Simple Focus App Offline',
        'short_name' => 'Simple Focus',
        'description' => 'Standalone DOS-style focus app for offline task tracking.',
        'id' => '/simple-focus-app-offline/v1',
        'start_url' => '/simple-focus-app-offline/v1',
        'scope' => '/simple-focus-app-offline/v1',
        'display' => 'standalone',
        'background_color' => '#000000',
        'theme_color' => '#000000',
        'orientation' => 'portrait-primary',
        'categories' => ['productivity', 'utilities'],
        'icons' => [
            [
                'src' => '/simple-focus-app-offline/v1/icon.svg',
                'sizes' => 'any',
                'type' => 'image/svg+xml',
                'purpose' => 'any maskable',
            ],
        ],
        'shortcuts' => [
            [
                'name' => 'Open Focus App',
                'short_name' => 'Focus',
                'description' => 'Open the focus workspace',
                'url' => '/simple-focus-app-offline/v1',
                'icons' => [
                    [
                        'src' => '/simple-focus-app-offline/v1/icon.svg',
                        'sizes' => 'any',
                        'type' => 'image/svg+xml',
                    ],
                ],
            ],
        ],
    ];

    return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 200)
        ->header('Content-Type', 'application/manifest+json');
})->name('simple-focus-app-offline.v1.manifest');

Route::get('/simple-focus-app-offline/v1/icon.svg', function () {
    $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="Simple Focus">
  <rect width="512" height="512" rx="96" fill="#000000"/>
  <rect x="36" y="36" width="440" height="440" rx="72" fill="none" stroke="#22c55e" stroke-width="12"/>
  <path d="M110 150h292v42H110zM110 226h206v42H110zM110 302h160v42H110z" fill="#22c55e"/>
  <circle cx="390" cy="328" r="42" fill="#22c55e"/>
  <path d="M380 328l10 10 22-24" fill="none" stroke="#000" stroke-width="14" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;

    return response($svg, 200)->header('Content-Type', 'image/svg+xml');
})->name('simple-focus-app-offline.v1.icon');

// Simple Focus App Offline v2 manifest and icon
Route::get('/simple-focus-app-offline/v2/manifest.webmanifest', function () {
    $manifest = [
        'name' => 'Simple Focus App Offline v2',
        'short_name' => 'Simple Focus v2',
        'description' => 'Enhanced DOS-style focus app for offline task tracking.',
        'id' => '/simple-focus-app-offline/v2',
        'start_url' => '/simple-focus-app-offline/v2',
        'scope' => '/simple-focus-app-offline/v2',
        'display' => 'standalone',
        'background_color' => '#000000',
        'theme_color' => '#000000',
        'orientation' => 'portrait-primary',
        'icons' => [
            [
                'src' => '/simple-focus-app-offline/v2/icon.svg',
                'sizes' => 'any',
                'type' => 'image/svg+xml',
            ],
        ],
    ];

    return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 200)
        ->header('Content-Type', 'application/manifest+json');
})->name('simple-focus-app-offline.v2.manifest');

Route::get('/simple-focus-app-offline/v2/icon.svg', function () {
    $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="Simple Focus v2">
  <rect width="512" height="512" rx="96" fill="#000000"/>
  <rect x="36" y="36" width="440" height="440" rx="72" fill="none" stroke="#4ade80" stroke-width="12"/>
  <path d="M110 150h292v42H110zM110 226h206v42H110zM110 302h160v42H110z" fill="#4ade80"/>
  <circle cx="390" cy="328" r="42" fill="#4ade80"/>
  <path d="M380 328l10 10 22-24" fill="none" stroke="#000" stroke-width="14" stroke-linecap="round" stroke-linejoin="round"/>
  <circle cx="390" cy="230" r="8" fill="#a855f7"/>
  <circle cx="390" cy="200" r="8" fill="#a855f7"/>
  <circle cx="390" cy="170" r="8" fill="#a855f7"/>
</svg>
SVG;

    return response($svg, 200)->header('Content-Type', 'image/svg+xml');
})->name('simple-focus-app-offline.v2.icon');

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
// Weekly System V1 (Feature-First Architecture) - NEW
include dirname(__DIR__).'/routes/weekly_system_v1.php';
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

Route::get('puzzle1', [Puzzle1Controller::class, 'index'])
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
            Route::get('/test', [\App\Http\Controllers\QuizSessionController::class, 'teacherControl'])
                ->name('test');

            // Student join page
            Route::get('/join', [\App\Http\Controllers\QuizSessionController::class, 'studentJoin'])
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

    Route::get('/assessment/{id}', function ($id) {
        return Inertia::render('Courses/bm2/Assessment/Take', ['id' => $id]);
    })->name('bm2.assessment.take');

    Route::get('/assessment/{id}/results', function ($id) {
        return Inertia::render('Courses/bm2/Assessment/Results', ['assessmentId' => $id]);
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

    // Teacher Dashboard
    Route::get('/teacher/dashboard', function () {
        return Inertia::render('Courses/bm2/Teacher/Dashboard');
    })->name('bm2.teacher.dashboard');

    Route::get('/teacher/student/{id}', function ($id) {
        return Inertia::render('Courses/bm2/Teacher/StudentProgress', ['studentId' => $id]);
    })->name('bm2.teacher.student');
});

// Include Laravel Cache Management Routes
include dirname(__DIR__).'/routes/fix_laravel_cache.php';

// Add route for LessonTemplateManager
Route::get('/lesson-template-manager', function () {
     return Inertia::render('LessonTemplateManager/index');
})->name('lesson-template-manager');
