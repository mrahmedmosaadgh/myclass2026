<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grade extends Model
{
    protected $fillable = [
        'name',
        'school_id',
        'stage_id',
        'subject_ids'
    ];

    protected $casts = [
        'subject_ids' => 'json',
    ];
    protected $table = 'grades';

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }
}
