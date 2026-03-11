<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bm2LearningPath Model
 * 
 * Represents a personalized learning path generated from assessment results.
 */
class Bm2LearningPath extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'bm2_learning_paths';

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'assessment_id',
        'title',
        'description',
        'recommended_modules',
        'total_lessons',
        'completed_lessons',
        'completion_percentage',
        'estimated_minutes',
        'status',
        'target_completion_date',
        'started_at',
        'completed_at',
        'is_active',
    ];

    /**
     * Attributes that should be cast to native types.
     */
    protected $casts = [
        'recommended_modules' => 'array',
        'total_lessons' => 'integer',
        'completed_lessons' => 'integer',
        'completion_percentage' => 'decimal:2',
        'estimated_minutes' => 'integer',
        'target_completion_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the student this learning path belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the assessment that generated this learning path.
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Bm2Assessment::class, 'assessment_id');
    }

    /**
     * Scope to get active learning paths.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get in-progress paths.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to get completed paths.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Increment completed lessons count.
     */
    public function incrementProgress(): void
    {
        $this->increment('completed_lessons');
        $this->updateCompletionPercentage();
    }

    /**
     * Update completion percentage based on progress.
     */
    public function updateCompletionPercentage(): void
    {
        if ($this->total_lessons > 0) {
            $this->completion_percentage = 
                ($this->completed_lessons / $this->total_lessons) * 100;
            
            // Mark as completed if 100%
            if ($this->completed_lessons >= $this->total_lessons) {
                $this->status = 'completed';
                $this->completed_at = now();
            } elseif ($this->completed_lessons > 0 && $this->status === 'not_started') {
                $this->status = 'in_progress';
                $this->started_at = now();
            }

            $this->save();
        }
    }

    /**
     * Get recommended modules for a specific topic.
     */
    public function getModulesForTopic(string $topic): array
    {
        $modules = $this->recommended_modules ?? [];
        
        return collect($modules)
            ->filter(fn($module) => $module['topic'] === $topic)
            ->toArray();
    }

    /**
     * Get high priority modules.
     */
    public function getHighPriorityModules(): array
    {
        $modules = $this->recommended_modules ?? [];
        
        return collect($modules)
            ->filter(fn($module) => ($module['priority'] ?? '') === 'high')
            ->toArray();
    }

    /**
     * Check if learning path is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get estimated days to completion.
     */
    public function getEstimatedDaysToCompletionAttribute(): ?int
    {
        if (!$this->target_completion_date || $this->isComplete()) {
            return null;
        }

        return max(0, now()->diffInDays($this->target_completion_date));
    }
}
