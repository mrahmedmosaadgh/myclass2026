<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bm2QuestionBank Model
 * 
 * Represents a question in the question bank repository.
 */
class Bm2QuestionBank extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'bm2_questions_bank';

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'question_text',
        'context_description',
        'subject',
        'grade_level',
        'topic',
        'difficulty',
        'question_format',
        'options',
        'correct_answer',
        'explanation',
        'image_url',
        'visual_properties',
        'estimated_time_seconds',
        'points_default',
        'allows_calculator',
        'has_hint',
        'hints',
        'times_used',
        'success_rate',
        'discrimination_index',
        'is_active',
        'is_verified',
        'created_by',
    ];

    /**
     * Attributes that should be cast to native types.
     */
    protected $casts = [
        'options' => 'array',
        'visual_properties' => 'array',
        'hints' => 'array',
        'allows_calculator' => 'boolean',
        'has_hint' => 'boolean',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'times_used' => 'integer',
        'estimated_time_seconds' => 'integer',
        'points_default' => 'integer',
        'success_rate' => 'decimal:2',
        'discrimination_index' => 'decimal:2',
    ];

    /**
     * Get the user who created this question.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all assessment questions using this question.
     */
    public function assessmentQuestions(): HasMany
    {
        return $this->hasMany(Bm2AssessmentQuestion::class, 'question_bank_id');
    }

    /**
     * Scope to get only active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get verified questions.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get questions by grade level.
     */
    public function scopeOfGrade($query, string $grade)
    {
        return $query->where('grade_level', $grade);
    }

    /**
     * Scope to get questions by topic.
     */
    public function scopeOfTopic($query, string $topic)
    {
        return $query->where('topic', $topic);
    }

    /**
     * Scope to get questions by difficulty.
     */
    public function scopeOfDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope to get questions by format.
     */
    public function scopeOfFormat($query, string $format)
    {
        return $query->where('question_format', $format);
    }

    /**
     * Increment usage counter.
     */
    public function incrementUsage(bool $wasCorrect): void
    {
        $this->increment('times_used');
        
        // Recalculate success rate
        $this->updateSuccessRate();
    }

    /**
     * Update success rate based on historical data.
     */
    public function updateSuccessRate(): void
    {
        $totalAttempts = $this->assessmentQuestions()->count();
        
        if ($totalAttempts > 0) {
            $correctAnswers = $this->assessmentQuestions()->correct()->count();
            $this->success_rate = ($correctAnswers / $totalAttempts) * 100;
            $this->save();
        }
    }

    /**
     * Get formatted options for frontend.
     */
    public function getFormattedOptionsAttribute(): array
    {
        $options = $this->options ?? [];
        
        // If it's a multiple choice question, shuffle options but keep track of correct answer
        if ($this->question_format === 'multiple_choice' && is_array($options)) {
            // Options are already stored correctly
            return $options;
        }

        return $options;
    }

    /**
     * Check if question is suitable for adaptive progression.
     */
    public function isSuitableForAdaptive(): bool
    {
        return $this->is_active && 
               $this->discrimination_index !== null && 
               $this->discrimination_index > 0.2;
    }
}
