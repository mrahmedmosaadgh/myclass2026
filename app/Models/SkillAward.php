<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillAward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'award_type',
        'skill_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array', // Store as JSON
        'earned_at' => 'datetime',
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
}