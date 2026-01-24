# 2026-01-24 15:45 | Fix School Settings Update Error

**Detailed description of changes:**

### 1. Backend Fixes
- **School Controller**: Fixed `apiUpdate` method where it was trying to update `academic_year_id` and `semester_id` directly. Added mapping to correct database columns `active_academic_year_id` and `active_semester_id`.
- **School Model**: Updated `$fillable` array and relationships to use the correct schema column names (`active_academic_year_id`, `active_semester_id`) instead of the incorrect legacy names.

### 2. Impact
- Resolved `SQLSTATE[42S22]: Column not found` error when saving school settings.
- Resolved 500 error during school configuration updates.

### 3. Remaining Tasks
- [ ] Verify other controllers (e.g., `SchoolBrowserController`) for similar column name mismatches if strictly used.
