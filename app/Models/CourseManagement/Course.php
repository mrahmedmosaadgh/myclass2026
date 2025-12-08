<?php

namespace App\Models\CourseManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use App\Models\Teacher;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function levels(): HasMany
    {
        return $this->hasMany(CourseLevel::class)->orderBy('order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(CourseTeacherAssignment::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'course_teacher_assignments')
            ->withPivot(['assigned_by', 'assigned_at', 'notes', 'is_active'])
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }
}
