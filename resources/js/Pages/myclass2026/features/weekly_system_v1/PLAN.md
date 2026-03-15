# Weekly System V1 - Feature-First Restructuring Plan

**Created:** 2026-03-15  
**Status:** Planning Phase  
**Based on:** Feature-First Architecture Principles (see Untitled-1 analysis)

---

## 📋 Executive Summary

This plan restructures the weekly system from a **Role-First** to a **Feature-First** architecture, consolidating duplicated code, improving maintainability, and establishing clear separation of concerns between shared feature logic and role-specific presentations.

### Current Problems
- ❌ Duplicate `curriculum_lessons/Index.vue` in school-admin and teacher folders (22.5KB vs 17.2KB)
- ❌ Scattered routes across `weekly_system.php` with mixed responsibilities
- ❌ Backend controllers partially organized but not fully leveraged
- ❌ Shared components buried in role-specific directories

### Target Architecture
- ✅ Single source of truth for shared weekly system logic
- ✅ Role-specific views only for UI/permission differences
- ✅ Clear backend controller structure with diverging responses
- ✅ Unified route organization

---

## 🎯 Goals & Principles

### Primary Goals
1. **Eliminate Code Duplication**: Merge shared curriculum lessons logic
2. **Clear Separation**: Feature logic vs. role-specific presentation
3. **Maintainability**: Easy to add new roles (parent, student) later
4. **Performance**: Load only data needed for each role
5. **Type Safety**: Consistent data structures across frontend/backend

### Guiding Principles (from Untitled-1 Analysis)
1. **Feature-First for Shared LMS Functionality**: Curriculum, weekly plans, schedules are shared concepts
2. **Role-First Exception Only for Dashboards**: Admin dashboard vs Teacher dashboard can be separate
3. **Backend Diverging Responses**: Single controller, different Inertia responses per role
4. **v-if Heuristic**: Split component if it has 3+ role-based conditionals

---

## 📁 Directory Structure

### Target Frontend Structure

```
resources/js/Pages/myclass2026/features/weekly_system_v1/
├── README.md                           # Feature documentation
├── routes.ts                           # Route definitions (optional TypeScript)
│
├── components/                         # SHARED components (role-agnostic)
│   ├── common/
│   │   ├── PeriodSelector.vue
│   │   ├── WeekSelector.vue
│   │   ├── SemesterSelector.vue
│   │   └── AcademicYearSelector.vue
│   │
│   ├── curriculum/
│   │   ├── CurriculumForm.vue
│   │   ├── CurriculumList.vue
│   │   ├── CurriculumCard.vue
│   │   └── LockDateEditor.vue
│   │
│   ├── lesson_plan/
│   │   ├── LessonPlanEditor.vue
│   │   ├── LessonPlanViewer.vue
│   │   ├── LessonPlanBulkActions.vue
│   │   └── LessonPlanStatusBadge.vue
│   │
│   └── weekly_plan/
│       ├── WeeklyPlanGrid.vue
│       ├── WeeklyPlanCopyDialog.vue
│       ├── WeeklyPlanSyncButton.vue
│       └── CompletionStats.vue
│
├── curriculum_lessons/                 # Feature: Curriculum & Lessons
│   ├── Index.vue                       # SHARED base component
│   ├── AdminCurriculumView.vue         # Admin-specific presentation
│   ├── TeacherCurriculumView.vue       # Teacher-specific presentation
│   └── types.ts                        # TypeScript types (if using TS)
│
├── weekly_plans/                       # Feature: Weekly Plans Management
│   ├── Manager.vue                     # SHARED manager
│   ├── AdminWeeklyPlansManager.vue     # Admin-specific (all teachers)
│   ├── TeacherWeeklyPlansEditor.vue    # Teacher-specific (my plans)
│   └── composables/
│       └── useWeeklyPlans.ts           # Shared state management
│
├── timetable/                          # Feature: Timetable Editor
│   ├── Editor.vue                      # SHARED editor logic
│   ├── AdminTimetableEditor.vue        # Admin view (full control)
│   └── TeacherTimetableView.vue        # Teacher view (read-only)
│
├── schedule_copies/                    # Feature: Schedule Versions
│   ├── Index.vue
│   ├── ScheduleCopiesManager.vue
│   └── ScheduleCopyCard.vue
│
├── dashboards/                         # Role-specific entry points
│   ├── AdminDashboard.vue              # Navigation cards (current admin/Index.vue)
│   └── TeacherDashboard.vue            # Teacher's weekly system home
│
└── layouts/
    └── WeeklySystemLayout.vue          # Shared layout wrapper
```

### Target Backend Structure

