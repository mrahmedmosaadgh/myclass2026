<?php

return [
    /**
     * Predefined modules for menu organization
     * Admins can only select from these modules
     */
    'modules' => [
        'academics',
        'attendance',
        'administration',
        'course-management',
        'weekly-plans',
        'curriculum',
        'reports',
        'settings',
        'developer',
        'super-system',
        'system-admin',
        'school-admin',
    ],

    /**
     * Maximum menu depth (parent + children = 2 levels)
     */
    'max_depth' => 2,

    /**
     * Cache TTL in seconds (1 hour)
     */
    'cache_ttl' => 3600,

    /*
    |--------------------------------------------------------------------------
    | Menu Structures by Role
    |--------------------------------------------------------------------------
    |
    | Define menu structures for each role. Each item requires:
    | - id: unique identifier
    | - label: array with 'en' and 'ar' translations
    | - route: Laravel named route
    | - icon: Material Icon name
    | - permission (optional): if not set, defaults to route name
    |
    */

    'teacher' => [
        [
            'id' => 'dashboard',
            'label' => ['en' => 'Dashboard', 'ar' => 'لوحة القيادة'],
            'route' => 'teacher.dashboard',
            'icon' => 'dashboard',
        ],
        [
            'id' => 'schedule',
            'label' => ['en' => 'My Schedule', 'ar' => 'جدولي'],
            'route' => 'teacher.schedule',
            'icon' => 'calendar_today',
        ],
        [
            'id' => 'classes',
            'label' => ['en' => 'My Classes', 'ar' => 'فصولي'],
            'route' => 'teacher.classes.index',
            'icon' => 'school',
        ],
        [
            'id' => 'students',
            'label' => ['en' => 'Students', 'ar' => 'الطلاب'],
            'route' => 'teacher.students.index',
            'icon' => 'people',
            'permission' => 'teacher.students.view',
        ],
        [
            'id' => 'exams',
            'label' => ['en' => 'Exams & Quizzes', 'ar' => 'الاختبارات'],
            'route' => 'teacher.exams.index',
            'icon' => 'quiz',
        ],
        [
            'id' => 'lessons',
            'label' => ['en' => 'Lessons', 'ar' => 'الدروس'],
            'route' => 'teacher.lessons.index',
            'icon' => 'book',
        ],
        [
            'id' => 'rewards',
            'label' => ['en' => 'Reward System', 'ar' => 'نظام المكافآت'],
            'route' => 'teacher.rewards.index',
            'icon' => 'emoji_events',
        ],
    ],

    'student' => [
        [
            'id' => 'dashboard',
            'label' => ['en' => 'Dashboard', 'ar' => 'لوحة القيادة'],
            'route' => 'student.dashboard',
            'icon' => 'dashboard',
        ],
        [
            'id' => 'schedule',
            'label' => ['en' => 'My Schedule', 'ar' => 'جدولي'],
            'route' => 'student.schedule',
            'icon' => 'calendar_today',
        ],
        [
            'id' => 'exams',
            'label' => ['en' => 'My Exams', 'ar' => 'امتحاناتي'],
            'route' => 'student.exams.index',
            'icon' => 'quiz',
        ],
        [
            'id' => 'grades',
            'label' => ['en' => 'My Grades', 'ar' => 'درجاتي'],
            'route' => 'student.grades.index',
            'icon' => 'grade',
        ],
        [
            'id' => 'rewards',
            'label' => ['en' => 'My Rewards', 'ar' => 'مكافآتي'],
            'route' => 'student.rewards.index',
            'icon' => 'emoji_events',
        ],
    ],

    'admin' => [
        [
            'id' => 'dashboard',
            'label' => ['en' => 'Dashboard', 'ar' => 'لوحة القيادة'],
            'route' => 'admin.dashboard',
            'icon' => 'dashboard',
        ],
        [
            'id' => 'schools',
            'label' => ['en' => 'Schools', 'ar' => 'المدارس'],
            'route' => 'admin.schools.index',
            'icon' => 'account_balance',
            'permission' => 'admin.schools.view',
        ],
        [
            'id' => 'users',
            'label' => ['en' => 'Users', 'ar' => 'المستخدمون'],
            'route' => 'admin.users.index',
            'icon' => 'people',
            'permission' => 'admin.users.view',
        ],
        [
            'id' => 'menus',
            'label' => ['en' => 'Menu Management', 'ar' => 'إدارة القوائم'],
            'route' => 'admin.menus.index',
            'icon' => 'menu',
            'permission' => 'admin.menus.manage',
        ],
        [
            'id' => 'settings',
            'label' => ['en' => 'System Settings', 'ar' => 'إعدادات النظام'],
            'route' => 'admin.settings.index',
            'icon' => 'settings',
            'permission' => 'admin.settings.manage',
        ],
    ],

    'parent' => [
        [
            'id' => 'dashboard',
            'label' => ['en' => 'Dashboard', 'ar' => 'لوحة القيادة'],
            'route' => 'parent.dashboard',
            'icon' => 'dashboard',
        ],
        [
            'id' => 'children',
            'label' => ['en' => 'My Children', 'ar' => 'أبنائي'],
            'route' => 'parent.children.index',
            'icon' => 'family_restroom',
        ],
        [
            'id' => 'attendance',
            'label' => ['en' => 'Attendance', 'ar' => 'الحضور'],
            'route' => 'parent.attendance.index',
            'icon' => 'check_circle',
        ],
        [
            'id' => 'grades',
            'label' => ['en' => 'Grades', 'ar' => 'الدرجات'],
            'route' => 'parent.grades.index',
            'icon' => 'grade',
        ],
    ],
];
