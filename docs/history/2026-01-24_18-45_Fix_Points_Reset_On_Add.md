# Fix: Points Reset on Add (Frontend/Backend Sync)

## Context
When a user added a point while viewing "Overall" (or specific filter) stats, the interface would momentarily reset the student's points to the session value (often 0 or 1). This occurred because the `quickCreate` API returned the new record with only session-scoped calculations, deleting the previous accumulated total from the frontend state.

## Changes Implemented

### 1. Backend (`StudentBehaviorController.php`)
*   **Change**: Updated `quickCreate` to accept a `points_mode` parameter.
*   **Logic**:
    *   If `points_mode` is provided (e.g., 'overall', 'all_subjects', 'competition'), the controller manually calculates the aggregate totals using `StudentBehaviorsMainController` logic.
    *   Injects these totals (`points_plus`, `points_minus`) into the returned `StudentBehavior` model.
    *   Defaults to 'session' logic if no mode is provided.

### 2. Frontend (`reward_sys.vue` & Service)
*   **File**: `resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue`
    *   **Change**: Passed `points_mode` (mapped from `pointsDisplayMode`) to the `applyBehaviorToStudents` function call.
*   **File**: `resources/js/Pages/.../reward_sys_comp/reward_sys_point_action.js`
    *   **Change**: Updated `applyBehaviorToStudents` service method to include `points_mode` in the API payload sent to `/quick-create`.

## Verification
*   **Scenario**: User selects "Overall", sees 50 points. Adds +1 point.
*   **Result**:
    *   API Request: Sends `points_mode: 'overall'`.
    *   API Response: Returns record with `points_plus: 51`.
    *   UI Update: Updates to 51 (instead of resetting to 1).
