# Classroom Records v1 — Detailed Tasks List

> **Legend:** `[ ]` todo · `[/]` in-progress · `[x]` done · `[!]` blocked/question needed
> **Status key per task:** ✅ Good as-is · ⚠️ Needs refinement · 🔒 Decision locked
>
> **Decisions locked (2026-03-15):**
>
> - `week_number` = ISO standard calendar week of the date (not academic). Academic label is free/custom and can change anytime.
> - Absent → present restore: **reset all other scores to default (5)**. No need to cache pre-absent values.
> - Overall Points default filter: **subject + semester**. All filter options available.
> - `wallet_balance` (reward_sys): **deferred to v1.1+**. Phase 4.3 is display stub only.
> - Roster source: **`students.classroom_id`** column — no separate junction table needed.
> - Admin access: **yes — read-only view + reports access**.

---

## 🎉 Phase 0 Status: COMPLETE (2026-03-15)

**Summary:**
Phase 0 successfully completed all wiring and context setup for the Classroom Records feature.

**Deliverables:**
- ✅ Routes configured for both Teacher and Admin access levels
- ✅ Inertia page entry created with proper prop passing
- ✅ SessionContextBar component built with dual-mode support
- ✅ Deep link support from teacher schedule implemented
- ✅ Read-only mode logic established for admin users

**Next Up:** Phase 1 — Backend (Fast Session Load)

---

## 🎉 Phase 1 Status: COMPLETE (2026-03-15) — PRODUCTION READY

**Summary:**
Phase 1 backend implementation is complete with all critical security issues resolved and production-ready.

**Deliverables:**
- ✅ Database schema (4 migrations with constraints & indexes)
- ✅ Eloquent models (4 models with relationships)
- ✅ PeriodCodeGenerator helper (ISO week calculation)
- ✅ API endpoints (init-session, batch update)
- ✅ Authorization & security fixes implemented
- ✅ Idempotency & data integrity ensured
- ✅ Absent lock behavior fully implemented
- ✅ Default category mappings seeder

**Critical Fixes Applied:**
- ✅ Fixed total_score formula (includes attendance = 20 points)
- ✅ Fixed teacher ID spoofing vulnerability
- ✅ Fixed admin write access (read-only enforcement)
- ✅ Fixed init-session idempotency (no overwrite)
- ✅ Fixed absent lock reversal (change away from absent)
- ✅ Fixed batch authorization scoping

**Documentation:**
- See [PHASE_1_COMPLETE.md](PHASE_1_COMPLETE.md) for full details

**Next Up:** Phase 2 — Frontend (Cards + Fast Input)

---

## ✅ Phase 0 — Wiring & Context [COMPLETE]

**Completed:** 2026-03-15

### ✅ 0.1 — Add Route & Inertia Entry [COMPLETE]

> **Decision locked:** Admin also gets access (read-only view + reports). Two route groups needed.

- [x]  Define named Laravel route `cr.index` under `/cr/classroom-records`
- [x]  Register in **two** route groups:
  - Teacher middleware group: full access (tap + save)
  - Admin middleware group: read-only access (view + reports, no batch save)
- [x]  Create the Inertia controller method returning initial props:
  - `classrooms[]`, `subjects[]`, `currentUser`, `role ('teacher'|'admin')`, `apiBase`
- [x]  Add the Vue page file `ClassroomRecordsPage.vue` as the Inertia page entry
- [x]  Pass `readOnly: true` prop when `role === 'admin'` so UI disables tap targets

---

### ✅ 0.2 — SessionContextBar Component [COMPLETE]

> **Good:** Designed well with `mode: interactive | readonly` — reusable across schedule and standalone.
> **Recommendation:** Separate the component early so it can be tested in isolation before the full page.

- [x]  Create `SessionContextBar.vue` with props:
  - `modelValue: { classroom_id, subject_id, date, day_number, period_number, period_code }`
  - `mode: 'interactive' | 'readonly'`
  - `source: 'standalone' | 'teacher_schedule'`
  - `options: { classrooms?, subjects? }`
- [x]  In `interactive` mode: render dropdowns/selects for classroom, subject, date, day, period
- [x]  In `readonly` mode: render as display-only badges/chips (no inputs)
- [x]  Auto-derive `period_code` from selected values when all fields are filled
- [x]  Emit `update:modelValue` when any field changes
- [x]  Emit `context-ready` event when all required fields are valid (triggers init-session)

---

### ✅ 0.3 — Teacher Schedule Deep Link Support [COMPLETE]

