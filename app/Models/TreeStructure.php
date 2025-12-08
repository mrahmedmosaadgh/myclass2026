<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class TreeStructure extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'tree_data'];

    protected $casts = [
        'tree_data' => 'array', // Cast JSON to array automatically
    ];
}
