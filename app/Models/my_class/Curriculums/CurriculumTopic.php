<?php

namespace App\Models\my_class\Curriculums;

use App\Models\Curriculum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumTopic extends Model
{
    protected $fillable = [
        'curriculum_version_id',
        'number',
        'title',
        'description'
    ];

    // Relationships
    public function curriculumVersion(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CurriculumVersion::class, 'curriculum_version_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CurriculumLesson::class, 'topic_id')->orderBy('lesson_number');
    }

    // Scopes
    public function scopeForVersion($query, $versionId)
    {
        return $query->where('curriculum_version_id', $versionId);
    }

    // Helper methods
    public function getLessonCount(): int
    {
        return $this->lessons()->count();
    }
}
