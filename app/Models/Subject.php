<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'nour_name',
        'nour_id',
        'description',
        'active',
        'notes',
        'school_id',
        'color_bg',
        'color_text',
        'lesson_plan_templates'
    ];

    protected $casts = [
        'active' => 'boolean',
        'lesson_plan_templates' => 'json'
    ];
    protected $table = 'subjects';

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

public function curricula()
{
    return $this->hasMany(Curriculum::class);
}
}
