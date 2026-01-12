<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Calendar extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'date',
        'semester_id',
        'academic_year_id',
        'school_id',
        'status',
        'vacation',
        'vacation_students',
        'event',
        'event_academic',
        'week_number',
        'day_number',
        'data'
    ];

    protected $casts = [
        'date' => 'date',
        'data' => 'json'
    ];

    /**
     * Dynamic accessors for computed fields
     */
    protected $appends = [
        'day_name',
        'day_number',
        'week_of_semester',
        'semester_number',
        'status_label'
    ];

    /**
     * Boot method to auto-sync semester fields
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            $model->syncSemesterFields();
        });
    }

    /**
     * Automatically sync semester_id and academic_year_id
     * 
     * This ensures data integrity by:
     * - Auto-filling academic_year_id when semester_id is set
     * - Validating consistency between both fields
     */
    public function syncSemesterFields()
    {
        if ($this->semester_id) {
            $semester = Semester::find($this->semester_id);
            if ($semester) {
                $this->academic_year_id = $semester->academic_year_id;
            }
        }
    }

    /**
     * Relationships
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function periodActivities(): HasMany
    {
        return $this->hasMany(PeriodActivity::class);
    }

    /**
     * Dynamic Accessors (computed fields)
     */
    
    /**
     * Get the semester number (business meaning)
     * Dynamically retrieved from the related semester
     */
    public function getSemesterNumberAttribute()
    {
        return $this->semester?->semester_number;
    }

    /**
     * Get the day name (Monday, Tuesday, etc.)
     * Computed from the date field
     */
    public function getDayNameAttribute()
    {
        return Carbon::parse($this->date)->format('l');
    }

    /**
     * Get the day number (1 = Monday, 7 = Sunday)
     * Computed from the date field using ISO-8601
     */
    public function getDayNumberAttribute()
    {
        return Carbon::parse($this->date)->dayOfWeekIso;
    }

    /**
     * Get the week number within the semester
     * Computed based on semester start_date
     */
    public function getWeekOfSemesterAttribute()
    {
        if (!$this->semester || !$this->semester->start_date) {
            return null;
        }

        return Carbon::parse($this->date)
            ->diffInWeeks(Carbon::parse($this->semester->start_date)) + 1;
    }

    /**
     * Get the status label (human-readable)
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            0 => 'Day Off',
            1 => 'Work Day',
            2 => 'Activity',
            3 => 'Test',
            4 => 'Final Exam',
            default => 'Unknown',
        };
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}


