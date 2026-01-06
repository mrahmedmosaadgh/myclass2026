# 2026-01-06 11:11 | Remove Cards tab; keep Table only

## Overview
Changed the Weekly Plans page to show only the Table view. The Cards tab and its panel are removed from the UI (panel markup left commented for reference). Table is now the default active view. No backend/API changes.

## Key changes
- UI (Vue/Quasar): resources/js/Pages/my_table_mnger/weekly_system/teacher/SimpleWeeklyPlans.vue
  - Tabs: removed the “Cards” tab, kept only the “Table” tab visible.
  - Panels: commented out the Cards panel template so it’s not rendered.
  - Default state: set `activeTab` to `table` so the page lands on the table view by default.

## Technical details
- SimpleWeeklyPlans.vue
  - Tabs markup now contains only `<q-tab name="table" ... />`.
  - The old Cards panel block is wrapped in an HTML comment to preserve it for potential future reference, avoiding runtime rendering.
  - `activeTab` changed from `'cards'` to `'table'`.
  - No route, data, or API changes; all existing table actions (edit, copy/paste, print) remain intact.
  - Note: There may be unused imports or state that were only used by the Cards view (e.g., `StatusBadge`). These do not affect runtime but can be cleaned up later if desired.