```
app/Http/Controllers/WeeklySystemV1/
├── WeeklySystemController.php          # Main controller (diverging responses)
├── CurriculumController.php            # Curriculum CRUD (shared)
├── CurriculumLessonController.php      # Lesson plans within curriculum
├── WeeklyPlanController.php            # Weekly plan operations
├── TimetableController.php             # Timetable management
├── ScheduleCopyController.php          # Schedule versioning
└── Api/
    ├── Admin/
    │   ├── DashboardStatsController.php
    │   └── SchoolDataController.php
    └── Teacher/
        ├── MyScheduleController.php
        └── MyWeeklyPlansController.php

app/Services/WeeklySystemV1/
├── WeeklyPlanService.php               # Business logic for weekly plans
├── TimetableService.php                # Timetable operations
├── CurriculumService.php               # Curriculum management
└── CopyPlanService.php                 # Plan copying logic

app/Http/Resources/WeeklySystemV1/
├── CurriculumResource.php
├── WeeklyPlanResource.php
├── ScheduleResource.php
└── TimetableResource.php
```

### Route Organization

```
routes/weekly_system_v1.php             # Main route file
routes/modules/weekly_system/
├── admin.php                           # Admin-specific routes
├── teacher.php                         # Teacher-specific routes
└── api.php                             # API endpoints
```

---

## 🗺️ Migration Strategy

### Phase 1: Foundation Setup (Week 1)

#### Task 1.1: Create Directory Structure
- [ ] Create all directories under `resources/js/Pages/myclass2026/features/weekly_system_v1/`
- [ ] Create `app/Http/Controllers/WeeklySystemV1/` folder
- [ ] Create `app/Services/WeeklySystemV1/` folder
- [ ] Create `app/Http/Resources/WeeklySystemV1/` folder

#### Task 1.2: Establish Shared Components
- [ ] Move `PeriodSelector`, `WeekSelector`, `SemesterSelector` to `components/common/`
- [ ] Extract shared form components from existing curriculum_lessons files
- [ ] Create base composables for state management

#### Task 1.3: Documentation
- [ ] Write `README.md` explaining feature-first structure
- [ ] Document component hierarchy and data flow
- [ ] Create migration guide for future features

### Phase 2: Backend Consolidation (Week 1-2)

#### Task 2.1: Controller Refactoring
**Current State:**
- `WeeklySystemController.php` (1009 lines) - monolithic
- `WeeklySystem/CurriculumController.php` 
- `WeeklySystem/CurriculumLessonController.php`
- `WeeklySystem/LessonPlanController.php`

**Target State:**
```php
// app/Http/Controllers/WeeklySystemV1/WeeklySystemController.php
class WeeklySystemController extends Controller
{
    public function curriculumLessonsIndex(Request $request)
    {
        $user = auth()->user();
        
        // Diverging response based on role
        if ($user->hasRole('school-admin')) {
            return Inertia::render(
                'features/weekly_system_v1/curriculum_lessons/AdminCurriculumView',
                [
                    'schools' => $this->getSchoolData($user),
                    'lockDates' => $this->getLockDates($user),
                    'curricula' => $this->getSchoolCurricula($user),
                ]
            );
        }
        
        // Teacher view - only their assigned subjects
        return Inertia::render(
            'features/weekly_system_v1/curriculum_lessons/TeacherCurriculumView',
            [
                'assignedCurricula' => $this->getTeacherAssignedCurricula($user),
                'myClasses' => $this->getTeacherClassroomAssignments($user),
            ]
        );
    }
    
    public function weeklyPlansManager(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('school-admin')) {
            // Admin sees all teachers' plans
            return Inertia::render(
                'features/weekly_system_v1/weekly_plans/AdminWeeklyPlansManager',
                [
                    'allTeachers' => Teacher::with('user')->get(),
                    'allClassrooms' => Classroom::all(),
                ]
            );
        }
        
        // Teacher sees only their own plans
        $teacher = $user->teacher;
        return Inertia::render(
            'features/weekly_system_v1/weekly_plans/TeacherWeeklyPlansEditor',
            [
                'myAssignments' => $teacher->classroomSubjectTeachers()->with(['classroom', 'subject'])->get(),
            ]
        );
    }
}
```

#### Task 2.2: Service Layer Extraction
- [ ] Move business logic from controllers to Services
- [ ] Create `WeeklyPlanService::generateForWeek()`
- [ ] Create `WeeklyPlanService::copyPlansBetweenClassrooms()`
- [ ] Create `TimetableService::getActiveSchedule()`

#### Task 2.3: API Resources
- [ ] Create `CurriculumResource` for consistent JSON responses
- [ ] Create `WeeklyPlanResource` with role-based filtering
- [ ] Create `ScheduleResource` with CST relationships

### Phase 3: Frontend Migration (Week 2-3)

#### Task 3.1: Curriculum Lessons Migration

**Before:**
```
resources/js/Pages/myclass2026/roles/school-admin/weekly_system/curriculum_lessons/Index.vue (22.5KB)
resources/js/Pages/myclass2026/roles/teacher/weekly_system/curriculum_lessons/Index.vue (17.2KB)
```

