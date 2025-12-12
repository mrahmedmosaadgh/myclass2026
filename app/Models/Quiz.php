<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'school_id',
        'subject_id',
        'grade_id',
        'created_by_id',
        'status',
        'time_limit_minutes',
        'shuffle_questions',
        'shuffle_options',
        'allow_review',
        'metadata',
    ];

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'allow_review' => 'boolean',
        'time_limit_minutes' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the school that owns the quiz.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the subject for this quiz.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the grade for this quiz.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the user who created this quiz.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the questions for this quiz.
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_question')
            ->withPivot('order_index')
            ->withTimestamps()
            ->orderBy('quiz_question.order_index');
    }

    /**
     * Scope a query to only include active quizzes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by school.
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope a query to filter by grade.
     */
    public function scopeForGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    /**
     * Scope a query to filter by subject.
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Get the question count for this quiz.
     */
    public function getQuestionCountAttribute(): int
    {
        return $this->questions()->count();
    }
}
