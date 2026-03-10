# 2026-03-10 16:37 | Auto-Generate Full Calendar on Academic Year Creation

**Date:** March 10, 2026  
**Developer:** AI Assistant (Antigravity)  
**Type:** Feature Plan  
**Status:** 📝 Planned (Not Yet Implemented)

---

## Summary

Planning to enhance the Academic Calendar system so that when a new Academic Year is created, the system **automatically generates a full calendar** for the entire year — starting from **July 1st** — instead of requiring the admin to manually generate it semester by semester.

This means all days (July → June of the following year) will be pre-populated in the `calendars` table with sensible defaults, ready to be edited individually as needed.

---

## Current Behavior

1. Admin creates a new Academic Year (via `YearForm.vue`).
2. System creates the year + 4 semesters entries in the DB.
3. Admin must **manually** open each semester card and click **"Generate Calendar"** to populate daily records.
4. Days only exist in the calendar after the manual generation step.

---

## Planned Change

### Goal
When a new Academic Year is saved, **auto-generate ALL calendar days** for the full year starting from **July 1st** of the start year through **June 30th** of the following year (or the year's `end_date`, whichever applies).

### Default day rules (same as existing logic):
| Day        | Default Status |
|------------|----------------|
| Mon–Thu    | `1` (Work Day) |
| Fri        | `0` (Day Off)  |
| Sat        | `0` (Day Off)  |
| Sun        | `1` (Work Day) ← *adjust if needed per school* |

### Files to Modify

#### Backend
- **`app/Http/Controllers/YearSemesterCalendarController.php`**
  - Method: `storeYear()`
  - After creating the `AcademicYear` and its 4 semesters, call a new private method `autoGenerateFullCalendar($year)`.
  - Generate all days from `July 1` of start-year to `June 30` of end-year (or actual `end_date`).
  - Use `semester_id` mapping — each day is assigned to the semester whose date range it falls within.
  - Days outside all semester ranges still get created, assigned to the nearest semester or left with `semester_id = null` (TBD).

#### Frontend
- **`resources/js/Pages/my_class/admin/year_semester_calendar/Index.vue`**
  - Update the success notification/message after year creation to mention that the calendar was auto-generated.
  - No UX change required — the calendar preview will just already have data.

---

## Pseudo-Logic for `autoGenerateFullCalendar($academicYear)`

```php
$start = Carbon::create($academicYear->start_date->year, 7, 1);
$end   = Carbon::create($academicYear->start_date->year + 1, 6, 30);

$current = $start->copy();
$weekNumber = 1;

while ($current <= $end) {
    $dayOfWeek = $current->dayOfWeek;    // 0=Sun, 6=Sat
    $dayNumber = $current->dayOfWeekIso; // 1=Mon, 7=Sun

    $status = in_array($dayOfWeek, [5, 6]) ? 0 : 1; // Fri/Sat = Off

    $semester = $academicYear->semesters->first(function ($s) use ($current) {
        return $current->between($s->start_date, $s->end_date);
    });

    Calendar::firstOrCreate(
        ['date' => $current->format('Y-m-d'), 'school_id' => $academicYear->school_id],
        [
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester?->id,
            'status'           => $status,
            'week_number'      => $weekNumber,
            'day_number'       => $dayNumber,
        ]
    );

    if ($dayOfWeek === 6) $weekNumber++; // Increment at end of Sat
    $current->addDay();
}
```

---

## Benefits

- **Zero manual steps** → Admin creates a year and immediately has a complete, editable calendar.
- **Consistent data** → No gaps. Every day from July to June exists in the DB.
- **Easy editing** → Admin can change individual day statuses, add events, or mark holidays after the fact.
- **Faster setup** → Especially useful at the start of a new school year.

---

## What Still Needs to be Done

- [ ] Implement `autoGenerateFullCalendar()` private method in `YearSemesterCalendarController`
- [ ] Call it inside `storeYear()` after semesters are created
- [ ] Determine handling for days that fall outside all semester date ranges (assign to closest semester, or leave `null`)
- [ ] Update success message in frontend after year creation
- [ ] Test: Create a new year and verify all ~365 days appear in the calendar preview
- [ ] Consider performance: bulk insert instead of one-by-one for large date ranges

---

## Files to Touch

| File | Change Type |
|------|-------------|
| `app/Http/Controllers/YearSemesterCalendarController.php` | MODIFY — add `autoGenerateFullCalendar()` + call in `storeYear()` |
| `resources/js/Pages/my_class/admin/year_semester_calendar/Index.vue` | MODIFY — update success messaging (minor) |

---

**End of History Document**
