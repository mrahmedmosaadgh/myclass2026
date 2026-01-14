<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuAnswer extends Model
{
    protected $fillable = [
        'qu_attempt_id',
        'qu_question_id',
        'selected_options',
        'answer_text',
        'marks_obtained',
        'feedback',
        'graded_at',
        'graded_by'
    ];

    protected $casts = [
        'selected_options' => 'array',
        'graded_at' => 'datetime',
    ];

    // Relationships
    public function attempt()
    {
        return $this->belongsTo(QuAttempt::class, 'qu_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(QuQuestion::class, 'qu_question_id');
    }
}