**After:**
```
resources/js/Pages/myclass2026/features/weekly_system_v1/curriculum_lessons/
├── Index.vue                       # SHARED: 60% of code (API calls, forms, logic)
├── AdminCurriculumView.vue         # ADMIN: 20% (school-wide data, lock dates)
└── TeacherCurriculumView.vue       # TEACHER: 20% (assigned classes only)
```

**Migration Steps:**
1. Extract shared logic into `Index.vue`:
   - Form validation
   - API call functions
   - Data transformation logic
   - Event handlers

2. Create role-specific wrappers:
   ```vue
   <!-- AdminCurriculumView.vue -->
   <template>
     <CurriculumLessonsIndex
       :initial-data="adminData"
       :can-create-curriculum="true"
       :can-set-lock-dates="true"
       :show-school-selector="true"
     />
   </template>
   ```

3. Update routes:
   ```php
   // OLD
   Route::get('/weekly-system/curriculum-lessons/admin', function () {
       return Inertia::render('myclass2026/roles/school-admin/weekly_system/curriculum_lessons/Index');
   });
   
   Route::get('/weekly-system/curriculum-lessons/teacher', function () {
       return Inertia::render('myclass2026/roles/teacher/weekly_system/curriculum_lessons/Index');
   });
   
   // NEW
   Route::get('/weekly-system/curriculum-lessons', [WeeklySystemController::class, 'curriculumLessonsIndex'])
       ->name('curriculum-lessons.index');
   ```

#### Task 3.2: Weekly Plans Manager Migration

**Current Files:**
- `resources/js/Pages/my_table_mnger/weekly_system/admin/WeeklyPlansManager.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/teacher/SimpleWeeklyPlans.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/teacher/MyWeeklyPlans.vue`

**Target Structure:**
```
resources/js/Pages/myclass2026/features/weekly_system_v1/weekly_plans/
├── Manager.vue                       # SHARED: Grid layout, filters, search
├── AdminWeeklyPlansManager.vue       # ADMIN: All teachers filter, bulk actions
├── TeacherWeeklyPlansEditor.vue      # TEACHER: My assignments only
└── composables/
    └── useWeeklyPlans.ts             # Pinia store or composable
```

**Key Changes:**
1. Remove role checks from shared component
2. Pass permissions as props:
   ```vue
   <WeeklyPlansManager
     :can-view-all-teachers="auth.user.can('view-all-weekly-plans')"
     :can-bulk-copy="auth.user.can('bulk-copy-plans')"
     :initial-filters="{ teacher_id: auth.user.teacher?.id }"
   />
   ```

#### Task 3.3: Dashboard Creation

**Admin Dashboard** (existing, minor changes):
```vue
<!-- resources/js/Pages/myclass2026/features/weekly_system_v1/dashboards/AdminDashboard.vue -->
<template>
  <WeeklySystemLayout>
    <div class="row">
      <q-card v-for="menu in adminMenus" @click="navigateTo(menu.route)">
        <!-- Existing navigation cards -->
      </q-card>
    </div>
  </WeeklySystemLayout>
</template>

<script setup>
const adminMenus = [
  {
    title: 'Curriculum & Locks',
    route: 'weekly-system-v1.curriculum-lessons.index',
  },
  {
    title: 'Weekly Plans Manager',
    route: 'weekly-system-v1.weekly-plans-manager',
  },
  // ... more
]
</script>
```

**Teacher Dashboard** (new):
```vue
<!-- resources/js/Pages/myclass2026/features/weekly_system_v1/dashboards/TeacherDashboard.vue -->
<template>
  <WeeklySystemLayout>
    <div class="row">
      <q-card v-for="menu in teacherMenus" @click="navigateTo(menu.route)">
        <q-icon name="calendar_today" />
        <div>{{ menu.title }}</div>
      </q-card>
    </div>
  </WeeklySystemLayout>
</template>

<script setup>
const teacherMenus = [
  {
    title: 'My Weekly Plans',
    route: 'weekly-system-v1.my-weekly-plans',
  },
  {
    title: 'My Schedule',
    route: 'weekly-system-v1.my-schedule',
  },
  {
    title: 'Curriculum Access',
    route: 'weekly-system-v1.curriculum-lessons.index',
  },
]
</script>
```

### Phase 4: Route Consolidation (Week 3)

#### Task 4.1: Create New Route File

**File:** `routes/weekly_system_v1.php`

