<?php

namespace App\Models\Fg;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FgNote extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'domain_id',
        'body',
        'source',
        'tags',
        'version',
        'sync_status',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function domain()
    {
        return $this->belongsTo(FgDomain::class, 'domain_id');
    }
}
