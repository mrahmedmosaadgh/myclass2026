<?php

return [
    // --- User & Organization Management ---
    [
        'id' => 'schools',
        'label' => ['en' => 'Schools', 'ar' => 'المدارس'],
        'route' => 'v2.system-admin.schools.index',
        'icon' => 'account_balance',
        'permission' => 'view schools',
    ],
    [
        'id' => 'users',
        'label' => ['en' => 'Users', 'ar' => 'المستخدمون'],
        'route' => 'v2.system-admin.users.index',
        'icon' => 'people',
        'permission' => 'view users',
    ],

    // --- Academic Structure ---
    [
        'id' => 'academics_group',
        'label' => ['en' => 'Academic Structure', 'ar' => 'الهيكل الأكاديمي'],
        'icon' => 'school',
        'children' => [
        ]
    ],

    // --- Academic Management ---
    [
        'id' => 'curriculum_group',
        'label' => ['en' => 'Curriculum & Calendar', 'ar' => 'المناهج والتقويم'],
        'icon' => 'calendar_month',
        'children' => [
            [
                'id' => 'curriculum',
                'label' => ['en' => 'Course Mgmt', 'ar' => 'إدارة المقررات'],
                'route' => 'school-admin.curriculum.courses.index',
                'icon' => 'book',
            ],
            [
                'id' => 'academic_calendar',
                'label' => ['en' => 'Academic Calendar', 'ar' => 'التقويم الدراسي'],
                'route' => 'admin.academic_calendar.index',
                'icon' => 'event_note',
            ],
            [
                'id' => 'weekly_system_v1',
                'label' => ['en' => 'Weekly System V1', 'ar' => 'نظام الأسبوع الجديد'],
                'route' => 'weekly-system-v1.dashboard',
                'icon' => 'view_week',
            ],
            [
                'id' => 'my_schedule',
                'label' => ['en' => 'My Schedule', 'ar' => 'جدولي'],
                'route' => 'schedules.teacher.my-schedule',
                'icon' => 'calendar_today',
            ],
        ]
    ],

    // --- HR & Staff ---
    [
        'id' => 'hr_group',
        'label' => ['en' => 'HR & Staff', 'ar' => 'الموارد البشرية'],
        'icon' => 'badge',
        'children' => [
             [
                'id' => 'teachers',
                'label' => ['en' => 'Teachers', 'ar' => 'المعلمين'],
                'route' => 'school-admin.users.teachers.index',
                'icon' => 'school',
             ],
             [
                'id' => 'students',
                'label' => ['en' => 'Students', 'ar' => 'الطلاب'],
                'route' => 'school-admin.users.students.index',
                'icon' => 'face',
             ],
             [
                'id' => 'parents',
                'label' => ['en' => 'Parents', 'ar' => 'أولياء الأمور'],
                'route' => 'school-admin.users.parents.index',
                'icon' => 'family_restroom',
             ],
             [
                'id' => 'teachers_import',
                'label' => ['en' => 'Import Teachers', 'ar' => 'استيراد المعلمين'],
                'route' => 'teacher.import', // Adjusted to match old_features.php naming
                'icon' => 'upload',
             ],
        ]
    ],

    // --- Modules & Features ---
    [
        'id' => 'modules_group',
        'label' => ['en' => 'Modules', 'ar' => 'الوحدات'],
        'icon' => 'extension',
        'children' => [
             [
                'id' => 'quizzes',
                'label' => ['en' => 'Quiz Management', 'ar' => 'إدارة الاختبارات'],
                'route' => 'quizzes.index',
                'permission' => 'manage quizzes', // Ensure this permission exists or remove line
             ],
             [
                'id' => 'qu_exams',
                'label' => ['en' => 'Qu Exams', 'ar' => 'الامتحانات الجديدة'],
                'route' => 'qu.exams.index',
                'icon' => 'assignment',
                'permission' => 'manage quizzes',
             ],
             [
                'id' => 'questions',
                'label' => ['en' => 'Question Bank', 'ar' => 'بنك الأسئلة'],
                'route' => 'questions.index1',
                'permission' => 'manage questions',
             ],
             [
                'id' => 'qu_questions',
                'label' => ['en' => 'Qu Questions', 'ar' => 'أسئلة Qu'],
                'route' => 'qu.questions.index',
                'icon' => 'quiz',
                'permission' => 'manage questions',
             ],
             [
                'id' => 'behaviors',
                'label' => ['en' => 'Behavior Management', 'ar' => 'إدارة السلوك'],
                'route' => 'school-admin.modules.gamification.index',
                'permission' => 'manage behaviors',
             ],
             [
                'id' => 'skills',
                'label' => ['en' => 'Skill Management', 'ar' => 'إدارة المهارات'],
                'route' => 'school-admin.modules.skills.index',
                'icon' => 'psychology',
                'permission' => 'manage skills',
             ],
             [
                'id' => 'qudrat',
                'label' => ['en' => 'Qudrat', 'ar' => 'القدرات'],
                'icon' => 'analytics',
                'children' => [
                    [
                        'id' => 'page_views_report',
                        'label' => ['en' => 'Page Views Report', 'ar' => 'تقرير زيارات الصفحة'],
                        'route' => 'admin.page-views.report',
                        'icon' => 'bar_chart',
                    ],
                ],
             ],
               [
                'id' => 'chatbot',
                'label' => ['en' => 'Chatbot', 'ar' => 'روبوت الدردشة'],
                'route' => 'admin.chatbot.index',
                'permission' => 'manage chatbot',
             ],
        ]
    ],

    // --- System Tools ---
    [
        'id' => 'tools_group',
        'label' => ['en' => 'System Tools', 'ar' => 'أدوات النظام'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'menus',
                'label' => ['en' => 'Menu Management', 'ar' => 'إدارة القوائم'],
                'route' => 'admin.menus.index', 
                'permission' => 'manage-menus',
            ],
             [
                'id' => 'activity_logs',
                'label' => ['en' => 'Activity Logs', 'ar' => 'سجلات النشاط'],
                'route' => 'admin.activity-logs.index',
                'permission' => 'view activity logs',
            ],
        ]
    ],
];
