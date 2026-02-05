# Teacher Schedule Integration in Lesson Player

## Overview
Integrated the Teacher Schedule view directly into the Student Lesson Player to allow teachers to check their schedule without leaving the lesson presentation.

## Changes Created
1.  **LessonPlayer.vue**:
    -   Repurposed "Reward System" button to "Teacher Schedule".
    -   Implemented a custom overlay (`<div>` with `v-show`) to host the Schedule iframe.
    -   Added "Minimize" functionality to hide the overlay while keeping the iframe active (no reload).
    -   Added a Floating Action Button (FAB) to restore the minimized schedule.
    -   Refactored `goToRewardSystem` to load `/schedules/my-schedule`.

2.  **Documentation**:
    -   Created `docs/history/lesson_player_interaction_ref.md` detailing the integration.

## Technical Details
-   **State Preservation**: Switched from `q-dialog` to `v-show` div to ensure the iframe context is not destroyed when minimized.
-   **Classroom Selection**: The logic for selecting a classroom was implemented but currently commented out in favor of a direct schedule load.

## Status
-   [x] Open Schedule in Dialog
-   [x] Minimize/Restore without Reload
-   [x] Documentation

## Next Steps
-   Monitor user feedback on whether classroom-specific filtering is needed for the schedule.
