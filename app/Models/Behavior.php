<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Behavior extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'type',
        'points',
        'school_id',
        'year_id',
        'teacher_id',
    ];

    /**
     * Scope to get behaviors for a specific teacher
     * Returns school defaults (teacher_id = null) + teacher's custom behaviors
     */
    public function scopeForTeacher($query, $schoolId, $teacherId = null)
    {
        return $query->where('school_id', $schoolId)
            ->where(function ($q) use ($teacherId) {
                $q->whereNull('teacher_id'); // School defaults
                if ($teacherId) {
                    $q->orWhere('teacher_id', $teacherId); // Teacher custom
                }
            });
    }

    public function teacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class);
    }

    public function studentBehaviors()
    {
        return $this->hasMany(StudentBehavior::class);
    }
}
