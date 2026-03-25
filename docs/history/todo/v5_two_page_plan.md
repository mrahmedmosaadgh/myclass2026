# 🎓 V5: Two-Page Live Quiz System — Updated Plan

> All audit issues resolved. Aligned with existing project architecture.
> See audit: `/Users/ahmedmosaad/.gemini/antigravity/brain/30d83fc7-8870-4081-9e2f-a3b7411e48b9/v5_plan_audit.md`

---

## 🗺️ Architecture Overview

```
/classroom-records/presentation/builder-v5        → Index.vue (existing)
/classroom-records/presentation/remote/teacher    → remote/TeacherPresenter.vue  [NEW]
/classroom-records/presentation/remote/student    → remote/StudentInteract.vue   [NEW]
```

---

## 🏗️ System Architecture

### Hybrid: MySQL (data) + Firebase (signals only)

| Layer | Tool | Responsibility |
|---|---|---|
| **Persistent data** | MySQL via Laravel | Sessions, participants, answers, scores |
| **Real-time signals** | Firebase | `QUIZ_STARTED`, `SLIDE_CHANGED`, `QUIZ_ENDED` |
| **Data fetching** | Laravel API (axios) | Classrooms, students, groups, question details |

> **Firebase carries signal envelopes only** (event name + minimal context IDs). All actual data lives in MySQL and is fetched via Laravel API. Uses existing `RealtimeEvent` + `useRealtimeChannel` pattern.

---

## 🗂️ Existing Infrastructure to Reuse

### Backend (already built ✅)
| Resource | Path |
|---|---|
| `QuizSession` model | `app/Models/QuizSession.php` |
| `QuizSessionParticipant` model | `app/Models/QuizSessionParticipant.php` |
| `QuizSessionController` | `app/Http/Controllers/QuizSessionController.php` |
| `quiz_sessions` table | `access_code`, `status`, `settings` (JSON), `teacher_id` |
| `quiz_session_participants` | `student_id`, `score`, `status` |
| `RealtimeEvent` | `app/Events/RealtimeEvent.php` |
| Students endpoint | `GET /my_classes_with_students` |

### Frontend (already built ✅)
| Resource | Path |
|---|---|
| `useRealtimeChannel` | `resources/js/composables/useRealtimeChannel.js` |
| `gameStore` | `v5/stores/gameStore.js` — **extend, don't replace** |

---

## 🔥 Firebase Channels (Signals Only)

```
channels/quiz_{access_code}           ← students listen
  { event: 'QUIZ_STARTED', context: { sessionId, questionId, endTime } }
  { event: 'SLIDE_CHANGED', context: { sessionId, slideIndex } }
  { event: 'QUIZ_ENDED',   context: { sessionId } }

channels/quiz_{access_code}_teacher   ← teacher listens
  { event: 'ANSWER_SUBMITTED', context: { sessionId, participantId } }
  { event: 'STUDENT_JOINED',  context: { sessionId, participantId } }
```

Students receive `QUIZ_STARTED` → fetch question from **Laravel API**.
Teacher receives `ANSWER_SUBMITTED` → fetch stats from **Laravel API**.

---

## 📄 Page 1: `remote/TeacherPresenter.vue`

**Route:** `GET /classroom-records/presentation/remote/teacher`
**Layout:** Mobile-responsive — stacked on phones, side-by-side on tablet/desktop.

```
┌────────────────────────────────────────────────┐
│  Code [AB12CD] | Classroom | 🟢 Participants  │
├────────────┬───────────────────────────────────┤
│ SLIDE PANEL│     CANVAS + Quiz Launch Panel    │
├────────────┴───────────────────────────────────┤
│  ◀ [2/8] ▶  |  🏆 Leaderboard                 │
└────────────────────────────────────────────────┘
│  LIVE RESULTS: correct/wrong, participant list  │
└────────────────────────────────────────────────┘
```

### Components

| Component | Role | Signal |
|-----------|------|--------|
| `SessionHeader.vue` | Loads session from Laravel API | — |
| `SlideRemoteControl.vue` | Triggers slide change | fires `SLIDE_CHANGED` via `RealtimeEvent` |
| `QuizLauncher.vue` | POST to `QuizSessionController` | controller fires `QUIZ_STARTED` |
| `LiveResultsPanel.vue` | Listens on `quiz_{code}_teacher` channel | fetch stats via Laravel |
| `ParticipantRoster.vue` | Listens for joins | initial data from Laravel |

### Quiz Launch Flow

```
Teacher submits question →
  POST /quiz/live/test (QuizSessionController.updateState)
  → saves to MySQL
  → fires RealtimeEvent("quiz_{code}", 'QUIZ_STARTED', { questionId, endTime })
  → students receive signal → fetch question from GET /api/questions/{id}
```

---

## 📄 Page 2: `remote/StudentInteract.vue`

**Route:** `GET /classroom-records/presentation/remote/student`
**Layout:** Mobile-first, large tap targets (min 48px).

```
┌─────────────────────────┐
│  Code + Name             │
├─────────────────────────┤
│  SLIDE VIEW (synced)     │
├─────────────────────────┤
│  QUIZ: Question + [A-D]  │
│  ⏱ Timer  ✅ Status      │
├─────────────────────────┤
│  Score | Group           │
└─────────────────────────┘
```

### Components

| Component | Role | Signal |
|-----------|------|--------|
| `JoinForm.vue` | POST to `QuizSessionController.join` | — |
| `StudentSlideView.vue` | Listens for `SLIDE_CHANGED` | fetch slide data |
| `QuizCard.vue` | Listens for `QUIZ_STARTED` → fetch question | POST answer to Laravel |
| `TimerBar.vue` | Uses `endTime` from signal | — |
| `StudentScoreHUD.vue` | Loads score from Laravel | — |

### Answer Submit Flow

```
Student taps answer →
  POST /api/cr/submit-answer { sessionId, selectedIndex }
  → Laravel validates: active? timer? already answered?
  → saves to quiz_attempts (MySQL)
  → fires RealtimeEvent("quiz_{code}_teacher", 'ANSWER_SUBMITTED', { participantId })
  → student UI locks
```

> **All validation server-side.** Frontend never determines correctness or accepts late answers.

---

## 🔗 Routes to Add

```php
// In routes/myclass2026/cr/web.php, inside presentation prefix group:
Route::prefix('remote')->name('remote.')->group(function () {
    Route::get('/teacher', fn() => Inertia::render(
        'myclass2026/features/cr/classroom_records_v1/peresentation/v5/remote/TeacherPresenter',
        ['title' => 'Teacher Live V5']
    ))->name('teacher');

    Route::get('/student', fn() => Inertia::render(
        'myclass2026/features/cr/classroom_records_v1/peresentation/v5/remote/StudentInteract',
        ['title' => 'Student Quiz V5']
    ))->name('student');
});
```

---

## 🗂️ New Files

```
v5/remote/
├── TeacherPresenter.vue
├── StudentInteract.vue
└── components/
    ├── teacher/
    │   ├── SessionHeader.vue
    │   ├── SlideRemoteControl.vue
    │   ├── QuizLauncher.vue
    │   ├── LiveResultsPanel.vue
    │   └── ParticipantRoster.vue
    └── student/
        ├── JoinForm.vue
        ├── StudentSlideView.vue
        ├── QuizCard.vue
        ├── TimerBar.vue
        └── StudentScoreHUD.vue

v5/stores/
└── gameStore.js  [EXTEND: add sessionId, accessCode fields]

# No new sessionStore.js or useFirebaseSession.js needed
# Reuse: useRealtimeChannel.js (already in resources/js/composables/)
```
