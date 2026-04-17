<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineUser extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'timeline_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'display_name',
        'preferences',
        'last_login_at',
        'last_login_ip',
        'is_active',
        'subscription_tier',
        'storage_quota_used',
        'storage_quota_limit'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'preferences' => 'array',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'storage_quota_used' => 'integer',
        'storage_quota_limit' => 'integer'
    ];

    /**
     * Get the user that owns the timeline profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get user preference by key.
     */
    public function getPreference($key, $default = null)
    {
        $preferences = $this->preferences ?? [];
        return $preferences[$key] ?? $default;
    }

    /**
     * Set user preference by key.
     */
    public function setPreference($key, $value)
    {
        $preferences = $this->preferences ?? [];
        $preferences[$key] = $value;
        $this->preferences = $preferences;
        $this->save();
    }

    /**
     * Get user's theme preference.
     */
    public function getThemeAttribute()
    {
        return $this->getPreference('theme', 'light');
    }

    /**
     * Get user's language preference.
     */
    public function getLanguageAttribute()
    {
        return $this->getPreference('language', 'en');
    }

    /**
     * Get user's timezone preference.
     */
    public function getTimezoneAttribute()
    {
        return $this->getPreference('timezone', 'UTC');
    }

    /**
     * Get user's notification preference.
     */
    public function getNotificationsAttribute()
    {
        return $this->getPreference('notifications', true);
    }

    /**
     * Update storage quota usage.
     */
    public function updateStorageQuota($bytesUsed)
    {
        $this->storage_quota_used = $bytesUsed;
        $this->save();
    }

    /**
     * Check if user has exceeded storage quota.
     */
    public function hasExceededStorageQuota()
    {
        return $this->storage_quota_used > $this->storage_quota_limit;
    }

    /**
     * Get storage usage percentage.
     */
    public function getStorageUsagePercentage()
    {
        if ($this->storage_quota_limit === 0) {
            return 0;
        }
        
        return ($this->storage_quota_used / $this->storage_quota_limit) * 100;
    }
}
