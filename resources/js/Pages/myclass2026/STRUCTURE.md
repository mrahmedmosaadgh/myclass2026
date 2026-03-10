# MyClass 2026 — Folder Structure & Status

> Legend: ✅ Done | 🔧 Partial / In Progress | ❌ Not Started | 🆕 New (planned) | [EXISTS → path] = code is in legacy folder

---

## `_shared/` — Shared Across All Roles

```
_shared/
├── components/     ❌  Reusable UI components (buttons, cards, modals)
├── layouts/        ❌  Page layout wrappers per role
└── composables/    ❌  Vue 3 composables (useAuth, useRole, etc.)
```

---

## `roles/super-system/` — Platform Super Admin

```
super-system/
├── Dashboard.vue           🔧  [EXISTS → my_class/super_admin/]
├── UserManagement/         ❌
└── SystemReports/          ❌
```

---

## `roles/system-admin/` — System Administrator

```
system-admin/
├── Dashboard.vue           ❌
├── SchoolManagement/       ❌
└── Analytics/              ❌
```

---

## `roles/school-admin/` — School Administrator

```
school-admin/
├── Dashboard.vue               ❌
├── AcademicYears/              ✅  [EXISTS → my_class/admin/AcademicYears/]
├── Classrooms/                 ✅  [EXISTS → my_class/admin/Classrooms/]
├── ClassroomSubjectTeachers/   ✅  [EXISTS → my_class/admin/ClassroomSubjectTeachers/]
├── Curriculum/                 ✅  [EXISTS → my_class/admin/Curriculum/]
├── GradeSubjects/              ✅  [EXISTS → my_class/admin/GradeSubjects/]
├── Grades/                     ✅  [EXISTS → my_class/admin/Grades/]
├── PeriodActivities/           ✅  [EXISTS → my_class/admin/PeriodActivities/]
├── PeriodDetails/              ✅  [EXISTS → my_class/admin/PeriodDetails/]
├── QuestionBanks/              ✅  [EXISTS → my_class/admin/QuestionBanks/]
├── ScheduleCopies/             ✅  [EXISTS → my_class/admin/ScheduleCopies/]
├── ScheduleDailies/            ✅  [EXISTS → my_class/admin/ScheduleDailies/]
├── Schedules/                  ✅  [EXISTS → my_class/admin/Schedules/]
├── Schools/                    ✅  [EXISTS → my_class/admin/Schools/]
├── SemesterTests/              ✅  [EXISTS → my_class/admin/SemesterTests/]
├── Semesters/                  ✅  [EXISTS → my_class/admin/Semesters/]
├── Stages/                     ✅  [EXISTS → my_class/admin/Stages/]
├── StudentParents/             ✅  [EXISTS → my_class/admin/StudentParents/]
├── Students/                   ✅  [EXISTS → my_class/admin/Students/]
├── Subjects/                   ✅  [EXISTS → my_class/admin/Subjects/]
├── Teachers/                   ✅  [EXISTS → my_class/admin/Teachers/]
├── CourseManagement/           ✅  [EXISTS → CourseManagement/]
└── MenuManagement/             ✅  [EXISTS → Admin/MenuManagement/]
```

---

## `roles/teacher/` — Teacher Role

### `planning/` — Module 1: Instructional Planning

```
teacher/planning/
├── CurriculumMap/          ✅  [EXISTS → my_class/admin/Curriculum/]
├── WeeklyPlan/             ✅  [EXISTS → WeeklyPlans/]
└── LessonPrep/             ✅  [EXISTS → LessonTemplateManager/]
```

### `assessment/` — Module 2: Assessment Tools

```
teacher/assessment/
├── QuestionBank/           ✅  [EXISTS → my_class/QuQuestionBankSystem/QuQuestionList.vue]
├── QuestionForm/           ✅  [EXISTS → my_class/QuQuestionBankSystem/QuQuestionForm.vue]
├── ExamBuilder/            ✅  [EXISTS → my_class/QuQuestionBankSystem/QuExamForm.vue]
├── ExamEditor/             ✅  [EXISTS → my_class/QuQuestionBankSystem/QuExamEditor.vue]
├── ExamList/               ✅  [EXISTS → my_class/QuQuestionBankSystem/QuExamList.vue]
├── ExamGrading/            ✅  [EXISTS → my_class/QuQuestionBankSystem/QuGrading.vue]
├── GradingDialog/          ✅  [EXISTS → my_class/QuQuestionBankSystem/QuGradingDialog.vue]
├── GradingList/            ✅  [EXISTS → my_class/QuQuestionBankSystem/QuGradingList.vue]
├── ExamReports/            ✅  [EXISTS → my_class/QuQuestionBankSystem/QuAnalyticsDashboard.vue]
├── ExamPrint/              ✅  [EXISTS → my_class/QuQuestionBankSystem/QuExamPrint.vue]
└── ExamResults/            ✅  [EXISTS → my_class/QuQuestionBankSystem/QuExamResults.vue]
```

