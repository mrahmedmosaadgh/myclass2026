<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeQuestionComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_id',
        'answer_id',
        'comment',
        'media_type',
        'media_path',
        'is_public',
        'parent_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(ResumeQuestion::class, 'question_id');
    }

    public function answer()
    {
        return $this->belongsTo(ResumeAnswer::class, 'answer_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Get likes for this comment
     */
    public function likes()
    {
        return $this->hasMany(ResumeCommentLike::class, 'comment_id');
    }

    /**
     * Check if user has liked this comment
     */
    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Get likes count
     */
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    /**
     * Scope to get comments for a specific answer
     */
    public function scopeForAnswer($query, $answerId)
    {
        return $query->where('answer_id', $answerId);
    }

    /**
     * Scope to get top-level comments (no parent)
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get public comments
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get comments with replies for an answer
     */
    public static function getCommentsWithReplies($answerId)
    {
        return static::with(['user', 'replies.user', 'likes'])
            ->forAnswer($answerId)
            ->topLevel()
            ->public()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
