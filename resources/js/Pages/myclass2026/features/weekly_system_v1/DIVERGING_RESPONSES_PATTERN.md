# Admin & Teacher Share Same Routes - Diverging Responses Pattern

## Overview

The Weekly System V1 uses the **Diverging Responses Pattern** - a single route and controller method that renders different views based on the user's role. This is a core pattern in Feature-First architecture for multi-role systems.

## Architecture Pattern

```
┌─────────────────────────────────────────────────────┐
│              Single Route Definition                │
│  GET /weekly-system-v1/curriculum-lessons           │
│  → WeeklySystemController::curriculumLessonsIndex() │
└─────────────────────────────────────────────────────┘
                        ↓
        ┌───────────────┴───────────────┐
        ↓                               ↓
┌───────────────────┐           ┌───────────────────┐
│   ADMIN USER      │           │   TEACHER USER    │
│ hasRole('admin')  │           │ hasRole('teacher')│
└───────────────────┘           └───────────────────┘
        ↓                               ↓
┌───────────────────┐           ┌───────────────────┐
│ renderAdminCurri… │           │ renderTeacherCu…  │
│ View()            │           │ View()            │
└───────────────────┘           └───────────────────┘
        ↓                               ↓
┌───────────────────┐           ┌───────────────────┐
│ AdminCurriculumV… │           │ TeacherCurriculu… │
│ .vue              │           │ .vue              │
│ - Full CRUD       │           │ - Read-only       │
│ - All curricula   │           │ - Assigned only   │
│ - Lock dates      │           │ - No admin feat…  │
└───────────────────┘           └───────────────────┘
```

## Implementation Examples

### 1. Dashboard Route

**Route:** `GET /weekly-system-v1/`

**Controller Method:**
```php
public function dashboard(Request $request)
{
    $user = $request->user();
    
    // === ADMIN PATH ===
    if ($user->hasRole('school-admin')) {
        return Inertia::render(
            'myclass2026/features/weekly_system_v1/dashboards/AdminDashboard',
            [
                'schoolName' => $user->school->name ?? 'My School',
                'canManageCurriculum' => true,
                'canManageWeeklyPlans' => true,
                'canManageTimetable' => true,
            ]
        );
    }
    
    // === TEACHER PATH ===
    if ($user->hasRole('teacher')) {
        $teacher = $user->teacher;
        
        if (!$teacher) {
            abort(403, 'User does not have a teacher profile');
        }
        
        $assignedCount = $teacher->classroomSubjectTeachers()
            ->with(['classroom', 'subject'])
            ->count();
        
        return Inertia::render(
            'myclass2026/features/weekly_system_v1/dashboards/TeacherDashboard',
            [
                'teacherName' => $teacher->name,
                'assignedClassesCount' => $assignedCount,
                'canViewCurriculum' => true,
                'canEditWeeklyPlans' => true,
            ]
        );
    }
    
    abort(403, 'Unauthorized access to weekly system');
}
```

**Result:**
- **Admin sees:** AdminDashboard.vue with school-wide management features
- **Teacher sees:** TeacherDashboard.vue with personal planning features

---

### 2. Curriculum Lessons Route

**Route:** `GET /weekly-system-v1/curriculum-lessons`

**Controller Method:**
```php
public function curriculumLessonsIndex(Request $request)
{
    $user = $request->user();
    
    // === ADMIN PATH: School-wide curriculum management ===
    if ($user->hasRole('school-admin')) {
        return $this->renderAdminCurriculumView($user);
    }
    
    // === TEACHER PATH: Assigned curricula only ===
    if ($user->hasRole('teacher')) {
        return $this->renderTeacherCurriculumView($user);
    }
    
    abort(403, 'Unauthorized access');
}
```

