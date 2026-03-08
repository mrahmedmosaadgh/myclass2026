# Task: Rebuild MyTableSchedule as Vue Components

## 🎯 Objective
Rebuild the standalone PWA schedule (from `mytable/table/index.html`) as a modular Vue 3 component architecture inside `/Users/ahmedmosaad/Herd/myclass2026-main/resources/js/Pages/MicroComponentTest/mytable/MyTableSchedule`. 

## 🏗️ Architecture Plan

### Configuration Data
1. `schedule_timing.json` - JSON file defining the standard time slots (Periods 1-8).
2. `schedule_data.json` - JSON file defining the subjects and classes per day.

### Component Structure
All subcomponents will be moved to `components/` to keep the main component clean.
1. `MyTableSchedule.vue` (Main Wrapper)
   - Coordinates data fetching, state management (current time, view modes), and passes props down.
2. `components/ScheduleHeader.vue`
   - Clock, dynamic countdown, view toggle button, and notification permission button.
3. `components/ScheduleGrid.vue`
   - The main table layout.
4. `components/ScheduleRow.vue`
   - A single day row in the table, handling logic for highlighting the "active" day.
5. `components/ScheduleCell.vue`
   - Renders individual subject cells. Includes the visual progress fill and pulsing indicator line for active periods.

## ✨ Features to Port
- Live visual progress (animated background fill and pinpoint line).
- Floating countdown in the header.
- View filtering (Today vs Full Week).
- Dynamic highlighting of active class.
- Support for `schedule_timing.json` dynamic periods.

## 🛑 Pending Clarification (Socratic Gate)
- PWA/Notification behavior in a reusable component context.
- Hardcoded week days assumptions.
