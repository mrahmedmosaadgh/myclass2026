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
        'is_active' => 'boolean',
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

    public function hr()
    {
        return $this->hasOne(HR::class);
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




    public function schoolIdRole(): ?int
    {
        return match ($this->role) {
            'student' => $this->student?->school_id,
            'teacher' => $this->teacher?->school_id,
            'admin'   => $this->adminSchool()?->id,
            'hr_admin' => $this->school_id,
            default   => null,
        };
    }

    public function schoolId(): ?int
    {
        if ($this->school_id) {
            return $this->school_id;
        }

        if ($this->teacher) {
            return $this->teacher->school_id;
        }

        if ($this->student) {
            return $this->student->school_id;
        }

        return null; // no school
    }

    /**
     * Get the current school ID for the authenticated user.
     * Alias of schoolId() for consistency across the codebase.
     */
    public function currentSchoolId(): ?int
    {
        return $this->schoolId();
    }

    /**
     * Get the current academic year ID for the authenticated user.
     * Returns the current year from the school relationship or defaults to year 1.
     */
    public function currentAcademicYearId(): ?int
    {
        // Try to get from user's direct school relationship
        if ($this->school && $this->school->currentAcademicYearId) {
            return $this->school->currentAcademicYearId;
        }

        // Fallback: Get first active academic year for the school
        $schoolId = $this->schoolId();
        if ($schoolId) {
            $year = \App\Models\AcademicYear::where('school_id', $schoolId)
                ->where('active', true)
                ->first();
            
            if ($year) {
                return $year->id;
            }
        }

        // Ultimate fallback: return first academic year or 1
        return AcademicYear::first()?->id ?? 1;
    }


}




