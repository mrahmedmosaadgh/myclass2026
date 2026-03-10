# MyClass 2026 — Platform Features Reference

> This document describes all platform features, their modules, target roles, and build status.
> Based on: brainstorming session + role_based_structure.md

---

## Legend
| Symbol | Meaning |
|---|---|
| ✅ | Built and working |
| 🔧 | Partially built |
| ❌ | Not started |
| 🆕 | Newly designed — not built yet |
| [EXISTS → path] | Code lives in legacy folder |

---

## Module 1 — Instructional Planning
*Target Role: Teacher*

| # | Feature | Status | Location |
|---|---|---|---|
| 1.1 | Curriculum Map | ✅ | `my_class/admin/Curriculum/` |
| 1.2 | Weekly Planning | ✅ | `WeeklyPlans/` (Index.vue, Edit.vue) |
| 1.3 | Lesson Preparation | ✅ | `LessonTemplateManager/` |

---

## Module 2 — Assessment Tools
*Target Role: Teacher + SchoolAdmin*

| # | Feature | Status | Location |
|---|---|---|---|
| 2.1 | Question Bank | ✅ | `QuQuestionBankSystem/QuQuestionList.vue` |
| 2.2 | Question Form | ✅ | `QuQuestionBankSystem/QuQuestionForm.vue` |
| 2.3 | Quiz Builder | ✅ | `QuQuestionBankSystem/QuExamForm.vue` |
| 2.4 | Exam Builder | ✅ | `QuQuestionBankSystem/QuExamEditor.vue` |
| 2.5 | Exam List | ✅ | `QuQuestionBankSystem/QuExamList.vue` |
| 2.6 | Exam Print | ✅ | `QuQuestionBankSystem/QuExamPrint.vue` |
| 2.7 | Exam Grading | ✅ | `QuQuestionBankSystem/QuGrading.vue` |
| 2.8 | Grading Dialog | ✅ | `QuQuestionBankSystem/QuGradingDialog.vue` |
| 2.9 | Grading List | ✅ | `QuQuestionBankSystem/QuGradingList.vue` |
| 2.10 | Exam Reports / Analytics | ✅ | `QuQuestionBankSystem/QuAnalyticsDashboard.vue` |
| 2.11 | Exam Results | ✅ | `QuQuestionBankSystem/QuExamResults.vue` |
| 2.12 | Student Exam List | ✅ | `QuQuestionBankSystem/QuStudentExamList.vue` |
| 2.13 | Student Take Exam | ✅ | `QuQuestionBankSystem/QuTakeExam.vue` |
| 2.14 | Admin Question Banks | ✅ | `my_class/admin/QuestionBanks/` |
| 2.15 | Admin Semester Tests | ✅ | `my_class/admin/SemesterTests/` |

---

## Module 3 — Live Interaction
*Target Role: Teacher*

| # | Feature | Status | Location |
|---|---|---|---|
| 3.1 | Live Lesson | ❌ | To build in `teacher/live/LiveLesson/` |
| 3.2 | Live Quiz | 🔧 | `QuQuestionBankSystem/QuQuizManagement/` |
| 3.3 | **Smart Assessment Scanner** | 🆕 | `teacher/live/SmartScanner/` ← See section below |

### 🆕 Smart Assessment Scanner — Full Spec

**Concept:** Students each have a printed card with 4 QR codes (A, B, C, D) + 2 command codes (✅ Confirm / ❌ Cancel). Teacher's phone camera reads codes in real time.

**Flow:**
```
1. Teacher opens ScanStation on mobile (front camera mode)
2. Student walks up → holds A/B/C/D card to camera
3. Face detection confirms student is present (face-api.js)
4. Camera captures photo + reads QR code (student ID + choice)
5. System waits for Confirm QR scan (green) or Cancel (red)
6. If confirmed → answer saved + student photo logged
7. If cancelled → reset and re-scan
8. Screen updates live (student name appears, status shown)
```

