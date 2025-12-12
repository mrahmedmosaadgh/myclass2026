# Quiz Selector Filter Fix

## Problem
The QuizSelector Vue component couldn't choose grade or subject because:
1. The backend was fetching school grades and subjects but not returning them in the API response
2. The Vue component had no UI elements for grade/subject filtering
3. The filter state wasn't being managed properly

## Solution

### Backend Changes (QuizController.php)

1. **Modified `index()` method** to return both quizzes and filter options:
   - Now returns structured response with `quizzes` and `filters` (grades/subjects)
   - Properly uses the authenticated teacher's school_id
   - Cleaned up variable naming for consistency

2. **Added `filterOptions()` method**:
   - New endpoint `/api/quizzes/filter-options`
   - Returns available grades and subjects for the authenticated teacher's school
   - Can be used independently for populating filter dropdowns

### Frontend Changes (QuizSelector.vue)

1. **Added Filter UI**:
   - Grade dropdown filter
   - Subject dropdown filter
   - Both filters show "All" option by default

2. **State Management**:
   - Added `selectedGradeId` and `selectedSubjectId` reactive refs
   - Filters are independent from props, allowing user selection
   - Watchers update quiz list when filters change

3. **API Integration**:
   - Updated `fetchQuizzes()` to handle new response structure
   - Fallback support for old response format (backward compatible)
   - Filters are passed as query parameters to the API

4. **Enhanced Quiz Creation**:
   - New quizzes use selected filters (or props as fallback)
   - Better integration with current filter state

### Routes Changes (api.php)

Added new route for filter options (must be before the `/{id}` route):
```php
Route::get('/filter-options', [QuizController::class, 'filterOptions']);
```

## API Response Format

### GET /api/quizzes
**Before:**
```json
[
  { "id": 1, "name": "Quiz 1", ... }
]
```

**After:**
```json
{
  "quizzes": [
    { "id": 1, "name": "Quiz 1", ... }
  ],
  "filters": {
    "grades": [
      { "id": 1, "name": "Grade 1" }
    ],
    "subjects": [
      { "id": 1, "name": "Math" }
    ]
  }
}
```

### GET /api/quizzes/filter-options
**New endpoint:**
```json
{
  "grades": [
    { "id": 1, "name": "Grade 1" }
  ],
  "subjects": [
    { "id": 1, "name": "Math" }
  ]
}
```

## Usage

The QuizSelector component now provides:
1. Grade filter dropdown - filters quizzes by grade
2. Subject filter dropdown - filters quizzes by subject
3. Quiz selector - shows filtered quizzes
4. Create new quiz button - creates quiz with current filters applied

All filters work independently and update the quiz list in real-time.

## Additional Fix: Quiz Statistics Always Showing Zero

### Problem
The QuizDashboard statistics were always showing zero because:
1. The API was defaulting to only return `active` quizzes when no status parameter was provided
2. The QuizDashboard wasn't passing any status parameter, so draft quizzes were being filtered out
3. If all quizzes were in draft status, the statistics would show zero

### Solution
1. Updated QuizDashboard to pass `status: 'all'` by default to show all quizzes
2. Updated QuizController to handle `status: 'all'` and not apply any status filter
3. Users can still filter by specific status using the status dropdown

## Testing

To test the fix:
1. Open a page with QuizSelector component
2. Select a grade from the dropdown - quiz list should update
3. Select a subject from the dropdown - quiz list should update further
4. Create a new quiz - it should inherit the selected grade/subject
5. Clear filters (select "All") - should show all quizzes

To test the statistics fix:
1. Open QuizDashboard page
2. Statistics should now show correct counts for all quizzes (draft, active, archived)
3. Use the status filter to filter by specific status
4. Statistics should update accordingly

## Additional Fix: Missing Questions API Route

### Problem
The `/api/questions` route was not accessible because questions routes were only defined under the `/api/quiz/questions` prefix.

### Solution
Added questions routes at the root API level (`/api/questions`) in addition to the quiz-prefixed routes for better accessibility and consistency with other API endpoints.

### Available Question Endpoints
- `GET /api/questions` - List all questions with filters
- `POST /api/questions` - Create a new question
- `GET /api/questions/{id}` - Get a specific question
- `PUT /api/questions/{id}` - Update a question
- `DELETE /api/questions/{id}` - Delete a question
- `POST /api/questions/import` - Import questions from file

## Files Modified

- `app/Http/Controllers/QuizController.php` - Updated index() method to return structured response with filters and handle 'all' status
- `resources/js/Pages/my_table_mnger/lesson_presentation/components/QuizSelector.vue` - Added grade/subject filters
- `resources/js/Pages/QuizManagement/QuizDashboard.vue` - Updated to handle new API response structure and pass status='all'
- `resources/js/Pages/quiz_management/QuizManager.vue` - Updated to handle new API response structure
- `routes/api.php` - Added filter-options route and questions routes at root level

## Breaking Change Note

The `/api/quizzes` endpoint now returns a structured response instead of a flat array. All Vue components that consume this endpoint have been updated to handle both the new and old response formats for backward compatibility.

**Old Response:**
```json
[{ "id": 1, "name": "Quiz 1", ... }]
```

**New Response:**
```json
{
  "quizzes": [{ "id": 1, "name": "Quiz 1", ... }],
  "filters": {
    "grades": [...],
    "subjects": [...]
  }
}
```
