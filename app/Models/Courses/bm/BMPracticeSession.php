<?php

namespace App\Models\Courses\bm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BMPracticeSession extends Model
{
    use HasFactory;

    protected $table = 'bm_practice_sessions';

    protected $fillable = [
        'user_id',
        'domain',
        'questions_attempted',
        'questions_correct',
        'avg_time_ms',
        'session_date',
    ];

    protected $casts = [
        'session_date' => 'datetime',
        'questions_attempted' => 'integer',
        'questions_correct' => 'integer',
        'avg_time_ms' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
