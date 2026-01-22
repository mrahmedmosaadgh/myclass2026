# 2026-01-22 17:20 | Transition from Schedule Copies to JSON Drafts

## 🔄 Architectural Change: Deprecation of Schedule Copies

The previous design concept of "Schedule Copies" (where multiple timetable structures co-existed in a `schedule_copies` table) has been **deprecated and removed**.

The system has moved to a simplified model where:
1.  **There is only ONE live schedule.**
2.  **Drafts are used for versioning/backups.**

### 🚫 Deprecated Components
- `schedule_copies` table (concept removed).
- "Active Schedule Copy" logic.
- Routes related to schedule copies management.

---

## ✨ New "Drafts" Architecture

Drafts allow administrators to save snapshots of the current timetable and restore them later. This replaces the complex "Schedule Copy" switching mechanism.

### 1. Data Storage: JSON Column
Drafts are **not** stored in a separate table. Instead, they are stored directly on the `classroom_subject_teachers` (CST) table in a `drafts` JSON column.

*   **Model**: `ClassroomSubjectTeacher`
*   **Column**: `drafts` (JSON)
*   **Structure**:
    ```json
    {
      "Draft Name 1": {
        "timestamp": "2026-01-22T12:00:00",
        "entries": [
          { "day_number": 1, "period_number": 1 },
          { "day_number": 2, "period_number": 3 }
        ]
      },
      "Draft Name 2": { ... }
    }
    ```

### 2. Saving a Draft
*   **Endpoint**: `POST /weekly-system/api/drafts/save`
*   **Logic**:
    1.  Iterates through all CSTs for the school.
    2.  Finds current live `Schedule` entries for each CST.
    3.  Updates the `drafts` JSON column on the CST record with the new snapshot.

### 3. Loading a Draft
*   **Endpoint**: `POST /weekly-system/api/drafts/load`
*   **Logic**:
    1.  **Destructive Action**: Deletes ALL current `Schedule` records for the school.
    2.  Iterates through all CSTs.
    3.  Reads the `drafts` column for the specified draft name.
    4.  Re-creates `Schedule` records based on the saved entries.

### 4. Listing Drafts
*   **Endpoint**: `GET /weekly-system/api/drafts`
*   **Logic**: check the first available CST record with drafts to list available draft keys (assuming drafts are saved globally for the school).

---

## 📍 Timetable Editor Location Update

The Timetable Editor has been moved to a proper admin dashboard route to reflect its central importance.

*   **Old URL**: `/weekly-system/timetable-editor`
*   **New URL**: `/admin/schedules/dashboard`
*   **Route Name**: `admin.schedules.dashboard`

This route handles the full editing interface, including:
- Drag-and-drop / Click-to-assign grid.
- AI Import.
- Random Fill.
- Draft Save/Load.
- Conflict Detection.

---

## ⚠️ Important Notes for Developers
*   **Single Source of Truth**: The `schedules` table is the only place where *active* slot assignments exist.
*   **No "Active Copy"**: Do not look for an `is_active` flag on a schedule copy. The presence of a record in `schedules` means it is active.
