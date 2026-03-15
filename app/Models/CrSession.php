<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'year_id',
        'teacher_id',
        'classroom_id',
        'subject_id',
        'date',
        'day_number',
        'period_number',
        'period_code',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'day_number' => 'integer',
        'period_number' => 'integer',
        'status' => 'string',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'year_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function studentPeriods(): HasMany
    {
        return $this->hasMany(CrStudentPeriod::class);
    }
}
