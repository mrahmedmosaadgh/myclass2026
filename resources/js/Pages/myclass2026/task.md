# Task: MyClass2026 Folder Structure Plan

## Phase 1: Audit Existing Features (✅ Completed)
- [x] Explore `resources/js/Pages` - all existing folders
- [x] Read `role_based_structure.md` reference
- [x] Read brainstorming session (platform features)
- [x] Explore `my_class/` (admin, teacher, super_admin, hr, QuQuestionBankSystem)
- [x] Explore `WeeklyPlans/`, `CourseManagement/`, `SkillPractice/`, `Admin/`, `Dashboard/`
- [x] Map existing features to platform modules

## Phase 2: Write Implementation Plan (✅ Completed)
- [x] Create `implementation_plan.md` with folder tree proposal
- [x] Present to user for review and decisions
- [x] Write `myclass2026_master_plan.md` with 6 Sprints

## Phase 3: Setup & Shared Infrastructure (Sprint 5 & 6)
- [x] Scaffold `roles/` directory with all 8 role sub-folders
- [x] Scaffold `features/` directory for standalone modules
- [x] Create `_shared/components/` (AppCard, AppModal, etc.)
- [x] Create `_shared/layouts/RoleLayout.vue`
- [x] Create `_shared/composables/` (useAuth, useRole, useRealtime)
- [x] Update `GUIDE.md` with new folder locations

## Phase 4: Smart Scanner MVP (Sprint 1)
- [x] Create `features/smart-scanner/ScanStation.vue`
- [x] Create `features/smart-scanner/QrCardGenerator.vue`
- [x] Create `features/smart-scanner/components/FaceGuard.vue`
- [x] Create `features/smart-scanner/components/QrReader.vue`
- [x] Create `features/smart-scanner/components/ResultDisplay.vue`
- [x] Build backend `StudentAnswer` model & migration
- [x] Build `/api/scan/submit` endpoint & Reverb broadcast

## Phase 5: Role Dashboards (Sprint 2)
- [x] Create `roles/teacher/Dashboard.vue` and connect Teacher tools
- [x] Create `roles/student/Dashboard.vue` and connect Student tools
- [x] Create `roles/school-admin/Dashboard.vue` and connect admin panels

## Phase 6: Missing Core Features (Sprint 3)
- [x] Build `roles/teacher/records/TopicTracking/`
- [x] Build `roles/teacher/time-management/TodoList/`
- [x] Build `roles/teacher/live/LiveLesson/`
- [x] Enhance `roles/teacher/live/LiveQuiz/`

## Phase 7: Parent Portal (Sprint 4)
- [x] Build `roles/parent/Dashboard.vue`
- [x] Build `roles/parent/ChildProgress/`
- [x] Build `roles/parent/Communication/`
- [x] Build `roles/parent/Reports/`

## Phase 8: Legacy Feature Migration (Sprint 5)

*Protocol for EVERY Feature:*
1. *Check existing features/code.*
2. *Copy them to the correct new folder (`myclass2026/roles/...`).*
3. *Move old features to the "old features" folder.*
4. *Update the old routes in the new "old features" route file so the old links still work.*
5. *Create/update a single route file for the specific role matching the folder structure (e.g., `routes/myclass2026/roles/teacher.php`).*
6. *Update the role's menu in `config/menus/[role].php`.*
7. *Confirm with user before moving to the next feature.*

- [x] **Setup Phase:** Create `old_features/` folder and new `routes/old_features.php` file
- [x] **Feature 1:** Migrate **Daily Tasks & Weekly Plans** (Follow protocol 1-7)
- [x] **Feature 2:** Migrate **Course Management & Lessons** (Follow protocol 1-7)
- [x] **Feature 3:** Migrate **Vocab, Skills, Gamification** (Follow protocol 1-7)
- [x] **Feature 4:** Migrate **Chat & Communication** (Follow protocol 1-7)
- [x] **Feature 5:** Migrate **User Profiles (Student/Teacher/Parent)** (Follow protocol 1-7)
- [x] **Feature 6:** Migrate **Admin & System Tools** (Follow protocol 1-7)

## Existing Features Audit Summary

| Existing Location | Feature | Status | Proposed Role |
|---|---|---|---|
| `my_class/admin/` | AcademicYears, Classrooms, Curriculum, Grades, Schedules, Semesters, Stages, Schools, Subjects, Students, Teachers + more (25 folders!) | ✅ Done (rich) | SchoolAdmin |
| `my_class/teacher/` | Calendar, QuestionTypes, lesson_presentation, schedule, StudentRecords | ✅ Done | Teacher |
| `my_class/QuQuestionBankSystem/` | QuExamList, QuExamForm, QuExamEditor, QuGrading, QuQuestionList, QuTakeExam, QuAnalyticsDashboard (16 files) | ✅ Done (rich) | Teacher + Student |
| `WeeklyPlans/` | Weekly planning (Index, Edit, components) | ✅ Done | Teacher |
| `CourseManagement/` | Course, Lesson, Level, Section, Import | ✅ Done | SchoolAdmin/Teacher |
| `SkillPractice/` | SkillBrowser, SkillPracticeSession | ✅ Done | Student |
| `Admin/` | MenuManagement, SkillManagement, SchoolBrandingSettings | ✅ Done | SystemAdmin |
| `MicroComponentTest/` | mdEditor, FractionGenerator, test components | 🧪 Test/Dev | Dev only |
| `Qudrat/` | Landing page (assessment product) | ✅ Done | Standalone |
| `QudratQuantitative/` | Quantitative assessment | ✅ Done | Standalone |
| `Dashboard/` | Components | 🔧 In Progress | All roles |
| `my_class/teacher/Calendar/` | Calendar (Index.vue 42kb!) | ✅ Done | Teacher |
| `Notifications/`, `Chat/`, `PrivateChat/` | Communication features | ✅ Done | Shared |
| `LessonTemplateManager/` | Lesson templates | ✅ Done | Teacher |
| `VocabularyFlashcards/` | Flashcard practice | ✅ Done | Student |
| `my_class/hr/` | HR features | ✅ Done | SystemAdmin |
| `my_class/super_admin/` | Super admin features | ✅ Done | SuperSystem |

---------------------
myclass2026/
├── GUIDE.md
├── _shared/
│   ├── components/
│   ├── layouts/
│   └── composables/
├── roles/
│   ├── super-system/       (UserManagement, SystemReports)
│   ├── system-admin/       (SchoolManagement, Analytics)
│   ├── school-admin/       (Classrooms, Curriculum, Schedules, Students, Teachers...)
│   ├── teacher/
│   │   ├── planning/       (CurriculumMap, WeeklyPlan, LessonPrep)
│   │   ├── assessment/     (QuestionBank, ExamBuilder, ExamGrading, ExamReports)
│   │   ├── live/           (LiveLesson, LiveQuiz, SmartScanner 🆕)
│   │   ├── records/        (ClassRecords, TopicTracking, Feedback)
│   │   ├── time-management/(Calendar, Timetable, TodoList)
│   │   └── presentation/
│   ├── student/            (MyClasses, TakeExam, MyGrades, SkillPractice, Flashcards)
│   ├── parent/             (ChildProgress, Communication, Reports)
│   ├── hr/                 (StaffManagement, Payroll, Attendance)
│   └── developer/          (ComponentTests, ApiDocs, FeatureFlags)
└── features/
    ├── smart-scanner/      🆕 (ScanStation, QrCardGenerator)
    ├── notifications/
    ├── chat/
    ├── qr-tools/
---------------------