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

    'teacher' => require __DIR__ . '/menus/teacher.php',

    'student' => require __DIR__ . '/menus/student.php',

    'admin' => require __DIR__ . '/menus/admin.php',
    'super_admin' => require __DIR__ . '/menus/admin.php',
    'parent' => require __DIR__ . '/menus/parent.php',
];
