# 2026-01-01 | HR, Schools, Teachers, and Admin Roles - Complete Reference Guide

## Overview

This document provides a comprehensive reference for understanding the hierarchical relationship between HR, Schools, Teachers, and Admin roles in the system. It covers database design, best practices, role permissions, and security considerations.

---

## Database Architecture

### Hierarchy Structure

```
h_r_s (HR table)
  ↓ (one-to-many)
schools (each school belongs to one HR)
  ↓ (one-to-many)
teachers (each teacher has primary school + optional extra schools)
  ↓ (many-to-many through pivot)
classroom_subject_teachers (assignment junction table)
```

### Key Tables

#### 1. `h_r_s` Table

-   **Purpose**: HR (Human Resources) administrators who manage multiple schools
-   **Key Fields**:
    -   `id` - Primary key
    -   `user_id` - Foreign key to users table
    -   `name` - HR name
    -   `active` - Boolean status
    -   `data` - JSON for additional metadata

#### 2. `schools` Table

-   **Purpose**: Individual schools managed by HR
-   **Key Fields**:
    -   `id` - Primary key
    -   `h_r_id` - Foreign key to h_r_s table (required)
    -   `name` - School name
    -   `data` - JSON for school metadata (address, phone, logo, etc.)

#### 3. `teachers` Table

-   **Purpose**: Teachers who work at one or more schools
-   **Key Fields**:
    -   `id` - Primary key
    -   `t_id` - Unique teacher identifier (auto-generated)
    -   `school_id` - **PRIMARY SCHOOL** (nullable but should always be set)
    -   `schools_number` - Count of schools teacher works at (default: 1)
    -   `school_extra_ids` - JSON array of additional school IDs
    -   `user_id` - Foreign key to users table (auto-created)
    -   `name`, `email`, `phone_number`, `national_id`, etc.
    -   `deleted_at` - Soft delete timestamp

#### 4. `classroom_subject_teachers` Table

-   **Purpose**: Junction table for classroom-subject-teacher assignments
-   **Key Fields**:
    -   `school_id` - Required for data isolation
    -   `academic_year_id` - Required for historical tracking
    -   `classroom_id` - Foreign key to classrooms
    -   `subject_id` - Foreign key to subjects
    -   `teacher_id` - Foreign key to teachers (nullable)
    -   `classes_per_week` - Number of periods
    -   `deleted_at` - Soft delete for historical data preservation

#### 5. `users` Table

-   **Purpose**: Authentication and authorization
-   **Key Fields**:
    -   `id` - Primary key
    -   `role` - ENUM: 'SuperAdmin', 'admin', 'supervisor', 'teacher', 'student', 'parent', 'user', 'guest', 'hr_admin'
    -   `school_id` - Direct school association (nullable)
    -   `is_active` - Boolean status (synced with teacher soft deletes)

---

## Role Definitions and Permissions

### 1. HR Admin (`hr_admin`)

**Who**: Top-level administrators managing multiple schools

**Database Relationships**:

-   Has a record in `h_r_s` table
-   `user.school_id` is set to their primary school
-   Can own multiple schools via `schools.h_r_id`

**Permissions**:

-   ✅ Create new schools
-   ✅ Manage all schools they own
-   ✅ Access all schools' data
-   ✅ Add/edit/delete teachers across all their schools
-   ✅ Add/edit/delete students across all their schools
-   ✅ Full system access for their schools

**School Access Logic**:

```php
case 'hr_admin':
    return School::select('id', 'name')->get(); // All schools
```

### 2. School Admin (`admin` role)

**Who**: School-level administrators (can be promoted teachers)

**Database Relationships**:

-   May have a record in `teachers` table (if promoted from teacher)
-   `user.school_id` OR derived from `teacher.school_id`
-   NO record in `h_r_s` table

**Permissions**:

-   ✅ Manage their assigned school only
-   ✅ Add/edit/delete teachers in their school
-   ✅ Add/edit/delete students in their school
-   ✅ Manage subjects, grades, classrooms, schedules
-   ✅ Import teachers via Excel
-   ✅ Access admin dashboard and features
-   ❌ **CANNOT create new schools** (requires HR record)
-   ❌ Cannot access other schools' data

**School Access Logic**:

```php
case 'admin':
    $schoolId = $user->schoolId();
    if ($schoolId) {
        return School::where('id', $schoolId)->select('id', 'name')->get();
    }
    return collect();
```

### 3. Teacher (`teacher` role)

