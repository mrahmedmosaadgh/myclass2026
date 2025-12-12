<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'teacher_id',
        'access_code',
        'status',
        'current_question_id',
        'settings',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the quiz associated with this session
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the teacher who created this session
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the current question being displayed
     */
    public function currentQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'current_question_id');
    }

    /**
     * Get all participants in this session
     */
    public function participants(): HasMany
    {
        return $this->hasMany(QuizSessionParticipant::class);
    }

    /**
     * Get all quiz attempts made during this session
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Generate a unique access code
     */
    public static function generateAccessCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        } while (self::where('access_code', $code)->exists());

        return $code;
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if session is waiting
     */
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Check if session is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
