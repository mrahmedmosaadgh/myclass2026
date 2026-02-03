<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillPracticeSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skill_id',
        'questions_attempted',
        'questions_correct',
        'start_score',
        'end_score',
        'time_spent_seconds',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function answers()
    {
        return $this->hasMany(SkillPracticeAnswer::class, 'session_id');
    }

    // Methods
    public function complete()
    {
        $this->ended_at = now();
        $this->save();
        
        return $this;
    }

    public function calculateAccuracy()
    {
        if ($this->questions_attempted == 0) {
            return 0;
        }
        
        return ($this->questions_correct / $this->questions_attempted) * 100;
    }

    public function isActive()
    {
        return is_null($this->ended_at);
    }

    public function isCompleted()
    {
        return !is_null($this->ended_at);
    }

    public function getDurationAttribute()
    {
        if ($this->isActive()) {
            return now()->diffInSeconds($this->started_at);
        }
        
        return $this->time_spent_seconds;
    }
}