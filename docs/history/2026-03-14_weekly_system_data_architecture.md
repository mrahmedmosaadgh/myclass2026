# Weekly System — Data Architecture & Recommendations

**Date:** 2026-03-14

---

## 1. Table Reference

### `curricula` — The Book
Represents the textbook definition for a subject/grade within a school.

| Column | Type | Nullable | Notes / Expected Data |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary key |
| `name` | string | No | `"Mathematics Grade 4"` |
| `description` | text | Yes | Brief overview of the book |
| `grade_id` | FK → grades | No | `4` (Grade 4) |
| `subject_id` | FK → subjects | No | `25` (Mathematics) |
| `school_id` | FK → schools | No | Tenant isolation |
| `edit_lock_date` | date | Yes | `2026-04-01` — after this date, lesson plans are read-only |
| `deleted_at` | timestamp | Yes | Soft delete |

---

### `curriculum_versions` — The Edition
Controls which version/year of a book is active. Enables long-term data preservation without duplicating curricula.

| Column | Type | Nullable | Notes / Expected Data |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary key |
| `curriculum_id` | FK → curricula | No | Which book this is an edition of |
| `title` | string | No | `"2025/2026 Edition"` |
| `academic_year` | string | Yes | `"2025-2026"` |
| `status` | enum | No | `draft` / `active` / `archived` |
| `version_number` | int | No | `1`, `2`, ... |

---

### `curriculum_topics` — The Chapters
Groups of lessons within a version (e.g., "Unit 1: Number Systems").

| Column | Type | Nullable | Notes / Expected Data |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary key |
| `curriculum_version_id` | FK | No | Links to a specific edition |
| `number` | string | No | `"Unit 1"`, `"Chapter 3"` |
| `title` | string | No | `"Fractions & Decimals"` |
| `description` | text | Yes | Optional overview |

---

### `curriculum_lessons` — The Table of Contents
The individual lesson items as they appear in the physical book. This is the **most critical missing layer** in the current workflow.

| Column | Type | Nullable | Notes / Expected Data |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary key |
| `curriculum_version_id` | FK | No | Ties to the book edition |
| `topic_id` | FK → topics | Yes | Optional grouping into a chapter |
| `lesson_number` | string | No | `"Lesson 3"`, `"3-1"` |
| `lesson_title` | string | No | `"Adding Fractions"` |
| `page_number` | int | Yes | `42` |
| `description` | text | Yes | What the lesson covers |
| `type` | enum | No | `main` / `revision` / `quiz` / `project` |
| `data` | JSON | Yes | Flexible metadata, skill tags, links |

---

### `curriculum_lesson_plans` — Teacher's Content
The actual lesson material prepared by the teacher for a specific lesson. Can be AI-generated or hand-written.

| Column | Type | Nullable | Notes / Expected Data |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary key |
| `school_id` | FK | No | Tenant isolation |
| `curriculum_id` | FK | Yes | Which book this plan belongs to |
| `curriculum_lesson_id` | FK | Yes | The specific lesson from the ToC |
| `subject_id` | FK | No | Denormalized for fast querying |
| `grade_id` | FK | No | Denormalized for fast querying |
| `classroom_id` | FK | ⚠️ Should be `Yes` | Currently required — **causes save errors** |
| `teacher_id` | FK | No | The teacher who wrote the plan |
| `title` | string | No | `"Adding Fractions - Week 3"` |
| `cw` | text | Yes | Class work content (HTML or plain text, AI-generated) |
| `hw` | text | Yes | Homework instructions |
| `objectives` | text | Yes | Learning objectives |
| `plan` | JSON | Yes | Structured slide plan (from AI generation) |
| `status` | tinyint | No | `0=draft`, `1=final` |
| `planned_date` | date | Yes | `2026-03-20` |

---

### `curriculum_maps` — The Pacing Guide
Maps lessons/topics to calendar weeks for a teacher's yearly plan. Acts as the annual roadmap.

