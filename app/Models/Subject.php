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
        'lesson_plan_templates', // DEPRECATED: Use lessonPlanTemplates() relationship instead
        'templates_migrated_at'
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

    /**
     * Get lesson plan templates for this subject (database-backed).
     * This replaces the legacy JSON templates stored in lesson_plan_templates column.
     */
    public function lessonPlanTemplates()
    {
        return $this->hasMany(\App\Models\CourseManagement\LessonPlanTemplate::class, 'subject_id');
    }
}
