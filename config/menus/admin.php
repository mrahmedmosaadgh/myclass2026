<?php

return [
    [
        'id' => 'dashboard',
        'label' => ['en' => 'Dashboard', 'ar' => 'لوحة القيادة'],
        'route' => 'admin.dashboard', // Keep as is if valid, or v2.system-admin.dashboard
        'icon' => 'dashboard',
    ],
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
    [
        'id' => 'roles',
        'label' => ['en' => 'Roles & Permissions', 'ar' => 'الأدوار والصلاحيات'],
        'route' => 'admin.roles.index', // Verify if exists
        'icon' => 'security',
        'permission' => 'manage roles',
    ],

    // --- Academic Structure ---
    [
        'id' => 'academics_group',
        'label' => ['en' => 'Academic Structure', 'ar' => 'الهيكل الأكاديمي'],
        'icon' => 'school',
        'children' => [
            [
                'id' => 'subjects',
                'label' => ['en' => 'Subjects', 'ar' => 'المواد الدراسية'],
                'route' => 'admin.subjects.index',
                'permission' => 'manage subjects',
            ],
            [
                'id' => 'classrooms',
                'label' => ['en' => 'Classrooms', 'ar' => 'الفصول الدراسية'],
                'route' => 'admin.classrooms.index',
                'permission' => 'manage classrooms',
            ],
            [
                'id' => 'grades',
                'label' => ['en' => 'Grades / Levels', 'ar' => 'المراحل الدراسية'],
                'route' => 'admin.grades.index',
                'permission' => 'manage grades',
            ],
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
                'label' => ['en' => 'Curriculum Mgmt', 'ar' => 'إدارة المناهج'],
                'route' => 'admin.curriculum.management',
                'icon' => 'book',
            ],
            [
                'id' => 'academic_calendar',
                'label' => ['en' => 'Academic Calendar', 'ar' => 'التقويم الدراسي'],
                'route' => 'admin.academic_calendar.index',
                'icon' => 'event_note',
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
                'id' => 'teachers_import',
                'label' => ['en' => 'Import Teachers', 'ar' => 'استيراد المعلمين'],
                'route' => 'teachers.import',
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
                'id' => 'questions',
                'label' => ['en' => 'Question Bank', 'ar' => 'بنك الأسئلة'],
                'route' => 'questions.index1',
                'permission' => 'manage questions',
             ],
             [
                'id' => 'behaviors',
                'label' => ['en' => 'Behavior Management', 'ar' => 'إدارة السلوك'],
                'route' => 'admin.behaviors',
                'permission' => 'manage behaviors',
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
                'id' => 'branding',
                'label' => ['en' => 'School Branding', 'ar' => 'العلامة التجارية'],
                'route' => 'admin.school-branding.index',
                'permission' => 'manage settings',
             ],
            [
                'id' => 'settings',
                'label' => ['en' => 'Global Settings', 'ar' => 'الإعدادات العامة'],
                'route' => 'admin.settings.index',
                'permission' => 'view settings',
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
