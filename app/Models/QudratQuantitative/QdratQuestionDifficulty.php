<?php
namespace App\Models\QudratQuantitative;

use Illuminate\Database\Eloquent\Model;

class QdratQuestionDifficulty extends Model
{
    protected $table = 'qdrat_question_difficulties';

    protected $fillable = ['name', 'description', 'order', 'created_by'];

    public function questions()
    {
        return $this->hasMany(QdratQuestion::class, 'difficulty_level_id');
    }
}
