<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseManagement\LessonPlanTemplate;
use App\Models\User;

class DefaultLessonSectionTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        
        LessonPlanTemplate::create([
            'name' => 'Standard Lesson Sections',
            'is_active' => true,
            'created_by' => $user?->id ?? 1,
            'structure' => [
                'sections' => [
                    [
                        'id' => 'objectives',
                        'title' => 'Objectives',
                        'icon' => '🎯',
                        'qIcon' => 'flag',
                        'bg' => '#fffbeb',
                        'bgActive' => '#fef3c7',
                        'borderColor' => '#f59e0b',
                        'textColor' => '#92400e'
                    ],
                    [
                        'id' => 'warmup',
                        'title' => 'Warm-Up',
                        'icon' => '🔥',
                        'qIcon' => 'whatshot',
                        'bg' => '#fff7ed',
                        'bgActive' => '#fed7aa',
                        'borderColor' => '#ea580c',
                        'textColor' => '#9a3412'
                    ],
                    [
                        'id' => 'learn',
                        'title' => 'Learn',
                        'icon' => '📚',
                        'qIcon' => 'menu_book',
                        'bg' => '#eff6ff',
                        'bgActive' => '#dbeafe',
                        'borderColor' => '#3b82f6',
                        'textColor' => '#1e40af'
                    ],
                    [
                        'id' => 'practice',
                        'title' => 'Practice',
                        'icon' => '✍️',
                        'qIcon' => 'edit_note',
                        'bg' => '#faf5ff',
                        'bgActive' => '#e9d5ff',
                        'borderColor' => '#a855f7',
                        'textColor' => '#6b21a8'
                    ],
                    [
                        'id' => 'homework',
                        'title' => 'Homework',
                        'icon' => '📖',
                        'qIcon' => 'assignment',
                        'bg' => '#eef2ff',
                        'bgActive' => '#c7d2fe',
                        'borderColor' => '#6366f1',
                        'textColor' => '#3730a3'
                    ],
                    [
                        'id' => 'quiz',
                        'title' => 'Quiz',
                        'icon' => '📝',
                        'qIcon' => 'quiz',
                        'bg' => '#f0fdf4',
                        'bgActive' => '#bbf7d0',
                        'borderColor' => '#22c55e',
                        'textColor' => '#15803d'
                    ]
                ]
            ]
        ]);
    }
}
