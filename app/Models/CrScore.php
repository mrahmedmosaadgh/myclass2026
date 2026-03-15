<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_period_id',
        'mapping_id',
        'numeric_value',
        'text_value',
        'json_value',
    ];

    protected $casts = [
        'numeric_value' => 'decimal:2',
        'json_value' => 'array',
    ];

    public function studentPeriod(): BelongsTo
    {
        return $this->belongsTo(CrStudentPeriod::class, 'student_period_id');
    }

    public function mapping(): BelongsTo
    {
        return $this->belongsTo(CrCategoryMapping::class, 'mapping_id');
    }
}
