<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassroomSubjectTeacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'classroom_id',
        'subject_id',
        'teacher_id',
        'classes_per_week',
        'color_custom', // Added
        'c_text',       // Added
        'c_bg',         // Added
        'color_custom_text', // Added
        'data'          // Keep if still used for other things
    ];

    protected $casts = [
        'data' => 'json',
    ];

    protected $appends = [
        'school_name',
        'grade_id',
        'grade_name',
        'classroom_name',
        'subject_name',
        'teacher_name'
    ];
//   ?????  // Load all relationships efficiently
// $assignments = ClassroomSubjectTeacher::with([
//     'school:id,name',
//     'grade:id,name',
//     'classroom:id,name',
//     'subject:id,name',
//     'teacher:id,name'
// ])->get();

    // Accessor methods
    public function getSchoolNameAttribute()
    {
        return $this->school ? $this->school->name : null;
    }

    public function getGradeIdAttribute()
    {
        return optional($this->classroom)->grade_id;
    }

    public function getGradeNameAttribute()
    {
        return $this->classroom->grade ? $this->classroom->grade->name : null;
    }

    public function getClassroomNameAttribute()
    {
        return $this->classroom ? $this->classroom->name : null;
    }

    public function getSubjectNameAttribute()
    {
        return $this->subject ? $this->subject->name : null;
    }

    public function getTeacherNameAttribute()
    {
        return $this->teacher ? $this->teacher->name : null;
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
    // public function classrooms()
    // {
    //     return $this->belongsToMany(Classroom::class, 'classroom_subject_teachers', 'teacher_id', 'classroom_id')
    //         ->withPivot(['subject_id', 'classes_per_week', 'data'])
    //         ->withTimestamps();
    // }
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

 

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

public function schedules()
{
    return $this->hasMany(Schedule::class, 'cst_id');
}

    public function cst()
    {
        return $this->belongsTo(ClassroomSubjectTeacher::class, 'cst_id');
    }
    protected static function boot()
    {
        parent::boot();

        // Use 'creating' to only run this logic when a new record is made
        static::creating(function ($classroomSubjectTeacher) {
            if ($classroomSubjectTeacher->classroom_id) {
                try {
                    $classroom = Classroom::findOrFail($classroomSubjectTeacher->classroom_id);

                    // Verify classroom belongs to the selected school
                    if ($classroomSubjectTeacher->school_id && $classroom->school_id != $classroomSubjectTeacher->school_id) {
                        // Consider logging this or handling it differently if needed
                        // For now, we'll just prevent saving by throwing an exception
                        throw new \Exception("Selected classroom does not belong to the selected school.");
                    }

                    // Generate random background and contrasting text color
                    $r = mt_rand(0, 255);
                    $g = mt_rand(0, 255);
                    $b = mt_rand(0, 255);
                    $bgColor = sprintf('#%02x%02x%02x', $r, $g, $b);

                    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                    $textColor = $luminance > 0.5 ? '#000000' : '#FFFFFF'; // Black for light bg, White for dark bg

                    // Set the dedicated color columns
                    $classroomSubjectTeacher->c_bg = $bgColor;
                    $classroomSubjectTeacher->c_text = $textColor;

                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    throw new \Exception("Invalid classroom selected");
                }
            }
        });

    }
}
