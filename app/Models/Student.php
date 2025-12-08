<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        's_id',
        'name',
        'name_ar',
        'name_cute',
        'order_1',
        'order_2',
        'notes',
        'user_id',
        'parent_id',
        'school_section_id',
        'school_id',
        'data',
        'classroom_id',
        'stage_id',
        'grade_id',
        'classroom_history'
    ];

    protected $casts = [
        'data' => 'array',
        'classroom_history' => 'array'
    ];

    protected $appends = [
        'classroom_name',
        'stage_name',
        'grade_name',
        'parent_name',
        'school_name',
        'first_name',
        'second_name',
        'last_name'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            // Generate a unique s_id if not provided
            if (empty($student->s_id)) {
                do {
                    $uniqueId = 'p' . strtolower(Str::random(4, 'abcdefghjkmnqrstuvwxyz')) . rand(10000, 99999);
                } while (self::where('s_id', $uniqueId)->exists());

                $student->s_id = $uniqueId;
            }







            // Check if a user with the given email already exists
            $user = User::where('email', $student->s_id)->first();
            // $user = User::where('email', $student->email)->first();

            if (!$user) {
                // Create a new user if not exists
                $user = User::create([
                    'name' => $student->name,
                    'email' => $student->s_id,
                    'role' =>  'parent' ,

                    // 'email' => $student->email,
                    // 'password' => bcrypt(Str::random(10)), // Generate a random password
                    'password' => bcrypt('12345678'), // Generate a random password
                ]);
            }

            $student->user_id = $user->id;
        });

        // Auto-assign lesson progress when student is created
        static::created(function ($student) {
            // Get all lessons for this student's grade
            $lessons = \App\Models\free\LessonPresentation::where('grade_id', $student->grade_id)->get();
            
            // Create progress records for each lesson (locked by default)
            foreach ($lessons as $lesson) {
                \App\Models\LessonStudentProgress::create([
                    'lesson_presentation_id' => $lesson->id,
                    'student_id' => $student->id,
                    'status' => 'locked',
                    'color_status' => 'gray',
                    'opened_by_teacher_id' => null,
                    'opened_at' => null,
                ]);
            }
        });
    }








    // Define all relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function parent()
    {
        return $this->belongsTo(StudentParent::class, 'parent_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Accessor methods
    public function getSchoolNameAttribute()
    {
        return $this->school ? $this->school->name : null;
    }

    public function getStageNameAttribute()
    {
        return $this->stage ? $this->stage->name : null;
    }

    public function getGradeNameAttribute()
    {
        return $this->grade ? $this->grade->name : null;
    }

    public function getClassroomNameAttribute()
    {
        return $this->classroom ? $this->classroom->name : null;
    }

    public function getParentNameAttribute()
    {
        return $this->parent ? $this->parent->name : null;
    }

    public function getFirstNameAttribute()
    {
        $name = trim((string)($this->name ?? ''));
        if ($name === '') return '';
        $parts = preg_split('/\s+/', $name);
        return $parts[0] ?? '';
    }

    public function getLastNameAttribute()
    {
        $name = trim((string)($this->name ?? ''));
        if ($name === '') return '';
        $parts = preg_split('/\s+/', $name);
        $count = count($parts);
        return $count > 1 ? ($parts[$count - 1] ?? '') : '';
    }

    public function getSecondNameAttribute()
    {
        $name = trim((string)($this->name ?? ''));
        $parts = preg_split('/\s+/', $name);
        $count = count($parts);
        if ($count > 2) return implode(' ', array_slice($parts, 1, $count - 2));
        return '';
    }


}



