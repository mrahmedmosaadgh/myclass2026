 C:\Users\me_5_2025\AppData\Roaming\Code\User\workspaceStorage\09b73f2f2477a56e7f14f2f0adac130d\Augment.vscode-augment
# Preferences
- The user prefers to use Quasar framework for UI components.
- The user prefers not to use QLayout and q-page components from Quasar.
- The user prefers not to use AppLayout with title prop and instead use Head component for page titles, as AppLayout is globally defined.
- The user prefers to configure axios once globally rather than adding headers to individual requests.
- The user prefers not to persist menu open/close state in localStorage and only wants the menu to close after clicking or by clicking outside.
- The user prefers to use direct icon names in MenuConfig files rather than relying on icon mapping in SidebarMenu.vue.
- The user prefers the DateTimeDisplay component to be visually prominent at the top of the page with a toggle icon to hide/show it, and wants this state saved in localStorage.
- The user prefers the DateTimeDisplay component to have a clickable time display that toggles between 24-hour and AM/PM time formats.
- The user prefers the DateTimeDisplay component to have a mode switch that toggles between the current design and a simple, single line format showing time, day, and date (e.g., '8:15 PM Sunday 18 May 2025') positioned at the top center.
- The user prefers news ticker components to animate from top to center, display for 5 seconds, fade down, and support an array of objects with title and details properties.
- The user prefers to implement dark mode using Quasar's built-in dark plugin as documented at quasar.dev/quasar-plugins/dark.
- The user prefers simple, creative UI designs using Quasar components with flag icons.

# Quasar Framework
- The user prefers to use Quasar components.
- QDrawer components from Quasar framework must be children of QLayout components.

# Data Structures
- period_activities is a copy of schedules with additional fields like teacher_substitute_id, period case status, and teacher notes; records are created when a teacher enters a period or edits data.
- student_period_records work the same way as period_activities but for individual students in a classroom, allowing teachers to record student activities.
- The 'teacher_plan' field is defined as an array that contains an object with properties: lesson_id, lesson_number, title, and page_number.

# Laravel
- In Laravel 12, Kernel.php files have been removed and middleware configurations are now handled in bootstrap/app.php instead.

# JavaScript
- When using localStorage.getItem(), the returned value is always a string, so comparisons should be done with string values (e.g., 'true' not true).