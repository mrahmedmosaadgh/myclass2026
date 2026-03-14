<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Bm2Assessment Model
 * 
 * Represents a single assessment session for a student.
 * Tracks overall performance, skill breakdown, and learning recommendations.
 */
class Bm2Assessment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'bm2_assessments';

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'title',
        'type',
        'game_mode',
        'game_settings',
        'game_stats',
        'overall_score',
        'grade_level_equivalent',
        'performance_level',
        'skill_breakdown',
        'recommended_modules',
        'firebase_session_id',
        'started_at',
        'completed_at',
        'total_time_seconds',
        'is_active',
    ];

    /**
     * Attributes that should be cast to native types.
     */
    protected $casts = [
        'skill_breakdown' => 'array',
        'recommended_modules' => 'array',
        'game_settings' => 'array',
        'game_stats' => 'array',
        'overall_score' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the student who took this assessment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get all questions answered in this assessment.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Bm2AssessmentQuestion::class, 'assessment_id');
    }

    /**
     * Get the generated learning path from this assessment.
     */
    public function learningPath(): HasOne
    {
        return $this->hasOne(Bm2LearningPath::class, 'assessment_id');
    }

    /**
     * Scope to get only active assessments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get completed assessments.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope to get assessments by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Calculate and update the overall score.
     */
    public function calculateScore(): float
    {
        $questions = $this->questions;
        
        if ($questions->isEmpty()) {
            return 0.0;
        }

        $totalPoints = $questions->sum('possible_points');
        $earnedPoints = $questions->sum('points_earned');

        $this->overall_score = $totalPoints > 0 
            ? ($earnedPoints / $totalPoints) * 100 
            : 0.0;

        $this->save();

        return $this->overall_score;
    }

    /**
     * Determine performance level based on score.
     */
    public function determinePerformanceLevel(): string
    {
        if ($this->overall_score >= 90) {
            return 'advanced';
        } elseif ($this->overall_score >= 70) {
            return 'proficient';
        } elseif ($this->overall_score >= 40) {
            return 'developing';
        } else {
            return 'emerging';
        }
    }

    /**
     * Check if assessment is complete.
     */
    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Get assessment duration in minutes.
     */
    public function getDurationMinutesAttribute(): ?float
    {
        if (!$this->total_time_seconds) {
            return null;
        }

        return round($this->total_time_seconds / 60, 2);
    }
}
