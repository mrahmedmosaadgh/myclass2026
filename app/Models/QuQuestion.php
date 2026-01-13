<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuQuestion extends Model
{
    protected $fillable = [
        'subject_id',
        'topic_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'difficulty',
        'bloom_level',
        'marks',
        'created_by'
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
    ];

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic()
    {
        return $this->belongsTo(\App\Models\my_class\Curriculums\CurriculumTopic::class, 'topic_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exams()
    {
        return $this->belongsToMany(QuExam::class, 'qu_exam_questions', 'qu_question_id', 'qu_exam_id')
            ->withPivot('order')
            ->withTimestamps();
    }

    public function answers()
    {
        return $this->hasMany(QuAnswer::class, 'qu_question_id');
    }
}