```php
<?php

use App\Http\Controllers\WeeklySystemV1\WeeklySystemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Weekly System V1 Routes (Feature-First Architecture)
|--------------------------------------------------------------------------
|
| This route file implements the Feature-First architecture for the 
| Weekly System module. All shared functionality lives in the feature
| folder, with role-specific views rendered by the controller.
|
*/

Route::middleware(['auth', 'verified'])->prefix('weekly-system-v1')->name('weekly-system-v1.')->group(function () {
    
    // === MAIN ENTRY POINTS (Role-aware rendering) ===
    
    // Dashboard - renders different views based on role
    Route::get('/', [WeeklySystemController::class, 'dashboard'])
        ->name('dashboard');
    
    // === CURRICULUM & LESSONS ===
    
    // Single route, controller decides which view to render
    Route::get('/curriculum-lessons', [WeeklySystemController::class, 'curriculumLessonsIndex'])
        ->name('curriculum-lessons.index');
    
    // Curriculum CRUD (shared, but permissions checked in controller)
    Route::post('/curriculum', [WeeklySystemController::class, 'storeCurriculum'])
        ->name('curriculum.store')
        ->can('create-curriculum');
    
    Route::put('/curriculum/{curriculum}', [WeeklySystemController::class, 'updateCurriculum'])
        ->name('curriculum.update')
        ->can('edit-curriculum', '{curriculum}');
    
    // === WEEKLY PLANS ===
    
    // Weekly Plans Manager - admin sees all, teacher sees own
    Route::get('/weekly-plans-manager', [WeeklySystemController::class, 'weeklyPlansManager'])
        ->name('weekly-plans-manager');
    
    // Teacher's personal weekly plans editor
    Route::get('/my-weekly-plans', [WeeklySystemController::class, 'myWeeklyPlans'])
        ->name('my-weekly-plans');
    
    // Copy plans between classrooms (teachers only)
    Route::post('/weekly-plans/copy-preview', [WeeklySystemController::class, 'previewCopyPlans'])
        ->name('weekly-plans.copy-preview');
    
    Route::post('/weekly-plans/commit-copy', [WeeklySystemController::class, 'commitCopyPlans'])
        ->name('weekly-plans.commit-copy');
    
    // === TIMETABLE EDITOR ===
    
    // Admin: full editor, Teacher: read-only view
    Route::get('/timetable-editor', [WeeklySystemController::class, 'timetableEditor'])
        ->name('timetable-editor');
    
    // === SCHEDULE COPIES (Admin only) ===
    
    Route::get('/schedule-copies', [WeeklySystemController::class, 'scheduleCopiesIndex'])
        ->name('schedule-copies.index')
        ->can('manage-schedule-copies');
    
    // === API ENDPOINTS (for frontend components) ===
    
    Route::prefix('api')->name('api.')->group(function () {
        // Curriculum API
        Route::get('/curricula', [WeeklySystemController::class, 'getCurriculaApi']);
        
        // Weekly Plans API
        Route::get('/weekly-plans', [WeeklySystemController::class, 'getWeeklyPlansApi']);
        Route::put('/weekly-plans/{weeklyPlan}', [WeeklySystemController::class, 'updateWeeklyPlanApi']);
        
        // Schedule API
        Route::get('/schedules', [WeeklySystemController::class, 'getSchedulesApi']);
        Route::post('/schedules/reorder', [WeeklySystemController::class, 'reorderSchedulesApi']);
    });
});

// Include role-specific sub-routes if needed
// include __DIR__ . '/modules/weekly_system/admin.php';
// include __DIR__ . '/modules/weekly_system/teacher.php';
```

#### Task 4.2: Update Route Includes

**File:** `routes/web.php`

```php
// Replace old weekly_system include with new version
// OLD:
// include dirname(__DIR__).'/routes/weekly_system.php';

// NEW:
include dirname(__DIR__).'/routes/weekly_system_v1.php';

// Keep old routes temporarily for backward compatibility (with deprecation notice)
// include dirname(__DIR__).'/routes/weekly_system.php'; // DEPRECATED
```

#### Task 4.3: Route Testing Checklist
- [ ] Admin can access `/weekly-system-v1/` → renders AdminDashboard
- [ ] Teacher can access `/weekly-system-v1/` → renders TeacherDashboard
- [ ] Admin accessing `/weekly-system-v1/curriculum-lessons` → gets AdminCurriculumView
- [ ] Teacher accessing same URL → gets TeacherCurriculumView
- [ ] Student accessing → gets 403 or StudentView (if implemented)
- [ ] All API endpoints return correct data for each role

### Phase 5: Component Extraction & Deduplication (Week 4)

#### Task 5.1: Identify Duplicated Code

**Analysis of current files:**

**School Admin Curriculum (22.5KB):**
- Likely contains: School selector, lock date editor, curriculum CRUD
- Unique: ~20% (school-wide operations)

**Teacher Curriculum (17.2KB):**
- Likely contains: Assigned classes view, lesson planning interface
- Unique: ~20% (personal assignments)

**Shared (~60%):**
- Curriculum display cards
- Form modals
- Search/filter logic
- API integration
- Loading states
- Error handling