**Private Helper Methods:**
```php
private function renderAdminCurriculumView($user)
{
    $schoolId = $user->school_id;
    
    // Load ALL school curricula
    $curricula = Curriculum::with(['grade', 'subject'])
        ->where('school_id', $schoolId)
        ->orderBy('name')
        ->get()
        ->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'description' => $c->description,
            'grade_name' => $c->grade?->name ?? 'N/A',
            'subject_name' => $c->subject?->name ?? 'N/A',
            'edit_lock_date' => $c->edit_lock_date?->format('Y-m-d'),
            'created_at' => $c->created_at->format('Y-m-d'),
        ]);
    
    return Inertia::render(
        'myclass2026/features/weekly_system_v1/curriculum_lessons/AdminCurriculumView',
        [
            'curricula' => $curricula,
            'canCreate' => true,
            'canEdit' => true,
            'canDelete' => true,
            'canSetLockDates' => true,
            'schoolName' => $user->school->name ?? 'School',
        ]
    );
}

private function renderTeacherCurriculumView($user)
{
    $teacher = $user->teacher;
    
    if (!$teacher) {
        abort(403, 'User does not have a teacher profile');
    }
    
    // Get teacher's assigned grades and subjects
    $teacherAssignments = $teacher->classroomSubjectTeachers()
        ->with(['classroom', 'subject'])
        ->get();
    
    $gradeIds = $teacherAssignments->pluck('classroom.grade_id')
        ->filter()->unique()->values();
    $subjectIds = $teacherAssignments->pluck('subject_id')
        ->filter()->unique()->values();
    
    // Load curricula matching teacher's assignments
    $curricula = Curriculum::with(['grade', 'subject'])
        ->where(function($query) use ($gradeIds, $subjectIds) {
            if ($gradeIds->isNotEmpty()) {
                $query->whereIn('grade_id', $gradeIds);
            }
            if ($subjectIds->isNotEmpty()) {
                $query->orWhereIn('subject_id', $subjectIds);
            }
        })
        ->orderBy('name')
        ->get()
        ->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'description' => $c->description,
            'grade_name' => $c->grade?->name ?? 'N/A',
            'subject_name' => $c->subject?->name ?? 'N/A',
            'edit_lock_date' => $c->edit_lock_date?->format('Y-m-d'),
            'isEditable' => $c->edit_lock_date?->isFuture() ?? false,
        ]);
    
    return Inertia::render(
        'myclass2026/features/weekly_system_v1/curriculum_lessons/TeacherCurriculumView',
        [
            'curricula' => $curricula,
            'canCreate' => false,
            'canEdit' => true,
            'canDelete' => false,
            'canSetLockDates' => false,
            'teacherName' => $teacher->name,
        ]
    );
}
```

**Result:**
- **Admin sees:** All school curricula with full CRUD permissions
- **Teacher sees:** Only curricula for their assigned grades/subjects with limited permissions

---

### 3. Weekly Plans Route

**Route:** `GET /weekly-system-v1/weekly-plans-manager`

**Pattern:**
```php
public function weeklyPlansManager(Request $request)
{
    $user = $request->user();
    
    if ($user->hasRole('school-admin')) {
        // Admin sees all teachers' plans
        return $this->renderAdminWeeklyPlansManager($user);
    }
    
    if ($user->hasRole('teacher')) {
        // Teacher redirected to their own plans
        return redirect()->route('weekly-system-v1.my-weekly-plans');
    }
    
    abort(403);
}
```

**Result:**
- **Admin sees:** AdminWeeklyPlansManager.vue - manage all teachers' plans
- **Teacher sees:** Redirected to TeacherWeeklyPlansEditor.vue - edit own plans only

---

## Key Benefits

### ✅ 1. **Single Source of Truth**
- One route definition
- One controller method
- Clear, maintainable code

### ✅ 2. **Role-Based Security**
- Authorization checked once in controller
- Different data sets per role
- Prevents unauthorized access

### ✅ 3. **Optimized Data Loading**
- Each role gets exactly what they need
- No over-fetching or under-fetching
- Efficient queries tailored to role

### ✅ 4. **Clean Separation**
- Separate Vue components per role
- Role-specific UI/UX
- Independent evolution of features

### ✅ 5. **Scalable Pattern**
- Easy to add new roles (parent, supervisor, etc.)
- Consistent pattern across all features
- Predictable architecture

---

## Route Comparison Table

| Route | Admin Sees | Teacher Sees |
|-------|-----------|--------------|
| `/` (Dashboard) | AdminDashboard.vue<br>- School overview<br>- Management cards | TeacherDashboard.vue<br>- Personal overview<br>- Planning cards |
| `/curriculum-lessons` | AdminCurriculumView.vue<br>- All curricula<br>- Full CRUD<br>- Lock dates | TeacherCurriculumView.vue<br>- Assigned curricula only<br>- Read/edit<br>- No admin features |
| `/weekly-plans-manager` | AdminWeeklyPlansManager.vue<br>- All teachers' plans<br>- Filter by teacher<br>- Oversight tools | Redirects to `/my-weekly-plans`<br>- Own plans only |
| `/my-weekly-plans` | ❌ Access denied | TeacherWeeklyPlansEditor.vue<br>- Edit own weekly plans |

---

## Permission Props Pattern

Each view receives permission props from the controller:

```php
return Inertia::render('ComponentName', [
    'canCreate' => true/false,
    'canEdit' => true/false,
    'canDelete' => true/false,
    'canSetLockDates' => true/false,
    // ... other role-specific data
]);
```

Vue component uses these props:
```vue
<script setup>
const props = defineProps({
  canCreate: Boolean,
  canEdit: Boolean,
  canDelete: Boolean,
  curricula: Array,
  teacherName: String,
  // ...
})
</script>

<template>
  <q-btn 
    v-if="canCreate" 
    label="Create New" 
    @click="createNew"
  />
</template>
```

---

## Alternative Approaches (NOT Used)

### ❌ Approach 1: Separate Routes per Role
```php
// NOT USED - Creates duplication
Route::get('/admin/curriculum', [AdminController::class, 'curriculum']);
Route::get('/teacher/curriculum', [TeacherController::class, 'curriculum']);
```
**Why not:** Duplicate logic, harder to maintain, violates DRY principle

