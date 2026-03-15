
# Classroom Records v1 — Phase 1 Readiness Checklist (Backend)

This checklist is meant to answer one question: **is Phase 1 ready to build Phase 2 UI on top of it without surprises?**

## ✅ Must-Pass Checks (Blocking)

### A) Route Registration (API is reachable)
- [ ] `POST /api/cr/init-session` is registered in the app’s route list (not just defined in a file)
- [ ] `PATCH /api/cr/batch` is registered in the app’s route list
- [ ] Route middleware matches the intended audience: authenticated only (`auth:sanctum`, `web`)
- [ ] There is exactly one canonical API base prefix: `/api/cr/*` (no duplicated legacy prefixes)

**Evidence pointers**
- Routes file: [cr-api.php](file:///c:/my_project/myclass2026-main/routes/modules/Academics/cr-api.php)
- Main API routes file (check inclusion): [api.php](file:///c:/my_project/myclass2026-main/routes/api.php)

### B) Database Schema (Tables + constraints + indexes)
- [ ] Tables exist: `cr_sessions`, `cr_student_periods`, `cr_category_mappings`, `cr_scores`
- [ ] Uniqueness is enforced at DB level:
  - [ ] `cr_sessions`: unique `(school_id, year_id, classroom_id, subject_id, teacher_id, date, period_code)`
  - [ ] `cr_student_periods`: unique `(school_id, year_id, date, period_code, student_id)`
  - [ ] `cr_scores`: unique `(student_period_id, mapping_id)`
- [ ] Indexes exist for report performance (at minimum):
  - [ ] `cr_student_periods(school_id, year_id, date)`
  - [ ] `cr_student_periods(student_id, school_id)`
  - [ ] `cr_scores(mapping_id)`

**Evidence pointers**
- [create_cr_sessions_table.php](file:///c:/my_project/myclass2026-main/database/migrations/2026_03_15_000001_create_cr_sessions_table.php)
- [create_cr_student_periods_table.php](file:///c:/my_project/myclass2026-main/database/migrations/2026_03_15_000002_create_cr_student_periods_table.php)
- [create_cr_category_mappings_table.php](file:///c:/my_project/myclass2026-main/database/migrations/2026_03_15_000003_create_cr_category_mappings_table.php)
- [create_cr_scores_table.php](file:///c:/my_project/myclass2026-main/database/migrations/2026_03_15_000004_create_cr_scores_table.php)

### C) Seed Data (v1 categories exist for the active school)
- [ ] School-scoped default mappings exist for the active school:
  - [ ] `book_participation`, `homework`, `behavior`
- [ ] Mapping defaults match the v1 plan (max=5, default=5)
- [ ] Seeder is actually executed in the environment (either called from `DatabaseSeeder` or run manually)

**Evidence pointers**
- Seeder: [CrCategoryMappingsSeeder.php](file:///c:/my_project/myclass2026-main/database/seeders/CrCategoryMappingsSeeder.php)
- Model: [CrCategoryMapping.php](file:///c:/my_project/myclass2026-main/app/Models/CrCategoryMapping.php)

### D) period_code Canonicalization (backend and frontend agree)
- [ ] Backend generator exists and matches format: `Y{year_id}-S{semester}-W{iso_week}-D{day}-P{period}`
- [ ] `isoWeek()` is used on the backend (not academic week)
- [ ] Parsing rejects invalid format safely

**Evidence pointers**
- [PeriodCodeGenerator.php](file:///c:/my_project/myclass2026-main/app/Helpers/PeriodCodeGenerator.php)

### E) Scoring Formula Flexibility (avoid hard-coded totals)
- [ ] Total score is computed from an extensible source of truth (not hard-coded per column)
- [ ] Recommended v1 formula (matches 20-point model):
  - [ ] `total_score = attendance_score + sum(numeric_value for all active mappings included in total)`
- [ ] Recommended v1.1+ upgrade path (maximum flexibility):
  - [ ] Treat Attendance as a mapping key (`attendance`) and compute `total_score = sum(all active included mappings)`
  - [ ] Keep `attendance_status` in `cr_student_periods` as the reporting source of truth
- [ ] If mappings can be disabled, there is a clear rule for totals when a mapping is inactive (excluded from total)

## ✅ Endpoint Readiness Checks

### 1.4 — init-session correctness (single-request fast load)
- [ ] Input validation matches the contract (context fields required)
- [ ] **Auth is safe**:
  - [ ] Teacher cannot spoof `teacher_id` to access other teachers’ sessions
  - [ ] Teacher assignment check is enforced (classroom+subject+teacher+school)
  - [ ] Admin/school-admin can load sessions read-only
- [ ] **Idempotency**:
  - [ ] Repeated calls do not overwrite existing saved records unintentionally
  - [ ] Locked/absent records are preserved on re-init (no reset-to-defaults on existing data)
- [ ] **Data correctness**:
  - [ ] `total_score` matches the v1 definition (Attendance + active categories = 0..20)
  - [ ] `total_score` is computed server-side using the formula in section E
  - [ ] Defaults are created only when missing (no “default overwrite”)
- [ ] Response payload includes enough data for Phase 2 UI:
  - [ ] session context
  - [ ] roster (students)
  - [ ] `student_period_id` per student
  - [ ] category scores with labels + max

**Evidence pointers**
- Controller: [CrSessionController.php](file:///c:/my_project/myclass2026-main/app/Http/Controllers/Api/Cr/CrSessionController.php#L26-L192)

### 1.5 — batch update correctness (tap-cycle safe)
- [ ] Payload validation accepts expected patch shape (`updates[]` with optional fields)
- [ ] **Authorization is scoped**:
  - [ ] `student_period_id` being updated belongs to current school/year
  - [ ] For teacher users: record belongs to teacher’s session context (not just “exists”)
  - [ ] Admin/school-admin is blocked from write endpoints (read-only enforcement)
- [ ] Per-item validation is strict:
  - [ ] `attendance_score` is 0..5
  - [ ] `numeric_value` is 0..max_value per mapping
  - [ ] Unknown mapping IDs are rejected
- [ ] Server recalculates totals and does not trust client totals
- [ ] Server recalculates totals using the formula in section E (Attendance + active mappings)
- [ ] Returns partial success `{ updated: [], errors: [] }` for UI resiliency

**Evidence pointers**
- Controller: [CrSessionController.php](file:///c:/my_project/myclass2026-main/app/Http/Controllers/Api/Cr/CrSessionController.php#L197-L292)

### 1.6 — Absent lock behavior matches locked decisions
- [ ] If status becomes `absent`:
  - [ ] `attendance_score = 0`
  - [ ] all category scores become 0
  - [ ] `total_score = 0`
  - [ ] `locked = true`
- [ ] If status changes away from `absent`:
  - [ ] `locked = false`
  - [ ] other categories reset to default (5) (no restore-from-cache)

## ⚠️ Current Repo Findings (what fails readiness today)

These are concrete mismatches found in the current implementation that should be resolved before Phase 2 depends on Phase 1.

### 1) total_score excludes Attendance (breaks 20-point model)
- init-session totals sum only category scores (missing `attendance_score`)  
  Reference: [CrSessionController.php:L143-L145](file:///c:/my_project/myclass2026-main/app/Http/Controllers/Api/Cr/CrSessionController.php#L143-L145)
- batch totals also sum only category scores (missing `attendance_score`)  
  Reference: [CrSessionController.php:L269-L271](file:///c:/my_project/myclass2026-main/app/Http/Controllers/Api/Cr/CrSessionController.php#L269-L271)

### 1b) total_score is computed with a hard-coded assumption (not future-proof)
- Total should be computed as Attendance + sum(active mappings) so categories can be added/removed without rewriting logic
- Recommended design is captured in section E

### 2) locked students cannot be changed away from absent (conflicts with “absent → present resets defaults”)
- Batch update rejects any change when `locked && attendance_status === absent`  
  Reference: [CrSessionController.php:L219-L222](file:///c:/my_project/myclass2026-main/app/Http/Controllers/Api/Cr/CrSessionController.php#L219-L222)

### 3) init-session overwrites existing state (can erase saved absent/locked)
- `updateOrCreate` sets `attendance_status=present` and `locked=false` every time  
  Reference: [CrSessionController.php:L103-L118](file:///c:/my_project/myclass2026-main/app/Http/Controllers/Api/Cr/CrSessionController.php#L103-L118)

### 4) Authorization gaps (security)
- Teacher assignment check relies on request `teacher_id` (spoofable)
  Reference: [CrSessionController.php:L29-L65](file:///c:/my_project/myclass2026-main/app/Http/Controllers/Api/Cr/CrSessionController.php#L29-L65)
- batchUpdate does not scope updates by school/year/session ownership (only existence)
  Reference: [CrSessionController.php:L199-L209](file:///c:/my_project/myclass2026-main/app/Http/Controllers/Api/Cr/CrSessionController.php#L199-L209)

### 5) Role mismatch for read-only access
- Only `admin` is treated as admin; `school_admin` / `super_admin` are not accounted for
  Reference: [CrSessionController.php:L42-L49](file:///c:/my_project/myclass2026-main/app/Http/Controllers/Api/Cr/CrSessionController.php#L42-L49)

### 6) CR API routes may be orphaned (not included)
- The CR API file exists but is not referenced from [api.php](file:///c:/my_project/myclass2026-main/routes/api.php) (verify actual route registration)
  Reference: [cr-api.php](file:///c:/my_project/myclass2026-main/routes/modules/Academics/cr-api.php)

## ✅ Phase 1 Ready When

- [x] CR API routes are confirmed registered under `/api/cr/*`
- [x] `total_score` consistently follows section E formula (0..20 in v1)
- [x] Absent lock can be reversed by changing attendance away from absent (reset other categories to default)
- [x] init-session is idempotent and never overwrites existing saved state
- [x] batchUpdate enforces school/year + teacher/session ownership
- [x] Admin/school-admin can read but cannot write
