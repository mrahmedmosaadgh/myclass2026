When a new Academic Year is created, the system now **automatically generates a full calendar** for the entire year — defaulting from **July 1** of the start year to **June 30** of the following year. 

The user can now **customize the start date, end date, and year name** directly in the form, and the auto-generation system will respect these choices.

---

## What Was Done

1. **Frontend UI Defaults (`YearForm.vue`)**:
   - Automatically pre-fills **Start Date** to July 1st of the current year.
   - Automatically pre-fills **End Date** to June 30th of the following year.
   - Automatically pre-fills **Year Name** (e.g. `2026-2027`).
   - Added logic to re-calculate name and end-date if the user changes the start date.

2. **Backend Logic Refinement (`YearSemesterCalendarController.php`)**:
   - Updated `autoGenerateFullCalendar()` to use the model's actual `start_date` and `end_date` rather than hardcoding July.
   - This ensures that if the user customizes their academic cycle, the daily calendar matches their input exactly.


---

## Files Modified

| File | Change |
|------|--------|
| `app/Http/Controllers/YearSemesterCalendarController.php` | Added `autoGenerateFullCalendar()` + updated `storeYear()` |
| `resources/js/Pages/my_class/admin/year_semester_calendar/Index.vue` | Added flash message watcher (success + warning) |

---

## Logic Summary

### `autoGenerateFullCalendar(AcademicYear $year): array`

1. **Checks for existing records first** — if `calendars` table already has records for this `academic_year_id`, it returns `['skipped', $count]` and does NOT override.
2. Uses the **user-provided start_date and end_date** (defaults to July 1st → June 30th).
3. Each day is mapped to its semester by date range comparison.
4. Default status: **Fri & Sat = 0 (Day Off)**, all others = **1 (Work Day)**.
5. Bulk-inserts in chunks of 500 for performance.
6. Returns `['generated', 0]` on success.

### UI Enhancements (`YearForm.vue`)
- Form fields are pre-filled for the user, saving time while allowing full customization.
- **Auto-Suggest Policy:** Changing the Start Date automatically updates the End Date (1 year later, June 30th) and the Year Name.


### Flash messages (Index.vue)
- ✅ **Success:** "Academic Year created! Full calendar (July → June) has been auto-generated successfully."
- ⚠️ **Warning (skipped):** "Academic Year created, but calendar was NOT auto-generated because {N} existing records were found for this year. Edit them manually."

---

## What Still Needs Manual Action

- Days that fall **outside all semester date ranges** get `semester_id = null`. Admin can reassign them later via the Calendar Preview.

---

## Test Checklist

- [ ] Create a new Academic Year → confirm ~365 days appear in Calendar Preview
- [ ] Verify Fri/Sat = Day Off, Mon–Thu & Sun = Work Day  
- [ ] Verify days fall under correct semesters
- [ ] Test skip behavior: insert 1 record manually, then create year again → warning shown
