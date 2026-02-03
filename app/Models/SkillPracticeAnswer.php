<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillPracticeAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'skill_question_id',
        'user_answer',
        'is_correct',
        'time_taken_ms',
        'difficulty_at_time',
        'score_change'
    ];

    protected $casts = [
        'user_answer' => 'array', // Store as JSON
        'is_correct' => 'boolean',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(SkillPracticeSession::class, 'session_id');
    }

    public function skillQuestion()
    {
        return $this->belongsTo(SkillQuestion::class, 'skill_question_id');
    }
}