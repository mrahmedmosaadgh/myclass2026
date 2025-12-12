<?php

namespace App\Models;

use App\Models\my_class\Curriculums\CurriculumLesson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'subject_id',
        'curriculum_id',
        'curriculum_lessons_id',
        'title',
        'body',
        'options',
        'correct_answer',
        'resources',
        'type',
        'score',
        'difficulty',
        'notes',
        'tags',
        'status',
        'author',
        'source',
        'metadata',
        'notes_admin',
        'notes_teacher',
        'explanation',
        'question_data',
        'created_by_id'
    ];

    protected $casts = [
        'options' => 'json',
        'resources' => 'json',
        'metadata' => 'json',
        'explanation' => 'json',
        'question_data' => 'json',
        'score' => 'integer'
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function curriculumLesson(): BelongsTo
    {
        return $this->belongsTo(CurriculumLesson::class, 'curriculum_lessons_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'created_by_id');
    }

    // Scopes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'mcq';
    }

    public function isTrueFalse(): bool
    {
        return $this->type === 'true_false';
    }

    public function isFillBlank(): bool
    {
        return $this->type === 'fill_blank';
    }
}



