<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question_type_id',
        'question_text',
        'grade_id',
        'subject_id',
        'topic_id',
        'bloom_level',
        'difficulty_level',
        'estimated_time_sec',
        'author_id',
        'status',
        'usage_count',
        'avg_success_rate',
        'discrimination_index',
        'hints',
        'explanation',
    ];

    protected $casts = [
        'bloom_level' => 'integer',
        'difficulty_level' => 'integer',
        'estimated_time_sec' => 'integer',
        'usage_count' => 'integer',
        'avg_success_rate' => 'decimal:2',
        'discrimination_index' => 'decimal:2',
        'hints' => 'array',
        'explanation' => 'array',
    ];

    /**
     * Get the question type for this question.
     */
    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }

    /**
     * Get the options for this question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order_index');
    }

    /**
     * Get the grade for this question.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }
    
    /**
     * Alias for grade() to maintain backward compatibility.
     */
    public function gradeLevel(): BelongsTo
    {
        return $this->grade();
    }

    /**
     * Get the subject for this question.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the topic for this question.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Get the author (user) who created this question.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope a query to filter by question type.
     */
    public function scopeByType(Builder $query, int $questionTypeId): Builder
    {
        return $query->where('question_type_id', $questionTypeId);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include active questions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by grade.
     */
    public function scopeByGrade(Builder $query, int $gradeId): Builder
    {
        return $query->where('grade_id', $gradeId);
    }
    
    /**
     * Alias for scopeByGrade() to maintain backward compatibility.
     */
    public function scopeByGradeLevel(Builder $query, int $gradeLevelId): Builder
    {
        return $this->scopeByGrade($query, $gradeLevelId);
    }

    /**
     * Scope a query to filter by subject.
     */
    public function scopeBySubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }
}
