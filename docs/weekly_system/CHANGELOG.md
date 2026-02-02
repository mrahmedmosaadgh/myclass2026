# Weekly Plan System - Changelog

## Version History and Notable Updates

### January 2026

#### [2026-01-10] Refactor & Sync Enhancements
- **Refactor:** Major cleanup of `WeeklyPlanController` to use `WeeklyPlanService`.
- **Feature:** Added "Batch Sync" capability to handle schedule changes mid-semester.
- **Fix:** Solved "Orphan Plans" issue where plans lost their teacher association after schedule edits.
- **UI:** Updated `WeeklyPlansManager.vue` with tabbed interface (Sync | Monitor | Classroom).

#### [2026-01-05] Sync Dashboard
- **New Feature:** Introduced `WeeklyPlanSyncDashboard` for Admins.
- **Capability:** Visual feedback on "Created" vs "Updated" vs "Skipped" plans during generation.

### December 2025

#### [2025-12-29] Tabs Interface
- **UI:** Replaced long scrolling admin page with efficient Tab view.
- **UX:** improved loading states for heavy data queries.

#### [2025-12-28] Sync Protection & Error Handling
- **Security:** Added integrity checks to prevent overwriting existing plan content during Sync.
- **Fix:** Resolved `SQL Integrity Constraint Violation` on duplicate plan generation. Added unique composite key constraints.

### July 2025

#### [2025-07-18] Initial Release
- **Database:** Created `weekly_plans` table.
- **Core:** Basic CRUD operations for Classwork and Homework.