**Who**: Regular teachers teaching classes

**Database Relationships**:

-   Has a record in `teachers` table
-   `teacher.school_id` is their primary school
-   `teacher.school_extra_ids` for additional schools (JSON array)

**Permissions**:

-   ✅ View their classes and students
-   ✅ Manage grades and attendance
-   ✅ Create lesson plans and materials
-   ✅ Access teacher dashboard
-   ❌ Cannot add other teachers
-   ❌ Cannot manage school structure
-   ❌ Limited administrative access

**School Access Logic**:

```php
case 'teacher':
    $schoolId = $user->teacher?->school_id;
    if ($schoolId) {
        return School::where('id', $schoolId)->select('id', 'name')->get();
    }
    return collect();
```

### 4. Teacher-Admin (Dual Role)

**Who**: Teachers promoted to admin without losing teacher status

**Database Relationships**:

-   Has a record in `teachers` table (keeps teacher identity)
-   Has BOTH `teacher` and `admin` Spatie roles
-   `teacher.school_id` determines their school scope

**Permissions**:

-   ✅ All teacher permissions
-   ✅ All school admin permissions (scoped to their school)
-   ✅ Can access both teacher and admin routes
-   ❌ Still cannot create new schools

**Implementation**:

```php
// Assign dual roles using Spatie
$user->assignRole(['teacher', 'admin']);

// They keep teacher record for school_id
// They get admin permissions for management
```

---

## Best Practices

### 1. Always Set `school_id` for Teachers

**✅ DO:**

```php
Teacher::create([
    'school_id' => $schoolId,  // REQUIRED
    'name' => $name,
    // user_id auto-created by boot() method
]);
```

**❌ DON'T:**

```php
Teacher::create([
    'school_id' => null,  // Avoid this
    'name' => $name,
]);
```

**Why**:

-   Ensures data integrity
-   Enables proper school-based filtering
-   Required for permission checks
-   Prevents orphaned records

### 2. Use Database Transactions for Multi-Step Operations

```php
DB::transaction(function () use ($schoolId, $teacherData) {
    // 1. Create user account
    $user = User::create([...]);

    // 2. Create teacher record
    $teacher = Teacher::create([
        'school_id' => $schoolId,
        'user_id' => $user->id,
        ...
    ]);

    // 3. Create assignments
    ClassroomSubjectTeacher::create([...]);
});
```

### 3. Validate School Ownership in Controllers

```php
public function update(Request $request, Teacher $teacher)
{
    $user = auth()->user();

    // Ensure admin can only edit teachers in their school
    if ($user->role === 'admin' && $teacher->school_id !== $user->schoolId()) {
        abort(403, 'Unauthorized: Cannot edit teachers from other schools');
    }

    $teacher->update($request->validated());
}
```

### 4. Handle Multi-School Teachers Properly

```php
// Primary school
$teacher->school_id = 1;

// Additional schools
$teacher->school_extra_ids = [2, 3]; // JSON array
$teacher->schools_number = 3; // Total count

// Access all schools
$allSchools = $teacher->schools(); // Returns collection
```

### 5. Use Excel Import for First-Time Setup

**Recommended for bulk teacher creation:**

-   Faster than manual entry
-   Validates data automatically
-   Creates teachers + assignments together
-   Handles user account creation

**Excel Format:**

```
Classroom | Subject      | Teacher Name | Periods_per_Week | Teacher Email
10-A      | Mathematics  | John Smith   | 5                | john.smith@school.com
10-A      | English      | Jane Doe     | 4                | jane.doe@school.com
```

---

## Common Workflows

### Workflow 1: HR Creates a New School

```php
DB::transaction(function () use ($hrId, $schoolData) {
    // 1. Create school
    $school = School::create([
        'name' => $schoolData['name'],
        'h_r_id' => $hrId,
        'data' => [
            'address' => $schoolData['address'] ?? null,
            'phone' => $schoolData['phone'] ?? null,
        ]
    ]);

    // 2. Create academic year
    AcademicYear::create([
        'school_id' => $school->id,
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-06-30',
        'active' => true
    ]);

    // 3. Create basic structure (stages, grades, etc.)
    // ...

    return $school;
});
```

### Workflow 2: Admin Adds a Teacher

