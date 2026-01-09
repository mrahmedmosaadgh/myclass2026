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
];
