<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeCommentLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'comment_id'
    ];

    /**
     * Get the user who liked the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comment being liked
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(ResumeQuestionComment::class, 'comment_id');
    }

    /**
     * Scope to get likes for a specific comment
     */
    public function scopeForComment($query, $commentId)
    {
        return $query->where('comment_id', $commentId);
    }

    /**
     * Scope to get likes by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if user has liked a comment
     */
    public static function hasUserLiked($userId, $commentId)
    {
        return static::where('user_id', $userId)
            ->where('comment_id', $commentId)
            ->exists();
    }

    /**
     * Get like count for a comment
     */
    public static function getLikeCount($commentId)
    {
        return static::where('comment_id', $commentId)->count();
    }

    /**
     * Toggle like for a comment
     */
    public static function toggleLike($userId, $commentId)
    {
        $like = static::where('user_id', $userId)
            ->where('comment_id', $commentId)
            ->first();

        if ($like) {
            $like->delete();
            return false; // Unliked
        } else {
            static::create([
                'user_id' => $userId,
                'comment_id' => $commentId
            ]);
            return true; // Liked
        }
    }
}
