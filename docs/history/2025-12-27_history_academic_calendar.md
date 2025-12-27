# Academic Calendar Management System

**Date:** 2025-12-27

## Objective
Implement a robust academic calendar management system using Laravel, Inertia.js (Vue 3), and Quasar components. The system allows administrators to manage academic years, semesters, and daily calendar records with automatic naming, semester generation, and gap detection.

## Key Accomplishments

### Backend (Laravel)
- **Model Enhancements**: 
    - `AcademicYear`: Added automatic 4-semester generation in the `booted` method. Enforced single active year per school.
    - `Semester`: Integrated active state management and soft deletes.
    - `User`: Added `schoolId()` helper to resolve school association for teachers and students.
- **Controller Logic**: 
    - Created `YearSemesterCalendarController` for CRUD operations and logic.
    - Implemented automatic year naming (e.g., "2025-2026").
    - Developed `generateCalendar` logic with "skip-if-exists" to avoid unique constraint violations during regeneration.
    - Implemented `getMissingDays` for academic year gap analysis.
- **Routing**: Added structured admin routes for all calendar management features.

### Frontend (Vue 3 + Quasar)
- **Component Refactoring**: Transitioned UI from plain HTML/Tailwind to Quasar components (`q-toggle`, `q-btn`, `q-input`, `q-card`, `q-dialog`) for a premium look and feel.
- **Features**:
    - **Year Management**: `YearForm` with auto-suggested naming and date ranges.
    - **Semester Control**: `SemesterCard` for managing semester dates, weeks, and active status.
    - **Calendar Logic**: Weeks-to-Date automatic calculation.
    - **Preview System**: Fullscreen `CalendarPreview` dialog with semester-based filtering and status visualization.
    - **Status Reporting**: Real-time feedback on calendar generation results (created vs updated vs skipped).

## Technical Note
- The project follows a "single active semester per year" and "single active year per school" policy enforced at the database/model level.
- Calendar storage is date-based with a unique constraint on `[date, school_id]`.