> **Good:** Query params approach is correct. Avoids storing state in localStorage which is fragile.
> **Recommendation:** Define the exact query param names now to avoid mismatch between schedule and CR pages.

- [x]  Define accepted query params on the CR page route:
  `?classroom_id=&subject_id=&teacher_id=&date=&day_number=&period_number=&period_code=`
- [x]  In `ClassroomRecordsPage.vue`: read Inertia props OR fallback to `$page.props` query values
- [x]  Pass resolved context to `SessionContextBar` with `mode="readonly"`
- [x]  Add a "Change Session" button visible only in standalone mode (not when coming from schedule)

---

## ✅ Phase 1 — Backend (Fast Session Load) [COMPLETE]

**Completed:** 2026-03-15
**Status:** PRODUCTION READY

### ✅ 1.1 — Migrations [COMPLETE]

> **Good:** Clean `cr_*` prefix with isolated tables avoids polluting existing schema.
> **Recommendation:** Run migrations in order; use foreign keys with `constrained()` for integrity.

- [x]  Create migration: `create_cr_sessions_table`
- [x]  Create migration: `create_cr_student_periods_table`
- [x]  Create migration: `create_cr_category_mappings_table`
- [x]  Create migration: `create_cr_scores_table`

---

### ✅ 1.2 — Seed Default Category Mappings [COMPLETE]

> **Good:** Seeding 3 mappings (book_participation, homework, behavior) gives a working baseline.
> **Note:** Seed must be school-scoped — avoid seeding globally or for all schools blindly.

- [x]  Create `CrCategoryMappingsSeeder`
- [x]  Seed for dev/testing school only (or use a config-driven list of school IDs)
- [x]  Seed entries:
  - `book_participation` (label: "Book & Participation", max: 5, default: 5)
  - `homework` (label: "Homework", max: 5, default: 5)
  - `behavior` (label: "Behavior", max: 5, default: 5)
- [x]  Use `updateOrCreate` so seeder is re-runnable without duplicates

---

### ✅ 1.3 — period_code Generator [COMPLETE]

> **Decision locked:** `week_number` = ISO standard calendar week of the `date` (e.g. `date-fns/getISOWeek`). Academic week labels are cosmetic and managed separately — they do not affect `period_code` storage.
> **Recommendation:** Extract into a shared helper so backend and frontend always agree.

- [x]  Create `PeriodCodeGenerator` helper (PHP) + matching JS util:
  - Input: `year_id`, `semester_number`, `date` (week_number auto-derived via ISO week), `day_number`, `period_number`
  - Output: `Y{year_id}-S{s}-W{iso_week}-D{d}-P{p}` e.g. `Y2026-S1-W12-D2-P3`
  - PHP: use `Carbon::parse($date)->isoWeek()`
  - JS: use `import { getISOWeek } from 'date-fns'`
- [x]  Add unit test for the PHP helper (at least 3 date cases across year boundaries)
- [x]  Use the same JS util inside `SessionContextBar` for client-side `period_code` preview
- [x]  Document: academic calendar labels ("Week 1", "Week 2" etc.) are display-only and CAN be remapped at will — `period_code` always uses ISO week number for storage integrity

---

### ✅ 1.4 — `/api/cr/init-session` Endpoint [COMPLETE]

> **Good:** Single-request session load is the right performance choice.
> **Recommendation:** Use `DB::transaction()` for the upsert block to avoid partial state if any upsert fails.

