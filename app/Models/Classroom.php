<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'name',
        'capacity',
        'stage_id',
        'grade_id',
        'school_id',
    ];

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'classroom_subject_teachers')
                    ->withPivot('subject_id', 'classes_per_week', 'data', 'c_text', 'c_bg', 'color_custom', 'color_custom_text')
                    ->withTimestamps();
    }
}