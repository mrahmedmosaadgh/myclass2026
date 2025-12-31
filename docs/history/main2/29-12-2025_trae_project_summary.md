# myclass2026 Project Summary (Trae)

Date: 2025-12-29  
Repo root: `/Users/ahmedmosaad/Herd/myclass2026-main`

## 1) What this project is

This is a school/education management system built as a Laravel monolith with an Inertia + Vue 3 frontend. Laravel serves both:

- Server-rendered “page endpoints” that return Inertia pages (`Inertia::render(...)`)
- JSON APIs (mostly behind `auth:sanctum`) for data-heavy modules and SPA interactions

The UI is a Vue 3 app (no Vue Router; navigation is via Inertia), styled with Tailwind + Quasar, and built with Vite.

## 2) Tech stack and core libraries

### Backend

- PHP `^8.2`
- Laravel `^12.0` (web + API)
- Inertia Laravel adapter: `inertiajs/inertia-laravel`
- Auth: Jetstream + Fortify (session-based web auth) + Sanctum (API auth)
- RBAC: `spatie/laravel-permission` (roles/permissions)
- Web push: `laravel-notification-channels/webpush` + `minishlink/web-push`
- Spreadsheet import/export: `phpoffice/phpspreadsheet`
- Ziggy: `tightenco/ziggy` (route helpers exposed to frontend)

