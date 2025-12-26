<?php

namespace App\Models\my_class\Curriculums;

use App\Models\Curriculum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumTopic extends Model
{
    protected $fillable = [
        'curriculum_id',
        'number',
        'title',
        'description'
    ];

    // Relationships
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CurriculumLesson::class, 'topic_id')->orderBy('lesson_number');
    }

    // Scopes
    public function scopeForCurriculum($query, $curriculumId)
    {
        return $query->where('curriculum_id', $curriculumId);
    }

    // Helper methods
    public function getLessonCount(): int
    {
        return $this->lessons()->count();
    }
}
