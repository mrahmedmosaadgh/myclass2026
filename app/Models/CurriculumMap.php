<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'subject_id',
        'grade_id',
        'teacher_id',
        'curriculum_id',
        'title',
        'description',
        'weekly_plan',
        'status',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'weekly_plan' => 'json',
        'status' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    // Scopes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
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

    public function activate()
    {
        $this->update(['status' => 1]);
    }

    public function complete()
    {
        $this->update(['status' => 2]);
    }
}
