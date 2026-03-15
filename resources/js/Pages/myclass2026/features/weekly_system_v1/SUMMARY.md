# 📋 Weekly System V1 Migration - Executive Summary

## 🎯 Mission Statement

Restructure the weekly system from a **Role-First** to a **Feature-First** architecture to eliminate code duplication, improve maintainability, and establish clear separation between shared feature logic and role-specific presentations.

---

## 📊 Current State Analysis

### Problems Identified

1. **Code Duplication Crisis**
   - `roles/school-admin/weekly_system/curriculum_lessons/Index.vue` (22.5KB)
   - `roles/teacher/weekly_system/curriculum_lessons/Index.vue` (17.2KB)
   - **~60% duplicate code** between these files
   - Total waste: ~39.7KB with only ~16KB unique content

2. **Scattered Routes**
   - Mixed responsibilities in `routes/weekly_system.php`
   - Role-based route splitting creates confusion
   - No clear API vs. Page separation

3. **Backend Complexity**
   - Monolithic `WeeklySystemController` (1009 lines)
   - Partial controller organization not fully leveraged
   - Services exist but not consistently used

4. **Maintenance Burden**
   - Bug fixes must be applied in 2+ places
   - New features require duplicate implementation
   - Hard to add new roles (parent, student) later

---

## ✨ Target Architecture

### Core Principles

1. **Feature-First for Shared Functionality**
   - Curriculum, weekly plans, schedules are school-wide concepts
   - Shared components live in feature folder
   - Role-specific views are thin wrappers

2. **Diverging Backend Responses**
   - Single controller method handles all roles
   - Different Inertia renders based on user role
   - Each role receives only data they need

3. **Props-Based Permissions**
   - Shared components receive permissions as props
   - No hardcoded role checks in components
   - Easy to test and reason about

---

## 📁 New Structure

```
resources/js/Pages/myclass2026/features/weekly_system_v1/
├── PLAN.md              # Complete migration plan (1227 lines)
├── README.md            # Quick start guide (118 lines)
├── TASKS.md             # Task checklist (364 lines)
├── ARCHITECTURE.md      # Visual diagrams (398 lines)
│
├── dashboards/          # Entry points per role
├── curriculum_lessons/  # Curriculum management
├── weekly_plans/        # Weekly plan editor
├── timetable/           # Schedule management
└── components/          # Reusable UI components
```

### Before vs After Code Size

| Component | Before | After | Reduction |
|-----------|--------|-------|-----------|
| Admin Curriculum | 22.5KB | 5KB wrapper | 78% ↓ |
| Teacher Curriculum | 17.2KB | 5KB wrapper | 71% ↓ |
| **Shared Logic** | - | 15KB base | - |
| **Total** | 39.7KB | 25KB | **37% ↓** |

---

## 🗺️ Migration Roadmap

### Phase 1: Foundation (Week 1) ✅
- [x] Create directory structure
- [x] Write documentation (PLAN, README, TASKS, ARCHITECTURE)
- [ ] Extract shared components

### Phase 2: Backend (Week 1-2)
- [ ] Refactor controllers with diverging responses
- [ ] Extract service layer methods
- [ ] Create API resources

### Phase 3: Frontend (Week 2-3)
- [ ] Migrate curriculum lessons
- [ ] Migrate weekly plans manager
- [ ] Create role-specific dashboards

### Phase 4: Routes (Week 3)
- [ ] Create `routes/weekly_system_v1.php`
- [ ] Update route includes
- [ ] Test all combinations

### Phase 5: Testing (Week 4)
- [ ] Write unit tests (>80% coverage)
- [ ] Integration tests
- [ ] E2E workflow tests

### Phase 6: Deployment (Week 5+)
- [ ] Staging environment testing
- [ ] Pilot school rollout
- [ ] Full deployment

---

## 🔑 Key Features

### For Admins

**Dashboard:** `/weekly-system-v1/`

**Capabilities:**
- ✅ View ALL school curricula
- ✅ Create/edit/delete curricula
- ✅ Set lock dates
- ✅ View all teachers' weekly plans
- ✅ Bulk copy plans between classrooms
- ✅ School-wide analytics

### For Teachers

**Dashboard:** `/weekly-system-v1/`

**Capabilities:**
- ✅ View assigned curricula only
- ✅ Edit my weekly plans
- ✅ Copy plans between MY classrooms
- ✅ Quick-edit interface
- ❌ Cannot create school curricula
- ❌ Cannot access admin features

---

## 💾 Backend Implementation

### Controller Pattern

```php
// Single controller, diverging responses
class WeeklySystemController extends Controller
{
    public function curriculumLessonsIndex(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('school-admin')) {
            return $this->renderAdminView($user);
        }
        
        if ($user->hasRole('teacher')) {
            return $this->renderTeacherView($user);
        }
        
        abort(403);
    }
}
```

### Service Layer

```php
// Business logic extraction
class CurriculumService 
{
    public function getSchoolCurricula($schoolId)
    {
        return Curriculum::where('school_id', $schoolId)
            ->with(['grade', 'subject'])
            ->get();
    }
    
    public function getTeacherAssignedCurricula($teacherId)
    {
        return Curriculum::whereHas('classroomSubjectTeachers', fn($q) => 
            $q->where('teacher_id', $teacherId)
        )->get();
    }
}
```

---

## 🎨 Frontend Pattern

### Shared Base Component

```vue
<!-- curriculum_lessons/Index.vue -->
<template>
  <div>
    <h1>{{ title }}</h1>
    <q-table :data="curricula" :columns="columns" />
    
    <!-- Role-specific slots -->
    <slot name="admin-actions"></slot>
    <slot name="teacher-actions"></slot>
  </div>
</template>

<script setup>
// SHARED LOGIC (60% of old code)
const props = defineProps({
  curricula: Array,
  canCreate: Boolean,
  canEdit: Boolean,
  canDelete: Boolean
})
</script>
```

