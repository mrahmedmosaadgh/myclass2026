<?php

return [
    [
        'id' => 'dashboard',
        'label' => ['en' => 'Dashboard', 'ar' => 'لوحة القيادة'],
        'route' => 'teacher.home',
        'icon' => 'dashboard',
    ],
    // --- Academics ---
    [
        'id' => 'classes_group',
        'label' => ['en' => 'Academics', 'ar' => 'الأكاديمية'],
        'icon' => 'school',
        'children' => [
            [
                'id' => 'classes',
                'label' => ['en' => 'My Classes', 'ar' => 'فصولي'],
                'route' => 'teacher.classes',
                'icon' => 'class',
            ],
            [
                'id' => 'schedule',
                'label' => ['en' => 'My Schedule', 'ar' => 'جدولي'],
                'route' => 'schedules.teacher.my-schedule', 
                'icon' => 'calendar_today',
            ],
            [
                'id' => 'weekly_system_v1',
                'label' => ['en' => 'Weekly System V1', 'ar' => 'نظام الأسبوع الجديد'],
                'route' => 'weekly-system-v1.dashboard',
                'icon' => 'view_week',
            ],
            [
                'id' => 'weekly_plans',
                'label' => ['en' => 'Weekly Plans', 'ar' => 'الخطط الأسبوعية'],
                'route' => 'teacher.planning.weekly-plans.index',
                'icon' => 'assignment',
            ],
            [
                'id' => 'daily_tasks',
                'label' => ['en' => 'Daily Tasks', 'ar' => 'المهام اليومية'],
                'route' => 'teacher.planning.daily-tasks.index',
                'icon' => 'task',
            ],
        ]
    ],
    
    // --- Student Performance ---
    [
        'id' => 'performance_group',
        'label' => ['en' => 'Performance', 'ar' => 'الأداء'],
        'icon' => 'assessment',
        'children' => [
            [
                'id' => 'attendance',
                'label' => ['en' => 'Attendance', 'ar' => 'الغياب'],
                'route' => 'teacher.attendance',
                'icon' => 'check_circle',
            ],
            [
                'id' => 'grades',
                'label' => ['en' => 'Grades', 'ar' => 'الدرجات'],
                'route' => 'teacher.grades',
                'icon' => 'grade',
            ],
        ]
    ],
    
    // --- Teaching Tools ---
    [
        'id' => 'tools_group',
        'label' => ['en' => 'Teaching Tools', 'ar' => 'أدوات التدريس'],
        'icon' => 'build',
        'children' => [
            [
                'id' => 'lesson_presentation',
                'label' => ['en' => 'Lesson Presentation', 'ar' => 'عرض الدرس'],
                'route' => 'teacher.lesson_presentation', // or teacher.presentation
                'icon' => 'slideshow',
            ],
             [
                'id' => 'exams',
                'label' => ['en' => 'Exams', 'ar' => 'الامتحانات'],
                'route' => 'qu.exams.index',
                'icon' => 'description',
            ],
            [
                'id' => 'questions',
                'label' => ['en' => 'Question Bank', 'ar' => 'بنك الأسئلة'],
                'route' => 'qu.questions.index',
                'icon' => 'storage',
            ],
            [
                'id' => 'vocabulary',
                'label' => ['en' => 'Vocabulary Flashcards', 'ar' => 'البطاقات التعليمية للمفردات'],
                'route' => 'teacher.tools.vocabulary.index',
                'icon' => 'style',
            ],
        ]
    ],

    // --- Communication ---
    [
        'id' => 'communication_group',
        'label' => ['en' => 'Communication', 'ar' => 'التواصل'],
        'icon' => 'chat',
        'children' => [
            [
                'id' => 'conversations',
                'label' => ['en' => 'Messages', 'ar' => 'الرسائل'],
                'route' => 'conversations.index',
                'icon' => 'chat',
            ],
        ]
    ],
];
