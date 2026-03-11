# 2026-03-11 01:41 | Project Understanding — myclass2026 Codebase Intelligence

> A full picture of what this project is, how it is structured, and the conventions every new module must follow.

---

## 1. What Is This Project?

**myclass2026** is a **comprehensive school management platform** built as a **Laravel monolith** with a **Vue 3 + Inertia.js** frontend. It serves multiple user roles (super-admin, school-admin, teacher, student, parent, HR) under a single codebase and single deployment.

It is **not** a multi-app system. Everything lives in one Laravel + Vite build.

---

## 2. Core Tech Stack

| Layer | Technology | Notes |
|---|---|---|
| Backend | **Laravel 12** | Monolith. Single `artisan serve`. |
| Frontend | **Vue 3 + Inertia.js** | No separate SPA. Inertia handles routing. |
| UI Framework | **Quasar** | Used alongside Inertia (not Quasar SPA mode). |
| State | **Pinia** | Vue 3 store. |
| I18n | **vue-i18n** | Multi-language support. |
| Build | **Vite** (single entry: `resources/js/app.js`) | One bundle. Manually chunked. |
| Realtime | **Firebase Realtime Database** | Already configured. Used for live scores, notifications, presence. |
| Auth | **Laravel Sanctum + Jetstream** | Session-based auth. `auth:sanctum` middleware. |
| Permissions | **Spatie Permission** | Role-based access control. |
| CSS | **Tailwind CSS** (configured) | Used alongside Quasar styles. |
| Hosting | Server-managed (Herd locally, production on custom server) |

---

## 3. Directory Map

```
myclass2026-main/
├── app/
│   ├── Http/Controllers/       ← All controllers (flat + subdirs for large groups)
│   ├── Models/                 ← All models (flat with prefix for module isolation)
│   └── Services/               ← Business logic services (implied)
├── database/
│   ├── migrations/             ← Standard Laravel migrations
│   └── seeders/
├── resources/
│   └── js/
│       ├── app.js              ← SINGLE Vite entry point
│       ├── Pages/              ← All Inertia pages
│       │   ├── myclass2026/    ← NEW architecture (role-based structure)
│       │   │   ├── STRUCTURE.md
│       │   │   ├── roles/
│       │   │   │   ├── school-admin/
│       │   │   │   ├── teacher/
│       │   │   │   ├── student/
│       │   │   │   ├── parent/
│       │   │   │   ├── hr/
│       │   │   │   ├── super-system/
│       │   │   │   ├── system-admin/
│       │   │   │   └── developer/
│       │   │   └── features/   ← Cross-role features (chat, notifications, qr)
│       │   ├── my_class/       ← LEGACY pages still in active use
│       │   ├── Admin/          ← Legacy admin pages
│       │   ├── Teacher/        ← Legacy teacher pages
│       │   ├── Student/        ← Legacy student pages
│       │   └── [other legacy dirs]
│       └── Components/         ← Reusable Vue components
├── routes/
│   ├── web.php                 ← MASTER router (includes all others)
│   ├── admin.php
│   ├── r_teacher.php
│   ├── r_student.php
│   ├── r_hr.php
│   ├── weekly_system.php
│   ├── myclass2026/roles/      ← NEW role-based route files
│   │   ├── teacher.php
│   │   ├── school-admin.php
│   │   ├── student.php
│   │   └── parent.php
│   ├── modules/                ← Auto-loaded module routes ← USE THIS for BM
│   │   └── Academics/
│   └── qudrat/                 ← Domain-specific routes (qudratpro.com)
├── docs/history/               ← Development logs and plans
└── vite.config.js              ← Single entry, manual chunking
```

---

## 4. How Routes Work

### Registration Chain in `web.php`

```php
// Role-based routes (new architecture)
include '.../routes/myclass2026/roles/teacher.php';
include '.../routes/myclass2026/roles/school-admin.php';
// ...

// Module auto-loader (scans routes/modules/* for web.php)
$modulesPath = base_path('routes/modules');
foreach (scandir($modulesPath) as $module) {
    require $modulesPath.'/'.$module.'/web.php';
}

// Course modules auto-loader (NEW pattern matching the unified layout)
$coursesPath = base_path('routes/Courses');
// ... logic to include routes/Courses/bm/web.php
```

### Naming Conventions

| Pattern | Example | Used For |
|---|---|---|
| `prefix('admin')->name('admin.')` | `admin.users.index` | Admin-scoped routes |
| `prefix('bm')->name('bm.')` | `bm.assessment.index` | ← BM module routes (`routes/Courses/bm/web.php`) |
| `prefix('developer')->name('developer.')` | `developer.tasks.get` | Developer tools |
| `prefix('quizzes')->name('quizzes.')` | `quizzes.create` | Quiz system |

---

## 5. How Controllers Work

### Naming Pattern

Modules use a **flat file with a 2-letter prefix** — no subfolder for small/medium modules:

