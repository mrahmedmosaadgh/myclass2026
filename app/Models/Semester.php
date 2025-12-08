<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'semester_number',
        'total_weeks',
        'start_date',
        'end_date',
        'academic_year_id',
        'school_id',
        'data',
        'active'
    ];

    protected $casts = [
        'data' => 'array',
        'active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }
}