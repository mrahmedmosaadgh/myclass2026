<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Behavior extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'points',
        'school_id',
        'year_id',
    ];

    public function studentBehaviors()
    {
        return $this->hasMany(StudentBehavior::class);
    }
}
