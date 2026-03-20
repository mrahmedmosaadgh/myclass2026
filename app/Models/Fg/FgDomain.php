<?php

namespace App\Models\Fg;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FgDomain extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'emoji',
        'color_hex',
        'is_active',
        'sort_order',
        'version',
        'sync_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::deleted(function ($domain) {
            // Unclassify tasks and notes on soft delete so they aren't lost
            $domain->tasks()->update(['domain_id' => null]);
            $domain->notes()->update(['domain_id' => null]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(FgTask::class, 'domain_id');
    }

    public function notes()
    {
        return $this->hasMany(FgNote::class, 'domain_id');
    }
}
