<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'has_options',
        'supports_hints',
        'supports_explanation',
    ];

    protected $casts = [
        'has_options' => 'boolean',
        'supports_hints' => 'boolean',
        'supports_explanation' => 'boolean',
    ];

    /**
     * Get the questions for this question type.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
