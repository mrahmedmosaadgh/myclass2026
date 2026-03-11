<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bm2AssessmentQuestion Model
 * 
 * Represents a single question response within an assessment.
 */
class Bm2AssessmentQuestion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'bm2_assessment_questions';

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'assessment_id',
        'question_bank_id',
        'question_text',
        'subject',
        'grade_level',
        'question_type',
        'difficulty',
        'student_answer',
        'correct_answer',
        'is_correct',
        'time_taken_seconds',
        'hints_used',
        'points_earned',
        'possible_points',
        'question_order',
        'was_adaptive',
        'answered_at',
    ];

    /**
     * Attributes that should be cast to native types.
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'was_adaptive' => 'boolean',
        'answered_at' => 'datetime',
        'points_earned' => 'integer',
        'possible_points' => 'integer',
        'time_taken_seconds' => 'integer',
        'hints_used' => 'integer',
        'question_order' => 'integer',
    ];

    /**
     * Get the assessment this question belongs to.
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Bm2Assessment::class, 'assessment_id');
    }

    /**
     * Get the original question from the bank.
     */
    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(Bm2QuestionBank::class, 'question_bank_id');
    }

    /**
     * Scope to get correct answers.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope to get incorrect answers.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope to get questions by difficulty.
     */
    public function scopeOfDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope to get questions by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('question_type', $type);
    }

    /**
     * Calculate points earned based on correctness and hints.
     */
    public function calculatePoints(): int
    {
        if (!$this->is_correct) {
            return 0;
        }

        $basePoints = $this->possible_points;
        
        // Reduce points for each hint used (10% per hint)
        $hintPenalty = $basePoints * 0.1 * $this->hints_used;
        
        // Bonus for speed (if answered in under 30 seconds, add 10%)
        $speedBonus = $this->time_taken_seconds < 30 ? $basePoints * 0.1 : 0;

        $this->points_earned = max(0, $basePoints - $hintPenalty + $speedBonus);

        return $this->points_earned;
    }

    /**
     * Check if answer was given without hints.
     */
    public function isIndependent(): bool
    {
        return $this->hints_used === 0;
    }

    /**
     * Get accuracy percentage for this question.
     */
    public function getAccuracyPercentageAttribute(): float
    {
        return $this->is_correct ? 100.0 : 0.0;
    }
}