**Multi-answer mode (Batch Input):**
- Student scans Answer Card for Q1 → Confirm
- Then Q2 → Confirm... up to 5 questions per station visit
- System submits full answer array at end

**Components to build:**

| Component | File | Description |
|---|---|---|
| Scan Station | `ScanStation.vue` | Main page — camera + state machine |
| QR Card Generator | `QrCardGenerator.vue` | Generates printable per-student card sets |
| Face Guard | `components/FaceGuard.vue` | Detects face before allowing scan |
| QR Reader | `components/QrReader.vue` | html5-qrcode wrapper |
| Result Display | `components/ResultDisplay.vue` | Live result panel (Reverb/broadcast) |

**QR Code Data Format:**
```
Answer cards:   {studentId}-A  /  {studentId}-B  /  {studentId}-C  /  {studentId}-D
Command codes:  CMD_CONFIRM  /  CMD_CANCEL
```

**Tech Stack:**
- Frontend: Vue 3 + html5-qrcode + face-api.js
- Backend: Laravel API endpoint to receive `{ student_id, choice, photo_base64 }`
- Real-time: Laravel Reverb for live dashboard update
- Offline: IndexedDB (Dexie.js) → sync when online

---

## Module 4 — Administrative Tracking
*Target Role: Teacher + SchoolAdmin*

| # | Feature | Status | Location |
|---|---|---|---|
| 4.1 | Class Records / Student Records | 🔧 | `my_class/teacher/StudentRecords.vue` |
| 4.2 | Topic Tracking | ❌ | To build in `teacher/records/TopicTracking/` |
| 4.3 | Feedback Collector | ❌ | To build in `teacher/records/FeedbackCollector/` |
| 4.4 | Attendance | 🔧 | `my_class/admin/` (partial) |

---

## Module 5 — Time Management
*Target Role: Teacher*

| # | Feature | Status | Location |
|---|---|---|---|
| 5.1 | Calendar | ✅ | `my_class/teacher/Calendar/Index.vue` (42KB!) |
| 5.2 | Timetable / Schedule | ✅ | `my_class/teacher/schedule/` |
| 5.3 | To-Do List (with topic filters) | ❌ | To build in `teacher/time-management/TodoList/` |
| 5.4 | Admin Calendar | ✅ | `my_class/admin/Calendars/` |
| 5.5 | Year/Semester Calendar | ✅ | `my_class/admin/year_semester_calendar/` |

---

## Module 6 — Lesson Delivery / Presentation
*Target Role: Teacher*

| # | Feature | Status | Location |
|---|---|---|---|
| 6.1 | Lesson Presentation V1 | ✅ | `my_class/teacher/lesson_presentation/` |
| 6.2 | Lesson Presentation V2 | ✅ | `my_class/teacher/peresntation_2/` |
| 6.3 | Question Types (in lesson) | ✅ | `my_class/teacher/QuestionTypes/` |

---

## Module 7 — Student Learning
*Target Role: Student*

| # | Feature | Status | Location |
|---|---|---|---|
| 7.1 | My Classes | 🔧 | `Student/Dashboard/` |
| 7.2 | Take Exam | ✅ | `QuQuestionBankSystem/QuTakeExam.vue` |
| 7.3 | My Grades | 🔧 | Partial |
| 7.4 | My Schedule | ✅ | `Students/Schedule/` |
| 7.5 | Skill Practice | ✅ | `SkillPractice/SkillBrowser.vue` |
| 7.6 | Skill Practice Session | ✅ | `SkillPractice/SkillPracticeSession.vue` |
| 7.7 | Vocabulary Flashcards | ✅ | `VocabularyFlashcards/` |

---

## Module 8 — School Administration
*Target Role: SchoolAdmin*

