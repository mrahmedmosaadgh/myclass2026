<?php
namespace App\Models\QudratQuantitative;

use Illuminate\Database\Eloquent\Model;

class QdratLesson extends Model
{
    protected $table = 'qdrat_lessons';

    protected $fillable = [
        'title', 'video_url', 'content', 'skill_id',
        'category_id', 'type', 'order', 'created_by'
    ];

    public function category()
    {
        return $this->belongsTo(QdratLessonCategory::class, 'category_id');
    }

    public function skill()
    {
        return $this->belongsTo(QdratSkill::class, 'skill_id');
    }
}