```php
DB::transaction(function () use ($teacherData, $schoolId) {
    // 1. Create user account
    $user = User::create([
        'name' => $teacherData['name'],
        'email' => $teacherData['email'],
        'password' => Hash::make('default123'),
        'role' => 'teacher',
        'school_id' => $schoolId,
        'is_active' => true
    ]);

    // 2. Create teacher record (boot() method auto-creates user if needed)
    $teacher = Teacher::create([
        't_id' => Teacher::generateUniqueTeacherId(),
        'user_id' => $user->id,
        'school_id' => $schoolId, // PRIMARY SCHOOL
        'schools_number' => 1,
        'name' => $teacherData['name'],
        'email' => $teacherData['email'],
        'phone_number' => $teacherData['phone'] ?? null,
    ]);

    return $teacher;
});
```

### Workflow 3: Promote Teacher to Admin

```php
// Get teacher's user account
$user = User::find($teacherId);

// Option 1: Add admin role (keeps teacher role)
$user->assignRole('admin');
// Now has BOTH roles: ['teacher', 'admin']

// Option 2: Change primary role
$user->role = 'admin';
$user->save();
$user->assignRole('admin');

// Teacher record remains intact
// school_id comes from teacher->school_id
// Can access both teacher and admin routes
```

### Workflow 4: Assign Teacher to Classroom

```php
ClassroomSubjectTeacher::create([
    'school_id' => $schoolId,           // Required for isolation
    'academic_year_id' => $academicYearId,
    'classroom_id' => $classroomId,
    'subject_id' => $subjectId,
    'teacher_id' => $teacherId,
    'classes_per_week' => 5,
    'color_custom' => '#FF5722',        // Optional
    'color_custom_text' => '#FFFFFF',   // Optional
]);
```

---

## Security Considerations

### 1. School Data Isolation

**Problem**: Admins should only see their school's data

**Solution**:

```php
// In controllers
$user = auth()->user();
$schoolId = $user->schoolId();

// Filter queries by school
$teachers = Teacher::where('school_id', $schoolId)->get();
$students = Student::where('school_id', $schoolId)->get();
$classrooms = Classroom::where('school_id', $schoolId)->get();
```

### 2. Prevent Cross-School Access

**Always validate ownership:**

```php
// Before updating/deleting
if ($resource->school_id !== auth()->user()->schoolId()) {
    abort(403, 'Unauthorized access');
}
```

### 3. School Creation Restriction

**Only HR admins can create schools:**

```php
// In SchoolController@store
public function store(Request $request)
{
    // Validate h_r_id exists
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'h_r_id' => 'required|exists:h_r_s,id',
    ]);

    // Only users with HR record can create schools
    $hr = HR::where('user_id', auth()->id())->first();
    if (!$hr) {
        abort(403, 'Only HR admins can create schools');
    }

    School::create($validated);
}
```

### 4. Soft Delete Synchronization

**Teacher soft deletes sync with user status:**

```php
// In Teacher model boot() method
static::deleting(function ($teacher) {
    // On soft delete, deactivate user account
    if ($teacher->user) {
        $teacher->user->update(['is_active' => false]);
    }
});

static::restored(function ($teacher) {
    // On restore, reactivate user account
    if ($teacher->user) {
        $teacher->user->update(['is_active' => true]);
    }
});
```

---

## User Model School Resolution

### How `schoolId()` Works

```php
public function schoolId(): ?int
{
    // 1. Check direct school_id field
    if ($this->school_id) {
        return $this->school_id;
    }

    // 2. Check teacher relationship
    if ($this->teacher) {
        return $this->teacher->school_id;
    }

    // 3. Check student relationship
    if ($this->student) {
        return $this->student->school_id;
    }

    // 4. No school found
    return null;
}
```

### Role-Based School Resolution

```php
public function schoolIdRole(): ?int
{
    return match ($this->role) {
        'student' => $this->student?->school_id,
        'teacher' => $this->teacher?->school_id,
        'admin'   => $this->adminSchool()?->id,  // May need fixing
        'hr_admin' => $this->school_id,
        default   => null,
    };
}
```

**⚠️ Note**: The `adminSchool()` method may need implementation for teacher-admins.

---

## Comparison Table: Role Capabilities

