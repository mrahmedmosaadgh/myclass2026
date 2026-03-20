<?php

namespace App\Models\Fg;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FgSubTask extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'task_id',
        'title',
        'is_done',
        'sort_order',
        'version',
        'sync_status',
    ];

    protected $casts = [
        'is_done' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(FgTask::class, 'task_id');
    }
}
