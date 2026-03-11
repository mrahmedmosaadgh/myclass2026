<?php

namespace App\Models\Courses\bm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BMLessonProgress extends Model
{
    use HasFactory;

    protected $table = 'bm_lesson_progress';

    protected $fillable = [
        'user_id',
        'module',
        'lesson_number',
        'status',
        'score',
        'completed_at',
        'time_spent_seconds',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'score' => 'integer',
        'time_spent_seconds' => 'integer',
        'lesson_number' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
