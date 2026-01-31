<?php

return [
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
];
