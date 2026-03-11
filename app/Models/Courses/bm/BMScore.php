<?php

namespace App\Models\Courses\bm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BMScore extends Model
{
    use HasFactory;

    protected $table = 'bm_scores';

    protected $fillable = [
        'user_id',
        'bm_assessment_id',
        'final_score',
    ];

    protected $casts = [
        'final_score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function assessment()
    {
        return $this->belongsTo(BMAssessment::class, 'bm_assessment_id');
    }
}
