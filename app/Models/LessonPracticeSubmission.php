<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPracticeSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_student_progress_id',
        'submission_type',
        'file_path',
        'drawing_data',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function progress()
    {
        return $this->belongsTo(LessonStudentProgress::class, 'lesson_student_progress_id');
    }

    // Helper Methods
    public function isUpload()
    {
        return $this->submission_type === 'upload';
    }

    public function isDrawing()
    {
        return $this->submission_type === 'drawing';
    }

    public function getFileUrl()
    {
        if ($this->isUpload() && $this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }
}
