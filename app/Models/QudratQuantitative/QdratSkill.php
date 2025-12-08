<?php
namespace App\Models\QudratQuantitative;

use Illuminate\Database\Eloquent\Model;

class QdratSkill extends Model
{
    protected $table = 'qdrat_skills';

    protected $fillable = ['name', 'description', 'level_id', 'order', 'created_by', 'import_batch'];

    public function level()
    {
        return $this->belongsTo(QdratSkillLevel::class, 'level_id');
    }

    public function questions()
    {
        return $this->belongsToMany(QdratQuestion::class, 'qdrat_question_skill');
    }

    public function lessons()
    {
        return $this->hasMany(QdratLesson::class, 'skill_id');
    }
}
