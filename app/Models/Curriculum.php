<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'grade_id',
        'school_id',
        'subject_id',
        'edit_lock_date'
    ];

    protected $casts = [
        'edit_lock_date' => 'date'
    ];

    // Relationships
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CurriculumVersion::class);
    }

    public function activeVersion()
    {
        return $this->hasOne(CurriculumVersion::class)->where('status', 'active');
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }

    // Scopes
    public function scopeForGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }
}