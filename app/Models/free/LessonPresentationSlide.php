<?php

namespace App\Models\free;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPresentationSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_presentation_id',
        'section',
        'slide_type',
        'slide_content',
    ];

    protected $casts = [
        'slide_content' => 'array',
    ];

    public function presentation()
    {
        return $this->belongsTo(LessonPresentation::class, 'lesson_presentation_id');
    }
}
