<?php

namespace App\Models\CourseManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CourseManagement\Course;
use App\Models\Teacher;
use App\Models\User;

class CourseTeacherAssignment extends Model
{
    protected $table = 'course_teacher_assignments';
    
    protected $fillable = [
        'course_id',
        'teacher_id',
        'assigned_by',
        'assigned_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}