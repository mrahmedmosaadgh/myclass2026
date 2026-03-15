# Weekly System V1 - Quick Start Guide

## 🎯 What's Changing?

**Before (Role-First):**
```
roles/school-admin/weekly_system/curriculum_lessons/Index.vue (22.5KB)
roles/teacher/weekly_system/curriculum_lessons/Index.vue (17.2KB)
→ 60% duplicate code!
```

**After (Feature-First):**
```
features/weekly_system_v1/curriculum_lessons/Index.vue (15KB shared)
features/weekly_system_v1/curriculum_lessons/AdminCurriculumView.vue (5KB admin-specific)
features/weekly_system_v1/curriculum_lessons/TeacherCurriculumView.vue (5KB teacher-specific)
→ 37% less code, easier to maintain!
```

## 📁 New Structure at a Glance

```
features/weekly_system_v1/
├── dashboards/           # Entry points (Admin vs Teacher)
├── curriculum_lessons/   # Curriculum management
├── weekly_plans/         # Weekly plan editor
├── timetable/            # Schedule editor
└── components/           # Shared UI components
```

## 🔑 Key Concepts

### 1. **Single Route, Multiple Views**
```php
// ONE route
Route::get('/curriculum-lessons', [WeeklySystemController::class, 'index']);

// Controller decides which view to render:
if ($user->is_admin) {
    return Inertia::render('.../AdminCurriculumView');
} else {
    return Inertia::render('.../TeacherCurriculumView');
}
```

### 2. **Shared Components with Props**
```vue
<!-- Parent passes permissions as props -->
<CurriculumList 
  :can-create="true"
  :can-edit="false"
  :can-delete="false"
/>
```

### 3. **Backend Diverging Responses**
```php
// Admin gets ALL curricula
Curriculum::where('school_id', $schoolId)->get();

// Teacher gets only ASSIGNED curricula  
Curriculum::whereHas('classroomSubjectTeachers', fn($q) => 
    $q->where('teacher_id', $teacher->id)
)->get();
```

## 🚀 Usage Examples

### For Admins

**Access Point:** `/weekly-system-v1/`

**What You Can Do:**
- ✅ View all school curricula
- ✅ Create/edit/delete curricula
- ✅ Set lock dates
- ✅ View all teachers' weekly plans
- ✅ Copy plans between classrooms

### For Teachers

**Access Point:** `/weekly-system-v1/`

**What You Can Do:**
- ✅ View assigned curricula only
- ✅ Edit my weekly plans
- ✅ Copy plans between MY classrooms
- ❌ Cannot create school curricula
- ❌ Cannot see other teachers' data

## 📋 Migration Timeline

| Week | Phase | Status |
|------|-------|--------|
| 1 | Foundation Setup | 📝 Planning |
| 2 | Backend Consolidation | ⏳ Pending |
| 3 | Frontend Migration | ⏳ Pending |
| 4 | Component Extraction | ⏳ Pending |

## 🆘 Quick Troubleshooting

**Problem:** "I can't see the new pages"  
**Solution:** Make sure you're using the new route prefix: `/weekly-system-v1/` instead of old `/weekly-system/`

**Problem:** "Button X is missing"  
**Solution:** Check your role - some buttons are role-specific (e.g., "Set Lock Dates" for admins only)

**Problem:** "Data looks wrong"  
**Solution:** Clear browser cache and reload - old cached components may be interfering

## 📖 Full Documentation

See [`PLAN.md`](./PLAN.md) for complete architecture details, code examples, and migration steps.

---

**Questions?** Check the main plan or ask in the dev channel!