#### Task 5.2: Extraction Pattern

**Step 1: Create Shared Base**
```vue
<!-- resources/js/Pages/myclass2026/features/weekly_system_v1/curriculum_lessons/Index.vue -->
<template>
  <div>
    <h1>{{ title }}</h1>
    
    <!-- Shared UI -->
    <q-table :data="curricula" :columns="columns">
      <!-- Shared table slots -->
    </q-table>
    
    <!-- Role-specific slots -->
    <slot name="admin-actions"></slot>
    <slot name="teacher-actions"></slot>
  </div>
</template>

<script setup>
// SHARED LOGIC HERE
const props = defineProps({
  initialCurricula: Array,
  canEdit: Boolean,
  canDelete: Boolean,
  // ... other shared props
})

const emit = defineEmits(['update', 'delete', 'create'])

// Shared API calls
const fetchCurricula = async () => { /* ... */ }

// Shared form logic
const showCreateDialog = ref(false)
const handleCreate = async (data) => { /* ... */ }
</script>
```

**Step 2: Create Admin Wrapper**
```vue
<!-- resources/js/Pages/myclass2026/features/weekly_system_v1/curriculum_lessons/AdminCurriculumView.vue -->
<template>
  <CurriculumLessonsIndex
    title="School Curricula"
    :initial-curricula="curricula"
    :can-edit="true"
    :can-delete="canDelete"
    @update="handleUpdate"
  >
    <!-- Admin-specific actions -->
    <template #admin-actions>
      <q-btn label="Set Lock Dates" @click="showLockDateDialog = true" />
      <q-btn label="Create Curriculum" @click="showCreateDialog = true" />
    </template>
  </CurriculumLessonsIndex>
</template>

<script setup>
// ADMIN-SPECIFIC DATA ONLY
const curricula = inject('adminCurricula')
const canDelete = inject('canDeleteCurriculum')

const handleUpdate = async (data) => {
  // Admin-specific update logic (e.g., notify all teachers)
}
</script>
```

**Step 3: Create Teacher Wrapper**
```vue
<!-- resources/js/Pages/myclass2026/features/weekly_system_v1/curriculum_lessons/TeacherCurriculumView.vue -->
<template>
  <CurriculumLessonsIndex
    title="My Curricula"
    :initial-curricula="assignedCurricula"
    :can-edit="canEditAssigned"
    :can-delete="false"
    @update="handleUpdate"
  >
    <!-- Teacher-specific actions -->
    <template #teacher-actions>
      <q-btn label="Plan Lessons" @click="openLessonPlanner" />
    </template>
  </CurriculumLessonsIndex>
</template>

<script setup>
// TEACHER-SPECIFIC DATA ONLY
const assignedCurricula = inject('teacherAssignedCurricula')
const canEditAssigned = inject('canEditAssignedCurriculum')
</script>
```

#### Task 5.3: Testing Strategy

**Unit Tests (Jest/Vitest):**
```javascript
// tests/js/features/weekly_system_v1/curriculum_lessons.spec.js

describe('CurriculumLessons Index', () => {
  it('renders curriculum list correctly', () => {
    const curricula = [{ id: 1, name: 'Math Grade 5' }]
    const wrapper = mount(CurriculumLessonsIndex, {
      props: { initialCurricula: curricula }
    })
    
    expect(wrapper.text()).toContain('Math Grade 5')
  })
  
  it('emits update event when editing', async () => {
    const wrapper = mount(CurriculumLessonsIndex)
    await wrapper.find('[data-testid="edit-btn"]').trigger('click')
    
    expect(wrapper.emitted('update')).toBeTruthy()
  })
})

describe('AdminCurriculumView', () => {
  it('shows admin-only actions', () => {
    const wrapper = mount(AdminCurriculumView)
    expect(wrapper.text()).toContain('Set Lock Dates')
  })
})

describe('TeacherCurriculumView', () => {
  it('shows only assigned curricula', () => {
    const wrapper = mount(TeacherCurriculumView, {
      props: { assignedCurricula: [...] }
    })
    // ...
  })
})
```

**Integration Tests (Laravel Dusk):**
```php
// tests/Browser/WeeklySystemV1/CurriculumLessonsTest.php

public function test_admin_sees_school_wide_curricula()
{
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->adminUser)
                ->visit('/weekly-system-v1/curriculum-lessons')
                ->assertSee('School Curricula')
                ->assertSee('Set Lock Dates'); // Admin-only button
    });
}

public function test_teacher_sees_only_assigned_curricula()
{
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->teacherUser)
                ->visit('/weekly-system-v1/curriculum-lessons')
                ->assertSee('My Curricula')
                ->assertDontSee('Set Lock Dates');
    });
}
```

---

## 🔧 Technical Implementation Details

### Backend: Diverging Response Pattern