### Role-Specific Wrapper

```vue
<!-- AdminCurriculumView.vue -->
<template>
  <CurriculumLessonsIndex
    title="School Curricula"
    :curricula="curricula"
    :can-create="true"
    :can-delete="true"
  >
    <template #admin-actions>
      <q-btn label="Set Lock Dates" @click="..." />
    </template>
  </CurriculumLessonsIndex>
</template>
```

---

## 🔒 Security Model

### Authorization Layers

1. **Middleware Level**
   - Authentication required
   - Role verification
   
2. **Controller Level**
   - Policy checks
   - Gate authorization
   
3. **Service Level**
   - School ID scoping
   - Ownership verification
   
4. **Database Level**
   - Foreign key constraints
   - Soft deletes
   - Unique indexes

### Example: Teacher Data Isolation

```php
// WRONG: Trusts client input
$plan = WeeklyPlan::findOrFail($request->plan_id);

// CORRECT: Verifies ownership
$plan = WeeklyPlan::whereHas('schedule.cst', function($q) use ($teacher) {
    $q->where('teacher_id', $teacher->id);
})->findOrFail($request->plan_id);
```

---

## 📈 Expected Benefits

### Quantitative Improvements

| Metric | Current | Target | Improvement |
|--------|---------|--------|-------------|
| Code Size | 39.7KB | 25KB | 37% ↓ |
| Duplication | 60% | <5% | 92% ↓ |
| Test Coverage | ~20% | >80% | 4x ↑ |
| Component Reuse | 40% | 80% | 2x ↑ |
| File Count | 2 files | 3 files | +50% (but smaller!) |

### Qualitative Benefits

✅ **Maintainability**: Fix bugs once, not 2-3 times  
✅ **Extensibility**: Easy to add parent/student views  
✅ **Clarity**: Clear separation of concerns  
✅ **Performance**: Load only data needed per role  
✅ **Testing**: Higher coverage, easier mocks  

---

## 🚀 Getting Started

### For Developers

1. **Read Documentation**
   - Start with `README.md` (quick overview)
   - Deep dive into `PLAN.md` (complete details)
   - Reference `ARCHITECTURE.md` (visual guides)

2. **Understand Patterns**
   - Feature-First vs Role-First
   - Diverging responses
   - Props-based permissions

3. **Start Small**
   - Begin with component extraction
   - Test each piece independently
   - Gradually migrate features

### For Testers

1. **Know the Workflows**
   - Admin: School-wide management
   - Teacher: Personal assignments only

2. **Test Scenarios**
   - Role switching
   - Permission boundaries
   - Edge cases (no data, multiple roles)

3. **Regression Checklist**
   - Old routes still work (temporarily)
   - New routes functional
   - No data loss or corruption

---

## 📞 Support & Resources

### Documentation Files

- **PLAN.md** - Complete migration plan with code examples
- **README.md** - Quick start guide
- **TASKS.md** - Detailed task checklist
- **ARCHITECTURE.md** - Visual diagrams and flows

### Existing References

- **CourseManagement** - Similar feature-first pattern
- **SkillManagement** - Component extraction example
- **Communication** - Role-based view splitting

### Contact Points

- Architecture questions → See PLAN.md Section 12
- Vue patterns → Review existing features
- Backend patterns → CourseManagement controllers

---

## ⚠️ Important Notes

### During Migration

1. **Keep Old Routes Working**
   - Don't break existing functionality
   - Run both systems in parallel
   - Add deprecation notices later

2. **Test Extensively**
   - Every role combination
   - All permission levels
   - Edge cases and errors

3. **Document As You Go**
   - Update TASKS.md when completing items
   - Note any deviations from plan
   - Record lessons learned

### After Migration

1. **Monitor Performance**
   - Page load times
   - API response times
   - Database query counts

2. **Gather Feedback**
   - User experience (admin vs teacher)
   - Developer experience (adding features)
   - Tester experience (finding bugs)

3. **Iterate**
   - Fix issues found in pilot
   - Optimize based on metrics
   - Refine documentation

---

## 🎉 Success Criteria

Migration is successful when:

✅ All automated tests passing (>80% coverage)  
✅ Manual QA sign-off from testers  
✅ Pilot school reports no critical issues  
✅ Performance metrics meet targets  
✅ Team trained on new patterns  
✅ Old routes deprecated without issues  

---

## 📅 Timeline Summary

| Phase | Duration | Status |
|-------|----------|--------|
| Planning & Documentation | 1 day | ✅ Complete |
| Backend Consolidation | 1 week | ⏳ Pending |
| Frontend Migration | 2 weeks | ⏳ Pending |
| Testing & QA | 1 week | ⏳ Pending |
| Deployment | 2-3 weeks | ⏳ Pending |

**Total Estimated Time:** 5-7 weeks

---

## 🏁 Next Steps

1. **Review This Plan**
   - Read all documentation files
   - Understand the patterns
   - Ask clarifying questions

2. **Setup Development Environment**
   - Create feature branch
   - Backup current state
   - Prepare testing accounts

3. **Begin Phase 1**
   - Create directory structure
   - Extract first shared component
   - Write initial tests

4. **Track Progress**
   - Update TASKS.md daily
   - Commit frequently
   - Document learnings

---

**Ready to begin? Start with TASKS.md Phase 1!** 🚀

---

*Last Updated: 2026-03-15*  
*Version: 1.0*  
*Status: Planning Complete, Ready for Implementation*
