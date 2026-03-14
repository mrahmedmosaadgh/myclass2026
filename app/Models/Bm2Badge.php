<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Bm2Badge Model
 * 
 * Represents a gamification badge that students can earn in the BM2 platform.
 */
class Bm2Badge extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'bm2_badges';

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'icon_url',
        'category',
        'earning_criteria',
        'points_value',
        'rarity',
        'display_order',
        'is_active',
    ];

    /**
     * Attributes that should be cast to native types.
     */
    protected $casts = [
        'earning_criteria' => 'array',
        'points_value' => 'integer',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Badge categories.
     */
    const CATEGORY_ACHIEVEMENT = 'achievement';
    const CATEGORY_MILESTONE = 'milestone';
    const CATEGORY_SKILL_MASTERY = 'skill_mastery';
    const CATEGORY_SPEED = 'speed';
    const CATEGORY_CONSISTENCY = 'consistency';

    /**
     * Badge rarities.
     */
    const RARITY_COMMON = 'common';
    const RARITY_UNCOMMON = 'uncommon';
    const RARITY_RARE = 'rare';
    const RARITY_EPIC = 'epic';
    const RARITY_LEGENDARY = 'legendary';

    /**
     * Get students who have earned this badge.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bm2_student_badges')
            ->withPivot('earned_at', 'context_data')
            ->withTimestamps();
    }

    /**
     * Scope for active badges.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for badges by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for badges by rarity.
     */
    public function scopeByRarity($query, string $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Check if badge can be awarded to student.
     * 
     * @param User $student
     * @param array $contextData
     * @return bool
     */
    public function canBeAwarded(User $student, array $contextData = []): bool
    {
        // Check if student already has this badge
        if ($student->badges()->where('badge_id', $this->id)->exists()) {
            // Check if badge is stackable (can be earned multiple times)
            if (!$this->isStackable()) {
                return false;
            }
        }

        // Check earning criteria
        return $this->meetsEarningCriteria($student, $contextData);
    }

    /**
     * Check if badge meets earning criteria.
     * 
     * @param User $student
     * @param array $contextData
     * @return bool
     */
    protected function meetsEarningCriteria(User $student, array $contextData = []): bool
    {
        $criteria = $this->earning_criteria;
        
        if (!isset($criteria['type'])) {
            return false;
        }

        switch ($criteria['type']) {
            case 'score_threshold':
                return ($contextData['score'] ?? 0) >= ($criteria['value'] ?? 0);
            
            case 'streak':
                return ($contextData['streak'] ?? 0) >= ($criteria['value'] ?? 0);
            
            case 'total_assessments':
                $totalCompleted = $student->bm2Assessments()
                    ->whereNotNull('completed_at')
                    ->count();
                return $totalCompleted >= ($criteria['value'] ?? 0);
            
            case 'perfect_score':
                return ($contextData['score'] ?? 0) == 100;
            
            case 'speed_demon':
                $timeTaken = $contextData['time_taken_seconds'] ?? 0;
                $maxTime = $criteria['value'] ?? 300; // 5 minutes default
                return $timeTaken <= $maxTime && ($contextData['score'] ?? 0) >= 90;
            
            default:
                return false;
        }
    }

    /**
     * Check if badge is stackable (can be earned multiple times).
     */
    public function isStackable(): bool
    {
        // By default, badges are not stackable unless specified
        return $this->earning_criteria['stackable'] ?? false;
    }

    /**
     * Get badge icon URL.
     */
    public function getIconUrlAttribute(?string $value): ?string
    {
        return $value ?? '/images/badges/default-badge.png';
    }

    /**
     * Get formatted points value.
     */
    public function getFormattedPointsAttribute(): string
    {
        return '+' . $this->points_value . ' pts';
    }

    /**
     * Get formatted rarity display.
     */
    public function getRarityDisplayAttribute(): string
    {
        return ucfirst($this->rarity);
    }
}
