# MyClass2026 Folder Structure Plan

This plan organizes the `resources/js/Pages/myclass2026/` directory with a proper **role-based structure** and creates a root `GUIDE.md` that tracks what's done, what's missing, and where everything lives.

---

## 🔎 Key Discovery

The project already has **significant work done** — but scattered across many folders (40+ in Pages). The `myclass2026/` folder is currently **empty**. The strategy:

1. **Do NOT move any existing files** (too risky to break routes/imports)
2. Create a clean **role+feature folder tree** inside `myclass2026/` for **new development going forward**
3. The `GUIDE.md` will document both old and new locations so nothing gets lost

---

## User Review Required

> [!IMPORTANT]  
> **You must decide on 3 questions before I proceed:**
> 
> **Q1 — Should I move existing files into the new structure, or leave them in place and only reference them in GUIDE.md?**
> - ✅ Option A: Leave existing files in place, only create new structure for future work *(safe, no risk)*
> - 🔄 Option B: Gradually migrate files (needs route/import updates too)  
> 
> **Q2 — Which roles does your platform support?**  
> Based on brainstorming + role_based_structure.md, I found: `SuperSystem`, `SystemAdmin`, `SchoolAdmin`, `Teacher`, `Student`, `Parent`. Should I add **`Developer`** and **`HR`** as well (I see those folders exist)?
>
> **Q3 — Which platform modules from your brainstorming session should be included in the structure?**  
> From the session, agreed features were:
> - Instructional Planning (Curriculum, Weekly Planning, Lesson Prep)
> - Assessment Tools (Question Bank, Quiz, Exam, Exam Reports)
> - Live Interaction (Live Lesson, Live Quiz, QR Smart Scanner)
> - Administrative Tracking (Class Records, Topic Tracking, Feedback)
> - Time Management (Timetable, Calendar, Personal Workspace)
> - Task Management (To-Do List, Topic Filters)
> - **NEW: Smart Assessment Station** (QR Cards + Camera + Voice Confirmation)

---

## Proposed Folder Tree

