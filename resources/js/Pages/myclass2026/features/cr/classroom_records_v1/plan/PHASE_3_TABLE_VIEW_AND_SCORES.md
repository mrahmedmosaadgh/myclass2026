## Phase 3 — Table View & Score Persistence

### 1. Goals

- **Consistent editing**: Book / Homework / Behavior editable in both **Card** and **Table** views.
- **Reliable persistence**: All score changes auto-save via `/api/cr/batch` (no silent drops).
- **Robust data model**: Every active category mapping has a corresponding `cr_scores` row per `cr_student_period`.
- **Clear UX**: Edit rules are obvious (Edit mode, Present vs Absent) and colors reflect totals.

---

### 2. High-level Plan

- **2.1 Backend data integrity**
  - Guarantee that for every `CrStudentPeriod` and active `CrCategoryMapping`, there is a `CrScore` row.
  - Auto-seed default mappings (Book, Homework, Behavior) per school if missing.
  - Backfill any legacy `student_period` records that have empty or incomplete `scores`.

- **2.2 Autosave behavior**
  - Ensure `update:scores` / `update:attendance` events from both views always:
    - Optimistically update local `sessionData`.
    - Call `markDirty` with a payload that merges correctly (no overwriting).
  - Confirm `useDirtyBatch` merges multiple changes for the same `student_period_id` and sends a single, correct `updates` item.

- **2.3 Table view UX**
  - For each student row:
    - Show **dropdowns** for Book / Homework / Behavior when **Edit mode ON** and student **Present**.
    - Show read-only numbers otherwise.
  - Attendance and attendance score behave identically between Card and Table.
  - Total chip color reflects latest totals (0–20).

---

### 3. Detailed Tasks

#### 3.1 Backend — Category mappings & scores

- [x] **Seed default mappings for school**  
  - File: `database/seeders/CrCategoryMappingsSeeder.php`  
  - Ensure mappings exist for keys: `book_participation`, `homework`, `behavior` for the dev/test school.

- [x] **Auto-seed mappings when missing (init-session)**  
  - File: `app/Http/Controllers/Api/Cr/CrSessionController.php`  
  - If `$activeMappings` is empty for the current `school_id`, create the 3 defaults, then reload `$activeMappings`.

- [x] **Create missing `cr_scores` rows for existing student periods**  
  - In `initSession`, when a `CrStudentPeriod` already exists:
    - Loop all `$activeMappings`.
    - For each mapping with no existing `CrScore`, create a new one using `default_value`.
    - Recalculate and update `total_score` if any scores were created.

- [ ] **(Optional) Backfill script for legacy data**  
  - Create an artisan command or one-off script to:
    - Iterate all `CrStudentPeriod` records for current school/year.
    - Ensure each has `CrScore` rows for all active mappings.
    - Fix any `total_score` inconsistencies.

#### 3.2 Frontend — Event flow & autosave

- [x] **Emit score changes from Card view**
  - File: `StudentCard.vue`
  - `emitScore` sends `{ student_period_id, mapping_id, numeric_value }`.
  - Category click and select changes call `emitScore`.

- [x] **Emit score changes from Table view**
  - File: `StudentTable.vue`
  - Each Book / Homework / Behavior select:
    - Binds to current `numeric_value`.
    - Calls `emitScore(student, scoreRecord, $event.target.value)` on `@change`.
  - `emitScore`:
    - Guards on `editMode`, `disabled`, `student_period_id`, and `mapping_id`.
    - Logs clear warnings if `scores` are missing (helps diagnose backend issues).

- [x] **Central score handler in main page**
  - File: `ClassroomRecordsPage.vue`
  - `handleScoreUpdate(updateData)`:
    - Updates local `sessionData.students[*].scores[*].numeric_value`.
    - Calls `recalculateTotal(student)`.
    - Calls `markDirty(student_period_id, { student_period_id, scores: [{ mapping_id, numeric_value }] })`.

- [x] **Merge multiple dirty updates correctly**
  - File: `useDirtyBatch.js`
  - `markDirty` and `markMultipleDirty`:
    - Merge new payloads with existing items for the same `student_period_id`.
    - Merge `scores` arrays by `mapping_id` so latest value wins but previous category edits are preserved.
  - Debounce lowered to ~500ms for snappier saves.

- [ ] **Verify `/api/cr/batch` payloads in browser**
  - With Edit mode ON and students Present:
    - Change scores in Card view → confirm `updates[*].scores` includes mapping + value.
    - Change scores in Table view → confirm the same.
    - Confirm attendance + scores can be changed before one autosave and both get sent.

#### 3.3 Frontend — UX rules & consistency

- [x] **Edit mode gating**
  - In both `StudentCard.vue` and `StudentTable.vue`:
    - All scoring and attendance controls are disabled when `editMode` is false or `readOnly/disabled` is true.

- [x] **Absent gating**
  - When student is Absent:
    - All scoring and attendance-score controls are disabled.
    - Only the attendance status control can move student back to Present (to fix mistakes).

- [x] **Table vs Card feature parity**
  - Card view and Table view both:
    - Use the same events and handler signatures (`update:scores`, `update:attendance`, `mark-absent`).
    - Respect the same Edit/Absent/Read-only rules.

- [ ] **Visual polish**
  - Confirm column widths and truncation work well at common breakpoints.
  - Optionally add subtle hover state for editable cells when Edit mode is ON.

---

### 4. QA Checklist

- [ ] New session for classroom/subject:
  - All students show Book/Homework/Behavior in both Card and Table.
  - `scores` arrays are non-empty in the API response.

- [ ] Existing sessions with legacy data:
  - After hitting **Load Session**, previously empty `scores` arrays are populated.
  - Table warnings about “no score record/mapping_id” no longer appear.

- [ ] Editing scenarios:
  - Card view:
    - Tapping / selecting scores updates totals and autosaves.
  - Table view:
    - Changing Book/Homework/Behavior updates totals and autosaves.
  - Mixed:
    - Change scores in Table, then in Card, before autosave → single `/api/cr/batch` with merged updates.

- [ ] Absent logic:
  - Mark student Absent:
    - Scores zero out, total is 0, controls locked.
  - Change back to Present:
    - Scores reset to defaults, total recalculated, controls enabled.

