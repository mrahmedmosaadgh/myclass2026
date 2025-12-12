<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpFocusLog extends Model
{
    protected $fillable = ['user_id', 'dp_daily_task_id', 'start_time', 'end_time', 'duration_minutes', 'distraction_count', 'notes'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyTask()
    {
        return $this->belongsTo(DpDailyTask::class, 'dp_daily_task_id');
    }
}
