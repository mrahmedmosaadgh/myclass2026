<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HR extends Model
{
    use HasFactory;

    protected $table = 'h_r_s';

    protected $fillable = [
        'name',
        'user_id',
        'data',
        'active'
    ];

    protected $casts = [
        'data' => 'array',
        'active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}