- [x]  Create `CrSessionController@initSession`
- [x]  Validate input: `classroom_id`, `subject_id`, `teacher_id`, `date`, `period_code`, `day_number`, `period_number` (all required)
- [x]  Upsert `cr_sessions` (create or return existing)
- [x]  Fetch classroom roster: **`Student::where('classroom_id', $classroom_id)->where('active', true)->get()`** (no junction table — students.classroom_id is the source)
- [x]  Upsert `cr_student_periods` for each student (insert defaults if not existing)
- [x]  Upsert `cr_scores` for each (student_period × active_mapping) pair (insert defaults if not existing)
- [x]  Return: `{ session, students: [{ ...student, period: {...}, scores: [...] }] }`
- [x]  Wrap entire upsert block in `DB::transaction()`
- [x]  Add authorization:
  - Teacher: must be assigned to this classroom+subject (using authenticated user's teacher ID)
  - Admin: allowed read-only access (can call init-session to view, cannot save via batch)
- [x]  **Security fix**: Use authenticated user's teacher record, not request input (prevents spoofing)
- [x]  **Idempotency fix**: Don't overwrite existing student period records
- [x]  **Total score fix**: Calculate as `attendance_score + sum(category_scores)`

---

### ✅ 1.5 — `/api/cr/batch` Endpoint [COMPLETE]

> **Good:** Debounced batch approach is correct for high-frequency UI interactions (tap-cycle).
> **Recommendation:** Validate each item individually; return partial success list so UI knows what failed.

- [x]  Create `CrSessionController@batchUpdate`
- [x]  Accept: `{ updates: [{ student_period_id, attendance_status?, attendance_score?, attendance_note?, total_score?, scores?: [{ mapping_id, numeric_value }] }] }`
- [x]  For each update item:
  - [x]  Authorize: student_period belongs to the teacher's session
  - [x]  Validate: `attendance_score` 0..5, `total_score` 0..20, `numeric_value` 0..max\_value from mapping
  - [x]  Apply absent-lock server-side (if `attendance_status` = absent → force all scores to 0)
  - [x]  Update `cr_student_periods`
  - [x]  Update `cr_scores` for each mapping in `scores[]`
- [x]  Return: `{ updated: [...ids], errors: [...] }`
- [x]  **Security fix**: Enforce school/year scoping and teacher assignment verification
- [x]  **Admin fix**: Block admins from writing (read-only enforcement)
- [x]  **Absent lock fix**: Allow changing away from absent (resets to defaults)

---

### ✅ 1.6 — Attendance=Absent Server-Side Lock [COMPLETE]

> **Good:** Enforcing this server-side is the right call — client-side only is insecure.
> **Recommendation:** Also set `locked = true` in `cr_student_periods` when absent, so future reads know it's locked.

- [x]  In `batchUpdate`: if `attendance_status === 'absent'`:
  - Set `attendance_score = 0`
  - Set all `cr_scores.numeric_value = 0` for this student_period
  - Set `total_score = 0`
  - Set `locked = true`
- [x]  In `initSession`: if existing record has `locked = true`, skip default overwrite
- [x]  **Fix**: Allow changing away from absent → unlock and reset categories to defaults

---

### ✅ 1.7 — Unique Constraints (DB Level) [COMPLETE]

> **Good:** Already defined in schema. Just needs to be verified in migrations and used for upserts.

- [x]  Confirm `UNIQUE` indexes exist as per cr_recommended_schema.md
- [x]  Use `updateOrCreate` or `upsert()` instead of `create()` in all init-session logic to prevent race conditions
- [x]  Verify migrations run successfully (batch 36)
- [x]  Run seeder to populate default categories
- [x]  Register API routes in main routes file
- [x]  Create JS utility for period_code generation (frontend counterpart)

---

## Phase 2 — Frontend (Cards + Fast Input) [/]

**Components Created:** 2026-03-15  
**Status:** 🔴 NOT READY (blocking integration issues)

> **Reality check (code):** Components exist, but current wiring will not behave correctly until the blockers below are fixed.

### 2.0 — Blockers (Must Fix Before UAT) [!]

- [!] API calls are using Inertia router for JSON endpoints (`/api/*`) — responses are JSON, not Inertia props
- [!] Batch save uses `POST /api/cr/batch` but backend route is `PATCH /api/cr/batch`
- [!] Standalone mode: `teacher_id` is not guaranteed but API requires it
- [!] period_code generation is missing required inputs in the context form (`year_id`, `semester`, `day_number`)
- [!] StudentCard does not apply optimistic UI updates (tap does not update visible score reliably)
- [!] mapping_id source is inconsistent (category definitions don’t carry mapping ids; should use scoreRecord.mapping_id)
- [!] Attendance “late” state is documented but not implemented in UI (toggle only)

---

### 2.1 — ClassroomRecordsPage Shell [/]

- [/] Create `ClassroomRecordsPage.vue` layout:
  - Top bar: `SessionContextBar` (readonly or interactive depending on source)
  - Body: Student grid (only rendered after `context-ready` is emitted)
  - Loading skeleton while waiting for init-session response
- [/] Handle error state (failed init-session) with retry button
- [/] Save status indicator (idle, saving, success)
- [/] Manual save button for force-save
- [/] Admin read-only mode support
- [!] Replace Inertia router requests with JSON client (axios/fetch) for `/api/cr/*`
- [!] Ensure init-session reads JSON response and sets `sessionData` correctly

---

### 2.2 — StudentCard Component [/]

> **Good:** Card layout direction is correct.
> **Gap:** Needs optimistic updates and correct mapping id handling.

- [/] Render student name + avatar (or initials fallback)
- [/] Render 3 category tap targets (Book, Homework, Behavior) + Attendance toggle
- [/] Color-coded feedback (green=5, yellow=3, red=0)
- [/] Responsive grid layout (mobile-first)
- [!] Implement optimistic UI update (tap immediately updates visible values)
- [!] Emit updates using `scoreRecord.mapping_id` (avoid missing mapping ids)

---

### 2.3 — Tap-Cycle Logic + Absent Lock [/]

> **Decision locked:** Absent → present = **reset all other scores to default (5)**.

- [/] Implement tap-cycle: `5 → 3 → 0 → 5` for each category value
- [!] Attendance UI must support `late` (not just present/absent), or the plan must be updated
- [/] When Attendance becomes absent:
  - Set other categories to 0 in UI (optimistic)
  - Disable tap targets for other categories
  - Server-side: zero out all scores, set locked=true
- [/] When Attendance changes away from absent:
  - Reset other categories to default (5) in UI (optimistic)
  - Re-enable tap targets
  - Server-side: unlock and reset scores to defaults

---

### 2.4 — Local Dirty Tracking + Debounce Batch Save [/]

- [/] Track dirty updates in-memory and debounce saves (1.5s)
- [/] Page unload protection (warn when unsaved)
- [/] Manual save button
- [!] Use correct HTTP method + JSON client (`PATCH /api/cr/batch`)
- [!] Parse JSON response and clear only successfully saved ids (`updated[]`)
- [/] Partial success handling (`updated[]`, `errors[]`)

---

## Phase 3 — Reporting Support

### 3.1 — DB Indexes for Reports ⚠️

> **Recommendation:** Add these in the migration files directly rather than as a separate migration later.

- [ ]  Add index on `cr_student_periods(school_id, year_id, date)` — for date-range queries
- [ ]  Add index on `cr_student_periods(student_id, school_id)` — for per-student dashboards
- [ ]  Add index on `cr_scores(mapping_id)` — for per-category report aggregation

---

### 3.2 — `/api/cr/stats` Endpoint 🔒

> **Decision locked:** Default filter = **subject + current semester**. All filter combinations available.

- [ ]  Create `CrReportsController@stats`
- [ ]  Accept filters (all optional, defaults noted):
  - `subject_id` (default: current session subject)
  - `classroom_id` (default: current session classroom)
  - `year_id` (default: current academic year)
  - `semester` (default: current semester)
  - `date_from`, `date_to` (optional override — overrides semester default if both provided)
  - `scope`: `day | week | month | semester | all_time` (default: `semester`)
  - `student_id` (optional — for single-student detail view)
- [ ]  Query: `SUM(total_score)`, `COUNT(sessions)`, avg per category, `COUNT(absent)`, `COUNT(late)`
- [ ]  Return: per-student summary array with breakdown per category
- [ ]  Auth: teacher sees only their assigned students; admin sees all

---

### 3.3 — attendance_status as Report Source of Truth ✅

> **Already decided in plan — just needs to be enforced.**

- [ ]  Ensure all attendance reports use `attendance_status` column (not `attendance_score`)
- [ ]  Add comment in `CrStudentPeriod` model explaining this distinction

---

## Phase 4 — Overall Points UI

### 4.1 — Card Panel Mode Selector ⚠️

> **Recommendation:** Implement this as a toggle on the grid level (one toggle switches ALL cards), not card-by-card.

- [ ]  Add `viewMode: 'session_total' | 'overall_points'` state on `ClassroomRecordsGrid`
- [ ]  Toggle button in the grid header (not inside each card)
- [ ]  In `session_total` mode: show `total_score / 20` per session
- [ ]  In `overall_points` mode: show cumulative total from stats API

> ❓ **Open question:** Does "overall points" show all-time lifetime total, or filtered by current subject/semester? This determines what query parameters to pass to `/api/cr/stats`.

---

### 4.2 — Overall Points Filters ⚠️

- [ ]  Add filter controls (scope + date range) — visible only in `overall_points` mode
- [ ]  Scope options: `this_week | this_month | this_semester | all_time`
- [ ]  Subject filter: optional (all subjects or current subject only)
- [ ]  Trigger re-fetch of stats when filters change (debounced)

---

### 4.3 — Combined Power Score (Visual Only — Stub for v1) 🔒

> **Decision locked:** `wallet_balance` integration with `reward_sys` is **deferred to v1.1+**. v1 shows lifetime total only.

- [ ]  Display: `lifetime_total` from `SUM(cr_student_periods.total_score)` on each card
- [ ]  Show `wallet_balance` as `—` or hidden with a `coming soon` badge until reward_sys integration is built
- [ ]  Add tooltip/label: "Lifetime Score (Wallet coming soon)"
- [ ]  Design the component to accept an optional `walletBalance` prop — when `null`, show stub UI
- [ ]  No reward_sys API calls in v1 — zero coupling

---

## Phase 5 — Flex Admin (v1.1+)

### 5.1 — Admin Mappings UI ⚠️

> **This is v1.1+ — do not block v1 on this.**

- [ ]  Admin page to list `cr_category_mappings` for a school
- [ ]  CRUD operations: add/edit/deactivate categories
- [ ]  Reorder via drag-and-drop (sort_order)

---

### 5.2 — Schema Endpoint ✅

- [ ]  `GET /api/cr/schema` → returns active mappings for the school (used by UI to build card layout dynamically)
- [ ]  Cache response (mappings change rarely)

---

## Definition of Done (v1) ✅

These are the acceptance criteria — do NOT mark v1 complete until ALL of these pass:

- [ ]  Teacher opens session directly from schedule link without selecting context manually
- [ ]  Init-session loads roster + records in one request (< 500ms target)
- [ ]  Tap-cycle works: 5 → 3 → 0 for each category
- [ ]  Absent lock: marking absent zeroes and locks all other categories instantly
- [ ]  Batch save fires after 1.5s debounce; no double-save
- [ ]  Server rejects invalid scores (out-of-range values)
- [ ]  Server enforces absent-lock independently of client state
- [ ]  Unique constraint prevents duplicate student-period records

---

## 🔒 Clarification Questions — All Resolved (2026-03-15)


| #  | Question                             | Decision                                                                                                                                                                                                         |
| -- | ------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Q1 | How is`week_number` derived?         | **ISO standard calendar week of the date** (`Carbon::isoWeek()` / `date-fns getISOWeek`). Academic labels (Week 1, Week 2…) are cosmetic and can be renamed or reordered anytime without affecting stored data. |
| Q2 | Absent → present: restore or reset? | **Reset all categories to default (5).** No pre-absent value cache needed.                                                                                                                                       |
| Q3 | "Overall Points" scope?              | **Default = subject + current semester.** Full filter UI available (week / month / semester / all-time).                                                                                                         |
| Q4 | `wallet_balance` source?             | **Deferred to v1.1+.** v1 shows lifetime total only, wallet shows as stub "coming soon".                                                                                                                         |
| Q5 | Roster source?                       | **`students.classroom_id`** column — no separate junction table exists.                                                                                                                                         |
| Q6 | Admin access?                        | **Yes — read-only view + full reports access.** Two separate route groups needed.                                                                                                                               |

---

## Phase 6 — ✈️ Fly Mode (Personal Teacher Workspace)

> ### What Fly Mode IS vs. what it is NOT
>
>
> |                       | Fly Mode                                         | Main System              |
> | --------------------- | ------------------------------------------------ | ------------------------ |
> | **Who owns the data** | The teacher personally                           | The school/institution   |
> | **Backend required**  | ❌ No — works fully offline                     | ✅ Yes                   |
> | **Reports**           | ❌ No complex reports                            | ✅ Full reports          |
> | **Student access**    | ✅ Simple virtual login (view points only)       | ✅ Full student portal   |
> | **Data storage**      | IndexedDB (browser) + optional JSON cloud backup | Database (cr_* tables)   |
> | **Admin role**        | Teacher IS the admin                             | School admin is separate |
> | **Complexity**        | Minimal, intentional                             | Full-featured            |
>
> **Key insight:** Fly Mode is NOT "offline version of the school system". It is a **personal scratch workspace** the teacher owns. A student in a school that doesn't use MyClass2026 can still check their score here.

> ### Relationship to Offline-First (Main App)
>
> **Offline-first** (future concern) = the main connected system working when internet drops temporarily (service workers, cache, sync queue).
> **Fly Mode** = a fully separate personal workspace that exists alongside the main system and doesn't merge into it unless the teacher chooses to export.

---

### 6.1 — Fly Mode Entry & Identity ⚠️

- [ ]  Add a "✈️ Start Fly Mode" button on the login page (no credentials needed)
- [ ]  On first launch: ask teacher to enter their name (stored locally as `fly_teacher_name`)
- [ ]  Generate a persistent `fly_teacher_id` (UUID, stored in IndexedDB, never changes)
- [ ]  Show "✈️ Fly Mode" badge in the top bar — always visible
- [ ]  Allow opening the main system separately in another tab (no conflict)

---

### 6.2 — Local Data Store (IndexedDB via Dexie.js) ⚠️

> **Recommendation:** [Dexie.js](https://dexie.org/) — clean, Promise-based IndexedDB wrapper. Lightweight and battle-tested.

- [ ]  Install `dexie`
- [ ]  Create local DB `FlyModeDB` (Dexie) with versioned schema (v1, v2…)
- [ ]  Define tables + indexes for offline-first queries:
  - `fly_years` — (`id`, `label`, `start_date`, `end_date`)
  - `fly_classrooms` — (`id`, `year_id`, `name`, `grade`, `section`)
  - `fly_students` — (`id`, `classroom_id`, `name`, `display_order`, `virtual_username`, `virtual_pin`)
  - `fly_sessions` — (`id`, `classroom_id`, `date`, `period_number`, `period_code`)
  - `fly_student_periods` — (`id`, `session_id`, `student_id`, `attendance_status`, `attendance_score`, `total_score`, `locked`)
  - `fly_scores` — (`id`, `student_period_id`, `mapping_key`, `numeric_value`)
  - `fly_category_mappings` — seeded on first run with defaults
- [ ]  All IDs: `crypto.randomUUID()` — consistent, no server needed
- [ ]  Seed 3 default mappings on first launch: `book_participation`, `homework`, `behavior`
- [ ]  Add transactional write helpers (single source of truth for all writes)
- [ ]  Add guarded open/upgrade handling (blocked/upgrade errors show a clear UI state)
- [ ]  Add snapshot import/export helpers (used by backup + export features)
- [ ]  Add a safe reset flow (delete DB + re-seed defaults)

---

### 6.3 — Teacher Setup Wizard (First Launch) ✅

> Simple 3-step flow, no forms complexity.

- [ ]  **Step 1 — Year:**

  - Enter year label + optionally start/end date
  - Creates `fly_years` record
- [ ]  **Step 2 — Classrooms:**

  - Enter classroom name (e.g. "Grade 4-A")
  - Add as many as needed — one per line ideally
  - Creates `fly_classrooms` records
- [ ]  **Step 3 — Students (Paste Mode):**

  - Large textarea — paste names one per line or comma-separated
  - Live preview: numbered list with generated username/pin shown inline
  - "Confirm & Save" → writes to `fly_students`
  - Auto-generates `virtual_username` (e.g. `A01`, `A02`) and `virtual_pin` (4-digit random number) per student
- [ ]  Wizard is re-enterable from a "Setup" panel anytime (add/edit/remove classrooms or students)

---

### 6.4 — Paste-to-Create Students ✅

> **Killer feature** — no clicking per student. Paste a class list → done in 10 seconds.

- [ ]  Create `StudentPasteInput.vue`:
  - Textarea for multi-line or comma-separated names
  - Live preview panel showing: `#` `Name` `Username` `PIN`
  - "Confirm & Add" → bulk-writes to `fly_students`
- [ ]  Edge cases:
  - Empty lines → skip silently
  - Duplicate names → warn inline but allow (two students can share a name)
  - Class limit: soft warn at 50 students
- [ ]  Partial paste: appending more names later is allowed (does not overwrite existing)

---

### 6.5 — Session & Scoring Flow ✅

> Identical tap-cycle UX to main system — zero relearning for the teacher.

- [ ]  Create `useFlyStore.js` composable (same API shape as the main `useCrApi`):
  - `initSession(context)` → upserts `fly_sessions` + `fly_student_periods` + `fly_scores` in Dexie
  - `batchUpdate(updates)` → writes immediately (no debounce needed — instant local write)
- [ ]  `ClassroomRecordsPage` checks `flyMode` flag → uses `useFlyStore` instead of API
- [ ]  All scoring logic (tap-cycle, absent-lock, total calculation) is **shared code** — no duplication
- [ ]  Context bar in Fly Mode: dropdowns pull from local Dexie store (no API calls)
- [ ]  No reports in Fly Mode — session history is view-only via the student's virtual login

---

### 6.6 — Student Virtual Access 🆕

> Students can check their own points with a simple virtual username + PIN.
> No email, no real account — just a code the teacher gives them.

- [ ]  **Student Login page** (Fly Mode only, no server auth):

  - Input: `username` (e.g. `A01`) + `PIN` (4-digit)
  - Lookup in local `fly_students` table — matched locally
  - On match: show student's personal score view
- [ ]  **Student Score View** (read-only):

  - Shows: name, total points per session (last 7 days by default)
  - Simple table or card list: `date | period | total_score`
  - No category breakdown visible (keep it simple)
  - No absent/late details visible — just the score
- [ ]  **Credential display for teacher:**

  - In student list, show credentials with a "Show PIN" reveal toggle
  - Option to print or copy a class credentials sheet
- [ ]  **Security note (in code comment & UI tooltip):**

  - "This is a simple access code, not a secure login. Do not use for sensitive data."
  - Virtual credentials are only valid while data is in this browser

---

### 6.7 — Cloud Backup (Simple JSON Sync) ⚠️

> **Goal:** Teacher never loses their personal data even if they change browsers or clear browser storage.
> **Design:** A single DB table stores the teacher's entire Fly Mode as a JSON blob. No complex sync — just snapshot in/out.

- [ ]  Create migration: `fly_teacher_backups` table:

  ```
  id, teacher_id (nullable FK), fly_teacher_id (UUID string),
  snapshot_json (JSON column), snapshot_at (timestamp), version (int)
  ```
- [ ]  **Backup flow** (teacher-initiated or auto):

  - Serialize entire Dexie DB → one JSON object
  - `POST /api/fly/backup` with `{ fly_teacher_id, snapshot_json }`
  - Server upserts `fly_teacher_backups` by `fly_teacher_id`
  - This requires the teacher to be **logged in** for backup — if not logged in, backup is skipped (offline-only)
- [ ]  **Restore flow:**

  - Teacher logs into the main system from a new browser
  - Sees "Restore Fly Mode" option → `GET /api/fly/backup?fly_teacher_id=`
  - Downloads snapshot JSON → reimports into local Dexie DB
- [ ]  **Auto-backup trigger:**

  - After every 5 sessions: prompt "Backup to cloud?" (requires login)
  - Manual backup button always available in Settings
- [ ]  **Constraints:**

  - One backup slot per `fly_teacher_id` (overwrites previous — no versioning in v1)
  - Server stores only JSON, does NOT process or query the data
  - No reports, no joins, no complexity on the server side

---

### 6.8 — Export (JSON + Excel) ✅

- [ ]  **JSON Export:** Full snapshot — same structure as the cloud backup (`export_version`, `year`, `classrooms`, `students`, `sessions`, `student_periods`, `scores`)
- [ ]  **Excel Export:** via `SheetJS (xlsx)` — no server needed:
  - Sheet 1: Students (name, username, PIN, total all-time)
  - Sheet 2: Session Scores (rows = students, columns = session dates, values = total_score)
- [ ]  Export UI: classroom selector + date range + format toggle (JSON / Excel / Both) + Download button

---

### 6.9 — Persistence & Safety ⚠️

- [ ]  Data survives page refresh (IndexedDB is persistent — not sessionStorage)
- [ ]  Warning banner: `"✈️ Fly Mode data lives in this browser. Backup or export regularly."`
- [ ]  "Clear All Fly Data" button in Settings — requires typing `DELETE` to confirm
- [ ]  Auto-backup reminder every 5 sessions (if logged in), auto-export reminder if not logged in

---

## Phase 7 — 📶 Offline-First (Main Connected System)

> **What this means:** The main system (teacher logged in, using the school database) must keep working when internet drops — mid-session.
> The teacher should never lose a tap. Saves queue up silently and sync when the connection returns.
>
> **This is different from Fly Mode** — the data still belongs to the school, the server is the source of truth. Offline-first just means the UI never blocks on network.

---

### 7.1 — Service Worker (Vite PWA / Workbox) ⚠️

> **Recommendation:** Use `vite-plugin-pwa` with `Workbox` — integrates cleanly with Vite/Laravel Inertia.

- [ ]  Install `vite-plugin-pwa` + configure in `vite.config.js`
- [ ]  Register service worker scoped to the CR feature routes only (or globally, decision needed)
- [ ]  Configure **cache strategies** per resource type:
  - `StaleWhileRevalidate` — Inertia page shell, static assets, fonts
  - `CacheFirst` — `GET /api/cr/schema` (category mappings — changes rarely)
  - `NetworkFirst` with local fallback — `POST /api/cr/init-session` (tries server, falls back to cached roster)
- [ ]  Cache the last-loaded session roster so the page renders even offline
- [ ]  Show install prompt (PWA "Add to Home Screen") for teachers using mobile

---

### 7.2 — Offline Write Queue (Pending Saves) ✅

> **Core mechanism:** When the batch save API call fails (network offline), the update goes into a local queue instead of being lost. When connection returns, the queue drains automatically.

- [ ]  Create `useOfflineQueue.js` composable:
  - Backed by a small IndexedDB table: `cr_pending_writes`
  - Columns: `id`, `endpoint`, `payload`, `queued_at`, `retry_count`
- [ ]  In `useDirtyBatch.js`: wrap `PATCH /api/cr/batch` in a try/catch:
  - **Online:** send normally → clear dirty map on success
  - **Offline / timeout:** push to `cr_pending_writes` queue → show "queued" indicator
- [ ]  Create `useQueueDrainer.js`:
  - Listens to `navigator.onLine` change event → `window.addEventListener('online', drain)`
  - `drain()`: reads all `cr_pending_writes`, replays each `PATCH /api/cr/batch` in order, removes on success
  - Uses **Background Sync API** (`ServiceWorkerRegistration.sync.register('cr-batch')`) if available (Chrome/Android — more reliable than `online` event)

---

### 7.3 — Connection Status UI ✅

> Teacher must always know their save state — especially during a class session.

- [ ]  Add a `ConnectionStatus.vue` component in the top bar:
  - 🟢 **Online — Saved** (all synced)
  - 🟡 **Online — Saving…** (batch in flight)
  - 🟠 **Offline — X saves queued** (pending writes waiting)
  - 🔴 **Offline** (no pending writes, just disconnected)
- [ ]  Animate transition between states (subtle, not distracting)
- [ ]  Show a dismissible toast when connection is restored: "✅ Back online — X saves synced"

---

### 7.4 — Init-Session Offline Fallback ⚠️

> If a teacher opens a session without internet, the page should still load the last known roster.

- [ ]  Cache the last successful `POST /api/cr/init-session` response in IndexedDB keyed by `period_code`
- [ ]  On `initSession` failure (network error):
  - Load cached response → render UI normally
  - Show banner: "📶 Offline — showing last cached roster. Scores will sync when you reconnect."
- [ ]  Scores entered offline go into the pending write queue (7.2)
- [ ]  On reconnect: re-call `initSession` to check for any server-side changes, then drain queue

---

### 7.5 — Conflict Resolution (Simple) ⚠️

> When offline writes are queued and then replayed, there can be conflicts (e.g. another teacher updated the same student from a different device).

- [ ]  Strategy for v1: **"Last write wins"** — server timestamp decides
  - Simple, no UI required, acceptable for solo-teacher use
- [ ]  Log conflicts to console (dev) or to a hidden error log (prod) — no user-facing conflict UI in v1
- [ ]  Add a future-facing note: conflict UI (show diff, let teacher choose) is a v2 concern

---

### 7.6 — Offline Testing ⚠️

- [ ]  Test offline mode using Chrome DevTools → Network → Offline
- [ ]  Scenario 1: Open session online, go offline mid-session, continue tapping → reconnect → verify saves synced
- [ ]  Scenario 2: Open session while already offline → fallback roster loads → reconnect → verify
- [ ]  Scenario 3: Queue overflow (> 50 pending writes) → verify queue drains in order

---

## 📊 Phase Priority Summary


| Phase                       | Priority  | Blocks v1? | Estimated Complexity | Status |
| --------------------------- | --------- | ---------- | -------------------- | ------ |
| Phase 0 — Wiring           | 🔴 Must   | Yes        | Low                  | ✅ COMPLETE |
| Phase 1 — Backend          | 🔴 Must   | Yes        | High                 | ✅ COMPLETE |
| Phase 2 — Frontend         | 🔴 Must   | Yes        | High                 | ⏳ PENDING |
| Phase 3 — Reporting        | 🟡 Should | No         | Medium               | ⏳ PENDING |
| Phase 4 — Overall Points   | 🟡 Should | No         | Medium               | ⏳ PENDING |
| Phase 5 — Flex Admin       | 🟢 Nice   | No (v1.1+) | Medium               | ⏳ PENDING |
| Phase 6 — ✈️ Fly Mode    | 🟢 Nice   | No (v1.1+) | High                 | ⏳ PENDING |
| Phase 7 — 📶 Offline-First | 🟡 Should | No (v1.1+) | High                 | ⏳ PENDING |
