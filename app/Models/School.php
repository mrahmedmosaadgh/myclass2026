<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'section',
        'section_ar',
        'h_r_id',
        'data',
        'is_active',
        'academic_year_id',
        'semester_id',
        'schedule_copy_id',
        'weekly_plan_settings',
        'resolved_by',
        'resolved_at',
        'week_number',
        'locked',
        'weekly_settings'
    ];

    protected $casts = [
        'data' => 'array',
        'weekly_plan_settings' => 'array',
        'weekly_settings' => 'array',
        'is_active' => 'boolean',
        'week_number' => 'integer',
        'locked' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function hr()
    {
        return $this->belongsTo(HR::class, 'h_r_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function parents()
    {
        return $this->hasMany(StudentParent::class);
    }

    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function academic_years()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function activeAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function activeSemester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function activeScheduleCopy()
    {
        return $this->belongsTo(ScheduleCopy::class, 'schedule_copy_id');
    }
    
    public function scheduleCopies()
    {
        return $this->hasMany(ScheduleCopy::class);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}