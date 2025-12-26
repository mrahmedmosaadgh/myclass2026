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
        'active'
    ];

    protected $casts = [
        'active' => 'integer' // Using tinyInteger approach: 0=inactive, 1=active
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

    public function lessons(): HasMany
    {
        return $this->hasMany(CurriculumLesson::class)->orderBy('topic_number', 'lesson_number');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(\App\Models\my_class\Curriculums\CurriculumTopic::class)->orderBy('number');
    }

    public function lessonPlans(): HasMany
    {
        return $this->hasMany(CurriculumLessonPlan::class);
    }

    public function maps(): HasMany
    {
        return $this->hasMany(CurriculumMap::class);
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    // Business Logic Methods
    public function activate()
    {
        // Deactivate other curricula for the same school+subject+grade
        static::where('school_id', $this->school_id)
              ->where('subject_id', $this->subject_id)
              ->where('grade_id', $this->grade_id)
              ->where('id', '!=', $this->id)
              ->update(['active' => 0]);

        // Activate this curriculum
        $this->update(['active' => 1]);
    }

    public function deactivate()
    {
        $this->update(['active' => 0]);
    }

    public function isActive(): bool
    {
        return $this->active === 1;
    }
}