| Feature                     | HR Admin     | School Admin          | Teacher-Admin         | Teacher     |
| --------------------------- | ------------ | --------------------- | --------------------- | ----------- |
| **Create Schools**          | ✅ Yes       | ❌ No                 | ❌ No                 | ❌ No       |
| **Manage Multiple Schools** | ✅ All owned | ❌ One only           | ❌ One only           | ❌ One only |
| **Add Teachers**            | ✅ Yes       | ✅ Yes (their school) | ✅ Yes (their school) | ❌ No       |
| **Add Students**            | ✅ Yes       | ✅ Yes (their school) | ✅ Yes (their school) | ❌ Limited  |
| **Manage Schedules**        | ✅ Yes       | ✅ Yes (their school) | ✅ Yes (their school) | ❌ Limited  |
| **User Management**         | ✅ Yes       | ✅ Yes (limited)      | ✅ Yes (limited)      | ❌ No       |
| **Access Admin Routes**     | ✅ Yes       | ✅ Yes                | ✅ Yes                | ❌ No       |
| **Has HR Record**           | ✅ Yes       | ❌ No                 | ❌ No                 | ❌ No       |
| **Has Teacher Record**      | ❌ No        | Maybe                 | ✅ Yes                | ✅ Yes      |
| **Excel Import**            | ✅ Yes       | ✅ Yes                | ✅ Yes                | ❌ No       |
| **View All Schools**        | ✅ Yes       | ❌ No                 | ❌ No                 | ❌ No       |

---

## Routes and Middleware

### Admin Routes (School-Scoped)

```php
// routes/r_hr.php
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('school', SchoolController::class);
        Route::resource('teacher', TeacherController::class);
        Route::resource('students', StudentController::class);
        Route::resource('classroom', ClassroomController::class);
        Route::resource('subject', SubjectController::class);
        // ... more resources
    });
```

### Teacher Routes

```php
// routes/r_teacher.php
Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/home', [TeacherController::class, 'home']);
        Route::get('/classes', [TeacherController::class, 'classes']);
        Route::get('/attendance', [TeacherController::class, 'attendance']);
        // ... more routes
    });
```

### Dual Role Access (Teacher-Admin)

```php
// Teacher-admins can access BOTH:
// - /teacher/* routes (via 'teacher' role)
// - /admin/* routes (via 'admin' role)

// Middleware checks using Spatie
Route::middleware(['auth', 'role:teacher|admin'])
```

---

## Database Migrations Reference

### Key Migrations

1. **`2023_12_31_000000_create_h_r_s_table.php`**

    - Creates HR table with user_id, name, active

2. **`2024_01_01_000000_create_schools_table.php`**

    - Creates schools table with h_r_id foreign key

3. **`2024_11_29_145203_create_teachers_table.php`**

    - Creates teachers table with school_id, school_extra_ids, schools_number

4. **`2024_11_29_145520_create_classroom_subject_teachers_table.php`**

    - Creates assignment junction table with school_id for isolation

5. **`2025_12_30_022758_add_school_id_to_users_table.php`**

    - Adds school_id to users table for direct association

6. **`2025_12_30_022426_add_hr_admin_to_users_role_enum.php`**
    - Adds 'hr_admin' to user role ENUM

---

## Models Reference

### Key Model Relationships

#### School Model

```php
public function hr() { return $this->belongsTo(HR::class, 'h_r_id'); }
public function teachers() { return $this->hasMany(Teacher::class); }
public function students() { return $this->hasMany(Student::class); }
public function stages() { return $this->hasMany(Stage::class); }
public function grades() { return $this->hasMany(Grade::class); }
public function classrooms() { return $this->hasMany(Classroom::class); }
public function subjects() { return $this->hasMany(Subject::class); }
```

#### Teacher Model

```php
public function school() { return $this->belongsTo(School::class); }
public function user() { return $this->belongsTo(User::class); }
public function classroomSubjectTeachers() { return $this->hasMany(ClassroomSubjectTeacher::class); }

// Multi-school support
public function schools() {
    $primarySchool = $this->belongsTo(School::class, 'school_id')->first();
    $extraSchools = School::whereIn('id', $this->school_extra_ids ?? [])->get();
    return collect([$primarySchool])->merge($extraSchools);
}
```

#### User Model

```php
public function teacher() { return $this->hasOne(Teacher::class); }
public function student() { return $this->hasOne(Student::class); }
public function school() { return $this->belongsTo(School::class); }
```

---

## Troubleshooting

### Issue 1: Teacher-Admin Cannot Access School Data

**Symptom**: 403 errors or empty data

**Cause**: School access logic assumes admins have `h_r_id`

**Fix**:

