<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuAttempt extends Model
{
    protected $fillable = [
        'qu_exam_id',
        'user_id',
        'score',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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
}
