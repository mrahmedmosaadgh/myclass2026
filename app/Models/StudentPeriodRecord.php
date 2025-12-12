<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPeriodRecord extends Model
{
    protected $fillable = [
        'period_activity_id',
        'student_id',
        'attendance_status',
        'late_minutes',
        'homework_completed',
        'homework_score',
        'behavior_plus_marks',
        'behavior_minus_marks',
        'behavior_notes',
        'participation_score',
        'participation_notes'
    ];

    protected $casts = [
        'late_minutes' => 'integer',
        'homework_completed' => 'boolean',
        'homework_score' => 'decimal:2',
        'behavior_plus_marks' => 'integer',
        'behavior_minus_marks' => 'integer',
        'participation_score' => 'integer'
    ];

    public function periodActivity(): BelongsTo
    {
        return $this->belongsTo(PeriodActivity::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
