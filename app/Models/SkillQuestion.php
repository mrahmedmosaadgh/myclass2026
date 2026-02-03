<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_id',
        'qu_question_id',
        'difficulty_level',
        'explanation'
    ];

    public $timestamps = false; // Since the migration doesn't include timestamps

    // Relationships
    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function question()
    {
        return $this->belongsTo(QuQuestion::class, 'qu_question_id');
    }
}