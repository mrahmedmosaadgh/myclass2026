<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSection extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'school_id', 'data'];

    protected $casts = [
        'data' => 'json'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}