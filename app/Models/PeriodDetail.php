<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'code',
        'sequence',
        'name',
        'main',
        'time_before',
        'from',
        'to',
        'notes'
    ];

    protected $casts = [
        'code' => 'integer',
        'sequence' => 'integer',
        'main' => 'integer',
        'time_before' => 'integer',
        'from' => 'datetime:H:i',
        'to' => 'datetime:H:i',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}