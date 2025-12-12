<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodActivity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'schedule_id',
        'calendar_id',
        'teacher_id',
        'teacher_substitute_id',
        'teacher_present',
        'teacher_plan',
        'period_status',
        'event_id',
        'student_attendance',
        'student_behavior',
        'student_participation',
        'homework_records',
        'lesson_notes',
        'improvement_notes',
        'was_duty_period',
        'duty_notes',
        'created_by',
        'updated_by'
    ];

    /**
     * The teacher_plan field is structured as an object with the following properties:
     * - lesson_id: string - The ID of the lesson
     * - lesson_number: string - The lesson number
     * - title: string - The title of the lesson
     * - page_number: string - The page number of the lesson
     */
    protected $casts = [
        'teacher_present' => 'boolean',
        'teacher_plan' => 'object',
        'was_duty_period' => 'boolean',
        'student_attendance' => 'array',
        'student_behavior' => 'array',
        'student_participation' => 'array',
        'homework_records' => 'array'
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function substituteTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_substitute_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'updated_by');
    }

    public function studentRecords(): HasMany
    {
        return $this->hasMany(StudentPeriodRecord::class);
    }
}
