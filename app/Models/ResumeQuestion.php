<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'category',
        'language',
        'tags',
        'options',
        'default_answer',
        'is_required',
        'description',
        // Legacy fields for backward compatibility
        'body',
        'meta',
        'is_active'
    ];

    protected $casts = [
        'category' => 'array',
        'tags' => 'array',
        'options' => 'array',
        'is_required' => 'boolean',
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function answers() { return $this->hasMany(ResumeAnswer::class, 'question_id'); }
    public function comments() { return $this->hasMany(ResumeQuestionComment::class, 'question_id'); }
}
