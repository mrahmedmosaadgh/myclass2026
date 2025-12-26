<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use SoftDeletes;
    use HasPushSubscriptions;

    protected $fillable = [
        'name', 'email', 'password', 'last_login', 'last_active', 'is_active', 'role', 'first_login', 'school_id'
    ];
    protected $dates = ['last_login', 'last_active'];
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }
    
    public function student()
    {
        return $this->hasOne(student::class);
    }
    
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    /**
     * Get the conversations that the user belongs to.
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)
            ->withPivot('last_read_at')
            ->withTimestamps()
            ->orderByPivot('updated_at', 'desc');
    }

    /**
     * Get the messages that the user has sent.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get all unread messages for the user.
     */
    public function unreadMessages()
    {
        $conversationIds = $this->conversations()->pluck('conversations.id');

        return Message::whereIn('conversation_id', $conversationIds)
            ->where('user_id', '!=', $this->id)
            ->where('created_at', '>', function ($query) {
                $query->select('last_read_at')
                    ->from('conversation_user')
                    ->where('user_id', $this->id)
                    ->whereColumn('conversation_id', 'messages.conversation_id')
                    ->limit(1);
            })
            ->orWhereNotExists(function ($query) {
                $query->select('last_read_at')
                    ->from('conversation_user')
                    ->where('user_id', $this->id)
                    ->whereColumn('conversation_id', 'messages.conversation_id')
                    ->whereNotNull('last_read_at');
            })
            ->get();
    }
    public function dpTasks()
    {
        return $this->hasMany(DpTask::class);
    }

    public function dpDailyTasks()
    {
        return $this->hasMany(DpDailyTask::class);
    }

    public function dpFocusLogs()
    {
        return $this->hasMany(DpFocusLog::class);
    }

    public function dpRewards()
    {
        return $this->hasMany(DpReward::class);
    }
}





