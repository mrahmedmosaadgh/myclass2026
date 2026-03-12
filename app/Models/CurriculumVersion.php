<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\my_class\Curriculums\CurriculumTopic;
use App\Models\my_class\Curriculums\CurriculumLesson;

class CurriculumVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_id',
        'title',
        'academic_year',
        'status',
        'version_number'
    ];

    // Relationships
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function topics()
    {
        return $this->hasMany(CurriculumTopic::class)->orderBy('number');
    }

    public function lessons()
    {
        return $this->hasMany(CurriculumLesson::class)->orderBy('topic_number', 'lesson_number');
    }

    public function maps(): HasMany
    {
        return $this->hasMany(CurriculumMap::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    // Business Logic Methods
    public function activate()
    {
        // Archive other versions for this curriculum
        static::where('curriculum_id', $this->curriculum_id)
              ->where('id', '!=', $this->id)
              ->where('status', 'active')
              ->update(['status' => 'archived']);

        $this->update(['status' => 'active']);
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
