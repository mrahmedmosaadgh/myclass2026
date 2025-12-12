<?php
namespace App\Models\QudratQuantitative;

use Illuminate\Database\Eloquent\Model;

class QdratQuestion extends Model
{
    protected $table = 'qdrat_questions';

    protected $fillable = [
        'content', 'type', 'difficulty_level_id',
        'answer_text', 'options_json', 'created_by'
    ];

    protected $casts = [
        'options_json' => 'array',
    ];

    public function difficulty()
    {
        return $this->belongsTo(QdratQuestionDifficulty::class, 'difficulty_level_id');
    }

    public function skills()
    {
        return $this->belongsToMany(QdratSkill::class, 'qdrat_question_skill');
    }
}
