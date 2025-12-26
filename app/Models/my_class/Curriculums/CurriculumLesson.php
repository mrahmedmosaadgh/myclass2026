<?php

namespace App\Models\my_class\Curriculums;

use App\Models\Curriculum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\School;

class CurriculumLesson extends Model
{
    protected $fillable = [
        'school_id',
        'topic_id',
        'lesson_number',
        'lesson_title',
        'page_number',
        'description',
        'standard',
        'strand',
        'content',
        'skill',
        'activities',
        'assignment',
        'assessment',
        'objective',
        'data',
        'type'
    ];

    protected $casts = [
        'page_number' => 'integer',
        'data' => 'json'
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CurriculumTopic::class, 'topic_id');
    }

    public function curriculum()
    {
        return $this->topic->curriculum();
    }

    public function lessonPlans(): HasMany
    {
        return $this->hasMany(CurriculumLessonPlan::class);
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'curriculum_lessons_id');
    }

    // Scopes
    public function scopeForCurriculum($query, $curriculumId)
    {
        return $query->whereHas('topic', function ($q) use ($curriculumId) {
            $q->where('curriculum_id', $curriculumId);
        });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithTopic($query)
    {
        return $query->with('topic');
    }

    // Helper methods
    public function isOfType($type): bool
    {
        return $this->type === $type;
    }
}

