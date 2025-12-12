<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentBehaviorsMain extends Model
{
    protected $table = 'student_behaviors_mains';

    protected $fillable = [
        'school_id',
        'year_id',
        'student_id',
        'teacher_id',
        'subject_id',
        'classroom_id',
        'period_code_main',
        'period_code',
        'date',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function behaviors(): HasMany
    {
        return $this->hasMany(StudentBehavior::class, 'student_behaviors_mains_id');
    }
}
