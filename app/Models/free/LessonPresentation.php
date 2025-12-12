<?php

namespace App\Models\free;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\School;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\LessonStudentProgress;

class LessonPresentation extends Model
{
    use HasFactory;

    public const SECTIONS = [
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
    ];

    protected $fillable = [
        'school_id',
        'teacher_id',
        'subject_id',
        'grade_id',
        'name',
        'description',
        'order',
        'quiz_id',
        'is_active',
        'lesson_plan_template_id',
        'template_snapshot',
        'is_template_applied',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'template_snapshot' => 'array',
        'is_template_applied' => 'boolean',
    ];

    public function slides()
    {
        return $this->hasMany(LessonPresentationSlide::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function lessonPlanTemplate()
    {
        return $this->belongsTo(\App\Models\CourseManagement\LessonPlanTemplate::class, 'lesson_plan_template_id');
    }

    public function studentProgress()
    {
        return $this->hasMany(LessonStudentProgress::class);
    }

    // Get progress for a specific student
    public function getProgressForStudent($studentId)
    {
        return $this->studentProgress()->where('student_id', $studentId)->first();
    }
}
