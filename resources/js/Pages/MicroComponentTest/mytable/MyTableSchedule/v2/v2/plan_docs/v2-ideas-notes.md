# V2 Ideas Notes

This document collects practical ideas to improve the standalone Schedule App V2 in future iterations.

## Product Ideas

- **Unified home screen**
  Add a landing dashboard that lets the user enter directly into:
  - personal schedule
  - school timetable
  - timing settings
  - data manager

- **Stage-aware quick switch**
  Add a sticky top control that switches stage and day without reopening selectors.

- **Saved presets**
  Let admin save timing presets like:
  - normal day
  - exam day
  - Ramadan day
  - short day

- **Teacher search and filter**
  Add search by:
  - teacher name
  - class name
  - subject
  - free teachers now

- **Today snapshot**
  Create a compact mobile widget showing:
  - current period
  - next period
  - who is free now
  - active stage/day

## School Timetable Ideas

- **Conflict detection**
  Highlight if:
  - same teacher appears in two classes in the same period
  - same class appears twice in one period
  - missing subject assignment exists

- **Free teacher mode**
  Add a mode that only shows teachers who are free in the selected period.

- **Period occupancy summary**
  Show per period stats:
  - busy teachers count
  - free teachers count
  - total active classes

- **Class-first view**
  Add another reusable layout where rows are classes instead of teachers.

- **Print-friendly mode**
  Add a simplified black/white print layout for school admin use.

## Mobile UX Ideas

- **Thumb-friendly bottom actions**
  Add a floating bottom sheet with quick actions:
  - switch view
  - jump to today
  - export
  - import
  - open timing editor

- **Swipe between days and stages**
  Support:
  - horizontal swipe for day change
  - long swipe for stage change

- **Offline status card**
  Show a small indicator explaining:
  - using cached data
  - last sync time
  - pending local changes

- **Pinned current period card**
  Keep the active class card pinned at top while scrolling.

## Data and Import/Export Ideas

- **Structured export packs**
  Offer export by category:
  - personal schedule only
  - school timetable only
  - timings only
  - full backup

- **Import preview**
  Before importing, show:
  - detected file type
  - created date
  - item counts
  - overwrite impact

- **Versioned file schema**
  Keep a schema version for every exported JSON to support future upgrades safely.

- **Merge mode on import**
  Add import strategies:
  - replace all
  - merge missing only
  - overwrite selected sections

## Admin Ideas

- **Bulk timing editor**
  Add batch actions:
  - apply to all stages
  - apply to selected stages
  - apply to selected days
  - duplicate from another stage/day

- **Role split later**
  Prepare for future roles:
  - admin
  - teacher
  - read-only viewer

- **Audit trail locally**
  Track local changes like:
  - timing changed
  - file imported
  - stage/day override added

## Technical Ideas

- **Shared data store**
  Move V2 state into a dedicated composable/store so all views read the same source of truth.

- **Schema validators**
  Validate imported JSON before applying it.

- **Derived helpers**
  Add reusable helpers for:
  - active period detection
  - time overlap detection
  - teacher availability lookup
  - stage/day timing resolution

- **Better routing structure**
  Split standalone routes for future sections such as:
  - `/my-schedule-app/v2`
  - `/my-schedule-app/v2/school`
  - `/my-schedule-app/v2/settings`
  - `/my-schedule-app/v2/data`

## Suggested Next Priorities

1. Add conflict detection in school timetable
2. Add import preview + overwrite confirmation
3. Add teacher search/filter
4. Add timing preset templates
5. Add print-friendly mode

## Recent Implementation Summary

- **Mobile reachability fixes completed**
  Updated V2 mobile behavior so bottom actions and modal controls are easier to reach on phones.
  - added safer bottom spacing and safe-area padding in the standalone shell
  - improved modal bottom spacing on small screens
  - replaced the phone test-time native picker path with a simpler manual `HH:MM` entry fallback on touch devices

- **Shared admin timing bar added**
  A shared top admin timing control was added so the admin can quickly see and change the active context.
  - shows current date/time, selected day, and selected stage
  - includes quick day/stage switching and a `Today` action
  - day click can open timing editing directly

- **Persistent admin stage/day selection added**
  Admin selection now survives reloads and is shared across views.
  - selected stage and selected day are stored locally
  - Cards, Table, List, and School views now follow the shared selection better
  - school timing resolution is driven from the shared parent-level state

- **Shared timing editing flow added**
  The timing manager now works as part of the shared admin flow.
  - supports opening timing editing for the selected day
  - supports stage-level "same for all days" timing editing
  - timing overrides remain stored in local storage and update all relevant views

- **Import improvements completed**
  Data Manager import was expanded to make admin import easier from mobile and desktop.
  - added direct paste JSON import
  - admin must first choose what the data is for:
    - personal schedule
    - school timetable
    - timing only
    - app settings
  - added per-target validation before applying imported data
  - validation can run without mutating saved data
  - paste/import UI was made more mobile-friendly

- **Teacher/admin access improvements already in place**
  Earlier work also added teacher-specific access support and admin sharing helpers.
  - teacher-specific links can be copied/shared from the school timetable
  - teacher-only access hides the school-wide mode and derives personal schedule data from the master timetable
