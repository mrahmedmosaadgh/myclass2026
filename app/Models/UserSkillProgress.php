<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSkillProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skill_id',
        'smart_score',
        'questions_answered',
        'correct_answers',
        'current_streak',
        'best_streak',
        'mastery_level',
    ];

    protected $casts = [
        'last_practiced_at' => 'datetime',
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

    // Methods
    public function updateSmartScore($change)
    {
        $this->smart_score += $change;
        $this->save();
        
        return $this;
    }

    public function incrementStreak()
    {
        $this->current_streak++;
        if ($this->current_streak > $this->best_streak) {
            $this->best_streak = $this->current_streak;
        }
        $this->save();
        
        return $this;
    }

    public function resetStreak()
    {
        $this->current_streak = 0;
        $this->save();
        
        return $this;
    }

    public function checkMastery()
    {
        // Determine mastery level based on smart score
        if ($this->smart_score >= 80) {
            return 'proficient';
        } elseif ($this->smart_score >= 100) {
            return 'mastered';
        } else {
            return 'developing';
        }
    }

    // Accessors
    public function getAccuracyAttribute()
    {
        if ($this->questions_answered == 0) {
            return 0;
        }
        
        return round(($this->correct_answers / $this->questions_answered) * 100, 2);
    }
}