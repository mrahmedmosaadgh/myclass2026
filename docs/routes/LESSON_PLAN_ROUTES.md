# Lesson Plan & Presentation Routes Documentation

**Last Updated**: 2025-12-12 21:13:29 +03:00

## Overview

This document lists all routes related to Lesson Plans and Lesson Presentations in the system. After the recent consolidation, the system now uses a unified database-backed template system.

---

## 1. Lesson Plan Template Routes

### API Routes (CourseManagement)

**Base Path**: `/api/lesson-plan-templates`

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| GET | `/api/lesson-plan-templates` | `LessonPlanTemplateController@index` | List all active templates (filterable by subject_id) |
| POST | `/api/lesson-plan-templates` | `LessonPlanTemplateController@store` | Create a new template |
| GET | `/api/lesson-plan-templates/{id}` | `LessonPlanTemplateController@show` | Get a specific template |
| PUT | `/api/lesson-plan-templates/{id}` | `LessonPlanTemplateController@update` | Update a template |
| DELETE | `/api/lesson-plan-templates/{id}` | `LessonPlanTemplateController@destroy` | Soft delete a template |

**Features**:
- Templates can be global (subject_id = null) or subject-specific
- Supports soft deletes to prevent broken references
- Returns templates with creator information
- Filterable by subject_id via query parameter

**Example Request**:
```bash
# Get all templates for subject ID 5
GET /api/lesson-plan-templates?subject_id=5

# Create a new template
POST /api/lesson-plan-templates
{
  "name": "Science Lab Template",
  "structure": {
    "sections": [
      {
        "id": "objectives",
        "slides": 2,
        "default_slide_type": "text"
      }
    ]
  },
  "subject_id": 5,
  "is_active": true
}
```

---

### Deprecated Routes (Legacy)

**⚠️ These routes are deprecated and will be removed in a future version**

| Method | Endpoint | Status | Replacement |
|--------|----------|--------|-------------|
| GET | `/admin/subject/{subject}/lesson-plan-templates` | 🔴 Commented Out | Use `/api/lesson-plan-templates?subject_id={id}` |
| PATCH | `/admin/subject/{subject}/lesson-plan-templates` | 🔴 Returns 410 Gone | Use POST/PUT `/api/lesson-plan-templates` |

---

## 2. Lesson Presentation Routes

### Main Routes

**Base Path**: `/lesson-presentation`  
**Middleware**: `auth:sanctum`, `verified`

#### Teacher Routes

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| GET | `/lesson-presentation/dashboard` | Inertia render | Teacher dashboard - list all lessons by grade |
| GET | `/lesson-presentation/edit` | Inertia render | Lesson editor (requires teacher authentication) |
| GET | `/lesson-presentation/teacher/progress/{lessonId}` | `teacherProgressDashboard` | View student progress for a lesson |
| GET | `/lesson-presentation/teacher/grades` | `getTeacherGrades` | Get teacher's assigned grades/classrooms/subjects |

#### Student Routes

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| GET | `/lesson-presentation/student/lessons` | `studentLessonList` | List all available lessons for student |
| GET | `/lesson-presentation/student/{id}` | Inertia render | View a specific lesson (requires student authentication) |

