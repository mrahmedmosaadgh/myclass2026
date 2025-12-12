<?php
namespace App\Models\QudratQuantitative;

use Illuminate\Database\Eloquent\Model;

class QdratSkillLevel extends Model
{
    protected $table = 'qdrat_skill_levels';

    protected $fillable = ['name', 'description', 'order', 'created_by'];

    public function skills()
    {
        return $this->hasMany(QdratSkill::class, 'level_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
