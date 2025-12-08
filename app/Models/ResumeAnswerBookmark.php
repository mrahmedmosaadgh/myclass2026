<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeAnswerBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'answer_id',
        'bookmark_type',
        'notes'
    ];

    /**
     * Get the user who bookmarked the answer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answer being bookmarked
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(ResumeAnswer::class, 'answer_id');
    }

    /**
     * Scope to get bookmarks for a specific answer
     */
    public function scopeForAnswer($query, $answerId)
    {
        return $query->where('answer_id', $answerId);
    }

    /**
     * Scope to get bookmarks by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get bookmarks by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('bookmark_type', $type);
    }

    /**
     * Check if user has bookmarked an answer
     */
    public static function hasUserBookmarked($userId, $answerId, $type = 'favorite')
    {
        return static::where('user_id', $userId)
            ->where('answer_id', $answerId)
            ->where('bookmark_type', $type)
            ->exists();
    }

    /**
     * Toggle bookmark for an answer
     */
    public static function toggleBookmark($userId, $answerId, $type = 'favorite', $notes = null)
    {
        $bookmark = static::where('user_id', $userId)
            ->where('answer_id', $answerId)
            ->where('bookmark_type', $type)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return false; // Removed bookmark
        } else {
            static::create([
                'user_id' => $userId,
                'answer_id' => $answerId,
                'bookmark_type' => $type,
                'notes' => $notes
            ]);
            return true; // Added bookmark
        }
    }

    /**
     * Get user's bookmarks by type
     */
    public static function getUserBookmarks($userId, $type = null)
    {
        $query = static::with(['answer', 'answer.user', 'answer.question'])
            ->where('user_id', $userId);

        if ($type) {
            $query->where('bookmark_type', $type);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
