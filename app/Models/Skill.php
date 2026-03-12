<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'difficulty_min',
        'difficulty_max',
        'mastery_threshold',
        'estimated_time_minutes',
        'is_active'
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(SkillCategory::class, 'category_id');
    }

    public function questions()
    {
        return $this->belongsToMany(
            QuQuestion::class, 
            'skill_questions', 
            'skill_id', 
            'qu_question_id'
        )->withPivot(['difficulty_level', 'explanation']);
    }

    public function userProgress()
    {
        return $this->hasMany(UserSkillProgress::class);
    }

    public function practiceSessions()
    {
        return $this->hasMany(SkillPracticeSession::class);
    }

    public function awards()
    {
        return $this->hasMany(SkillAward::class);
    }

    public function curriculumLessons(): MorphToMany
    {
        return $this->morphedByMany(\App\Models\my_class\Curriculums\CurriculumLesson::class, 'skillable');
    }

    // Methods
    public function getQuestionsAtDifficulty($level)
    {
        return $this->questions()->wherePivot('difficulty_level', $level)->get();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeForGradeLevel($query, $gradeId)
    {
        return $query->whereHas('category', function($q) use ($gradeId) {
            $q->where('grade_id', $gradeId);
        });
    }
}