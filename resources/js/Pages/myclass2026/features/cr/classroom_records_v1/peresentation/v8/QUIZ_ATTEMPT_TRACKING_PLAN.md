# Quiz Attempt Tracking & Student Presentation Route Plan

**Date:** 2026-05-15  
**Feature:** Save quiz attempts, display statistics, create student-only presentation route

---

## Requirements

1. **Quiz Attempt Tracking**
   - Save every quiz attempt with score in the presentation
   - Store attempt data (timestamp, score, answers, duration)
   - Persist across sessions

2. **Attempt History Display**
   - List of all attempts with scores
   - View individual attempt details
   - Filter/sort by date or score

3. **Statistics Summary**
   - Overall average score
   - Highest score
   - Lowest score
   - Total attempts count
   - Score trend visualization

4. **Student-Only Route**
   - Create route `/classroom-records/presentation/student/{id}`
   - Read-only presentation view (no editing tools)
   - Quiz functionality enabled
   - Attempt tracking enabled

---

## Implementation Strategy

### Part 1: Data Structure Enhancement

**Add to presentationStore.js:**
- `quizAttempts` array to store all attempts
- Each attempt structure:
  ```javascript
  {
    id: 'attempt-1234567890',
    timestamp: '2026-05-15T12:00:00Z',
    quizId: 'quiz-v2-xxx',
    quizTitle: 'Quiz Title',
    score: 80,
    totalQuestions: 10,
    correctCount: 8,
    wrongCount: 2,
    answers: { q1: 'a', q2: 'b', ... },
    duration: 120, // seconds
    timeRemaining: 0 // if timer was used
  }
  ```

**Add actions:**
- `saveQuizAttempt(attemptData)` - Save attempt to history
- `getQuizAttempts(quizId)` - Get attempts for specific quiz
- `getQuizStatistics(quizId)` - Calculate stats (avg, high, low)
- `clearQuizAttempts(quizId)` - Clear attempts for specific quiz

### Part 2: Attempt History Component

**Create component:** `QuizAttemptHistory.vue`
- Display list of attempts in a table/card format
- Show: date, score, correct/incorrect, duration
- Click to view detailed answers
- Delete individual attempts
- Export attempts to CSV/JSON

**Location:** `/components/quiz-v2/QuizAttemptHistory.vue`

### Part 3: Statistics Summary Component

**Create component:** `QuizStatisticsSummary.vue`
- Display overall statistics cards
- Average score with progress bar
- High score badge
- Low score indicator
- Total attempts counter
- Score trend chart (simple line or bar)

**Location:** `/components/quiz-v2/QuizStatisticsSummary.vue`

### Part 4: Student Presentation Route

**Backend (Laravel):**
- Add route in `/routes/myclass2026/cr/web.php`:
  ```php
  Route::get('/student/{presentationId}', function ($presentationId) {
      return Inertia::render(
          'myclass2026/features/cr/classroom_records_v1/peresentation/v8/StudentPresentation',
          [
              'presentationId' => $presentationId,
              'mode' => 'student',
              'title' => 'Student Presentation - MyClass2026'
          ]
      );
  })->name('student-presentation');
  ```

**Frontend Component:** `StudentPresentation.vue`
- Read-only version of Index.vue
- Hide editing tools (toolbar, element handles, context menu)
- Enable quiz functionality
- Enable attempt tracking
- Clean, student-focused UI
- Share URL functionality

**Location:** `/resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v8/StudentPresentation.vue`

### Part 5: Integration with QuizElementV2

**Modify QuizElementV2.vue:**
- Call `saveQuizAttempt()` when quiz completes
- Calculate score and duration
- Save attempt data with timestamp
- Show attempt history button in results view
- Link to statistics summary

---

## Files to Create/Modify

### New Files
1. `/resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v8/components/quiz-v2/QuizAttemptHistory.vue`
2. `/resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v8/components/quiz-v2/QuizStatisticsSummary.vue`
3. `/resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v8/StudentPresentation.vue`

### Modified Files
1. `/routes/myclass2026/cr/web.php` - Add student route
2. `/resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v8/stores/presentationStore.js` - Add attempt tracking
3. `/resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v8/components/quiz-v2/QuizElementV2.vue` - Save attempts on completion

---

## Testing Checklist

- [ ] Quiz attempts are saved when quiz completes
- [ ] Attempt history displays correctly
- [ ] Statistics calculate accurately (avg, high, low)
- [ ] Student route is accessible
- [ ] Student view is read-only (no editing)
- [ ] Quiz functionality works in student view
- [ ] Attempts persist across page reloads
- [ ] Can delete individual attempts
- [ ] Can export attempts data
