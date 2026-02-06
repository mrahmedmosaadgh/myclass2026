<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade_id',
        'subject_id',
        'icon',
        'display_order'
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    // Relationships
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function skills()
    {
        return $this->hasMany(Skill::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }
}