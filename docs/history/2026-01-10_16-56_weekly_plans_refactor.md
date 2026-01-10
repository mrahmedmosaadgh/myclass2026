# Weekly Plans Manager Refactor & Improvements

## Overview
Refactored the Weekly Plans Manager system to centralized filter controls, implemented a shared Pinia store linked to the global school context, and significantly improved the print/PDF functionality.

## Changes

### 1. Refactoring Filter Controls
- **Centralized Filters:** Created `WeeklyPlansFilterBar` component to handle week selection centrally.
- **Removed Redundancy:** Removed duplicate "Schedule Copy" and "Semester" selectors from `WeeklyPlansManager` and child components (`WeeklyPlanStats`, `WeeklyPlanClassroomView`).
- **Global Context:** The system now automatically inherits the active Schedule Link and Semester from the global `MainSchoolData` application state.

### 2. State Management (Pinia)
- **New Store:** Created `useWeeklyPlansStore` to manage `weekNumber`, `currentWeek`, and proxy context from `SchoolDataStore`.
- **Integration:** All child components now consume this shared store instead of relying on prop drilling or local state.

### 3. "By Classroom" View Improvements
- **HTML Rendering:** Enabled `v-html` support for "Classwork", "Homework", and "Notes" columns to correctly display rich text (bold, line breaks).
- **Print Integration:** Removed the separate "Print Weekly" tab and added context-aware "Print" and "Save as PDF" buttons directly to each classroom header.
- **Bug Fixes:** Resolved issues with classroom selection by ensuring robust dependency on the global school ID.

### 4. Print & PDF Aesthetics
- **Modern Design:** Upgraded the print output with 'Nunito' typography, gradient headers, and rounded styling.
- **Readability:** Added zebra-striping to tables and clear "CW/HW" tags for better parent readability.

## Components Modified
- `resources/js/Pages/my_table_mnger/weekly_system/admin/WeeklyPlansManager.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/admin/components/WeeklyPlansFilterBar.vue` (New)
- `resources/js/Stores/useWeeklyPlansStore.js` (New)
- `resources/js/Pages/my_table_mnger/weekly_system/admin/WeeklyPlanClassroomView.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/admin/WeeklyPlanStats.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/components/weekly-plans/WeeklyPlanSyncDashboard.vue`
