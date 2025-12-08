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
        'cst_id',
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
     * Get the academic year that owns the weekly plan.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

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