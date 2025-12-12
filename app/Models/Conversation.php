<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_group',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_group' => 'boolean',
    ];

    /**
     * Get the users that belong to the conversation.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    /**
     * Get the messages for the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest message for the conversation.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Check if the conversation is between two users only.
     */
    public function isPrivate(): bool
    {
        return !$this->is_group && $this->users()->count() === 2;
    }

    /**
     * Get the other user in a private conversation.
     */
    public function getOtherUser(User $user)
    {
        if ($this->is_group) {
            return null;
        }

        return $this->users()->where('users.id', '!=', $user->id)->first();
    }

    /**
     * Get unread messages count for a user.
     */
    public function unreadMessagesCount(User $user): int
    {
        $lastRead = $this->users()->where('users.id', $user->id)->first()?->pivot?->last_read_at;

        if (!$lastRead) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('created_at', '>', $lastRead)
            ->where('user_id', '!=', $user->id)
            ->count();
    }
}
