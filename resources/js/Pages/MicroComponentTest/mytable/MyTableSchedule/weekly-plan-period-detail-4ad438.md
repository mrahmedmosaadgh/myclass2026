# Weekly Class Editor + Table Popup

Add a new **Weekly** menu section where the teacher edits classwork, homework, resource links, and notes for each class and week, then show that saved data when a matching cell is clicked in `TableViewV2`.

## Target UX

- **Menu → Weekly** opens a weekly editor section inside the slide menu.
- The class list is built from the current schedule's unique non-empty class names, e.g. `7A`, `5B`, `4A`, `5A`.
- The teacher picks a week, then picks a class.
- Each week keeps:
  - immutable week ID like `2026-W14`
  - one shared editable week title like `Week Before Exam`
- For the selected class, show only that class's scheduled slots from the current weekly timetable, grouped by day/period, e.g.:
  - Sunday → Period 1
  - Monday → Period 1, Period 7
  - Tuesday → Period 7
- Each slot has fixed editable fields:
  - `CW`
  - `CW Pages`
  - `HW`
  - `HW Pages`
  - `Presentation Link`
  - `Material Link`
  - `Notes` (rich text with **safe/sanitized HTML rendering**)
- In `TableViewV2`, clicking a non-empty cell such as `d1 / p1 / 7A` opens a popup that displays the saved weekly entry for that class/day/period/week.
- In that popup:
  - `Presentation Link` is clickable
  - clicking it opens a **fullscreen presentation dialog/viewer shell**
  - `Material Link` is also clickable
  - `Notes` are rendered as sanitized rich content
- The weekly editor supports JSON import by either:
  - selected class only
  - all classes for the selected week
- Import input should support both:
  - paste JSON
  - open JSON file

## Proposed Data Shape

Store weekly plan data by **week → class → dayId → periodId** using fixed fields rather than free-form rows.

```json
{
  "2026-W14": {
    "meta": {
      "title": "Week Before Exam"
    },
    "classes": {
      "7A": {
        "d1": {
          "1": {
            "cw": "Topic 7 Topic Review",
            "cwPages": "419-421",
            "hw": "Student Book: Topic 7 Topic Review",
            "hwPages": "417-418",
            "presentationLink": "https://example.com/presentation/grade7-topic7",
            "materialLink": "https://example.com/materials/topic7-review.pdf",
            "notesHtml": "<p><strong>Focus:</strong> Review all Topic 7 skills.</p><ul><li>Warm-up questions</li><li>Quick correction</li></ul>"
          }
        },
        "d2": {
          "1": {
            "cw": "Fractions practice",
            "cwPages": "422-423",
            "hw": "Workbook exercises",
            "hwPages": "424",
            "presentationLink": "",
            "materialLink": "",
            "notesHtml": ""
          }
        }
      }
    }
  }
}
```

Notes:

- `d1..d6` matches the app's existing day IDs.
- `1`, `2`, `b1`, etc. match the existing timing slot IDs.
- `meta.title` is the shared editable week name, while `2026-W14` remains the unique week ID.
- This shape is simple for both form editing and lookup from `TableViewV2`.
- Storage can use existing `saveSetting('weeklyPlans', data)` / `getSetting('weeklyPlans')`, so no IndexedDB schema change is required.
- `notesHtml` is stored as rich text content but should be sanitized before rendering.

## Import JSON Shapes

### 1. Selected class import

```json
{
  "week": "2026-W14",
  "weekTitle": "Week Before Exam",
  "class": "7A",
  "days": {
    "d1": {
      "1": {
        "cw": "Topic 7 Topic Review",
        "cwPages": "419-421",
        "hw": "Student Book: Topic 7 Topic Review",
        "hwPages": "417-418",
        "presentationLink": "https://example.com/presentation/grade7-topic7",
        "materialLink": "https://example.com/materials/topic7-review.pdf",
        "notesHtml": "<p>Review lesson intro and common mistakes.</p>"
      }
    }
  }
}
```

### 2. All-classes import

