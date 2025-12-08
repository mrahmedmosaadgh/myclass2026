<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizSessionParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_session_id',
        'student_id',
        'score',
        'status',
    ];

    /**
     * Get the session this participant belongs to
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(QuizSession::class, 'quiz_session_id');
    }

    /**
     * Get the student user
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Check if participant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if participant is disconnected
     */
    public function isDisconnected(): bool
    {
        return $this->status === 'disconnected';
    }

    /**
     * Increment the participant's score
     */
    public function incrementScore(int $points): void
    {
        $this->increment('score', $points);
    }
}
