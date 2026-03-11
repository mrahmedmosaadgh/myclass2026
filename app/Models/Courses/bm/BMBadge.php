<?php

namespace App\Models\Courses\bm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BMBadge extends Model
{
    use HasFactory;

    protected $table = 'bm_badges';

    protected $fillable = [
        'user_id',
        'badge_type',
        'bm_assessment_id',
        'earned_at',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
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
