<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpDailyTask extends Model
{
    protected $fillable = ['user_id', 'dp_task_id', 'title', 'start_time', 'end_time', 'description', 'status', 'completed_at', 'date'];

    protected $casts = [
        'date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(DpTask::class, 'dp_task_id');
    }
}
