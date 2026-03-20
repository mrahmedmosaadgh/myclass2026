<?php

namespace App\Models\Fg;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FgTask extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'domain_id',
        'title',
        'notes',
        'importance',
        'status',
        'source',
        'is_today',
        'sort_order',
        'tags',
        'due_date',
        'completed_at',
        'version',
        'sync_status',
    ];

    protected $casts = [
        'is_today' => 'boolean',
        'tags' => 'array',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function domain()
    {
        return $this->belongsTo(FgDomain::class, 'domain_id');
    }

    public function subTasks()
    {
        return $this->hasMany(FgSubTask::class, 'task_id')->orderBy('sort_order');
    }

    public function sessions()
    {
        return $this->hasMany(FgSession::class, 'task_id');
    }
}
