# Documentation for Timeline v2.3 Page

## Overview

The Timeline v2.3 page is part of the teacher's schedule management interface in the MyClass8 application. It provides a detailed weekly timeline view of a teacher's class schedule, allowing teachers to view, manage, and record activities for each class period.

This page is implemented in the Vue component located at:
`resources/js/Pages/my_class/teacher/schedule/timelinev2.3/timeline.vue`

It uses the Quasar Framework for UI components and integrates with backend APIs to fetch and save schedule and period activity data.

---

## Key Features

### Weekly Timeline View

- Displays a timeline of scheduled class periods for each day of the week.
- Shows time markers from 6:00 AM to 5:00 PM with hour and minute divisions.
- Highlights the current time with a red indicator line.
- Allows switching between days using tabs at the top.
- Displays current and upcoming events in fixed cards for quick reference.

### Event Cards

- Each scheduled period is represented as an event card positioned according to its start and end times.
- Event cards show the period label, title (subject), classroom, and time range.
- Break periods are visually distinguished with reduced opacity.
- Clicking on an event card opens the Period Activity Drawer for detailed management.

### Period Activity Drawer

- A dialog drawer component (`PeriodActivityDrawer.vue`) that opens when a period event is clicked.
- Displays detailed information about the selected period, including:
  - Subject and classroom
  - Date and time
  - Teacher presence and substitute teacher selection
  - Period status (Completed, Cancelled, Modified, Event Affected)
  - Lesson notes and improvement notes
  - Student records with attendance, behavior, homework, and participation details
- Allows creating a new period activity if none exists.
- Supports saving updates to period activity and student records.
- Provides navigation to a full student records view for the period.

### Data Management

- Fetches schedule data for the selected day from the backend API.
- Validates and normalizes event times to ensure correct display.
- Updates the timeline dynamically when the day tab changes.
- Period activity and student records are loaded and saved via API calls.
- Uses Inertia.js for routing and axios for HTTP requests.

### UI and UX

- Responsive design with smooth scrolling and transitions.
- Loading indicators during data fetches.
- Notifications for success and error states.
- Scroll-to-now button to quickly navigate to the current time on the timeline.

---

## Component Structure

- `timeline.vue`: Main page component managing the weekly timeline and event interactions.
- `PeriodActivityDrawer.vue`: Drawer component for detailed period activity management.
- `CurrentEventCard.vue`: Displays the current ongoing event.
- `UpcomingEventsCard.vue`: Displays upcoming events for the day.
- Other supporting components for student records and UI elements.

---

## Usage

Teachers access this page to:

- View their weekly class schedule in a timeline format.
- Monitor current and upcoming classes.
- Record attendance, lesson notes, and student behavior for each period.
- Manage substitute teachers and period statuses.
- Access detailed student records linked to each period.

---

## Notes

- The component relies on accurate schedule data from the backend.
- Time calculations assume periods occur between 6:00 AM and 5:00 PM.
- The drawer component is tightly integrated with the timeline for seamless user experience.

---

## Future Improvements

- Add support for custom time ranges and multiple schedules.
- Enhance student record editing with bulk actions.
- Integrate AI assistance features (e.g., OpenAiCaller_use component) more prominently.
- Improve accessibility and mobile responsiveness.

---

## File Locations

- Timeline page: `resources/js/Pages/my_class/teacher/schedule/timelinev2.3/timeline.vue`
- Period Activity Drawer: `resources/js/Pages/my_class/teacher/schedule/timelinev2.3/components/PeriodActivityDrawer.vue`
- Routes: To be confirmed in the routing configuration.
