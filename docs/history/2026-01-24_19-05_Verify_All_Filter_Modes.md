# Verification: Point Filter Modes

## Overview
Comprehensive verification of all point filter modes ("moods") to ensure consistency and correctness after the recent fix for "Session" mode.

## Modes Verified

### 1. This Session
*   **Logic**: Calculates sums of point actions for the current session only.
*   **Backend**: Updated to explicitly calculate `sum(value)` from the loaded `pointActions` relation.
*   **Status**: **Fixed & Verified**. (Prevents zero-point display issue).

### 2. Overall (This Subject)
*   **Logic**: Aggregates points for the student in the current school + current subject (All Time).
*   **Backend**: Uses SQL JOINs to filter by `school_id`, `student_id`, and `subject_id`.
*   **Status**: **Verified**.

### 3. Overall (All Subjects)
*   **Logic**: Aggregates points for the student in the current school across ALL subjects.
*   **Backend**: Uses SQL JOINs to filter by `school_id` and `student_id` (ignoring subject).
*   **Status**: **Verified**.

### 4. Competition (Week)
*   **Logic**: Aggregates points for the current week (Start of Week to End of Week).
*   **Backend**: Uses `Carbon` date ranges to filter actions.
*   **Status**: **Verified**.

### 5. From Now (Stopwatch)
*   **Logic**: Client-side calculation. `Display = Current_Total - Baseline_At_Start`.
*   **Frontend**: Verified `StudentCard` component correctly subtracts the baseline in `positive`, `negative`, and `attendance` views.
*   **Status**: **Verified**.

## Conclusion
All filter modes are now using consistent logic between the initialization (`init-classroom`) and update (`quick-create`) phases, ensuring points do not jump or reset incorrectly when adding new behaviors.