| Column | Type | Nullable | Notes / Expected Data |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary key |
| `school_id` | FK | No | Tenant |
| `academic_year_id` | FK | No | The school year this map is for |
| `subject_id` | FK | No | The subject |
| `grade_id` | FK | No | The grade |
| `teacher_id` | FK | No | The responsible teacher |
| `curriculum_version_id` | FK | Yes | Which edition is being used |
| `weekly_plan` | JSON | Yes | `{1: {lessons: [3,4], objectives: "..."}}` |
| `status` | tinyint | No | `0=draft`, `1=active` |
| `start_date` / `end_date` | date | Yes | Academic period covered |

---

### `weekly_plans` — The Execution Frame
The weekly container linked to the school schedule. Represents one week for a specific period/classroom.

| Column | Type | Nullable | Notes / Expected Data |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary key |
| `academic_year_id` | FK | No | Which year |
| `semester_number` | tinyint | No | `1` or `2` |
| `week_number` | tinyint | No | `1` – `36` |
| `day_number` | tinyint | Yes | `1`=Sunday, `5`=Thursday |
| `period_order` | tinyint | Yes | `1`=first period of the day |
| `classroom_id` | FK | Yes | Which class |
| `subject_id` | FK | Yes | Which subject |
| `teacher_id` | FK | Yes | Who teaches it |
| `schedule_id` | FK | No | Links to the school timetable |
| `copy_id` | FK | No | The schedule copy used |
| `cw` / `hw` / `notes` | text | Yes | Quick weekly notes |

---

### `weekly_plan_sessions` — The Period Entries
Individual session entries within a weekly plan (one per school period).

| Column | Type | Nullable | Notes / Expected Data |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary key |
| `weekly_plan_id` | FK | No | The parent week |
| `session_index` | int | No | `1`, `2`, `3`... order within the week |
| `period_code` | string | No | `"2026.1.3.2"` (Year.Semester.Week.Day) |
| `type` | enum | No | `lesson` / `quiz` / `exam` / `extra` / `note` |
| `title` | string | No | `"Adding Fractions"` |
| `data` | JSON | Yes | Materials, Zoom links, homework, skill tags |

---

## 2. Data Flow

```
[Admin creates Curriculum]
        |
        v
[Admin (or AI) adds Topics & Lessons (Table of Contents)]
        |
        v
[Teacher creates Lesson Plans (cw, hw, AI-generated content)]
        |
        v
[Admin creates Curriculum Map → assigns lessons to weeks]
        |
        v
[System generates Weekly Plans from the school Schedule + Curriculum Map]
        |
        v
[Sessions are created per-period, linking to the Lesson Plan content]
```

---

## 3. Issues & Recommendations

### 🔴 Critical Issues (Must Fix)

| # | Issue | Affected Table | Recommendation |
| :-- | :--- | :--- | :--- |
| 1 | `classroom_id` is NOT NULL | `curriculum_lesson_plans` | **Make it nullable.** Plans should be book-level by default. |
| 2 | No `curriculum_lessons` management UI | `curriculum_lessons` | **Add ToC management in Admin.** Admins cannot currently define book lessons. |
| 3 | No linking between `lesson_plans` and `curriculum_lessons` | Both | **Require teachers to select a lesson from the ToC** when creating a plan. |

### 🟡 Design Gaps (Should Do)

| # | Gap | Recommendation |
| :-- | :--- | :--- |
| 4 | `curriculum_maps` is disconnected from `weekly_plans` | Auto-populate sessions from pacing guide when the week starts |
| 5 | No version inheritance | When creating a new `curriculum_version`, allow copying lessons from previous edition |
| 6 | `data` JSON in `weekly_plan_sessions` is untyped | Define a JSON schema or use structured columns for common fields (zoom_url, materials[]) |

### 🟢 Potential Enhancements (Nice to Have)

| # | Enhancement |
| :-- | :--- |
| 7 | AI "Table of Contents Parser" — paste raw ToC text and auto-create lessons |
| 8 | Add `co_teacher_ids` to `curriculum_maps` (team teaching support) |
| 9 | Weekly plan progress report for admin — how many sessions have content vs. empty |
| 10 | Lock cascade: when `edit_lock_date` passes, automatically mark all `draft` plans as `final` |