**Example Implementation:**

```php
// app/Http/Controllers/WeeklySystemV1/WeeklySystemController.php

namespace App\Http\Controllers\WeeklySystemV1;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Teacher;
use App\Services\WeeklySystemV1\CurriculumService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WeeklySystemController extends Controller
{
    public function __construct(
        protected CurriculumService $curriculumService
    ) {}
    
    /**
     * Handle curriculum lessons index with role-based rendering
     * 
     * Single route, single controller method, diverging Inertia responses
     */
    public function curriculumLessonsIndex(Request $request)
    {
        $user = auth()->user();
        
        // === ADMIN PATH ===
        if ($user->hasRole('school-admin')) {
            return $this->renderAdminCurriculumView($user, $request);
        }
        
        // === TEACHER PATH ===
        if ($user->hasRole('teacher')) {
            return $this->renderTeacherCurriculumView($user, $request);
        }
        
        // === DEFAULT/FALLBACK ===
        abort(403, 'Unauthorized access to curriculum lessons');
    }
    
    /**
     * Admin view: School-wide curriculum management
     */
    private function renderAdminCurriculumView($user, Request $request)
    {
        $schoolId = $user->school_id;
        
        // Load ALL school curricula (not filtered by teacher)
        $curricula = Curriculum::with(['grade', 'subject'])
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade_name' => $c->grade->name,
                'subject_name' => $c->subject->name,
                'edit_lock_date' => $c->edit_lock_date?->format('Y-m-d'),
            ]);
        
        return Inertia::render(
            'features/weekly_system_v1/curriculum_lessons/AdminCurriculumView',
            [
                'curricula' => $curricula,
                'canCreate' => true,
                'canDelete' => true,
                'canSetLockDates' => true,
                'schoolName' => $user->school->name,
            ]
        );
    }
    
    /**
     * Teacher view: Only assigned curricula
     */
    private function renderTeacherCurriculumView($user, Request $request)
    {
        $teacher = $user->teacher;
        
        if (!$teacher) {
            abort(403, 'User does not have a teacher profile');
        }
        
        // Load ONLY curricula this teacher is assigned to teach
        $curricula = Curriculum::with(['grade', 'subject'])
            ->whereHas('classroomSubjectTeachers', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade_name' => $c->grade->name,
                'subject_name' => $c->subject->name,
                'isEditable' => $c->edit_lock_date?->isFuture() ?? false,
            ]);
        
        return Inertia::render(
            'features/weekly_system_v1/curriculum_lessons/TeacherCurriculumView',
            [
                'curricula' => $curricula,
                'canCreate' => false, // Teachers cannot create school curricula
                'canDelete' => false,
                'canSetLockDates' => false,
            ]
        );
    }
}
```

### Frontend: Props-Based Permission Pattern

**Shared Component Example:**

```vue
<!-- resources/js/Pages/myclass2026/features/weekly_system_v1/curriculum_lessons/Index.vue -->
<template>
  <div class="curriculum-lessons-container">
    <div class="header row justify-between items-center q-mb-md">
      <h2 class="text-h5 text-weight-bold">{{ title }}</h2>
      
      <!-- Action buttons controlled by props -->
      <div class="actions">
        <q-btn
          v-if="canCreate"
          color="primary"
          label="Create Curriculum"
          @click="showCreateDialog = true"
          icon="add"
        />
        
        <q-btn
          v-if="canSetLockDates"
          color="secondary"
          label="Lock Dates"
          @click="showLockDateDialog = true"
          icon="lock"
          class="q-ml-sm"
        />
      </div>
    </div>
    
    <!-- Data Table -->
    <q-table
      :rows="curricula"
      :columns="columns"
      row-key="id"
      flat
      bordered
    >
      <!-- Actions column -->
      <template v-slot:body-cell-actions="props">
        <q-td key="actions" :props="props">
          <q-btn
            v-if="canEdit || canDelete"
            flat
            round
            dense
            color="primary"
            icon="edit"
            @click="editCurriculum(props.row)"
          >
            <q-tooltip>Edit</q-tooltip>
          </q-btn>
          
          <q-btn
            v-if="canDelete"
            flat
            round
            dense
            color="negative"
            icon="delete"
            @click="confirmDelete(props.row)"
          >
            <q-tooltip>Delete</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>
    
    <!-- Dialogs (shared) -->
    <CreateCurriculumDialog
      v-model="showCreateDialog"
      @created="handleCreated"
    />
    
    <LockDateDialog
      v-model="showLockDateDialog"
      @saved="handleLockDateSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import CreateCurriculumDialog from '../components/curriculum/CreateCurriculumDialog.vue'
import LockDateDialog from '../components/curriculum/LockDateDialog.vue'

// === PROPS (controlled by parent role-specific component) ===
const props = defineProps({
  title: {
    type: String,
    default: 'Curriculum Management'
  },
  curricula: {
    type: Array,
    required: true
  },
  canCreate: {
    type: Boolean,
    default: false
  },
  canEdit: {
    type: Boolean,
    default: false
  },
  canDelete: {
    type: Boolean,
    default: false
  },
  canSetLockDates: {
    type: Boolean,
    default: false
  }
})

// === EMITS ===
const emit = defineEmits(['refresh', 'created', 'updated', 'deleted'])

// === STATE ===
const showCreateDialog = ref(false)
const showLockDateDialog = ref(false)

// === TABLE COLUMNS ===
const columns = [
  { name: 'name', label: 'Name', field: 'name', align: 'left', sortable: true },
  { name: 'grade', label: 'Grade', field: 'grade_name', align: 'left' },
  { name: 'subject', label: 'Subject', field: 'subject_name', align: 'left' },
  { name: 'lockDate', label: 'Lock Date', field: 'edit_lock_date', align: 'center' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
]

// === ACTIONS ===
const editCurriculum = (curriculum) => {
  // Shared edit logic
}

const confirmDelete = (curriculum) => {
  // Shared delete confirmation
}

const handleCreated = (newCurriculum) => {
  emit('created', newCurriculum)
  // Refresh list or optimistic update
}

const handleLockDateSaved = () => {
  emit('updated')
}
</script>
```

