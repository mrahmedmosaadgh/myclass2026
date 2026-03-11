<?php

namespace App\Models\Courses\bm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BMQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bm_questions';

    protected $fillable = [
        'domain',
        'sub_skill',
        'difficulty',
        'template',
        'parameters_json',
        'correct_answer',
        'explanation',
    ];

    protected $casts = [
        'parameters_json' => 'array',
        'difficulty' => 'integer',
    ];
}