### `live/` — Module 3: Live Interaction

```
teacher/live/
├── LiveLesson/             ❌  To build
├── LiveQuiz/               🔧  [EXISTS partial → my_class/QuQuestionBankSystem/QuQuizManagement/]
└── SmartScanner/           🆕  NEW — QR Card + Camera + Confirmation system
    ├── ScanStation.vue     ❌  Main scanning interface (camera + face detection)
    ├── QrCardGenerator.vue ❌  Generate student QR card sets (A/B/C/D + Confirm/Cancel)
    └── components/
        ├── FaceGuard.vue   ❌  face-api.js integration (verify person present)
        ├── QrReader.vue    ❌  QR scanner wrapper
        └── ResultDisplay.vue ❌ Real-time result display
```

### `records/` — Module 4: Administrative Tracking

```
teacher/records/
├── ClassRecords/           🔧  [EXISTS → my_class/teacher/StudentRecords.vue]
├── TopicTracking/          ❌  To build
└── FeedbackCollector/      ❌  To build
```

### `time-management/` — Module 5: Time & Task Management

```
teacher/time-management/
├── Calendar/               ✅  [EXISTS → my_class/teacher/Calendar/Index.vue]
├── Timetable/              ✅  [EXISTS → my_class/teacher/schedule/]
└── TodoList/               ❌  To build (with topic filters)
```

### `presentation/` — Module 6: Lesson Delivery

```
teacher/presentation/
├── LessonPresentation/     ✅  [EXISTS → my_class/teacher/lesson_presentation/]
└── PresentationV2/         ✅  [EXISTS → my_class/teacher/peresntation_2/]
```

---

## `roles/student/` — Student Role

```
student/
├── Dashboard.vue           ❌
├── MyClasses/              🔧  [EXISTS partial → Student/Dashboard/]
├── TakeExam/               ✅  [EXISTS → my_class/QuQuestionBankSystem/QuTakeExam.vue]
├── StudentExamList/        ✅  [EXISTS → my_class/QuQuestionBankSystem/QuStudentExamList.vue]
├── MyGrades/               🔧  Partial
├── Schedule/               ✅  [EXISTS → Students/Schedule/]
├── SkillPractice/          ✅  [EXISTS → SkillPractice/]
└── Flashcards/             ✅  [EXISTS → VocabularyFlashcards/]
```

---

## `roles/parent/` — Parent Role

```
parent/
├── Dashboard.vue           ❌
├── ChildProgress/          ❌  To build
├── Communication/          ❌  To build
└── Reports/                ❌  To build
```

---

## `roles/hr/` — Human Resources

```
hr/
├── Dashboard.vue           ❌
├── StaffManagement/        🔧  [EXISTS partial → my_class/hr/]
├── Attendance/             🔧  [EXISTS partial → my_class/hr/]
└── Payroll/                ❌  To build
```

---

## `roles/developer/` — Developer Tools

```
developer/
├── ComponentTests/         🧪  [EXISTS → MicroComponentTest/]
├── ApiDocs/                ❌
└── FeatureFlags/           ❌
```

---

## `features/` — Cross-Role Standalone Features

```
features/
├── smart-scanner/          🆕  See teacher/live/SmartScanner above for full breakdown
├── notifications/          ✅  [EXISTS → Notifications/]
├── chat/                   ✅  [EXISTS → Chat/, PrivateChat/]
└── qr-tools/               ✅  [EXISTS → BarcodeScanner.vue, QrCodeGenerator.vue]
```

---

## Progress Summary

| Role | Done | Partial | Todo |
|---|---|---|---|
| super-system | 1 | 1 | 2 |
| system-admin | 0 | 0 | 3 |
| school-admin | 22 | 0 | 1 |
| teacher | 16 | 3 | 6 |
| student | 5 | 2 | 1 |
| parent | 0 | 0 | 4 |
| hr | 0 | 2 | 1 |
| developer | 1 | 0 | 2 |
| **features** | 3 | 0 | 1 |
