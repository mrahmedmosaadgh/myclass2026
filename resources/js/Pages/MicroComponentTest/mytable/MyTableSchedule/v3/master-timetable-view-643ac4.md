# Master Timetable View — School Stage × Day × Teacher Grid

A new installable-ready component added to the Schedule App V2 that shows a full school timetable (all teachers × all periods) for a selected stage and day, with flexible per-stage and per-day timing settings.

---

## What We're Building

A new view component: **`MasterTimetableView.vue`** (added alongside the 3 existing views in the previous v2 plan).

| Feature | Detail |
|---|---|
| Stage selector | Primary / Middle / Secondary |
| Day selector | D1 → D6 (Sun–Thu or custom labels) |
| Table rows | Teachers list |
| Table columns | Periods (based on timing settings) |
| Cell content | Classroom + Subject (e.g. `7A · Math`) |
| Timing | Per-stage, per-day — default: all the same |
| Data source | Local JSON (offline default) + optional backend API |

---

## File Structure (inside `/v2/`)

```
v2/
├── components/
│   ├── MasterTimetableView.vue     ← NEW main timetable component
│   ├── StageSelector.vue           ← NEW reusable stage tabs
│   ├── DaySelector.vue             ← NEW reusable day tabs
│   └── StageDayTimingManager.vue   ← NEW timing settings (stage × day)
├── composables/
│   └── useSchoolTimetable.js       ← NEW data + timing logic
├── data/
│   ├── master_timetable_data.json  ← NEW teachers × periods data
│   └── stage_day_timings.json      ← NEW timing per stage per day
```

---

## Data Structures

### `master_timetable_data.json`
```json
{
  "stages": {
    "prim": {
      "label": "Primary",
      "days": {
        "d1": {
          "teachers": [
            {
              "id": "t1",
              "name": "Ahmed Ali",
              "periods": {
                "1": { "class": "5A", "subject": "Math" },
                "2": { "class": "4B", "subject": "Math" },
                "b1": null,
                "3": null,
                "4": { "class": "6A", "subject": "Math" }
              }
            }
          ]
        }
      }
    },
    "middle": { ... },
    "sec": { ... }
  }
}
```

### `stage_day_timings.json`
```json
{
  "default": [ ...same as existing schedule_timing.json... ],
  "overrides": {
    "prim": {
      "default": null,
      "days": {
        "d1": null,
        "d2": [ ...custom timing... ]
      }
    }
  }
}
```

**Timing resolution order (priority):**
1. Stage + Day specific override (most specific)
2. Stage default override
3. Global default (fallback)

---

## Component Breakdown

### 1. `StageSelector.vue`
- Segmented tab control: **Primary | Middle | Secondary**
- Pill-style on mobile, full-width
- Emits `stage-change` event
- Highlights active stage

### 2. `DaySelector.vue`
- Horizontal scrollable day tabs: **D1 D2 D3 D4 D5 D6**
- Highlights today automatically
- Swipeable on mobile
- Emits `day-change` event

### 3. `StageDayTimingManager.vue`
The key admin tool with two modes:

**Mode A — Global (default):** Edit one timing list → applies to all stages & days
```
[ Apply to: ALL stages, ALL days ] (toggle)
```
**Mode B — Custom overrides:**
- First pick: stage (or "all stages")
- Then pick: day (or "all days for this stage")
- Edit timing for that selection
- Clear/reset to default option

Interface:
- Bottom sheet modal on mobile
- Tabbed layout: Global | Override Per Stage | Override Per Day
- Shows which stages/days have custom timings (badge indicator)
- One-click "Reset to global" per stage or day

### 4. `MasterTimetableView.vue` (main component)
- Horizontal scrollable table
- Sticky first column (teacher names)
- Period columns use resolved timing for selected stage + day
- Cells: `ClassName · Subject` or `—` if free
- Active period highlighted in real-time
- Loading skeleton while data fetches
- Offline fallback to JSON
- Empty state for free teachers

### 5. `useSchoolTimetable.js` (composable)
Handles:
- Loading from local JSON by default
- Optional fetch from backend API (`/api/school-timetable?stage=&day=`)
- Timing resolution logic (stage → day priority)
- Caching resolved data in localStorage
- Exposing: `teachers`, `resolvedPeriods`, `isLoading`, `error`

---

## Timing Settings — User Flow

```
Admin opens Timing Settings
       ↓
Default mode: Edit global timing (applies to all)
       ↓ (optional)
Toggle: "Customize per stage"
→ Select stage → Edit timing for that stage (all its days)
       ↓ (optional)
"Customize specific day"
→ Select stage + day → Edit timing for just that combination
```

Visual badge on DaySelector: shows "Custom ⚙" when a day has an override.

---

## Integration with V2 App

Add `MasterTimetableView` as a 4th view option in the `ViewModeSwitcher`:
- Icon: 🏫 or grid icon
- Label: **"School Table"**
- Works alongside Card / Table / List views

---

## Mobile UX Notes

- Stage/Day selectors use large touch targets (min 44px height)
- Table has horizontal momentum scroll on mobile
- Teacher column stays sticky on scroll
- Period header row stays sticky on scroll (sticky top)
- Cells have clear color: free = light gray, active = highlighted, break = blue tint
- Bottom sheet for timing manager (not a floating popup)

---

## Offline & Backend

- **Offline (default):** Reads from `master_timetable_data.json` + `stage_day_timings.json`
- **Online:** Fetches from `/api/school-timetable` if available, falls back to JSON
- All timing overrides saved in `localStorage` so admin edits survive offline

---

## What Is NOT Changing

- The existing 3 view modes (Card, Table, List) from the previous v2 plan are unchanged
- The original `schedule_timing.json` and `schedule_data.json` remain for the personal teacher view
- The `StandaloneScheduleAppV2.vue` just adds the new view to the switcher
