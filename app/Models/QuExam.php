<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuExam extends Model
{
    protected $fillable = [
        'title',
        'description',
        'subject_id',
        'duration_minutes',
        'total_marks',
        'bloom_distribution',
        'created_by',
        'is_published'
    ];

    protected $casts = [
        'bloom_distribution' => 'array',
        'is_published' => 'boolean',
    ];

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->belongsToMany(QuQuestion::class, 'qu_exam_questions', 'qu_exam_id', 'qu_question_id')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('qu_exam_questions.order');
    }

    public function attempts()
    {
        return $this->hasMany(QuAttempt::class, 'qu_exam_id');
    }
}
