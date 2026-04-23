<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_id',
        'order',
        'type',
        'marks',
        'section',
        'content',
        'options',
        'correct_answer',
        'explanation',
        'metadata',
        'custom_fields',
    ];

    protected $casts = [
        'content' => 'array',
        'options' => 'array',
        'correct_answer' => 'array',
        'explanation' => 'array',
        'metadata' => 'array',
        'custom_fields' => 'array',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
