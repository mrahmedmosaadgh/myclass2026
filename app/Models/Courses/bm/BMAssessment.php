<?php

namespace App\Models\Courses\bm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BMAssessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bm_assessments';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'started_at',
        'completed_at',
        'total_score',
        'accuracy_score',
        'fluency_score',
        'consistency_score',
        'level',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function responses()
    {
        return $this->hasMany(BMAssessmentResponse::class, 'bm_assessment_id');
    }
}
