<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeAnswerRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'answer_id',
        'rating',
        'review_comment'
    ];

    protected $casts = [
        'rating' => 'integer'
    ];

    /**
     * Get the user who made the rating
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answer being rated
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(ResumeAnswer::class, 'answer_id');
    }

    /**
     * Scope to get ratings for a specific answer
     */
    public function scopeForAnswer($query, $answerId)
    {
        return $query->where('answer_id', $answerId);
    }

    /**
     * Scope to get ratings by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get average rating for an answer
     */
    public static function getAverageRating($answerId)
    {
        return static::where('answer_id', $answerId)->avg('rating');
    }

    /**
     * Get rating count for an answer
     */
    public static function getRatingCount($answerId)
    {
        return static::where('answer_id', $answerId)->count();
    }

    /**
     * Get rating breakdown for an answer
     */
    public static function getRatingBreakdown($answerId)
    {
        $ratings = static::where('answer_id', $answerId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Ensure all ratings 1-5 are present
        $breakdown = [];
        for ($i = 1; $i <= 5; $i++) {
            $breakdown[$i] = $ratings[$i] ?? 0;
        }

        return $breakdown;
    }
}
