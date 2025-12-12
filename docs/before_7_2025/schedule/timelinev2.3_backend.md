# Backend and Route Details for Timeline v2.3 Page

## Routes

- **GET /teacher/timeline**  
  Controller: `TeacherNewTimeLineController@index`  
  Description: Loads the timeline page view using Inertia.js.

- **POST /teacher/timeline/schedule**  
  Controller: `TeacherNewTimeLineController@getTeacherSchedule`  
  Description: Returns the teacher's schedule data for a given school and day code in JSON format.

## Backend Controller: TeacherNewTimeLineController

Located at: `app/Http/Controllers/Teacher/TeacherNewTimeLineController.php`

### Methods

- `index()`  
  Renders the timeline page component using Inertia.js.

- `getTeacherSchedule(Request $request)`  
  - Accepts `school_id` and optional `day_code` parameters.  
  - Retrieves the authenticated teacher based on the logged-in user.  
  - Fetches schedule timings for the school.  
  - Queries active schedules for the teacher filtered by the day code.  
  - Formats the schedule with timing details, handling break periods, scheduled periods, and free periods distinctly.  
  - Returns a JSON response containing the formatted schedule and timing information.  
  - Handles exceptions and returns error messages if schedule loading fails.

### Models Used

- `Teacher`  
- `Schedule`  
- `ScheduleTiming`

### Schedule Formatting Logic

- Maps schedule items by period code.  
- Iterates over timing slots for the requested day.  
- Creates event objects for breaks, scheduled periods, and free periods with appropriate properties such as title, classroom, time, colors, and type.  
- Sorts events by start time before returning.

---

This backend logic supports the frontend timeline page by providing structured schedule data for display and interaction.