| Module | Prefix | Example Controllers |
|---|---|---|
| Question Bank | `Qu` | `QuExamController.php`, `QuQuestionController.php` |
| Daily Planner | `Dp` | `DpDailyPlannerController.php`, `DpFocusController.php` |
| **Basic Math** | `BM` | `BMAssessmentController.php`, `BMQuestionController.php` |

Subdirectories are only used for **large subsystems** that already exist:
`Admin/`, `Teacher/`, `Student/`, `AI/`, `Developer/`, `Curriculum/`, `QudratQuantitative/`

---

## 6. How Models Work

Same flat prefix pattern — no subfolder for new modules:

| Module | Models |
|---|---|
| Question Bank | `QuExam.php`, `QuQuestion.php`, `QuAttempt.php`, `QuAnswer.php` |
| Daily Planner | `DpDailyTask.php`, `DpFocusLog.php`, `DpReward.php`, `DpTask.php` |
| **Basic Math** | `BMAssessment.php`, `BMQuestion.php`, `BMLessonProgress.php`, etc. |

---

## 7. How Pages (Inertia) Work

### Render Pattern

```php
// In controller:
return Inertia::render('myclass2026/roles/student/BM/AssessmentWelcome', [
    'props' => $data
]);
```

### File Lives At

```
resources/js/Pages/myclass2026/roles/student/BM/AssessmentWelcome.vue
```

### New vs Legacy

| Directory | Status | Use For |
|---|---|---|
| `Pages/myclass2026/roles/` | ✅ Active (new architecture) | All new features |
| `Pages/my_class/` | 🔧 Legacy (still in use) | Existing features only |
| `Pages/Admin/`, `Pages/Teacher/` etc. | 🔧 Legacy | Existing features only |

---

## 8. How Vite Is Configured

- **Single entry:** `resources/js/app.js`
- **Only Quasar + laravel-vite-plugin + vue** as plugins
- **Manual chunks** are defined to prevent "1 file per component" explosion:

```js
// Existing chunk groups:
'vendor-firebase'       ← all Firebase packages
'vendor-xlsx'           ← xlsx package
'feature-quiz-engine'   ← quiz/question pages & components
'feature-admin-core'    ← admin pages
'feature-teacher-portal'← teacher pages

// To add for BM:
'feature-bm'            ← all BM pages and components
```

---

## 9. Firebase — How It's Used

Firebase is **already configured and wired up** in the project:

- Config: `.env` + `firebase.json` + `.firebaserc`
- Rules: `firebase-rules.json`
- Usage: Realtime Database for live class data, quiz sessions, presence
- Pattern: Data is namespaced by feature (e.g., quiz sessions use their own paths)

**BM will use:** `/bm_scores/`, `/bm_leaderboard/`, `/bm_sessions/` — all isolated under `/bm_*`.

---

## 10. How the `myclass2026` Architecture Works

The `myclass2026` folder is the **new, clean architecture** replacing legacy `my_class/`. It is documented in `STRUCTURE.md`.

- Role → Folder → Feature pages
- Cross-role features live in `features/`
- Shared components live in `_shared/`
- **Status of roles:** school-admin is mostly done; teacher ~60% done; student ~50%; parent 0%; hr ~20%

New modules like **BM** should be placed as a **subfolder inside the relevant role folder(s)**:
```
roles/student/BM/
roles/teacher/BM/
roles/parent/BM/
```

---

## 11. Database Conventions

| Convention | Example |
|---|---|
| Table prefix per module | `bm_assessments`, `qu_exams`, `dp_tasks` |
| Foreign key naming | `bm_assessment_id` (not just `assessment_id`) |
| Migration naming | `YYYY_MM_DD_HHMMSS_create_bm_assessments_table.php` |
| Soft deletes | Used on most core tables |
| Timestamps | Always `created_at` / `updated_at` |

---

## 12. BM Module — Integration Summary

Based on everything above, here is exactly where the **Basic Math (BM)** module slots into the existing project using the unified `Courses/bm/` namespace:

| Artifact | Location | Status |
|---|---|---|
| Route file | `routes/Courses/bm/web.php` | 🆕 To create |
| Controllers | `app/Http/Controllers/Courses/bm/BM*.php` | 🆕 To create |
| Models | `app/Models/Courses/bm/BM*.php` | 🆕 To create |
| Migrations | `database/migrations/..._create_bm_*_table.php` | 🆕 To create |
| Pages | `resources/js/Pages/Courses/bm/{Role}/` | 🆕 To create |
| Components | `resources/js/Components/Courses/bm/` | 🆕 To create |
| Composables | `resources/js/Composables/Courses/bm/` | 🆕 To create |
| Firebase namespace | `/bm_scores/`, `/bm_leaderboard/`, `/bm_sessions/` | 🆕 To configure |
| Vite chunk | `feature-bm` in `vite.config.js` manualChunks | 🆕 To add |

---

> This document reflects the codebase state as of **2026-03-11**.
> It should be updated whenever major architectural changes are made.
