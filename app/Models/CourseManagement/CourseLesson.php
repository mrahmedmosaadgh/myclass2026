<?php

namespace App\Models\CourseManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class CourseLesson extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'text',
        'data',
        'order',
        'course_section_id',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
