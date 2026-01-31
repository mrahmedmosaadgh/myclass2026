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
                'route' => 'teacher.timeline', 
                'icon' => 'calendar_today',
            ],
            [
                'id' => 'assignments',
                'label' => ['en' => 'Assignments', 'ar' => 'الواجبات'],
                'route' => 'teacher.my-weekly-plans',
                'icon' => 'assignment',
            ],
             [
                'id' => 'courses',
                'label' => ['en' => 'My Courses', 'ar' => 'دوراتي'],
                'route' => 'teacher.courses', // Found in grep
                'icon' => 'menu_book',
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
             [
                'id' => 'progress',
                'label' => ['en' => 'Progress Report', 'ar' => 'تقرير التقدم'],
                'route' => 'teacher.progress', // Found in grep
                'icon' => 'trending_up',
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
                'route' => 'qu-exams.index',
                'icon' => 'description',
            ],
            [
                'id' => 'questions',
                'label' => ['en' => 'Question Bank', 'ar' => 'بنك الأسئلة'],
                'route' => 'qu-questions.index',
                'icon' => 'storage',
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
