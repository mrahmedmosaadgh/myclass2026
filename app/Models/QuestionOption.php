<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_key',
        'option_text',
        'is_correct',
        'distractor_strength',
        'order_index',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'distractor_strength' => 'decimal:2',
    ];

    /**
     * Get the question that owns this option.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
