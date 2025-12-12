<?php

namespace App\Models\Resumes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'style',
        'is_public',
    ];

    protected $casts = [
        'style' => 'array',
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
