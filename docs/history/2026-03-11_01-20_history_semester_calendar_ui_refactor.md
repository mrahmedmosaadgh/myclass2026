# 2026-03-11 01:20 | Semester Card UI Refactor & Calendar Flow Restructure

## Summary

Major session covering SemesterCard UX improvements, AI Setup flow fixes, full-year calendar generation, and several bug fixes across the academic calendar module.

---

## ✅ What Was Done

### 1. SemesterCard — Edit Mode Toggle
- Added a pencil icon button to each `SemesterCard` header to toggle between **read-only** and **edit mode**.
- Read-only shows clean status/dates. Edit mode reveals input fields for dates and semester name.

### 2. Inline Events & Special Days (SemesterCard)
- Added a `q-expansion-item` to the read-only view of each SemesterCard.
- Lazy-loads events from the new `GET /admin/academic-calendar/semester/{id}/events` API when expanded.
- **Friday / Saturday toggles** filter out weekend day-off records.
- **Events Summary Table**: color-coded chips (legend) + vacation date-range table (Name / From / To / Days) appear above the detail list.
- Fixed the URL to use Ziggy's `route()` helper instead of a hardcoded path.

### 3. Active Semester Selector in Year Header
- Added a `q-select` dropdown in the year card header to change the active semester for that year.
- Calls new `PUT /admin/academic-calendar/year/{year}/active-semester/{semester}` API.
- Registered new routes: `semester.events` (GET) and `year.set_active_semester` (PUT).
- Added controller methods: `getSemesterEvents()` and `setActiveSemester()`.

### 4. Calendar Timeline View per Semester
- Added a 📅 calendar grid icon button in each SemesterCard header.
- Opens a full-screen dialog showing `CalendarPreview.vue` scoped to that semester only (via new `semesterId` prop).
- Updated `CalendarPreview.vue` to accept `semesterId` prop, hide semester tabs when scoped, and use Ziggy `route()` for the calendar-data API call.
- Passed `yearId` from `Index.vue` down to each `SemesterCard`.

### 5. AI Setup — Update vs Replace Mode
- Added mode selector (two radio cards) to Step 4 of `CalendarAIGeneratorDialog.vue`:
  - **Update Existing**: patches semesters/events, keeps existing calendar rows.
  - **Replace All**: force-deletes all semesters + calendar records, then rebuilds fresh from AI data.
- Backend `applyAISemesterSetup` respects the `mode` field from the request.

### 6. Full-Year Calendar Generation Flow Restructured
- **Year creation** (`storeYear`) no longer auto-generates semesters or calendar records.
- **`AcademicYear::created` model event** no longer auto-creates 4 default semesters.
- Calendar and semesters are now created only when the user applies semesters (via AI Setup or manual edit).
- New migration: `semester_id` in `calendars` is now **nullable** (via raw `ALTER TABLE` SQL) to support gap days between semesters.
- `applyAISemesterSetup` rewrote full-year calendar generation into **3 ordered passes**:
  1. **Pass 1**: Create/update semesters, collect vacation & event data into arrays (no DB writes yet).
  2. **Pass 2**: Bulk insert the entire year's days (from `year.start_date` → `year.end_date`) with correct `status` and `semester_id`.
  3. **Pass 3**: UPDATE vacation and event rows with `status` and `data` JSON **after** the rows exist.

### 7. Bug Fixes
- **Duplicate semester entry**: Fixed `applyAISemesterSetup` by using `Semester::withTrashed()->firstOrNew()` + `->restore()` to handle soft-deleted semesters blocking the unique key.
- **Empty `data` column**: Vacation/event UPDATEs were running before the bulk INSERT overwrote them — fixed by the 3-pass order above; also switched to `json_encode()` for the data field.
- **Replace mode duplicate entry**: `Calendar::delete()` was a soft delete, leaving rows that blocked the unique `(date, school_id)` index. Fixed with `Calendar::withTrashed()->forceDelete()`.
- **Route not found for events**: Fixed by using Ziggy `route()` helper in `SemesterCard.vue` instead of hardcoded URL.
- **Preview Calendar removed**: Removed the full-screen CalendarPreview dialog and button from `Index.vue` (replaced by per-semester timeline button in SemesterCard).
- **Route cache cleared** after adding new routes.

### 8. Unassigned Days Cards
- `Index.vue` `semestersWithGaps()` fills the entire year as cards — semester cards for semester periods, `UnassignedDaysCard` components for gap periods.

---

## 🔄 Still To Do / Known Gaps

- [ ] **Manual event editor**: Clicking a day cell in the CalendarPreview timeline to set its status/name directly (currently read-only).
- [ ] **Semester card count badges**: The `calendar_count` on semesters is not updated after AI setup without a page refresh — consider real-time Inertia reload.
- [ ] **UnassignedDaysCard**: Allow user to click "Create Semester" from the gap card to fill the period efficiently.
- [ ] **Week number reset per semester**: Currently week_number is absolute for the year — may need a `week_of_semester` field for display.
- [ ] **Soft-delete safety on calendars**: Verify other delete paths also use `forceDelete` where needed.

---

## 📁 Files Modified

### Backend
- `app/Http/Controllers/YearSemesterCalendarController.php`
- `app/Models/AcademicYear.php`
- `routes/admin.php`
- `database/migrations/2026_03_10_215200_make_semester_id_nullable_in_calendars.php` *(new)*

### Frontend
- `resources/js/Pages/my_class/admin/year_semester_calendar/Index.vue`
- `resources/js/Pages/my_class/admin/year_semester_calendar/SemesterCard.vue`
- `resources/js/Pages/my_class/admin/year_semester_calendar/CalendarPreview.vue`
- `resources/js/Pages/my_class/admin/year_semester_calendar/CalendarAIGeneratorDialog.vue`
- `resources/js/Pages/my_class/admin/year_semester_calendar/UnassignedDaysCard.vue`
