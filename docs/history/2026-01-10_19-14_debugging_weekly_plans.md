# 2026-01-10 19:14 | Debugging Weekly Plans & RTL Localization

## 🌍 Localization & RTL Support
- **Weekly Plans Table**: Implemented dynamic column alignment based on locale. Columns now align right for Arabic ('ar') and left for English ('en').
- **Status Badges**: Localized status labels (Completed, Partial, Empty).
- **Weekly Plan Editor**: Fully localized form labels, dialog titles, and notification messages.
- **Simple Weekly Plans**: Localized tabs and day names. Used localized day names in filters.
- **RTL CSS**: Updated CSS to use logical properties (`border-inline-start`, `padding-inline-start`) for proper rendering in RTL mode.

## 🐛 Bug Fixes
- **Translation Keys**: Fixed a warning where `weeklyPlans.filters.day` was missing by updating usage in `WeeklyPlansTable.vue` to `weeklyPlans.teacher.filters.day`.
- **Runtime Error**: Resolved `TypeError: Cannot read properties of undefined (reading '1')` in `WeeklyPlanClassroomList.vue` by correctly defining and passing the `days` computed property from `MyWeeklyPlans.vue`. This ensures day names are correctly resolved during rendering.

## 🛠️ Components Modified
- `resources/js/Pages/my_table_mnger/weekly_system/teacher/WeeklyPlansTable.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/teacher/MyWeeklyPlans.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/teacher/SimpleWeeklyPlans.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/teacher/WeeklyPlanEditor.vue`
- `resources/js/Pages/my_table_mnger/weekly_system/components/shared/StatusBadge.vue`
- `resources/js/lang/en.js`
- `resources/js/lang/ar.js`
