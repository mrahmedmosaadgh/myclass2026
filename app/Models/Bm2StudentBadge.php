<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Bm2StudentBadge Model
 * 
 * Pivot model for the many-to-many relationship between students and badges.
 */
class Bm2StudentBadge extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'bm2_student_badges';

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
        'context_data',
    ];

    /**
     * Attributes that should be cast to native types.
     */
    protected $casts = [
        'earned_at' => 'datetime',
        'context_data' => 'array',
    ];

    /**
     * Get the student who earned this badge.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the badge that was earned.
     */
    public function badge()
    {
        return $this->belongsTo(Bm2Badge::class, 'badge_id');
    }

    /**
     * Scope for badges earned in a specific context.
     */
    public function scopeByContext($query, string $context)
    {
        return $query->whereJsonContains('context_data->context', $context);
    }

    /**
     * Scope for badges earned after a specific date.
     */
    public function scopeEarnedAfter($query, $date)
    {
        return $query->where('earned_at', '>=', $date);
    }

    /**
     * Get formatted earned date.
     */
    public function getEarnedDateDisplayAttribute(): string
    {
        return $this->earned_at->format('M d, Y');
    }
}
