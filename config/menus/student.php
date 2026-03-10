<?php

return [
    [
        'id' => 'dashboard',
        'label' => ['en' => 'Dashboard', 'ar' => 'لوحة القيادة'],
        'route' => 'student.home',
        'icon' => 'dashboard',
    ],
    [
        'id' => 'schedule',
        'label' => ['en' => 'My Schedule', 'ar' => 'جدولي'],
        'route' => 'student.schedule', // or student.schedule.index, need to verify which one is consistent in r_student
        'icon' => 'calendar_today',
    ],
    [
        'id' => 'grades',
        'label' => ['en' => 'My Grades', 'ar' => 'درجاتي'],
        'route' => 'student.grades',
        'icon' => 'grade',
    ],
    [
        'id' => 'attendance',
        'label' => ['en' => 'Attendance', 'ar' => 'الغياب'],
        'route' => 'student.attendance',
        'icon' => 'check_circle',
    ],
    [
        'id' => 'exams',
        'label' => ['en' => 'My Exams', 'ar' => 'اختباراتي'],
        'route' => 'qu-student.exams.index',
        'icon' => 'assignment',
    ],
    [
        'id' => 'conversations',
        'label' => ['en' => 'Messages', 'ar' => 'الرسائل'],
        'route' => 'student.communication.messages.index',
        'icon' => 'chat',
    ],
];
