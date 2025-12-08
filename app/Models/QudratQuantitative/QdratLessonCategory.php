<?php
namespace App\Models\QudratQuantitative;

use Illuminate\Database\Eloquent\Model;

class QdratLessonCategory extends Model
{
    protected $table = 'qdrat_lesson_categories';

    protected $fillable = ['name', 'description', 'order', 'created_by'];

    public function lessons()
    {
        return $this->hasMany(QdratLesson::class, 'category_id');
    }
}
