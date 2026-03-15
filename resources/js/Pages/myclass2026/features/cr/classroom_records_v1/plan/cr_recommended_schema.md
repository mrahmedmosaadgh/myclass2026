# Classroom Records v1 — Recommended New DB Schema (cr_* Prefix)

This document proposes a clean, report-safe, extensible database schema for Classroom Records using **new tables only** with the prefix **`cr_`**.

The design follows the “Hybrid Approach”:
- **Hard on the outside:** strict uniqueness per student/time to protect attendance reporting.
- **Soft on the inside:** admin-defined categories stored in a long-format table (no migrations needed to add new categories).

***

## Goals

- One “event” per student per period/day (prevents duplicates that break reports).
- Fast dashboards using fixed columns + indexes.
- Unlimited custom categories per school via mappings + long-format scores.
- Safe “init-session” upsert logic (insert if missing, update if exists).

***

## Tables (Recommended)

### 1) `cr_sessions`
Stores teaching context for a session.

Key columns:
- `school_id`, `year_id`, `teacher_id`, `classroom_id`, `subject_id`
- `date`, `day_number`, `period_number`
- `period_code` (VARCHAR) — canonical slot key: `Y{year_id}-S#-W#-D#-P#`
- `status` (draft|active|locked)

Uniqueness:
- `UNIQUE (school_id, year_id, classroom_id, subject_id, teacher_id, date, period_code)`

***

### 2) `cr_student_periods` (Source of truth)
Report-safe “attendance event” per student per time-slot.

Key columns:
- `school_id`, `year_id`, `student_id`
- `date`, `period_code`
- `session_id` (nullable FK)
- `attendance_status` (present|absent|late|left_early)
- `attendance_score` (0..5)
- `attendance_note` (nullable)
- `total_score` (0..20)
- `locked` (boolean)

Source-of-truth uniqueness:
- `UNIQUE (school_id, year_id, date, period_code, student_id)`

Decision:
- Reports use `attendance_status` as the source of truth.
- `attendance_score` is for UX/other scoring needs.

***

### 3) `cr_category_mappings`
Admin-defined categories per school.

Key columns:
- `school_id`
- `key` (stable identifier)
- `label`, `type`, `max_value`, `passing_value`, defaults, `sort_order`, `active`

Uniqueness:
- `UNIQUE (school_id, key)`

Notes:
- “Seed 3 mappings” means creating default categories for v1:
  - `book_participation` (max 5, default 5)
  - `homework` (max 5, default 5)
  - `behavior` (max 5, default 5)
- Attendance stays fixed in `cr_student_periods`.

***

### 4) `cr_scores`
Long-format per-student-period per-category value.

Key columns:
- `student_period_id` (FK)
- `mapping_id` (FK)
- `numeric_value` (nullable)
- `text_value` (nullable)
- `json_value` (nullable)

Uniqueness:
- `UNIQUE (student_period_id, mapping_id)`

***

## Use Cases

### Session load (init-session)
- Upsert `cr_sessions`
- Upsert `cr_student_periods` per student (defaults)
- Upsert `cr_scores` per mapping (defaults)

### Batch save
- Update `cr_student_periods` attendance + total
- Update `cr_scores` values

### Reporting
- Attendance reports: `cr_student_periods.attendance_status`
- Points reports: `SUM(cr_student_periods.total_score)` over date range
