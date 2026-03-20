<?php

namespace App\Models\Fg;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FgSession extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'task_id',
        'intention',
        'energy_level',
        'status',
        'check_in_answer',
        'started_at',
        'ended_at',
        'duration_seconds',
        'version',
        'sync_status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(FgTask::class, 'task_id');
    }
}
