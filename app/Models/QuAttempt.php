<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuAttempt extends Model
{
    protected $fillable = [
        'qu_exam_id',
        'user_id',
        'score',
        'grading_status',
        'started_at',
        'completed_at',
        'graded_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    // Relationships
    public function exam()
    {
        return $this->belongsTo(QuExam::class, 'qu_exam_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(QuAnswer::class, 'qu_attempt_id');
    }

    // Helper methods
    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }

    public function isInProgress()
    {
        return !is_null($this->started_at) && is_null($this->completed_at);
    }

    /**
     * Check if this attempt needs manual grading
     */
    public function needsManualGrading(): bool
    {
        return $this->answers()
            ->whereHas('question', function($q) {
                $q->whereIn('question_type', ['short', 'long']);
            })
            ->whereNull('marks_obtained')
            ->exists();
    }

    /**
     * Check if this attempt is fully graded
     */
    public function isFullyGraded(): bool
    {
        return !$this->needsManualGrading();
    }

    /**
     * Recalculate the total score based on all answers
     */
    public function recalculateScore(): void
    {
        $totalMarks = $this->answers()->sum('marks_obtained');
        $this->update([
            'score' => $totalMarks,
            'grading_status' => $this->isFullyGraded() ? 'completed' : 'partial',
            'graded_at' => $this->isFullyGraded() ? now() : null
        ]);
    }
}
