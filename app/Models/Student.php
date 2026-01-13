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

            // Normalize the student name for consistent user account creation
            $normalizedName = self::normalizeName($student->name);

            // Check if a user with the given email already exists
            $user = User::where('email', $student->s_id)->first();
            // $user = User::where('email', $student->email)->first();

            if (!$user) {
                // Create a new user with normalized name
                $user = User::create([
                    'name' => $normalizedName,
                    'email' => $student->s_id,
                    'role' => 'student',

                    // 'email' => $student->email,
                    // 'password' => bcrypt(Str::random(10)), // Generate a random password
                    'password' => bcrypt('12345678'), // Generate a random password
                ]);
            } else {
                // Update existing user name to normalized version if different
                if ($user->name !== $normalizedName) {
                    $user->update(['name' => $normalizedName]);
                }
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

        // Track classroom and grade changes
        static::updating(function ($student) {
            if ($student->isDirty('classroom_id') || $student->isDirty('grade_id')) {
                $original = $student->getOriginal();
                
                // Only log if there was a previous classroom (not initial assignment)
                if ($original['classroom_id']) {
                    StudentClassroomHistory::create([
                        'student_id' => $student->id,
                        'from_classroom_id' => $original['classroom_id'],
                        'to_classroom_id' => $student->classroom_id,
                        'from_grade_id' => $original['grade_id'],
                        'to_grade_id' => $student->grade_id,
                        'academic_year_id' => $student->getActiveAcademicYearId(),
                        'semester_id' => $student->getActiveSemesterId(),
                        'changed_by_user_id' => auth()->id() ?? 1,
                        'change_reason' => 'Manual update',
                        'changed_at' => now(),
                    ]);
                }
            }
        });
    }

    /**
     * Normalize name for consistent formatting and duplicate detection
     * 
     * @param string $name Name to normalize
     * @return string Normalized name
     */
    protected static function normalizeName(string $name): string
    {
        // Trim whitespace
        $normalized = trim($name);
        
        // Replace multiple spaces with single space
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        // Convert to title case for consistency (e.g., "Ahmed Ali")
        // This preserves proper capitalization while ensuring consistency
        $normalized = mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
        
        return $normalized;
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

    public function classroomHistories()
    {
        return $this->hasMany(StudentClassroomHistory::class)->orderBy('changed_at', 'desc');
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

    /**
     * Helper methods for classroom management
     */
    public function changeClassroom($newClassroomId, $newGradeId, $reason, $notes = null)
    {
        $oldClassroomId = $this->classroom_id;
        $oldGradeId = $this->grade_id;

        $this->classroom_id = $newClassroomId;
        $this->grade_id = $newGradeId;
        $this->save();

        // Log the change with custom reason
        if ($oldClassroomId) {
            StudentClassroomHistory::create([
                'student_id' => $this->id,
                'from_classroom_id' => $oldClassroomId,
                'to_classroom_id' => $newClassroomId,
                'from_grade_id' => $oldGradeId,
                'to_grade_id' => $newGradeId,
                'academic_year_id' => $this->getActiveAcademicYearId(),
                'semester_id' => $this->getActiveSemesterId(),
                'changed_by_user_id' => auth()->id() ?? 1,
                'change_reason' => $reason,
                'notes' => $notes,
                'changed_at' => now(),
            ]);
        }

        return $this;
    }

    public function promoteToNextGrade($targetGradeId, $targetClassroomId, $academicYearId, $reason = 'Year-end promotion')
    {
        return $this->changeClassroom($targetClassroomId, $targetGradeId, $reason);
    }

    protected function getActiveAcademicYearId()
    {
        // Get active academic year for the student's school
        return $this->school->academic_year_id ?? null;
    }

    protected function getActiveSemesterId()
    {
        // Get active semester for the student's school
        return $this->school->semester_id ?? null;
    }

    /**
     * Query scopes
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('name_ar', 'like', "%{$term}%")
              ->orWhere('s_id', 'like', "%{$term}%");
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }


}