```php
// In SchoolAccessTrait or controllers
elseif ($isAdmin) {
    // Use schoolId() method instead of h_r_id
    $schoolId = $user->schoolId();
    if ($schoolId) {
        $schools = School::where('id', $schoolId)
            ->with(['stages', 'grades', 'classrooms'])
            ->get();
    }
}
```

### Issue 2: Cannot Create School

**Symptom**: Validation error on `h_r_id`

**Cause**: User doesn't have HR record

**Solution**: Only HR admins can create schools. Regular admins cannot.

### Issue 3: Teacher Soft Delete Not Syncing

**Symptom**: Deleted teachers can still log in

**Cause**: User `is_active` not synced

**Fix**: Ensure Teacher model boot() methods are working:

```php
static::deleting(function ($teacher) {
    if ($teacher->user) {
        $teacher->user->update(['is_active' => false]);
    }
});
```

### Issue 4: Orphaned Assignments

**Symptom**: 500 errors when loading school data

**Cause**: ClassroomSubjectTeacher references deleted records

**Fix**: Use null-safe operators and eager loading:

```php
$assignment->classroom?->grade?->name ?? 'N/A'
```

---

## Testing Checklist

### HR Admin Tests

-   [ ] Can create new schools
-   [ ] Can view all their schools
-   [ ] Can add teachers to any school
-   [ ] Can add students to any school
-   [ ] Cannot access other HR's schools

### School Admin Tests

-   [ ] Can view only their school
-   [ ] Can add teachers to their school
-   [ ] Can add students to their school
-   [ ] Cannot create new schools
-   [ ] Cannot access other schools

### Teacher-Admin Tests

-   [ ] Can access teacher routes
-   [ ] Can access admin routes
-   [ ] School data scoped correctly
-   [ ] Teacher record intact
-   [ ] Can manage their school

### Teacher Tests

-   [ ] Can view their classes
-   [ ] Cannot access admin routes
-   [ ] Cannot add other teachers
-   [ ] School data scoped correctly

---

## Files Reference

### Controllers

-   `app/Http/Controllers/SchoolController.php` - School CRUD
-   `app/Http/Controllers/SchoolHrAdminRegistrationController.php` - HR registration
-   `app/Http/Controllers/TeacherImportController.php` - Excel import
-   `app/Http/Controllers/TeacherManagementController.php` - Teacher CRUD
-   `app/Http/Controllers/SchoolBrowserController.php` - School data API

### Models

-   `app/Models/HR.php`
-   `app/Models/School.php`
-   `app/Models/Teacher.php`
-   `app/Models/User.php`
-   `app/Models/ClassroomSubjectTeacher.php`

### Routes

-   `routes/r_hr.php` - Admin routes (school management)
-   `routes/r_teacher.php` - Teacher routes
-   `routes/admin.php` - Additional admin routes
-   `routes/weekly_system.php` - Weekly system routes

### Middleware

-   `app/Http/Middleware/RoleMiddleware.php` - Custom role check
-   Spatie Permission middleware (role, permission, role_or_permission)

### Traits

-   `app/Traits/SchoolAccessTrait.php` - School access logic

---

## Summary

### Key Takeaways

1. **Database Design**:

    - HR → Schools → Teachers → Assignments
    - Always set `school_id` for teachers
    - Use `school_extra_ids` for multi-school teachers

2. **Role Hierarchy**:

    - HR Admin: Can create schools, manage all owned schools
    - School Admin: Can manage one school, cannot create schools
    - Teacher-Admin: Teacher + Admin permissions, scoped to their school
    - Teacher: Limited to teaching functions

3. **Best Practices**:

    - Use transactions for multi-step operations
    - Validate school ownership in controllers
    - Use Excel import for bulk teacher creation
    - Implement proper soft delete synchronization

4. **Security**:

    - Always filter by school_id
    - Validate cross-school access
    - Restrict school creation to HR admins
    - Sync teacher/user active status

5. **Teacher-Admin Pattern**:
    - Assign both 'teacher' and 'admin' roles
    - Keep teacher record for school_id
    - Get admin permissions for management
    - Perfect for school principals

---

## Related Documentation

-   `docs/history/2026-01-01_06-13_history_school_browser.md` - School Browser feature
-   `docs/history/main2/2025-12-30_history_school_hr_admin.md` - HR admin implementation
-   `docs/history/main2/2025-12-30_history_import_teacher_classroom_subject.md` - Teacher import

---

**Document Created**: 2026-01-01
**Last Updated**: 2026-01-01
**Author**: AI Assistant
**Status**: Complete Reference Guide
