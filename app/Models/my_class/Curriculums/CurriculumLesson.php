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
        'curriculum_id',
        'selected',
        'topic_number',
        'topic_title',
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
        'notes_admin',
        'notes_teacher',
        'objective',
        'data',
        'type'
    ];

    protected $casts = [
        'selected' => 'integer',
        'page_number' => 'integer',
        'data' => 'json'
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
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
    public function scopeSelected($query)
    {
        return $query->where('selected', 1);
    }

    public function scopeForCurriculum($query, $curriculumId)
    {
        return $query->where('curriculum_id', $curriculumId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function isSelected(): bool
    {
        return $this->selected === 1;
    }

    public function select()
    {
        $this->update(['selected' => 1]);
    }

    public function deselect()
    {
        $this->update(['selected' => 0]);
    }
}
