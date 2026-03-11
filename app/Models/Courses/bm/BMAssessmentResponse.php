<?php

namespace App\Models\Courses\bm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Courses\bm\BMAssessment;
use App\Models\Courses\bm\BMQuestion;

class BMAssessmentResponse extends Model
{
    use HasFactory;

    protected $table = 'bm_assessment_responses';

    protected $fillable = [
        'bm_assessment_id',
        'bm_question_id',
        'user_answer',
        'correct_answer',
        'is_correct',
        'time_taken_ms',
        'difficulty_level',
        'domain',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'time_taken_ms' => 'integer',
        'difficulty_level' => 'integer',
    ];

    public function assessment()
    {
        return $this->belongsTo(BMAssessment::class, 'bm_assessment_id');
    }

    public function question()
    {
        return $this->belongsTo(BMQuestion::class, 'bm_question_id');
    }
}
