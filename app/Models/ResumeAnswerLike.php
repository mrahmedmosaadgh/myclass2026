<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeAnswerLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'answer_id'
    ];

    /**
     * Get the user who liked the answer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answer being liked
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(ResumeAnswer::class, 'answer_id');
    }

    /**
     * Scope to get likes for a specific answer
     */
    public function scopeForAnswer($query, $answerId)
    {
        return $query->where('answer_id', $answerId);
    }

    /**
     * Scope to get likes by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if user has liked an answer
     */
    public static function hasUserLiked($userId, $answerId)
    {
        return static::where('user_id', $userId)
            ->where('answer_id', $answerId)
            ->exists();
    }

    /**
     * Get like count for an answer
     */
    public static function getLikeCount($answerId)
    {
        return static::where('answer_id', $answerId)->count();
    }

    /**
     * Toggle like for an answer
     */
    public static function toggleLike($userId, $answerId)
    {
        $like = static::where('user_id', $userId)
            ->where('answer_id', $answerId)
            ->first();

        if ($like) {
            $like->delete();
            return false; // Unliked
        } else {
            static::create([
                'user_id' => $userId,
                'answer_id' => $answerId
            ]);
            return true; // Liked
        }
    }
}
