<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPresentation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'presentation_data',
        'share_token',
        'is_public',
    ];

    protected $casts = [
        'presentation_data' => 'array',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studentAttempts(): HasMany
    {
        return $this->hasMany(StudentPresentationAttempt::class, 'presentation_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->share_token)) {
                $model->share_token = bin2hex(random_bytes(32));
            }
        });
    }
}
