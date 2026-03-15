# Classroom Records v1 — Tables, Relations, and Dataflow (cr_*)

This document describes the **new `cr_*` schema** and how data flows from Teacher Schedule into Classroom Records v1.

***

## Core Tables

### `cr_sessions`
Teaching context for a single class slot.
- Links: school/year/teacher/classroom/subject + date + period_code

### `cr_student_periods` (Source of truth)
One row per student per slot for reporting.
- Attendance reporting source: `attendance_status`
- Scoring support: `attendance_score`
- Session total: `total_score`

### `cr_category_mappings`
Admin-defined categories per school (e.g. homework, behavior).

### `cr_scores`
Long-format values: one row per (student_period, category).

***

## period_code (Slot Key)
Canonical format:
- `Y{year_id}-S#-W#-D#-P#`

Example:
- `Y2026-S1-W12-D2-P3`

***

## ER Diagram (cr_*)

```mermaid
erDiagram
  CR_SESSIONS ||--o{ CR_STUDENT_PERIODS : assigns
  CR_STUDENT_PERIODS ||--o{ CR_SCORES : has
  CR_CATEGORY_MAPPINGS ||--o{ CR_SCORES : defines

  SCHOOLS ||--o{ CR_SESSIONS : owns
  SCHOOLS ||--o{ CR_CATEGORY_MAPPINGS : owns
  SCHOOLS ||--o{ CR_STUDENT_PERIODS : owns

  ACADEMIC_YEARS ||--o{ CR_SESSIONS : in
  ACADEMIC_YEARS ||--o{ CR_STUDENT_PERIODS : in

  TEACHERS ||--o{ CR_SESSIONS : runs
  CLASSROOMS ||--o{ CR_SESSIONS : for
  SUBJECTS ||--o{ CR_SESSIONS : for
  STUDENTS ||--o{ CR_STUDENT_PERIODS : tracked
```

***

## Dataflow (Schedule → Records)

```mermaid
sequenceDiagram
  participant SCH as Teacher Schedule UI
  participant UI as Classroom Records UI
  participant API as Laravel API
  participant DB as Database

  SCH->>UI: Open deep link with context\n(classroom_id, subject_id, teacher_id,\ndate, day_number, period_number, period_code)

  UI->>API: POST /api/cr/init-session (context)
  API->>DB: Upsert cr_sessions
  API->>DB: Upsert cr_student_periods per student\n(default attendance + totals)
  API->>DB: Upsert cr_scores per mapping\n(default numeric_value)
  DB-->>API: session + roster + student_periods + scores
  API-->>UI: initial render payload

  UI->>API: PATCH /api/cr/batch (dirty updates)
  API->>DB: Validate + update cr_student_periods + cr_scores
  DB-->>API: success
  API-->>UI: success
```

***

## Report-Safe Constraints

Critical uniqueness to prevent broken attendance reports:
- `cr_student_periods`:
  - `UNIQUE (school_id, year_id, date, period_code, student_id)`

Rule:
- “Present” reporting uses `attendance_status` (not score).

