# 2026-01-22 18:00 | Draft Management System Implementation

## 📦 Enhanced Feature: Timetable Draft Management

Replaced the deprecated "Schedule Copies" architecture with a robust **JSON-based Draft Management System**.

### 🌟 Key Features

1.  **Multiple Named Drafts**: Admins can save unlimited versions of the schedule.
2.  **Metadata Tracking**: Drafts store creator name, timestamp, description, and statistics.
3.  **Safety First**:
    *   **Auto-Backup**: Loading a draft automatically creates a backup of the current live schedule (`AUTO_BACKUP_...`).
    *   **Comparison View**: "Diff" view showing additions/deletions/changes before publishing.
    *   **Interactive Manager**: Dedicated UI panel to manage all drafts.

### 🛠 Technical Implementation

#### Backend (`ScheduleController.php`)
*   **Storage**: Drafts stored in `classroom_subject_teachers.drafts` JSON column.
*   **New APIs**:
    *   `POST /drafts/save`: Enhanced with metadata validation.
    *   `POST /drafts/load`: Supports `create_backup` flag (default true).
    *   `POST /drafts/compare`: Returns diff stats (additions, deletions).
    *   `POST /drafts/delete`: Removes draft entry.
    *   `GET /drafts`: Returns list with aggregated metadata.

#### Frontend (Vue.js)
*   **New Components**:
    *   `DraftManagementPanel.vue`: Main interface for listing/filtering drafts.
    *   `DraftComparisonDialog.vue`: Pre-flight check before publishing.
*   **Integration**:
    *   Updated `TimetableEditor.vue` to use the new components.
    *   Replaced old simple "Save/Load" dialogs with the robust manager.

### 🔄 Workflow
`Live Schedule` -> `Save as Draft` -> `Edit Draft` -> `Compare vs Live` -> `Publish (Auto-Backup Live)`

---
### ⚠️ Deprecation Notice
*   Old `schedule_copies` table and controllers are fully deprecated.
*   Simple "Save Snapshot" buttons replaced by "Manage Drafts".