---

## 📊 Code Metrics & Targets

### Before Refactoring

| Metric | Value |
|--------|-------|
| Duplicate curriculum_lessons files | 2 (22.5KB + 17.2KB = 39.7KB total) |
| Shared logic duplication | ~60% estimated |
| Route file complexity | High (mixed responsibilities) |
| Controller cohesion | Medium (some separation exists) |

### After Refactoring (Targets)

| Metric | Target | Improvement |
|--------|--------|-------------|
| Shared component size | ~15KB | 62% reduction |
| Role-specific wrappers | ~5KB each | Focused responsibility |
| Total code size | ~25KB | 37% reduction |
| Test coverage | >80% | From ~20% |
| Component reusability | 80% shared | From 40% |

---

## ✅ Testing & Validation

### Automated Testing Checklist

#### Backend (PHPUnit)
- [ ] `WeeklySystemControllerTest::testAdminSeesAllCurricula()`
- [ ] `WeeklySystemControllerTest::testTeacherSeesOnlyAssignedCurricula()`
- [ ] `WeeklySystemControllerTest::testStudentGetsForbidden()`
- [ ] `WeeklyPlanServiceTest::testCopyPlansBetweenClassrooms()`
- [ ] `CurriculumServiceTest::testSetLockDates()`

#### Frontend (Vitest/Jest)
- [ ] `CurriculumLessonsIndex.spec.js` - Shared component tests
- [ ] `AdminCurriculumView.spec.js` - Admin wrapper tests
- [ ] `TeacherCurriculumView.spec.js` - Teacher wrapper tests
- [ ] `WeeklyPlansManager.spec.js` - Weekly plans grid tests
- [ ] `useWeeklyPlans.spec.js` - Composable tests

#### E2E (Laravel Dusk / Playwright)
- [ ] `AdminCurriculumFlowTest` - Full admin workflow
- [ ] `TeacherWeeklyPlansFlowTest` - Teacher planning workflow
- [ ] `RoleSwitchingTest` - Verify isolation between roles

### Manual Testing Checklist

#### Admin Perspective
- [ ] Can view all school curricula
- [ ] Can create new curriculum
- [ ] Can set/edit/delete lock dates
- [ ] Can view all teachers' weekly plans
- [ ] Can copy plans between classrooms
- [ ] Cannot see other schools' data (multi-tenant check)

#### Teacher Perspective
- [ ] Can view only assigned curricula
- [ ] Cannot create school-level curriculum
- [ ] Can edit own weekly plans
- [ ] Can copy plans between own classrooms
- [ ] Cannot see other teachers' plans
- [ ] Cannot access admin-only features

#### Edge Cases
- [ ] User with multiple roles (admin + teacher)
- [ ] Teacher without any assignments
- [ ] Admin in school with no curricula
- [ ] Network failure during plan copy
- [ ] Concurrent edits (race conditions)

---

## 🚀 Rollout Plan

### Stage 1: Development Environment (Week 1-4)
- [ ] Complete all phases in local dev
- [ ] Run parallel systems (old + new)
- [ ] Test with development data

### Stage 2: Staging/UAT (Week 5)
- [ ] Deploy to staging server
- [ ] Invite 2-3 teachers to test
- [ ] Gather feedback on UX changes
- [ ] Performance testing (load tests)

### Stage 3: Pilot School (Week 6-7)
- [ ] Deploy to one pilot school
- [ ] Monitor error logs daily
- [ ] Collect user feedback
- [ ] Iterate on issues found