Sources:
- [composer.json](file:///Users/ahmedmosaad/Herd/myclass2026-main/composer.json)

### Frontend

- Vue 3 + Inertia Vue 3 adapter (`@inertiajs/vue3`)
- Vite (Laravel Vite plugin) entry: `resources/js/app.js`
- UI: Quasar (`quasar`, `@quasar/vite-plugin`) + TailwindCSS
- State: Pinia
- i18n: `vue-i18n` (English + Arabic messages)
- Test runner: Vitest

Sources:
- [package.json](file:///Users/ahmedmosaad/Herd/myclass2026-main/package.json)
- [vite.config.js](file:///Users/ahmedmosaad/Herd/myclass2026-main/vite.config.js)
- [app.js](file:///Users/ahmedmosaad/Herd/myclass2026-main/resources/js/app.js)

## 3) Repository layout (high level)

### Laravel (backend)

- `app/Http/Controllers/*`: main controllers (admin, teacher/student flows, quiz, question bank, schedule, etc.)
- `app/Http/Middleware/*`: request-level shared props and custom tracking middleware
- `app/Models/*`: Eloquent models for most domains (schools, classrooms, students, quizzes, behavior, etc.)
- `app/Services/*`: domain services (e.g., schedule generation, quiz caching, weekly plan service)
- `routes/*`: split route files; `routes/web.php` includes multiple route modules
- `database/migrations/*`: schema for the system’s domains

### Vue (frontend)

- `resources/js/app.js`: Inertia/Vue app bootstrap, plugins, default layout, progress handling
- `resources/js/Pages/*`: Inertia pages (resolved by name via `resolvePageComponent`)
- `resources/js/Components/*`: reusable components (data tables, chat, quiz UI, question bank editors, etc.)
- `resources/js/Stores/*`: Pinia stores (user context, network, notifications, teacher, messages, “dp” modules)
- `resources/css/*`: app styles including RTL and layout-specific styles

## 4) How routing works (important mental model)

### Inertia pages (web routes)

- `routes/web.php` defines public + authenticated pages and then includes many route modules.
- Inertia pages are referenced by string names that map to Vue files under `resources/js/Pages`.

Examples:
- `/` returns `Inertia::render('LandingPage')`
- `/dashboard` returns `Inertia::render('Dashboard')` behind auth middleware

Source:
- [web.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/routes/web.php)

### JSON APIs

- `routes/api.php` contains many REST-like endpoints, commonly using `auth:sanctum` (and sometimes `web`) middleware.
- This project mixes classic Laravel resource routes (CRUD) with specialized endpoints (imports, analytics, session control, etc.).

Source:
- [api.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/routes/api.php)

## 5) Frontend bootstrapping (what happens in `resources/js/app.js`)

The SPA-like UI is created via `createInertiaApp(...)`. Key behaviors:

- Registers global components (`Head`, `Link`) and installs plugins (Pinia, ZiggyVue, vue-i18n, Quasar, Toastify).
- Sets a default layout (`AppLayoutDefault`) for pages that don’t specify their own layout.
- Adds navigation progress handling via NProgress using `router.on('start'|'finish'|'error')`.
- Supports RTL and multiple CSS bundles (`app.css`, `rtl.css`, `app-layout.css`, etc.).
- Enables Quasar Dark Mode based on localStorage or system preference.

Source:
- [app.js](file:///Users/ahmedmosaad/Herd/myclass2026-main/resources/js/app.js)

## 6) Shared server-side props and “user context” segmentation

The middleware [HandleInertiaRequests.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/app/Http/Middleware/HandleInertiaRequests.php) populates shared Inertia props:

- `csrf_token`
- `auth.user` (backward-compatible combined structure)
- `user_context` (segmented structure for offline-first caching)
- `context_meta` (per-segment expiry metadata set to 7 days)

Segments currently present (with 7-day expiry):

- `user_profile`: id, name, email, user_role
- `user_permissions`: roles
- `user_school`: schools/school list (teacher can have primary + extra school ids)
- `user_classroom`: teacher object and classroom collection (teacher) or classroom (student)
- `user_schedule`: expiry metadata exists; the shared props currently have schedule commented out, but the middleware includes a `getUserSchedule()` implementation

This design suggests a performance/offline goal: pages can rely on stable cached segments rather than re-sending a large monolithic `$page.props.auth.user` payload on every request.

Related API:
- [UserContextController.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/app/Http/Controllers/Api/UserContextController.php) exposes endpoints to fetch all context or individual segments (`profile`, `permissions`, `school`, `classroom`) with the same 7-day expiry metadata.

## 7) Offline mode + service worker (PWA-ish)

There’s an explicit “offline mode” toggle stored in localStorage:

- If `localStorage.offlineMode === 'true'`, the frontend lazy-loads offline modules and registers a service worker (`/sw.js`).
- `window.setOfflineMode(true|false)` is available for manual toggling.

Source:
- [app.js](file:///Users/ahmedmosaad/Herd/myclass2026-main/resources/js/app.js)

The service worker:

- Uses a cache named `education-app-v1`
- Caches basic routes like `/` and `/offline-test`
- Handles navigation requests with online-first + cache fallback
- Handles `/api/*` requests with online-first + cache fallback and a special-case health check response when offline

Source:
- [sw.js](file:///Users/ahmedmosaad/Herd/myclass2026-main/public/sw.js)

## 8) Tools Switcher (feature toggles)

There is a localStorage-backed “Tools Switcher” used to enable/disable expensive subsystems like Firebase and background services.

- Storage key: `toolsSwitcher`
- Categories: `firebase` and `backgroundServices`
- It exposes a global helper `window.toolsSwitcher`

Source:
- [toolsSwitcher.js](file:///Users/ahmedmosaad/Herd/myclass2026-main/resources/js/utils/toolsSwitcher.js)
- GUI panel: `resources/js/Components/ToolsSwitcherPanel.vue` (not linked here, but present)

## 9) Major product modules (observed from routes/controllers/models)

This is not a complete product spec, but these are clearly implemented domains:

### School structure and academics

- Schools, sections, stages, grades, subjects, classrooms
- Academic years and semesters
- Curriculum management (curricula, topics, lessons, lesson plans)

Example controller:
- [CurriculumController.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/app/Http/Controllers/Curriculum/CurriculumController.php)

### Scheduling

- Schedule copies, daily schedules/records, period details/timings
- Teacher/classroom/subject assignment mapping (`classroom_subject_teachers`)

### Weekly plans

- Weekly plans and sessions
- Service layer suggests heavier logic around weekly plan generation/validation

Example controller:
- [WeeklyPlanSessionController.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/app/Http/Controllers/WeeklyPlanSessionController.php)

### Behavior + attendance

- Student behavior sessions (`student_behaviors_mains`) and per-student behavior records
- Attendance updates are integrated with behavior session creation in some flows
- Dedicated “behavior incidents” system exists with extensive documentation and a model

Examples:
- [StudentBehaviorsMainController.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/app/Http/Controllers/StudentBehaviorsMainController.php)
- [BehaviorIncident.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/app/Models/BehaviorIncident.php)

### Quiz system + question bank

- Question types, questions, options, quizzes
- Quiz attempts + answers + performance indexes
- Live quiz sessions (teacher control page + student join page)

Example:
- [QuizSessionController.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/app/Http/Controllers/QuizSessionController.php)

### Chat / conversations / notifications

- Real-time-ish chat and conversation pages exist (Inertia pages + controllers)
- Web push subscription endpoints exist (`/push/subscribe`, `/push/unsubscribe`)
- Frontend includes `PushNotificationSubscribe.vue` and notification listeners

Source snippets:
- [web.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/routes/web.php)
- [PushNotificationSubscribe.vue](file:///Users/ahmedmosaad/Herd/myclass2026-main/resources/js/Components/PushNotificationSubscribe.vue)

### Course management

- Courses with levels/sections/lessons, teacher assignments, student imports

Example:
- [CourseStructureController.php](file:///Users/ahmedmosaad/Herd/myclass2026-main/app/Http/Controllers/CourseManagement/CourseStructureController.php)

## 10) Database and persistence (what stands out)

The migrations show a large schema spanning:

- Core school entities (schools, grades, subjects, classrooms, teachers, students)
- Scheduling and period tables
- Curriculum tables
- Behavior and incident tracking
- Quiz system tables
- Weekly plans + sessions
- Notifications + push subscriptions
- Roles/permissions tables (Spatie)

Schema entry point:
- `database/migrations/*` (large set)

## 11) Runtime configuration and environment

The `.env.example` indicates:

- Default DB is MySQL (`DB_CONNECTION=mysql`)
- Sessions, cache, and queue are database-backed by default
- Optional AI keys:
  - `DEEPSEEK_API_KEY`
  - `GEMINI_API_KEY`
- Firebase credentials path placeholder

Source:
- [.env.example](file:///Users/ahmedmosaad/Herd/myclass2026-main/.env.example)

## 12) How to run (local dev)

Typical Laravel + Vite setup:

1) Install dependencies

```bash
composer install
npm install
```

2) Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

3) Configure database in `.env`, then:

```bash
php artisan migrate
```

4) Start development

The repo defines a combined dev script in Composer that runs Laravel + queue + logs + Vite:

```bash
composer run dev
```

Or separately:

```bash
php artisan serve
npm run dev
```

Frontend tests:

```bash
npm test
```

Frontend production build:

```bash
npm run build
```

Sources:
- [composer.json](file:///Users/ahmedmosaad/Herd/myclass2026-main/composer.json)
- [package.json](file:///Users/ahmedmosaad/Herd/myclass2026-main/package.json)

## 13) Separate “MCP Server” folder (Node/Express)

There is a separate Node.js project under `MCP Server/`:

- Express server on port `3000`
- `GET /` health check
- `POST /get-time` returns a JSON payload with current time

Sources:
- [MCP Server/package.json](file:///Users/ahmedmosaad/Herd/myclass2026-main/MCP%20Server/package.json)
- [server.js](file:///Users/ahmedmosaad/Herd/myclass2026-main/MCP%20Server/server.js)