```json
{
  "week": "2026-W14",
  "weekTitle": "Week Before Exam",
  "classes": {
    "7A": {
      "d1": {
        "1": {
          "cw": "Topic 7 Topic Review",
          "cwPages": "419-421",
          "hw": "Student Book: Topic 7 Topic Review",
          "hwPages": "417-418",
          "presentationLink": "https://example.com/presentation/grade7-topic7",
          "materialLink": "https://example.com/materials/topic7-review.pdf",
          "notesHtml": "<p>Review lesson intro and common mistakes.</p>"
        }
      }
    },
    "5B": {
      "d1": {
        "5": {
          "cw": "Decimals review",
          "cwPages": "310-312",
          "hw": "Exercises 1-4",
          "hwPages": "313",
          "presentationLink": "",
          "materialLink": "https://example.com/materials/decimals-review.pdf",
          "notesHtml": "<p>Let students solve Q1-Q3 first.</p>"
        }
      }
    }
  }
}
```

## Files to Create / Modify

### 1. `components/SlideMenu.vue`
- Add a new `weekly` menu item.
- Render a new `MenuWeeklyPlan.vue` section.

### 2. `components/menu/MenuWeeklyPlan.vue` (new)
Main weekly editing UI.

Responsibilities:

- week picker / week navigator
- editable week title field tied to the selected week ID
- class list derived from current schedule data
- selected class editor showing only that class's scheduled periods
- fixed inputs for:
  - `CW`
  - `CW Pages`
  - `HW`
  - `HW Pages`
  - `Presentation Link`
  - `Material Link`
  - `Notes` rich text editor / HTML input area
- import tools:
  - paste JSON
  - open JSON file
  - import for selected class
  - import for all classes in the selected week
- validation/sanitization path for `notesHtml`

### 3. `composables/useAppStore.js`
- Add `weeklyPlans` reactive state.
- Load from `getSetting('weeklyPlans')`, fallback to JSON seed if desired.
- Add mutations/helpers such as:
  - `setWeeklyPlans(data)`
  - `setWeekTitle(weekKey, title)`
  - `updateWeeklyPlanEntry(weekKey, className, dayId, periodId, payload)`
  - `getWeeklyPlanEntry(weekKey, className, dayId, periodId)`
  - `getWeekTitle(weekKey)`
  - `getWeekKey(date)`
  - `getScheduleClasses()` → unique class list from current schedule
  - `getScheduledSlotsForClass(className)` → all matching day/period slots from current schedule

### 4. `components/views/TableViewV2.vue`
- Add click handling for non-empty cells.
- Resolve clicked cell to:
  - current week key
  - class name from `getSubject(slot.id, day)`
  - `dayId`
  - `periodId`
- Open a popup/dialog showing the saved weekly plan fields for that exact slot.
- Add actions inside the popup for:
  - open presentation fullscreen viewer
  - open material link

### 5. `components/views/WeeklyPlanDetailDialog.vue` (new)
Read-focused popup for cell click.

Display:

- header: `7A · Period 1 · Sunday, Apr 5, 2026`
- shared week title shown near the header when available
- `CW`
- `CW Pages`
- `HW`
- `HW Pages`
- `Presentation Link`
- `Material Link`
- `Notes` rendered as sanitized HTML
- empty state if nothing is saved yet
- optional shortcut button: `Edit in Weekly Menu`

### 6. `components/views/PresentationViewerDialog.vue` (new)
Fullscreen dialog/viewer shell opened from `presentationLink`.

Initial scope:

- fullscreen overlay
- header with close action
- iframe / embedded page container for the presentation URL
- fallback empty/error state if link is missing or blocked
- keep this simple now so presentation management can be expanded later

### 7. `data/weekly_plan_data.json` (optional seed)
Small sample dataset for current classes to make the feature visible on first run.

## Interaction Flow

```
Menu → Weekly
  → choose week
  → edit shared week title if needed
  → choose class (e.g. 7A)
  → see all scheduled slots for that class this week
  → type CW / CW Pages / HW / HW Pages / links / notes
  → auto-save or explicit save to IndexedDB app settings

TableViewV2 click on non-empty cell
  → derive class/day/period/week
  → open popup
  → show saved weekly entry for that exact slot and the shared week title
  → click Presentation Link to open fullscreen viewer
```

## Suggested Implementation Order

1. Add weekly plan state and helpers to `useAppStore.js`
2. Add `weekly` section to `SlideMenu.vue`
3. Build `MenuWeeklyPlan.vue` with week picker, class list, fixed field editor, links, notes, and import UI
4. Add JSON import validation for selected-class and all-classes formats
5. Add sanitization/rendering path for `notesHtml`
6. Add cell click popup in `TableViewV2.vue`
7. Add fullscreen `PresentationViewerDialog.vue` shell
8. Add optional seed JSON for demo / first-run visibility
