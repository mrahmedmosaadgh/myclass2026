<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\free\LessonPresentation;
use App\Models\Student;
use App\Models\Teacher;

class LessonStudentProgress extends Model
{
    use HasFactory;

    protected $table = 'lesson_student_progress';

    protected $fillable = [
        'lesson_presentation_id',
        'student_id',
        'opened_by_teacher_id',
        'status',
        'color_status',
        'learn_completed_at',
        'practice_score',
        'practice_submitted_at',
        'practice_graded_at',
        'quiz_attempts',
        'quiz_best_score',
        'quiz_passed',
        'force_passed',
        'opened_at',
        'practice_data',
        'quiz_data',
        'metadata',
    ];

    protected $casts = [
        'learn_completed_at' => 'datetime',
        'practice_submitted_at' => 'datetime',
        'practice_graded_at' => 'datetime',
        'opened_at' => 'datetime',
        'quiz_passed' => 'boolean',
        'force_passed' => 'boolean',
        'practice_data' => 'array',
        'quiz_data' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function lesson()
    {
        return $this->belongsTo(LessonPresentation::class, 'lesson_presentation_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function openedByTeacher()
    {
        return $this->belongsTo(Teacher::class, 'opened_by_teacher_id');
    }

    public function practiceSubmissions()
    {
        return $this->hasMany(LessonPracticeSubmission::class);
    }

    // Helper Methods
    public function isLocked()
    {
        return $this->status === 'locked';
    }

    public function isCompleted()
    {
        return $this->status === 'completed' || $this->force_passed;
    }

    public function canAccessPractice()
    {
        return $this->learn_completed_at !== null;
    }

    public function canAccessQuiz()
    {
        return $this->practice_score !== null && $this->practice_score >= 6;
    }

    public function hasFailedAllAttempts()
    {
        return $this->quiz_attempts >= 3 && !$this->quiz_passed;
    }

    public function calculateColorStatus()
    {
        if (!$this->opened_at) return 'gray';
        if ($this->force_passed) return 'green';
        if (!$this->learn_completed_at) return 'light_blue';
        if ($this->status === 'learning') return 'blue';
        if ($this->status === 'practice_submitted' && !$this->practice_score) return 'purple';
        
        if ($this->quiz_passed) {
            if ($this->quiz_attempts === 1) return 'green';
            if ($this->quiz_attempts === 2) return 'yellow';
            if ($this->quiz_attempts === 3) return 'dark_yellow';
            if ($this->quiz_attempts >= 4) return 'orange';
        }
        
        if ($this->quiz_attempts >= 3 && !$this->quiz_passed) return 'red';
        
        return 'blue';
    }

    public function updateColorStatus()
    {
        $this->color_status = $this->calculateColorStatus();
        $this->save();
    }
}
