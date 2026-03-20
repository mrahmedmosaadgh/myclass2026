<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrCategoryMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'grade_id',
        'subject_id',
        'key',
        'label',
        'icon',
        'color',
        'type',
        'max_value',
        'passing_value',
        'default_value',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'max_value' => 'integer',
        'passing_value' => 'integer',
        'default_value' => 'integer',
        'sort_order' => 'integer',
        'active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
