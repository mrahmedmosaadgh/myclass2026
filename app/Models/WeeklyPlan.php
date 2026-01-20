<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'academic_year_id',
        'semester_number',
        'week_number',
        'schedule_id',
        'cst_id',
        'cw',
        'hw',
        'notes',
        'comments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'semester_number' => 'integer',
        'week_number' => 'integer',
    ];

    /**
     * Appended attributes
     */
    protected $appends = ['status'];

    /**
     * Get computed status based on CW/HW content
     * 
     * @return string 'empty' | 'partial' | 'completed'
     */
    public function getStatusAttribute(): string
    {
        $hasCw = !empty(trim($this->cw ?? ''));
        $hasHw = !empty(trim($this->hw ?? ''));

        if (!$hasCw && !$hasHw) {
            return 'empty';
        }

        if ($hasCw && $hasHw) {
            return 'completed';
        }

        return 'partial';
    }

    /**
     * Get the academic year that owns the weekly plan.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the schedule that owns the weekly plan.
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // copy() relationship removed

    /**
     * Get the classroom subject teacher that owns the weekly plan.
     */
    public function classroomSubjectTeacher()
    {
        return $this->belongsTo(ClassroomSubjectTeacher::class, 'cst_id');
    }

    /**
     * Get the sessions for the weekly plan.
     */
    public function sessions()
    {
        return $this->hasMany(WeeklyPlanSession::class)->orderBy('session_index');
    }
}