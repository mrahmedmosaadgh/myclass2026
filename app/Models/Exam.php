<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'component_version',
        'settings',
        'metadata',
        'page_options',
        'header_options',
        'footer_options',
        'custom_fields',
    ];

    protected $casts = [
        'settings' => 'array',
        'metadata' => 'array',
        'page_options' => 'array',
        'header_options' => 'array',
        'footer_options' => 'array',
        'custom_fields' => 'array',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
