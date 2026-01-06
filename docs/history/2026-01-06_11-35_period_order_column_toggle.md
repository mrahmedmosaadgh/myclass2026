# 2026-01-06 11:35 | Show Period Order column only while editing

## Overview
Made the Period Order table column visible only when the "Period Order edit" toggle is enabled. Restored safe rendering in the Period Order cell so the numeric input appears only for rows with a valid schedule; otherwise a dash is shown.

## Key Changes
- WeeklyPlansTable.vue (QTable): Bind to computed `visibleColumns` instead of the static `columns`.
- Columns control: `visibleColumns` includes `periodOrder` only when `periodOrderEdit === true`.
- Cell template: Restored guard (`v-if="props.row.schedule?.id"`) and kept the numeric input only when editing is enabled.
- Behavior: When the toggle is OFF, the column is hidden; when ON, the column appears and allows inline editing.
- Grouped-by-classroom section: left unchanged.

## Technical Details
- File: `resources/js/Pages/my_table_mnger/weekly_system/teacher/WeeklyPlansTable.vue`
  - Added:
    - `const visibleColumns = computed(() => periodOrderEdit.value ? columns : columns.filter(c => c.name !== 'periodOrder'))`
    - `<q-table :columns="visibleColumns" ... />`
  - Updated Period Order cell slot `#body-cell-periodOrder`:
    - Guarded rendering with `v-if="props.row.schedule?.id"`.
    - Shows `<q-input type="number" ...>` when `periodOrderEdit` is true; shows read-only value or `-` otherwise.
  - Inline update method `onUpdatePeriodOrder(row)` remains responsible for PUT `/weekly-system/api/schedules/:id/period-order`.

No changes were made to print/export logic; previous "linkify and clickable in PDF" improvements remain in effect.

