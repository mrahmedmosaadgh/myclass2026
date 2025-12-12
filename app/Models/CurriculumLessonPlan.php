<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\my_class\Curriculums\CurriculumLesson;

class CurriculumLessonPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'curriculum_lesson_id',
        'subject_id',
        'grade_id',
        'classroom_id',
        'teacher_id',
        'co_teacher_ids',
        'title',
        'page_number',
        'cw',
        'hw',
        'objectives',
        'materials',
        'plan',
        'status',
        'planned_date'
    ];

    protected $casts = [
        'co_teacher_ids' => 'json',
        'materials' => 'json',
        'plan' => 'json',
        'status' => 'integer',
        'planned_date' => 'date',
        'page_number' => 'integer'
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function curriculumLesson(): BelongsTo
    {
        return $this->belongsTo(CurriculumLesson::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    // Scopes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePlannedForDate($query, $date)
    {
        return $query->where('planned_date', $date);
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->status === 0;
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public function isCompleted(): bool
    {
        return $this->status === 2;
    }

    public function markAsActive()
    {
        $this->update(['status' => 1]);
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 2]);
    }
}
