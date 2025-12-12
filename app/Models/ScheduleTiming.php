<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleTiming extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'options',
        'timing',
        'notes'
    ];

    protected $casts = [
        'options' => 'array',
        'timing' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
