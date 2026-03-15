<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrStudentPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'year_id',
        'student_id',
        'session_id',
        'date',
        'period_code',
        'attendance_status',
        'attendance_score',
        'attendance_note',
        'total_score',
        'locked',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance_score' => 'integer',
        'total_score' => 'integer',
        'locked' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'year_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CrSession::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(CrScore::class, 'student_period_id');
    }
}
