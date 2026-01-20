# 2026-01-20 05:58 | Timetable Refactor and Behavior Seeder

## 📦 What Was Done

### 1. Timetable System Refactor (Hybrid Approach)
- **Database Schema**:
    - Removed `schedule_copies` table.
    - Updated `schedules` table to strictly store the *active* schedule (removed `copy_id` and `active` columns).
    - Added `drafts` and `history` JSON columns to `classroom_subject_teachers` table for flexible storage of non-active versions.
    - Created migration `2026_01_19_173420_refactor_timetable_structure`.

- **Backend Logic**:
    - Refactored `WeeklySystemController` to remove dependencies on `ScheduleCopy`.
    - Updated `ClassroomSubjectTeacher` model to cast JSON columns.
    - Updated `ScheduleController` to manage drafts via the new JSON columns and simplified `index` queries.
    - **Fixes**: Resolved 500 errors by removing obsolete `where('active', true)` clauses and fixing a missing `user_classroom_data` table issue (by rolling back and using `classroom_subject_teachers` instead).

- **Frontend**:
    - Updated `TimetableEditor.vue` to replace the "Schedule Copy" selector with "Save Draft" / "Load Draft" actions.
    - Added translation for `common.assigned`.

### 2. Behavior Management Seeder
- Updated `BehaviorSeeder.php` to populate default behaviors for all schools:
    - Iterates through all schools and their academic years.
    - Creates standard behaviors (e.g., "Helping Others", "On Task") with positive points.
    - Maps the "Category" field to the `description` column (e.g., "Social", "Academic").
    - Ensures defaults are applied globally so schools can build upon them.

## 🔜 What's Next
- Monitor the new draft system in production for any edge cases with large JSON payloads.
- Allow schools to customize these default behaviors via a frontend interface (if not already present).
