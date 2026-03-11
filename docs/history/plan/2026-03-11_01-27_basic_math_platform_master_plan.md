# 2026-03-11 01:27 | Basic Math Platform — Master Plan (Revised)

> **Vision:** Build the ultimate "Basic Math" online platform whose Benchmark Assessment becomes the gold-standard reference for basic math proficiency among kids, teachers, and parents.
>
> **Module Code:** `BM` (Basic Math) — All files, routes, tables, and classes MUST use `bm_` / `BM` prefix.

---

## Table of Contents

1. [Benchmark Assessment Strategy](#1-benchmark-assessment-strategy)
2. [Curriculum Syllabus](#2-curriculum-syllabus)
3. [Technical Architecture (Aligned with Project Stack)](#3-technical-architecture)
4. [14-Day Beta Launch Strategy](#4-14-day-beta-launch-strategy)
5. [Marketing & Content Ideas](#5-marketing--content-ideas)

---

## 1. Benchmark Assessment Strategy

### 1.1 Design Philosophy — "The Math DNA Test"

The assessment must feel like a **game**, not an exam. A scientifically accurate placement test wrapped in a colourful, ClassDojo-style experience.

| Principle | Description |
|---|---|
| **Adaptive Difficulty (CAT)** | Next question difficulty adjusts based on previous answer. Narrows down the student's true level in ~20–25 questions. |
| **5-Domain Coverage** | Addition, Subtraction, Multiplication, Division, Fractions. Score maps to a per-domain proficiency level. |
| **Soft Time Pressure** | Visible timer per question (30–60s). Speed contributes to a "fluency" sub-score — does not lock the student out. |
| **Anti-Cheating** | Algorithm-generated questions from parameterized templates. No two tests are ever identical. |

### 1.2 Scoring Model — The "BM Score"

| Component | Weight | What it Measures |
|---|---|---|
| **Accuracy** | 50% | Percentage of correct answers per domain |
| **Fluency** | 25% | Average response time vs. expected time for the difficulty level |
| **Consistency** | 25% | Low variance in performance across similar difficulty (detects guessing) |

**Student output:**
- **BM Score**: 0–100 → Level: Beginner / Developing / Proficient / Advanced / Expert
- **Domain Radar Chart**: Visual strength per domain
- **Gap Report**: Specific weak skills flagged (e.g., "Carrying in 3-digit addition")

### 1.3 Gamification Layer

| Element | Implementation |
|---|---|
| **Mascot Guide** | Animated character reacts to answers (cheering, thinking, encouraging) |
| **Journey Map** | Visual progress bar (e.g., climbing a mountain) — not a boring question counter |
| **Sound Effects** | Positive on correct. Gentle (never harsh) on incorrect. |
| **End Badge** | Sharable badge/certificate auto-generated from score level |

### 1.4 Trust & Credibility — Making It "The Standard"

- **Curriculum Alignment**: Questions mapped to Common Core / local standards — state this on the landing page.
- **Norming Data**: After 500+ test-takers, publish anonymized percentile data (e.g., "Scored higher than 72% of peers").
- **Retest Policy**: Allow retesting every 30 days to track real growth.

---

## 2. Curriculum Syllabus

### 2.1 Module Overview

| Module | Lessons | Level Range | Prerequisite |
|---|---|---|---|
| M1: Addition Fundamentals | 10 | Beginner–Developing | None |
| M2: Subtraction Fundamentals | 10 | Beginner–Developing | M1 |
| M3: Multiplication Mastery | 12 | Developing–Proficient | M1, M2 |
| M4: Division Mastery | 10 | Developing–Proficient | M3 |
| M5: Fractions Foundation | 12 | Proficient–Advanced | M1–M4 |
| M6: Mixed Operations & Word Problems | 8 | Advanced–Expert | M1–M5 |

### 2.2 Lesson Structure (Every Lesson — 5–7 min)

```
[1 min] 🎬 Animated Concept Intro
[1 min] 🧩 Guided Example (step-by-step)
[2 min] ✏️  Interactive Practice (3–5 questions with instant feedback)
[1 min] 🏆 Challenge Question (earns XP)
[1 min] 📊 Summary + Progress Update
```

### 2.3 Assessment ↔ Curriculum Mapping

After the Benchmark Assessment, the platform automatically:
1. Unlocks all modules where the student scored **Proficient+**
2. Assigns specific lessons for domains scored **Developing** or below
3. Generates an infinite practice stream targeting identified weak skills

---

## 3. Technical Architecture

> ⚠️ **Aligned with your actual stack:** Laravel + Inertia.js + Vue 3 + Quasar + Firebase + single Vite entry (`app.js`).

### 3.1 Core Stack — No Changes Required

| Layer | Technology | Notes |
|---|---|---|
| Backend | Laravel (existing) | BM controllers, models, routes live inside the monolith |
| Frontend | Inertia.js + Vue 3 + Quasar | BM pages live under `Pages/` — no new entry point needed |
| Build | Vite (single entry: `app.js`) | Add BM chunk group in `vite.config.js` |
| Realtime | Firebase Realtime DB (existing) | Use `/bm_*` namespace to keep data isolated |
| Auth | Sanctum (existing) | No changes needed |

### 3.2 Route File Registration

**Pattern observed in project:** New module routes go in `routes/modules/{Module}/web.php` and are auto-loaded by the scanner at the bottom of `web.php`.

```
routes/
└── modules/
    └── BM/                       # NEW — auto-loaded by scanner
        └── web.php               # All BM Inertia + API routes
                                  # URL prefix: /bm
                                  # Route name prefix: bm.
```

**Example route structure in `routes/modules/BM/web.php`:**

```php
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->prefix('bm')
    ->name('bm.')
    ->group(function () {

        // Student Assessment
        Route::get('/assessment', [BMAssessmentController::class, 'index'])->name('assessment.index');
        Route::post('/assessment/start', [BMAssessmentController::class, 'start'])->name('assessment.start');
        Route::post('/assessment/submit', [BMAssessmentController::class, 'submit'])->name('assessment.submit');
        Route::get('/assessment/results/{id}', [BMAssessmentController::class, 'results'])->name('assessment.results');

        // Student Dashboard & Lessons
        Route::get('/dashboard', [BMStudentController::class, 'dashboard'])->name('student.dashboard');
        Route::get('/lesson/{module}/{lesson}', [BMCurriculumController::class, 'show'])->name('lesson.show');
        Route::post('/lesson/progress', [BMCurriculumController::class, 'saveProgress'])->name('lesson.progress');

        // Practice (Infinite Generator)
        Route::post('/practice/generate', [BMQuestionController::class, 'generate'])->name('practice.generate');

        // Teacher Dashboard
        Route::get('/teacher', [BMTeacherController::class, 'index'])->name('teacher.index');
        Route::get('/teacher/student/{userId}', [BMTeacherController::class, 'studentDetail'])->name('teacher.student');

        // Parent Portal
        Route::get('/parent', [BMParentController::class, 'index'])->name('parent.index');
    });
```

├── useBMTimer.js
├── useBMFirebase.js
└── useBMScore.js
```

### 3.4 Controllers — Flat `BM` Prefix (Following `Qu*`, `Dp*` Convention)

**Pattern observed:** Project uses flat `QuExamController.php`, `DpDailyPlannerController.php` — no subfolders for small modules.

```
app/Http/Controllers/
├── BMAssessmentController.php    # Start, submit, score assessment
├── BMQuestionController.php      # Generate parameterized questions
├── BMCurriculumController.php    # Lesson delivery + progress
├── BMStudentController.php       # Student dashboard
├── BMTeacherController.php       # Teacher dashboard + class reports
└── BMParentController.php        # Parent portal
```

### 3.5 Models — Flat `BM` Prefix (Following `Qu*`, `Dp*` Convention)

```
app/Models/
├── BMAssessment.php
├── BMAssessmentResponse.php
├── BMQuestion.php
├── BMLessonProgress.php
├── BMPracticeSession.php
└── BMBadge.php
```

### 3.6 Database Tables — `bm_` Prefix

All migration files named: `YYYY_MM_DD_HHMMSS_create_bm_{table}_table.php`

```sql
bm_assessments          (id, user_id, type, status, started_at, completed_at,
                          total_score, accuracy_score, fluency_score, consistency_score, level)
bm_assessment_responses (id, bm_assessment_id, bm_question_id, user_answer,
                          correct_answer, is_correct, time_taken_ms, difficulty_level, domain)
bm_questions            (id, domain, sub_skill, difficulty 1-10, template,
                          parameters_json, correct_answer, explanation)
bm_lesson_progress      (id, user_id, module, lesson_number, status, score,
                          completed_at, time_spent_seconds)
bm_practice_sessions    (id, user_id, domain, questions_attempted,
                          questions_correct, avg_time_ms, session_date)
bm_badges               (id, user_id, badge_type, earned_at, bm_assessment_id)
```

### 3.7 Firebase — `/bm_*` Namespace

Isolated within the existing Firebase project — no setup changes needed.

```
/bm_scores/{userId}              → Live score after assessment completes
/bm_leaderboard/daily/{...}     → Daily leaderboard
/bm_leaderboard/weekly/{...}    → Weekly leaderboard
/bm_leaderboard/alltime/{...}   → All-time leaderboard
/bm_sessions/{assessmentId}     → Live progress sync (visible to teachers)
```

### 3.8 Vite Config Update

Add a `feature-bm` chunk group in `vite.config.js` `manualChunks`:

```js
// In vite.config.js manualChunks callback — add this block:
if (id.includes('resources/js/Pages/myclass2026/roles/student/BM') ||
    id.includes('resources/js/Pages/myclass2026/roles/teacher/BM') ||
    id.includes('resources/js/Pages/myclass2026/roles/parent/BM') ||
    id.includes('resources/js/Components/BM')) {
    return 'feature-bm';
}
```

### 3.9 Algorithmic Question Generator

Questions are generated on-the-fly from parameterized templates — not stored as static records:

```
Template:     "{a} × {b} = ?"
Domain:       Multiplication
Sub-skill:    Times tables (6-9)
Constraints:  a ∈ [6,9], b ∈ [2,12]
Difficulty:   6/10
Answer:       a * b
```

This delivers **infinite unique practice questions** with zero database lookups in practice mode.

---

## 4. 14-Day Beta Launch Strategy

### Phase 1: Pre-Launch (Days 1–4)
- Seed 100 starter questions in `bm_questions` (20 per domain)
- Deploy assessment engine MVP
- Internal QA — team takes the test 10+ times
- Build landing micro-page: "What's Your Math Score?"
- Record 60-second demo video

### Phase 2: Closed Beta (Days 5–10)
- Invite **10 families** (friends, colleagues) for private beta
- Monitor via Firebase `/bm_sessions/`, fix critical bugs
- Expand to **30+ users** including 2–3 teachers
- Add first 3 lessons for users who finish the assessment
- Survey: "Did the score accurately reflect your child's level?"

### Phase 3: Soft Launch (Days 11–14)
- Open public access (free, email required)
- Launch social content (see Section 5)
- Introduce freemium: free = assessment + 3 lessons; premium = full course
- Set up payment flow

---

## 5. Marketing & Content Ideas

1. **"What's Your Math Grade?" Challenge (Viral Hook)**
   Kid takes the test on camera, score reveals, dares friends to beat it.

2. **"Can an Adult Pass a Kids' Math Test?" (Humility Hook)**
   Adult struggles on a 4th-grade fraction question. Short, funny, shareable.

3. **Teacher Testimonial Carousel (Trust Builder)**
   Anonymized class data showing how the platform surfaced hidden gaps — then fixed them.

---

## 6. Revenue Model

| Tier | Features | Price |
|---|---|---|
| **Free** | Full assessment, BM Score report, 3 lessons | $0 |
| **Student Premium** | Full course (62 lessons), ∞ practice, badges, progress tracking | $9.99/mo |
| **Classroom** | Class management, bulk assessment, gap analysis, reporting | $14.99/mo/teacher |

---

> **Next Step:** Confirm this plan. First coding task = migrations + seed questions + `BMAssessmentController`.
