# 2026-01-05 20:00 | Weekly Plans Sync Dashboard & Critical Fixes

## Overview
Implemented a comprehensive visual dashboard for synchronizing weekly plans with the active schedule. This update includes critical schema fixes to prevent data corruption, a visual sync preview dialog, and robust schedule status logic.

## Key Changes

### 1. 🛡️ Critical Schema & Data Integrity
- **Database**: Updated `weekly_plans` unique constraint to `['schedule_id', 'academic_year_id', 'semester_number', 'week_number']`.
- **Logic**: Removed implicit dependency on `period_order`. Sync eligibility is now correctly based on `day_number` + `period_number` assignment (PLACED status).
- **Validation**: Enforced "One Active Copy" rule per school/year/semester via model boot hook.

### 2. 📊 Visual Sync Dashboard
- **New Component**: `WeeklyPlanSyncDashboard.vue`
- **Stats**: Total slots, Complete, Missing, and Overall Progress %.
- **Granular View**: Classroom cards with circular progress indicators and expandable day-by-day breakdown.
- **Visual Diff**: "Sync Preview Dialog" shows side-by-side comparison of Schedule vs Weekly Plan.

### 3. 🔄 Sync Logic & API
- **New Endpoint**: `GET /weekly-system/api/sync-analysis`
  - Returns detailed missing slot info (Day, Period, Subject, Teacher).
- **New Endpoint**: `POST /weekly-system/api/weekly-plans/batch-create`
  - Supports selective syncing of specific schedule IDs.
- **Routes**: Properly registered inside `weekly-system/api` prefix group.

## Technical Details

### Backend
- **Schedule Model**: Updated `status` attribute:
  - `UNPLACED`: No day/period
  - `PLACED`: Has day/period but inactive
  - `READY`: Has day/period + active (Eligible for sync)
- **WeeklySystemController**:
  - `getSyncAnalysis`: Aggregates stats and detailed missing lists.
  - `batchCreate`: Bulk insert logic with duplicate checks.

### Frontend
- **WeeklyPlansManager**: Replaced old sync tab with new Dashboard.
- **Sync Preview**: Quasar dialog with data table and selection checkboxes.
- **UX**: Clear empty states, loading indicators, and success notifications.
