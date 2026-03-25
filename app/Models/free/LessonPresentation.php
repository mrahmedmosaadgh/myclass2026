<?php

namespace App\Models\free;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CourseManagement\LessonPlanTemplate;
use App\Models\School;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\LessonStudentProgress;

class LessonPresentation extends Model
{
    use HasFactory;

    // Sections are now handled dynamically via database templates


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
        'sections',
        'lesson_plan_template_id',
        'template_snapshot',
        'is_template_applied',
    ];

    protected $casts = [
        'quiz_id' => 'integer',
        'sections' => 'array',
        'is_active' => 'boolean',
        'template_snapshot' => 'array',
        'is_template_applied' => 'boolean',
    ];

    public function slides()
    {
        return $this->hasMany(LessonPresentationSlide::class);
    }

    public function getSections()
    {
        // If lesson has its own sections, return those
        if ($this->sections && is_array($this->sections)) {
            return $this->sections;
        }
        
        // Otherwise, get from active template
        $activeTemplate = LessonPlanTemplate::where('is_active', true)->first();
        if ($activeTemplate && isset($activeTemplate->structure['sections'])) {
            return $activeTemplate->structure['sections'];
        }
        
        // Ultimate fallback (empty array)
        return [];
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
