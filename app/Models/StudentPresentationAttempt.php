<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPresentationAttempt extends Model
{
    protected $fillable = [
        'presentation_id',
        'student_identifier',
        'quiz_attempts',
        'total_score',
        'total_questions',
        'completed_at',
    ];

    protected $casts = [
        'quiz_attempts' => 'array',
        'total_score' => 'integer',
        'total_questions' => 'integer',
        'completed_at' => 'datetime',
    ];

    public $timestamps = false;

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(UserPresentation::class, 'presentation_id');
    }
}