#### Print Route

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/lesson-presentation/print/{id}` | Print view for a lesson |

---

### CRUD API Routes

**Base Path**: `/lesson-presentation`

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| GET | `/lesson-presentation/list` | `index` | List presentations (filterable by grade_id) |
| POST | `/lesson-presentation` | `store` | Create a new presentation |
| GET | `/lesson-presentation/{id}` | `show` | Get presentation with slides and templates |
| PUT | `/lesson-presentation/{id}` | `update` | Update presentation |
| DELETE | `/lesson-presentation/{id}` | `destroy` | Delete presentation |

---

### Slide Management Routes

**Base Path**: `/lesson-presentation/{id}/slides`

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| POST | `/lesson-presentation/{id}/slides` | `addSlide` | Add a slide to presentation |
| PUT | `/lesson-presentation/{id}/slides/{slideId}` | `updateSlide` | Update a slide |
| DELETE | `/lesson-presentation/{id}/slides/{slideId}` | `deleteSlide` | Delete a slide |

---

### Template Application Route

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| POST | `/lesson-presentation/{id}/apply-template` | `applyTemplate` | Apply a template to create slides |

**Request Body**:
```json
{
  "lesson_plan_template_id": 5,
  "overwrite": true
}
```

---

### Progress & Student Management Routes

**Base Path**: `/lesson-presentation/progress`

#### Get Progress Data

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| GET | `/lesson-presentation/progress/student/{studentId}` | `getStudentProgress` | Get progress for a student |
| GET | `/lesson-presentation/progress/lesson/{lessonId}/students` | `getLessonProgress` | Get all students' progress for a lesson |
| GET | `/lesson-presentation/progress/{progressId}/submission` | `getPracticeSubmission` | Get practice submission details |

#### Teacher Actions

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| POST | `/lesson-presentation/progress/open` | `openLesson` | Open a lesson for students |
| POST | `/lesson-presentation/progress/lock` | `lockLesson` | Lock a lesson |
| POST | `/lesson-presentation/progress/force-pass` | `forcePass` | Force pass a student |
| POST | `/lesson-presentation/progress/grant-attempt` | `grantAttempt` | Grant additional attempt |
| POST | `/lesson-presentation/progress/reset` | `resetProgress` | Reset student progress |
| PUT | `/lesson-presentation/progress/{id}/practice-grade` | `gradePractice` | Grade practice submission |

#### Student Actions

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| PUT | `/lesson-presentation/progress/{id}/learn-complete` | `completeLearn` | Mark learn section complete |
| POST | `/lesson-presentation/progress/{id}/practice-submit` | `submitPractice` | Submit practice work |
| POST | `/lesson-presentation/progress/{id}/quiz-attempt` | `recordQuizAttempt` | Record quiz attempt |

---

### Utility Routes

| Method | Endpoint | Controller Method | Description |
|--------|----------|------------------|-------------|
| GET | `/lesson-presentation/students/by-grade/{gradeId}` | Closure | Get students by grade (for "Open to All") |
| POST | `/lesson-presentation/proxy-image` | `proxyImage` | Proxy external images (CORS workaround) |

---

## 3. Authentication Requirements

### Teacher Routes
All teacher routes now require proper authentication:
- ✅ `auth()->user()->teacher` must exist
- ✅ Returns 403 if user is not a teacher
- ❌ No longer uses hardcoded `Teacher::first()`

### Student Routes
All student routes now require proper authentication:
- ✅ `auth()->user()->student` must exist
- ✅ Returns 403 if user is not a student
- ❌ No longer uses hardcoded `Student::first()`

---

## 4. Migration History

### Recent Changes (2025-12-12)

1. **Template System Consolidation**
   - Migrated from dual system (JSON + DB) to database-only
   - Added soft deletes to templates
   - Deprecated legacy JSON template routes

2. **Authentication Fixes**
   - Replaced all `Student::first()` with `auth()->user()->student`
   - Replaced all `Teacher::first()` with `auth()->user()->teacher`
   - Added proper 403 responses for unauthorized access

3. **Code Cleanup**
   - Removed deprecated `PresentationController`
   - Deleted 106 backup files
   - Fixed 3 lint errors

---

## 5. File Locations

### Controllers
- `app/Http/Controllers/CourseManagement/LessonPlanTemplateController.php`
- `app/Http/Controllers/LessonPresentationController.php`
- `app/Http/Controllers/LessonProgressController.php`

### Models
- `app/Models/CourseManagement/LessonPlanTemplate.php`
- `app/Models/free/LessonPresentation.php`
- `app/Models/free/LessonPresentationSlide.php`
- `app/Models/LessonStudentProgress.php`

### Routes
- `routes/web_lesson_presentation.php` (Main presentation routes)
- `routes/api.php` (Template API routes)

### Frontend
- `resources/js/Pages/my_table_mnger/lesson_presentation/` (Presentation UI)
- `resources/js/Pages/my_class/admin/Subjects/LessonPlanTemplates.vue` (Template management)

---

## 6. Quick Reference

### Create a Lesson with Template
```bash
# 1. Create presentation
POST /lesson-presentation
{
  "name": "Introduction to Physics",
  "school_id": 1,
  "teacher_id": 5,
  "subject_id": 3,
  "grade_id": 8,
  "lesson_plan_template_id": 2
}

# Template is automatically applied during creation
```

### Apply Template to Existing Lesson
```bash
POST /lesson-presentation/{id}/apply-template
{
  "lesson_plan_template_id": 2,
  "overwrite": true
}
```

### Open Lesson for Students
```bash
POST /lesson-presentation/progress/open
{
  "lesson_presentation_id": 10,
  "student_ids": [1, 2, 3, 4]
}
```

---

## 7. Notes

- All routes require authentication via `auth:sanctum` middleware
- Templates support both global and subject-specific scoping
- Presentations automatically create progress records for all students in the grade
- Slides are ordered by creation order (no drag-drop reordering)
- Old JSON presentation files have been removed from `public/teacher/*/presentations/`

---

**For more information**, see:
- [Implementation Plan](../../../.gemini/antigravity/brain/a366f9b4-9fb9-455d-909b-397d41f50a00/implementation_plan.md)
- [Walkthrough](../../../.gemini/antigravity/brain/a366f9b4-9fb9-455d-909b-397d41f50a00/walkthrough.md)
