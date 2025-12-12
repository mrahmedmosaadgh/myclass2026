<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_id',
        'answer_text',
        'media_links',
        'attachments',
        'status',
        'notes',
        'is_public',
        'voice_note_path',
        'voice_note_duration',
        'voice_note_metadata',
        'views_count',
        'average_rating',
        'ratings_count',
        'likes_count',
        'comments_count',
        'is_featured',
        'featured_at',
        // Legacy fields for backward compatibility
        'answer',
        'media_type',
        'media_path'
    ];

    protected $casts = [
        'media_links' => 'array',
        'attachments' => 'array',
        'voice_note_metadata' => 'array',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'featured_at' => 'datetime',
        'average_rating' => 'decimal:2',
        'views_count' => 'integer',
        'ratings_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'voice_note_duration' => 'integer',
    ];

    protected $appends = [
        'voice_note_url'
    ];

    // ===== RELATIONSHIPS =====

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(ResumeQuestion::class, 'question_id');
    }

    public function comments()
    {
        return $this->hasMany(ResumeQuestionComment::class, 'answer_id');
    }

    public function ratings()
    {
        return $this->hasMany(ResumeAnswerRating::class, 'answer_id');
    }

    public function likes()
    {
        return $this->hasMany(ResumeAnswerLike::class, 'answer_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(ResumeAnswerBookmark::class, 'answer_id');
    }

    public function reports()
    {
        return $this->hasMany(ResumeAnswerReport::class, 'answer_id');
    }

    // ===== HELPER METHODS =====

    /**
     * Check if user has liked this answer
     */
    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Check if user has rated this answer
     */
    public function isRatedByUser($userId)
    {
        return $this->ratings()->where('user_id', $userId)->exists();
    }

    /**
     * Get user's rating for this answer
     */
    public function getUserRating($userId)
    {
        $rating = $this->ratings()->where('user_id', $userId)->first();
        return $rating ? $rating->rating : null;
    }

    /**
     * Check if user has bookmarked this answer
     */
    public function isBookmarkedByUser($userId, $type = 'favorite')
    {
        return $this->bookmarks()
            ->where('user_id', $userId)
            ->where('bookmark_type', $type)
            ->exists();
    }

    /**
     * Update rating statistics
     */
    public function updateRatingStats()
    {
        $this->update([
            'average_rating' => $this->ratings()->avg('rating') ?? 0,
            'ratings_count' => $this->ratings()->count()
        ]);
    }

    /**
     * Update likes count
     */
    public function updateLikesCount()
    {
        $this->update([
            'likes_count' => $this->likes()->count()
        ]);
    }

    /**
     * Update comments count
     */
    public function updateCommentsCount()
    {
        $this->update([
            'comments_count' => $this->comments()->count()
        ]);
    }

    /**
     * Increment views count
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    // ===== SCOPES =====

    /**
     * Scope to get featured answers
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get public answers
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to get answers with voice notes
     */
    public function scopeWithVoiceNotes($query)
    {
        return $query->whereNotNull('voice_note_path');
    }

    // ===== ACCESSORS =====

    /**
     * Get the voice note URL accessor
     */
    public function getVoiceNoteUrlAttribute()
    {
        if ($this->voice_note_path) {
            return asset('storage/' . $this->voice_note_path);
        }
        return null;
    }

    /**
     * Scope to order by rating
     */
    public function scopeOrderByRating($query, $direction = 'desc')
    {
        return $query->orderBy('average_rating', $direction)
                    ->orderBy('ratings_count', $direction);
    }

    /**
     * Scope to order by popularity (likes + views)
     */
    public function scopeOrderByPopularity($query, $direction = 'desc')
    {
        return $query->orderByRaw('(likes_count + views_count) ' . $direction);
    }
}