### Stage 4: Full Rollout (Week 8+)
- [ ] Gradual rollout to all schools
- [ ] Deprecate old routes (add warnings)
- [ ] Remove old code after 2 weeks stable

---

## 🔐 Security Considerations

### Authorization Checks

**Every controller method must verify:**
```php
// Example: Admin-only action
public function storeCurriculum(Request $request)
{
    // Gate/Policy check
    abort_unless($request->user()->can('create-curriculum'), 403);
    
    // School boundary check
    $schoolId = $request->user()->school_id;
    
    // ... proceed
}

// Example: Teacher action
public function updateWeeklyPlan(Request $request, WeeklyPlan $plan)
{
    $teacher = $request->user()->teacher;
    
    // Ownership check
    abort_unless($plan->schedule->cst->teacher_id === $teacher->id, 403);
    
    // Lock date check
    if ($plan->schedule->cst->editLockDate?->isPast()) {
        abort(403, 'This plan is locked');
    }
    
    // ... proceed
}
```

### Data Isolation

**Multi-tenancy rules:**
```php
// ALWAYS filter by school_id
$curricula = Curriculum::where('school_id', $user->school_id)->get();

// NEVER trust client-provided IDs without scope checks
$plan = WeeklyPlan::whereHas('schedule.cst', function($q) use ($user) {
    $q->where('teacher_id', $user->teacher->id);
})->findOrFail($request->plan_id);
```

---

## 📈 Performance Optimizations

### Backend
- [ ] Add eager loading: `->with(['grade', 'subject'])`
- [ ] Cache curriculum lists: `Cache::remember("school_{$id}_curricula", 3600, ...)`
- [ ] Index optimization: Add DB indexes on `teacher_id`, `school_id`, `week_number`
- [ ] Query count reduction: Use JOIN instead of nested whereHas

### Frontend
- [ ] Lazy load heavy components (timetable editor)
- [ ] Virtual scrolling for large tables (>100 rows)
- [ ] Debounce search inputs (300ms)
- [ ] Cache API responses in Pinia store
- [ ] Prefetch next week's data in background

---

## 🎓 Developer Onboarding

### New Developer Guide

**Reading Order:**
1. `docs/features/weekly_system_v1/ARCHITECTURE.md` (this file)
2. `resources/js/Pages/myclass2026/features/weekly_system_v1/README.md`
3. `app/Http/Controllers/WeeklySystemV1/README.md`
4. Review one complete feature flow (e.g., curriculum lessons)

**Key Concepts to Understand:**
- Feature-First vs Role-First architecture
- Diverging responses pattern
- Props-based permission system
- Shared component extraction strategy

---

## 🔄 Future Enhancements (Post-Migration)

### Phase 6: Advanced Features
- [ ] Add parent view (read-only weekly plans visibility)
- [ ] Add student view (simplified schedule viewer)
- [ ] AI-powered lesson plan suggestions
- [ ] Bulk import/export of curricula
- [ ] Version history for curriculum changes
- [ ] Real-time collaboration (multiple teachers editing)

### Phase 7: Analytics & Reporting
- [ ] Curriculum completion analytics
- [ ] Teacher workload reports
- [ ] Plan quality metrics (AI-scored)
- [ ] Export to PDF/Excel

---

## 📝 Migration Checklist Summary

### Pre-Migration
- [ ] Backup database
- [ ] Create feature branch
- [ ] Document current behavior with screenshots
- [ ] Set up monitoring/logging

### During Migration
- [ ] Create directory structure ✓
- [ ] Extract shared components
- [ ] Create role-specific wrappers
- [ ] Refactor controllers
- [ ] Update routes
- [ ] Write tests

### Post-Migration
- [ ] Run automated tests
- [ ] Manual QA testing
- [ ] Performance benchmarks
- [ ] Documentation updates
- [ ] Team training session

---

## 🆘 Troubleshooting & Common Issues

### Issue: "Component shows wrong data for role"
**Solution:** Check that controller is returning correct Inertia render with proper data

### Issue: "Permissions not working"
**Solution:** Verify props are passed correctly from role-specific wrapper to shared component

### Issue: "API returns 403 unexpectedly"
**Solution:** Check middleware and gate policies, ensure route names match

### Issue: "Duplicate API calls"
**Solution:** Verify composables are not calling fetch in both mounted() and watch()

---

## 📞 Support & Resources

- **Architecture Questions:** See `docs/architecture/feature-first-patterns.md`
- **Vue Patterns:** Refer to existing features (`SkillManagement`, `Communication`)
- **Backend Patterns:** Review `CourseManagement` controllers
- **Testing Examples:** Check `tests/Feature/CourseManagement/`

---

**Last Updated:** 2026-03-15  
**Maintained By:** Development Team  
**Next Review:** After Phase 3 completion
