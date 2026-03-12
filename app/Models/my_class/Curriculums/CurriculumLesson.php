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
        'curriculum_version_id',
        'topic_id',
        'lesson_number',
        'lesson_title',
        'page_number',
        'description',
        'standard',
        'strand',
        'content',
        'activities',
        'assignment',
        'assessment',
        'data',
        'type'
    ];

    protected $casts = [
        'page_number' => 'integer',
        'data' => 'json'
    ];

    // Relationships
    public function curriculumVersion(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CurriculumVersion::class, 'curriculum_version_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CurriculumTopic::class, 'topic_id');
    }

    public function curriculum()
    {
        return $this->curriculumVersion ? $this->curriculumVersion->curriculum() : null;
    }

    public function lessonPlans(): HasMany
    {
        return $this->hasMany(CurriculumLessonPlan::class);
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'curriculum_lessons_id');
    }

    public function skills()
    {
        return $this->morphToMany(\App\Models\Skill::class, 'skillable');
    }

    // Scopes
    public function scopeForVersion($query, $versionId)
    {
        return $query->where('curriculum_version_id', $versionId);
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