| # | Feature | Status | Location |
|---|---|---|---|
| 8.1 | Academic Years | ✅ | `my_class/admin/AcademicYears/` |
| 8.2 | Semesters | ✅ | `my_class/admin/Semesters/` |
| 8.3 | Stages | ✅ | `my_class/admin/Stages/` |
| 8.4 | Grades | ✅ | `my_class/admin/Grades/` |
| 8.5 | Subjects | ✅ | `my_class/admin/Subjects/` |
| 8.6 | Schools | ✅ | `my_class/admin/Schools/` |
| 8.7 | Classrooms | ✅ | `my_class/admin/Classrooms/` |
| 8.8 | Teachers | ✅ | `my_class/admin/Teachers/` |
| 8.9 | Students | ✅ | `my_class/admin/Students/` |
| 8.10 | Student Parents | ✅ | `my_class/admin/StudentParents/` |
| 8.11 | Schedules | ✅ | `my_class/admin/Schedules/` |
| 8.12 | Schedule Copies | ✅ | `my_class/admin/ScheduleCopies/` |
| 8.13 | Daily Schedules | ✅ | `my_class/admin/ScheduleDailies/` |
| 8.14 | Period Activities | ✅ | `my_class/admin/PeriodActivities/` |
| 8.15 | Period Details | ✅ | `my_class/admin/PeriodDetails/` |
| 8.16 | Grade Subjects | ✅ | `my_class/admin/GradeSubjects/` |
| 8.17 | Classroom Subject Teachers | ✅ | `my_class/admin/ClassroomSubjectTeachers/` |
| 8.18 | Teacher Import | ✅ | `my_class/admin/TeacherImport.vue` |
| 8.19 | Course Management | ✅ | `CourseManagement/` |
| 8.20 | School Branding | ✅ | `Admin/SchoolBrandingSettings.vue` |
| 8.21 | Menu Management | ✅ | `Admin/MenuManagement/` |
| 8.22 | QR Admin | ✅ | `my_class/admin/qr/` |

---

## Module 9 — Parent Portal
*Target Role: Parent*

| # | Feature | Status | Location |
|---|---|---|---|
| 9.1 | Dashboard | ❌ | To build |
| 9.2 | Child Progress | ❌ | To build |
| 9.3 | Communication | ❌ | To build |
| 9.4 | Reports | ❌ | To build |

---

## Module 10 — Communication (Shared)
*Target Role: All roles*

| # | Feature | Status | Location |
|---|---|---|---|
| 10.1 | Notifications | ✅ | `Notifications/` |
| 10.2 | Group Chat | ✅ | `Chat/` |
| 10.3 | Private Chat | ✅ | `PrivateChat/` |
| 10.4 | Conversations | ✅ | `Conversations/` |

---

## Module 11 — HR
*Target Role: HR*

| # | Feature | Status | Location |
|---|---|---|---|
| 11.1 | Staff Management | 🔧 | `my_class/hr/` |
| 11.2 | Attendance Tracking | 🔧 | `my_class/hr/` |
| 11.3 | Payroll | ❌ | To build |

---

## Overall Build Progress

| Module | Total | ✅ Done | 🔧 Partial | ❌ Todo |
|---|---|---|---|---|
| Instructional Planning | 3 | 3 | 0 | 0 |
| Assessment Tools | 15 | 14 | 0 | 1 |
| Live Interaction | 3 | 0 | 1 | 2 |
| Administrative Tracking | 4 | 0 | 2 | 2 |
| Time Management | 5 | 4 | 0 | 1 |
| Lesson Delivery | 3 | 3 | 0 | 0 |
| Student Learning | 7 | 5 | 2 | 0 |
| School Administration | 22 | 22 | 0 | 0 |
| Parent Portal | 4 | 0 | 0 | 4 |
| Communication | 4 | 4 | 0 | 0 |
| HR | 3 | 0 | 2 | 1 |
| **TOTAL** | **73** | **55** | **7** | **11** |
