<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PomodoroSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'type',
        'duration',
        'started_at',
        'ended_at',
        'notes',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the user that owns the pomodoro session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task associated with this pomodoro session.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Scope a query to only include work sessions.
     */
    public function scopeWork($query)
    {
        return $query->where('type', 'work');
    }

    /**
     * Scope a query to only include break sessions.
     */
    public function scopeBreak($query)
    {
        return $query->where('type', 'break');
    }

    /**
     * Scope a query to only include completed sessions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Calculate the actual duration of the session in minutes.
     */
    public function getActualDurationAttribute()
    {
        if (!$this->ended_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->ended_at);
    }
}
