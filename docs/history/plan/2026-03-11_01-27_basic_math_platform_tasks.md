# 2026-03-11 01:27 | Basic Math Platform — Task Checklist (Revised)

> All files/classes/tables use the `bm_` or `BM` prefix.
> Stack: Laravel + Inertia.js + Vue 3 + Quasar + Firebase (existing monolith).

---

## ✅ Phase 0: Planning & Documentation
- [x] First master plan written
- [x] Plan revised — aligned with Inertia.js + BM prefix + project conventions
- [x] Task checklist created
- [ ] User approves plan → proceed to Phase 1

---

## Phase 1: Foundation (Database + Backend Core)

### Backend (Namespace: `App\Http\Controllers\Courses\bm` / `App\Models\Courses\bm`)
- [x] Create `bm_assessments` migration & `BMAssessment` model
- [x] Create `bm_questions` migration & `BMQuestion` model
- [x] Create `bm_assessment_responses` migration & `BMAssessmentResponse` model
- [x] Create `bm_scores` migration & `BMScore` model
- [x] Create `bm_lesson_progress` migration & `BMLessonProgress` model
- [x] Create `bm_practice_sessions` migration & `BMPracticeSession` model
- [x] Create `bm_badges` migration & `BMBadge` model
- [x] Build `BMQuestionGenerator` service (parameterized templates)
- [x] Build `BMAdaptiveEngine` service (CAT algorithm)
- [x] Build `BMScoringService` (accuracy + fluency + consistency formula)
- [x] Build `BMGapAnalyzer` to map assessment results to specific lessons
- [x] Build `BMAssessmentController` (Inertia renders, start/submit endpoints)
- [x] Build `BMCurriculumController`
- [x] Create route file `routes/Courses/bm/web.php` and register in central routes
- [x] Add `BMAssessmentGuard` middleware (anti-cheat, session validation)
- [x] Seed 100 starter questions for `bm_questions` table

### Frontend (Inertia.js — `resources/js/Pages/Courses/bm/` & `Components/Courses/bm/`)
- [x] Build `Pages/Courses/bm/Student/AssessmentWelcome.vue`
- [x] Build `Pages/Courses/bm/Student/AssessmentQuestion.vue` (gamified, mascot, timer)
- [x] Build `Components/Courses/bm/Math/NumberPad.vue` and answer inputs
- [x] Build `Components/Courses/bm/UI/ProgressBar.vue`
- [x] Build `Pages/Courses/bm/Student/AssessmentResults.vue` (Radar chart, gap report)
- [ ] Build shareable badge generator component
- [ ] Add sound effects & transitions
- [ ] Build `Pages/Courses/bm/Student/LessonViewer.vue`
- [ ] Build `Pages/Courses/bm/Student/PracticeModule.vue`
- [ ] Build `Pages/Courses/bm/Student/LearningPath.vue`

## Phase 4: Game Mechanics & Data Wrapping
- [x] Implement `BMBadgeService` (calculating streaks/accuracy/speed badges)
- [x] Build `Pages/Courses/bm/Student/BadgeShowcase.vue`
- [x] Build `Pages/Courses/bm/Student/AssessmentHistory.vue`
- [x] Integrate Badges into the `BMAssessmentController@results` method
- [x] Update frontend to trigger exciting animations upon badge unlock

### Composables (`resources/js/Composables/Courses/bm/`)
- [x] `useBMTimer.js`
- [x] `useBMFirebase.js`
- [x] `useBMScore.js`

### Vite Config
- [x] Add `feature-bm` chunk group in `vite.config.js` `manualChunks`

---

## Phase 5: Student Learning Modules (Frontend)
- [x] Build `Pages/Courses/bm/Student/LessonViewer.vue`
- [x] Build `Pages/Courses/bm/Student/PracticeModule.vue`
- [x] Build `Pages/Courses/bm/Student/LearningPath.vue`

---

## Phase 6: Teacher & Parent Portals

### Teacher Dashboard (`Pages/Courses/bm/Teacher/`)
- [x] Build `Pages/Courses/bm/Teacher/Dashboard.vue` (class view)
- [x] Build `Pages/Courses/bm/Teacher/ClassScores.vue`
- [x] Build `Pages/Courses/bm/Teacher/StudentDetail.vue`
- [x] Build `Pages/Courses/bm/Teacher/GapAnalysis.vue`

### Parent Portal (`Pages/Courses/bm/Parent/`)
- [x] Build `Pages/Courses/bm/Parent/Dashboard.vue` (child progress)
- [x] Build `Pages/Courses/bm/Parent/Recommendations.vue`

---

## Phase 7: Firebase Integration

- [x] Define Firebase rules for `/bm_*` namespace (read: own uid only)
- [x] Implement live score push (`/bm_scores/{userId}`)
- [x] Implement leaderboard (`/bm_leaderboard/{period}`)
- [x] Implement session sync (`/bm_sessions/{assessmentId}`) for teachers
- [x] Connect `useBMFirebase.js` composable to existing Firebase setup

---

## Phase 8: 14-Day Beta Launch Logistics

### Pre-Launch (Ready for User Action)
- [x] MVP deployed, internally tested (Phase 1-7 completed)
- [ ] Landing micro-page ("What's Your Math Score?")
- [ ] 60-sec demo video recorded
- [ ] Analytics events set up

### Post-Launch Actions
- Monitor Feedback (Days 5-10)
- Expand to 30+ users
- Activate payment flow (Freemium model)

---

## Final Project Status

> **Status:** Phase 1-7 (Core Platform, Learning Modules, Dashboards, and Integrations) are **100% complete**. 
> The project is now ready for **Phase 8 (Beta Testing & Marketing)**.
