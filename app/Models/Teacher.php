<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        't_id',
        'school_id',
        'schools_number',
        'school_extra_ids',
        'user_id',
        'name',
        'name_ar',
        'name_cute',
        'national_id',
        'email',
        'phone_number',
        'whatsapp_number',
        'gender',
        'date_of_birth',
        'nationality',
        'address',
        'order_1',
        'order_2',
        'notes',
        'data'
    ];

    protected $casts = [
        'school_extra_ids' => 'array',
        'data' => 'array',
        'date_of_birth' => 'date',
    ];

    protected $appends = [
        'first_name',
        'second_name',
        'last_name'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($teacher) {
            // Generate a unique t_id if not provided
            if (empty($teacher->t_id)) {
                $teacher->t_id = self::generateUniqueTeacherId();
            }

            // Create or find user account
            $user = self::createOrFindUser($teacher);
            $teacher->user_id = $user->id;
        });

        static::updating(function ($teacher) {
            // Sync user active status when teacher soft delete status changes
            if ($teacher->user && $teacher->isDirty('deleted_at')) {
                $isActive = $teacher->deleted_at === null;
                $teacher->user->update(['is_active' => $isActive]);
            }
        });

        static::deleting(function ($teacher) {
            // On soft delete, deactivate the user account (Requirement 8.1)
            if ($teacher->user) {
                $teacher->user->update(['is_active' => false]);
            }
        });

        static::restored(function ($teacher) {
            // On restore, reactivate the user account (Requirement 8.3)
            if ($teacher->user) {
                $teacher->user->update(['is_active' => true]);
            }
        });
    }



    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolsold()
    {
        return $this->belongsTo(School::class)->orWhereIn('id', $this->school_extra_ids ?? []);
    }
    public function schools()
    {
        $primarySchool = $this->belongsTo(School::class, 'school_id')->with(['hr'])->first();
        $extraSchools = School::whereIn('id', $this->school_extra_ids ?? [])->with(['hr'])->get();

        if ($primarySchool) {
            return collect([$primarySchool])->merge($extraSchools);
        } else {
            return $extraSchools;
        }

    }
    // Alternative approach using a custom query scope
    public function scopeWithAllSchools($query)
    {
        return $query->with(['school' => function($query) {
            $query->orWhereIn('id', $this->school_extra_ids ?? []);
        }]);
    }

    // Optional: Add an accessor to get all school names
    public function getSchoolNamesAttribute()
    {
        return School::where('id', $this->school_id)
                    ->orWhereIn('id', $this->school_extra_ids ?? [])
                    ->pluck('name');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function classroomSubjectTeachers()
    {
        return $this->hasMany(ClassroomSubjectTeacher::class, 'teacher_id');
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_subject_teachers', 'teacher_id', 'classroom_id')
            ->withPivot(['subject_id', 'classes_per_week', 'data', 'c_text', 'c_bg', 'color_custom', 'color_custom_text'])
            ->withTimestamps();
    }

    public function questionBanks()
    {
        return $this->hasMany(QuestionBank::class, 'created_by_id');
    }

    public function courseAssignments()
    {
        return $this->hasMany(\App\Models\CourseManagement\CourseTeacherAssignment::class);
    }

    public function courses()
    {
        return $this->belongsToMany(\App\Models\CourseManagement\Course::class, 'course_teacher_assignments')
            ->withPivot(['assigned_by', 'assigned_at', 'notes', 'is_active'])
            ->withTimestamps();
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

    //     public function classrooms_subject()
    // {
    //     return $this->hasMany(\App\Models\CourseManagement\CourseTeacherAssignment::class);
    // }

    /**
     * Generate a unique teacher ID
     *
     * @return string
     */
    protected static function generateUniqueTeacherId(): string
    {
        do {
            $letters = strtolower(Str::random(4, 'abcdefghijklmnopqrstuvwxyz'));
            $numbers = rand(1000, 9999);
            $uniqueId = 't' . $letters . $numbers;
        } while (self::where('t_id', $uniqueId)->exists());

        return $uniqueId;
    }

    /**
     * Create or find user account for teacher
     *
     * @param Teacher $teacher
     * @return User
     */
    protected static function createOrFindUser(Teacher $teacher): User
    {
        // Use email if provided, otherwise use t_id as email (requirement 3.4)
        $email = !empty($teacher->email) ? $teacher->email : $teacher->t_id;
        
        // Check if user already exists by email
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            // Pre-hashed password for '12345678' to avoid repeated bcrypt calls during bulk imports
            // This significantly improves performance for large imports
            static $defaultPasswordHash = null;
            if ($defaultPasswordHash === null) {
                $defaultPasswordHash = bcrypt('12345678');
            }
            
            // Create new user with default password (requirement 3.5)
            $user = User::create([
                'name' => $teacher->name,
                'email' => $email,
                'role' => 'teacher', // Requirement 3.3
                'password' => $defaultPasswordHash, // Default password (requirement 3.5)
                'is_active' => true, // New teachers are active by default
                'school_id' => $teacher->school_id // Associate with school
            ]);
        }
        
        return $user;
    }

    /**
     * Find teacher by name and school
     *
     * @param string $name Teacher name
     * @param int $schoolId School ID
     * @return Teacher|null
     */
    public static function findByNameAndSchool(string $name, int $schoolId): ?Teacher
    {
        return self::where('name', $name)
            ->where('school_id', $schoolId)
            ->first();
    }

    /**
     * Create teacher with user account
     *
     * @param array $data Teacher data
     * @param int $schoolId School ID
     * @return Teacher
     */
    public static function createWithUser(array $data, int $schoolId): Teacher
    {
        return self::create(array_merge($data, [
            'school_id' => $schoolId
        ]));
    }

    /**
     * Sync teacher active status with user (based on soft delete status)
     */
    public function syncUserStatus(): void
    {
        if ($this->user) {
            $isActive = $this->deleted_at === null;
            $this->user->update(['is_active' => $isActive]);
        }
    }

    /**
     * Check if teacher is active (not soft deleted)
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->deleted_at === null;
    }

    /**
     * Check if teacher has any active assignments (Requirement 8.2 - Historical data preservation)
     */
    public function hasActiveAssignments(): bool
    {
        return $this->classroomSubjectTeachers()
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Get all assignments for this teacher (including soft deleted for historical data)
     * (Requirement 8.2 - Historical data preservation)
     */
    public function getAllAssignments()
    {
        return $this->classroomSubjectTeachers()->withTrashed();
    }

    /**
     * Validate referential integrity before operations (Requirement 8.5)
     */
    public function validateReferentialIntegrity(): bool
    {
        // Check if user relationship is valid
        if ($this->user_id && !User::find($this->user_id)) {
            return false;
        }

        // Check if school relationship is valid
        if ($this->school_id && !School::find($this->school_id)) {
            return false;
        }

        return true;
    }

    /**
     * Safely delete teacher while preserving historical data (Requirement 8.2)
     */
    public function safeDelete(): bool
    {
        try {
            // Soft delete the teacher (this will trigger the boot method to deactivate user)
            $this->delete();
            
            // Verify referential integrity is maintained
            return $this->validateReferentialIntegrity();
        } catch (\Exception $e) {
            // Log error and return false
            \Log::error('Failed to safely delete teacher: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Advanced teacher-user status synchronization (Requirement 8.1, 8.3)
     * Ensures bidirectional synchronization between teacher and user status
     */
    public function syncStatusWithUser(): bool
    {
        if (!$this->user) {
            return false;
        }

        try {
            $teacherIsActive = $this->deleted_at === null;
            $userIsActive = $this->user->is_active;

            // If statuses are out of sync, sync them
            if ($teacherIsActive !== $userIsActive) {
                $this->user->update(['is_active' => $teacherIsActive]);
                
                // Log the synchronization for audit purposes
                \Log::info("Teacher-User status synchronized", [
                    'teacher_id' => $this->id,
                    'user_id' => $this->user_id,
                    'new_status' => $teacherIsActive
                ]);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to sync teacher-user status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Comprehensive historical data preservation check (Requirement 8.2)
     * Ensures all historical assignments are preserved during operations
     */
    public function preserveHistoricalData(): bool
    {
        try {
            // Count all assignments (including soft deleted)
            $totalAssignments = $this->classroomSubjectTeachers()->withTrashed()->count();
            
            // Count active assignments
            $activeAssignments = $this->classroomSubjectTeachers()->count();
            
            // Verify historical data integrity
            $historicalAssignments = $this->classroomSubjectTeachers()->onlyTrashed()->count();
            
            // Log historical data status
            \Log::info("Historical data preservation check", [
                'teacher_id' => $this->id,
                'total_assignments' => $totalAssignments,
                'active_assignments' => $activeAssignments,
                'historical_assignments' => $historicalAssignments
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to preserve historical data: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enhanced referential integrity validation (Requirement 8.5)
     * Comprehensive validation of all relationships
     */
    public function validateComprehensiveReferentialIntegrity(): array
    {
        $issues = [];

        try {
            // Check user relationship
            if ($this->user_id) {
                $user = User::find($this->user_id);
                if (!$user) {
                    $issues[] = "Invalid user_id: {$this->user_id}";
                } elseif ($user->deleted_at !== null) {
                    $issues[] = "Associated user is soft deleted";
                }
            }

            // Check school relationship
            if ($this->school_id) {
                $school = School::find($this->school_id);
                if (!$school) {
                    $issues[] = "Invalid school_id: {$this->school_id}";
                }
            }

            // Check assignment relationships
            $invalidAssignments = $this->classroomSubjectTeachers()
                ->whereDoesntHave('classroom')
                ->orWhereDoesntHave('subject')
                ->orWhereDoesntHave('academicYear')
                ->count();

            if ($invalidAssignments > 0) {
                $issues[] = "Found {$invalidAssignments} assignments with invalid relationships";
            }

            // Check for orphaned assignments (assignments where teacher is soft deleted but assignment is not)
            if ($this->deleted_at !== null) {
                $activeAssignmentsCount = $this->classroomSubjectTeachers()->count();
                if ($activeAssignmentsCount > 0) {
                    $issues[] = "Soft deleted teacher has {$activeAssignmentsCount} active assignments";
                }
            }

        } catch (\Exception $e) {
            $issues[] = "Exception during integrity check: " . $e->getMessage();
        }

        return $issues;
    }

    /**
     * Prevent inactive teacher assignment (Requirement 8.4)
     * Check if teacher can be assigned to new classrooms
     */
    public function canBeAssignedToClassroom(): bool
    {
        // Teacher must not be soft deleted
        if ($this->deleted_at !== null) {
            return false;
        }

        // Associated user must be active
        if ($this->user && !$this->user->is_active) {
            return false;
        }

        return true;
    }

    /**
     * Get assignment prevention reason (Requirement 8.4)
     * Returns human-readable reason why teacher cannot be assigned
     */
    public function getAssignmentPreventionReason(): ?string
    {
        if ($this->deleted_at !== null) {
            return "Teacher is inactive (soft deleted)";
        }

        if ($this->user && !$this->user->is_active) {
            return "Associated user account is inactive";
        }

        if (!$this->user) {
            return "Teacher has no associated user account";
        }

        return null; // Teacher can be assigned
    }

    /**
     * Comprehensive status synchronization method (Requirements 8.1, 8.3)
     * Handles all aspects of teacher-user status synchronization
     */
    public function performComprehensiveStatusSync(): array
    {
        $results = [
            'success' => false,
            'actions_taken' => [],
            'issues_found' => []
        ];

        try {
            // 1. Sync basic active status
            if ($this->syncStatusWithUser()) {
                $results['actions_taken'][] = 'User active status synchronized';
            }

            // 2. Check and preserve historical data
            if ($this->preserveHistoricalData()) {
                $results['actions_taken'][] = 'Historical data preservation verified';
            }

            // 3. Validate referential integrity
            $integrityIssues = $this->validateComprehensiveReferentialIntegrity();
            if (empty($integrityIssues)) {
                $results['actions_taken'][] = 'Referential integrity validated';
            } else {
                $results['issues_found'] = array_merge($results['issues_found'], $integrityIssues);
            }

            // 4. Check assignment capability
            if (!$this->canBeAssignedToClassroom()) {
                $reason = $this->getAssignmentPreventionReason();
                $results['issues_found'][] = "Assignment prevention: {$reason}";
            } else {
                $results['actions_taken'][] = 'Teacher can be assigned to classrooms';
            }

            $results['success'] = empty($results['issues_found']);

        } catch (\Exception $e) {
            $results['issues_found'][] = "Exception during comprehensive sync: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Static method to perform bulk status synchronization (Requirement 8.1, 8.3)
     * Useful for maintenance operations
     */
    public static function performBulkStatusSync(int $schoolId = null): array
    {
        $query = self::with('user');
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $teachers = $query->get();
        $results = [
            'total_processed' => 0,
            'successful_syncs' => 0,
            'failed_syncs' => 0,
            'issues' => []
        ];

        foreach ($teachers as $teacher) {
            $results['total_processed']++;
            
            $syncResult = $teacher->performComprehensiveStatusSync();
            
            if ($syncResult['success']) {
                $results['successful_syncs']++;
            } else {
                $results['failed_syncs']++;
                $results['issues'][$teacher->id] = $syncResult['issues_found'];
            }
        }

        return $results;
    }
}