```
resources/js/Pages/myclass2026/
│
├── GUIDE.md                          ← Root guide (auto-updated checklist)
│
├── _shared/                          ← Shared across all roles
│   ├── components/                   ← Reusable UI components
│   ├── layouts/                      ← Shared layout wrappers
│   └── composables/                  ← Shared Vue composables
│
├── roles/
│   │
│   ├── super-system/                 ← SuperSystem role
│   │   ├── Dashboard.vue
│   │   ├── UserManagement/
│   │   └── SystemReports/
│   │
│   ├── system-admin/                 ← SystemAdmin role
│   │   ├── Dashboard.vue
│   │   ├── SchoolManagement/
│   │   └── Analytics/
│   │
│   ├── school-admin/                 ← SchoolAdmin role
│   │   ├── Dashboard.vue
│   │   ├── AcademicYears/            [EXISTS → my_class/admin/AcademicYears]
│   │   ├── Classrooms/               [EXISTS → my_class/admin/Classrooms]
│   │   ├── Curriculum/               [EXISTS → my_class/admin/Curriculum]
│   │   ├── Schedules/                [EXISTS → my_class/admin/Schedules]
│   │   ├── Students/                 [EXISTS → my_class/admin/Students]
│   │   ├── Teachers/                 [EXISTS → my_class/admin/Teachers]
│   │   └── CourseManagement/         [EXISTS → CourseManagement/]
│   │
│   ├── teacher/                      ← Teacher role
│   │   ├── Dashboard.vue
│   │   │
│   │   ├── planning/                 ← Module 1: Instructional Planning
│   │   │   ├── CurriculumMap/        [EXISTS → my_class/admin/Curriculum]
│   │   │   ├── WeeklyPlan/           [EXISTS → WeeklyPlans/]
│   │   │   └── LessonPrep/           [EXISTS → LessonTemplateManager/]
│   │   │
│   │   ├── assessment/               ← Module 2: Assessment Tools
│   │   │   ├── QuestionBank/         [EXISTS → my_class/QuQuestionBankSystem/QuQuestionList]
│   │   │   ├── ExamBuilder/          [EXISTS → my_class/QuQuestionBankSystem/QuExamForm]
│   │   │   ├── ExamGrading/          [EXISTS → my_class/QuQuestionBankSystem/QuGrading]
│   │   │   ├── ExamReports/          [EXISTS → my_class/QuQuestionBankSystem/QuAnalyticsDashboard]
│   │   │   └── ExamPrint/            [EXISTS → my_class/QuQuestionBankSystem/QuExamPrint]
│   │   │
│   │   ├── live/                     ← Module 3: Live Interaction
│   │   │   ├── LiveLesson/           [TODO - new]
│   │   │   ├── LiveQuiz/             [EXISTS partial → my_class/QuQuestionBankSystem/QuQuizManagement]
│   │   │   └── SmartScanner/         [TODO - new: QR+Camera+Confirmation system]
│   │   │
│   │   ├── records/                  ← Module 4: Administrative Tracking
│   │   │   ├── ClassRecords/         [EXISTS → my_class/teacher/StudentRecords.vue]
│   │   │   ├── TopicTracking/        [TODO - new]
│   │   │   └── FeedbackCollector/    [TODO - new]
│   │   │
│   │   ├── time-management/          ← Module 5: Time & Task Management
│   │   │   ├── Calendar/             [EXISTS → my_class/teacher/Calendar/]
│   │   │   ├── Timetable/            [EXISTS → my_class/teacher/schedule/]
│   │   │   └── TodoList/             [TODO - new]
│   │   │
│   │   └── presentation/             ← Module 6: Lesson Delivery
│   │       ├── LessonPresentation/   [EXISTS → my_class/teacher/lesson_presentation]
│   │       └── PresentationV2/       [EXISTS → my_class/teacher/peresntation_2]
│   │
│   ├── student/                      ← Student role
│   │   ├── Dashboard.vue
│   │   ├── MyClasses/                [EXISTS partial → Student/]
│   │   ├── TakeExam/                 [EXISTS → my_class/QuQuestionBankSystem/QuTakeExam]
│   │   ├── MyGrades/                 [EXISTS partial]
│   │   ├── Schedule/                 [EXISTS → Students/Schedule]
│   │   ├── SkillPractice/            [EXISTS → SkillPractice/]
│   │   └── Flashcards/               [EXISTS → VocabularyFlashcards/]
│   │
│   └── parent/                       ← Parent role
│       ├── Dashboard.vue
│       ├── ChildProgress/            [TODO - new]
│       ├── Communication/            [TODO - new]
│       └── Reports/                  [TODO - new]
│
└── features/                         ← Cross-role standalone features
    ├── smart-scanner/                 ← 🆕 QR Smart Assessment Station
    │   ├── ScanStation.vue
    │   ├── QrCardGenerator.vue
    │   └── components/
    ├── notifications/                 [EXISTS → Notifications/]
    ├── chat/                          [EXISTS → Chat/, PrivateChat/]
    └── qr-tools/                      [EXISTS → BarcodeScanner.vue, QrCodeGenerator.vue]
```

---

## GUIDE.md Content (to be created)

The `GUIDE.md` file in the root of `myclass2026/` will contain:
- ✅ Checked boxes for completed features
- 📂 Links to where existing code lives
- 🆕 Markers for what needs to be built
- 🔗 Route references for each feature

---

## Proposed Changes

### myclass2026/ (NEW files to create)

#### [NEW] `GUIDE.md` — root guide with folder instructions and status  
#### [NEW] `roles/` folder tree — all role+module folders (empty placeholders)  
#### [NEW] `_shared/` folder — shared components/layouts/composables  
#### [NEW] `features/smart-scanner/` — placeholder for the QR smart scanner system (from brainstorming)

---

## Verification Plan

### Manual Verification
Since this is a **folder structure + documentation** task (no running code yet), verification is:

1. Open `C:\my_project\myclass2026-main2\resources\js\Pages\myclass2026\` in VS Code Explorer
2. Confirm the folder tree matches the plan above
3. Open `GUIDE.md` and verify all items are clearly listed with ✅/🆕 markers
4. Search existing features in old folders — confirm they are still intact (no moves yet)

---

## What I Will NOT Do (Without Your Approval)
- ❌ Move or rename any existing files
- ❌ Change any existing routes or imports
- ❌ Create actual Vue component code (only folder + placeholder files)
