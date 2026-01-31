<?php

return [
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
];