### ❌ Approach 2: Same Component, Different Data
```php
// NOT USED - Limited flexibility
if ($isAdmin) {
    return Inertia::render('CurriculumView', ['data' => $allData]);
} else {
    return Inertia::render('CurriculumView', ['data' => $filteredData]);
}
```
**Why not:** Same UI for different use cases, confusing UX, limited customization

### ✅ Approach 3: Diverging Responses (CURRENT)
```php
// USED - Clean separation
if ($isAdmin) {
    return Inertia::render('AdminCurriculumView', [...]);
} else {
    return Inertia::render('TeacherCurriculumView', [...]);
}
```
**Why best:** Clear separation, role-optimized UX, maintainable, scalable

---

## Adding a New Role

To add a new role (e.g., "department-head"):

1. **Add role check in controller:**
```php
public function curriculumLessonsIndex(Request $request)
{
    $user = $request->user();
    
    if ($user->hasRole('school-admin')) {
        return $this->renderAdminCurriculumView($user);
    }
    
    // NEW ROLE
    if ($user->hasRole('department-head')) {
        return $this->renderDepartmentHeadCurriculumView($user);
    }
    
    if ($user->hasRole('teacher')) {
        return $this->renderTeacherCurriculumView($user);
    }
    
    abort(403);
}
```

2. **Create department-head specific method:**
```php
private function renderDepartmentHeadCurriculumView($user)
{
    // Department head sees curricula for their department only
    $curricula = Curriculum::with(['grade', 'subject'])
        ->where('school_id', $user->school_id)
        ->whereHas('grade', function($q) use ($user) {
            $q->where('department_id', $user->department_id);
        })
        ->get();
    
    return Inertia::render(
        'myclass2026/features/weekly_system_v1/curriculum_lessons/DepartmentHeadCurriculumView',
        [/* ... */]
    );
}
```

3. **Create new Vue component:**
```bash
resources/js/Pages/myclass2026/features/weekly_system_v1/curriculum_lessons/
  ├── AdminCurriculumView.vue
  ├── TeacherCurriculumView.vue
  └── DepartmentHeadCurriculumView.vue  ← NEW
```

---

## Best Practices

### ✅ DO:
- Use clear role naming (`school-admin`, `teacher`, etc.)
- Keep role checks at top of method
- Use private helper methods for each role's view
- Pass explicit permission props to views
- Handle missing profiles (e.g., user without teacher profile)
- Use `abort(403)` for unauthorized access

### ❌ DON'T:
- Mix role logic in the same view
- Use complex nested if-statements
- Assume user has related models (always check!)
- Share sensitive admin data with teachers
- Forget to handle edge cases (missing profiles, etc.)

---

## Files Using This Pattern

### Controllers
- [`WeeklySystemController.php`](file://c:\my_project\myclass2026-main\app\Http\Controllers\WeeklySystemV1\WeeklySystemController.php)
  - `dashboard()` - Lines 37-80
  - `curriculumLessonsIndex()` - Lines 87-102
  - `weeklyPlansManager()` - Lines 197-212

### Views (Admin)
- [`AdminDashboard.vue`](file://c:\my_project\myclass2026-main\resources\js\Pages\myclass2026\features\weekly_system_v1\dashboards\AdminDashboard.vue)
- [`AdminCurriculumView.vue`](file://c:\my_project\myclass2026-main\resources\js\Pages\myclass2026\features\weekly_system_v1\curriculum_lessons\AdminCurriculumView.vue)
- [`AdminWeeklyPlansManager.vue`](file://c:\my_project\myclass2026-main\resources\js\Pages\myclass2026\features\weekly_system_v1\weekly_plans\AdminWeeklyPlansManager.vue)

### Views (Teacher)
- [`TeacherDashboard.vue`](file://c:\my_project\myclass2026-main\resources\js\Pages\myclass2026\features\weekly_system_v1\dashboards\TeacherDashboard.vue)
- [`TeacherCurriculumView.vue`](file://c:\my_project\myclass2026-main\resources\js\Pages\myclass2026\features\weekly_system_v1\curriculum_lessons\TeacherCurriculumView.vue)
- [`TeacherWeeklyPlansEditor.vue`](file://c:\my_project\myclass2026-main\resources\js\Pages\myclass2026\features\weekly_system_v1\weekly_plans\TeacherWeeklyPlansEditor.vue)

---

## Summary

The **Diverging Responses Pattern** is the cornerstone of how the Weekly System V1 handles multi-role access:

1. **Single route** → Both admin and teacher use same URL
2. **Role detection** → Controller checks user role
3. **Different views** → Each role gets optimized UI
4. **Tailored data** → Queries filtered by role permissions
5. **Clean separation** → Easy to maintain and extend

This pattern provides the perfect balance of code reuse (shared routes/controllers) and role-specific optimization (separate views/data).
