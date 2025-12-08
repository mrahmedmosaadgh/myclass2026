<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'started_at',
        'completed_at',
        'total_questions',
        'correct_answers',
        'percentage',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'percentage' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns this quiz attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answers for this quiz attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class, 'attempt_id');
    }

    /**
     * Check if the quiz attempt is complete.
     */
    public function isComplete(): bool
    {
        return !is_null($this->completed_at);
    }

    /**
     * Calculate and update the quiz results.
     */
    public function calculateResults(): void
    {
        $this->correct_answers = $this->answers()->where('is_correct', true)->count();
        
        if ($this->total_questions > 0) {
            $this->percentage = ($this->correct_answers / $this->total_questions) * 100;
        } else {
            $this->percentage = 0;
        }
        
        $this->save();
    }
}
