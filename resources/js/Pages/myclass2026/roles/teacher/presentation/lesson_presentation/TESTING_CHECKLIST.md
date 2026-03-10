# Phase 6: Testing Checklist

## Database Tests

### ✓ Schema Verification
- [ ] Verify `lesson_student_progress` table exists
- [ ] Verify `lesson_practice_submissions` table exists
- [ ] Verify `lesson_presentations` has `order`, `quiz_id`, `is_active` columns
- [ ] Check all foreign key constraints work
- [ ] Test JSON field storage (practice_data, quiz_data, metadata)

### ✓ Model Tests
```php
// Test in tinker
$progress = LessonStudentProgress::create([...]);
$progress->calculateColorStatus(); // Should return correct color
$progress->canAccessQuiz(); // Should return true/false based on practice score
```

---

## Backend API Tests

### Teacher Actions

**1. Open Lesson**
```http
POST /lesson-presentation/progress/open
{
  "lesson_id": 1,
  "student_ids": [1, 2],
  "teacher_id": 1
}
```
- [ ] Creates progress records
- [ ] Sets status to 'opened'
- [ ] Sets color_status to 'light_blue'
- [ ] Records teacher_id and timestamp

**2. Lock Lesson**
```http
POST /lesson-presentation/progress/lock
{ "progress_id": 1 }
```
- [ ] Updates status to 'locked'
- [ ] Updates color_status to 'gray'

**3. Grade Practice**
```http
PUT /lesson-presentation/progress/{id}/practice-grade
{
  "score": 8,
  "feedback": "Good work!"
}
```
- [ ] Score ≥ 6: unlocks quiz
- [ ] Score < 6: keeps practice_pending
- [ ] Stores feedback in practice_data JSON

**4. Force Pass**
```http
POST /lesson-presentation/progress/force-pass
{ "progress_id": 1 }
```
- [ ] Sets force_passed = true
- [ ] Sets status = 'completed'
- [ ] Sets color_status = 'green'

**5. Grant Extra Attempt**
```http
POST /lesson-presentation/progress/grant-attempt
{ "progress_id": 1 }
```
- [ ] Increments extra_attempts_granted in quiz_data

**6. Reset Progress**
```http
POST /lesson-presentation/progress/reset
{ "progress_id": 1 }
```
- [ ] Deletes practice submissions
- [ ] Resets all progress fields
- [ ] Keeps opened_at and opened_by_teacher_id

### Student Actions

**7. Complete Learn**
```http
PUT /lesson-presentation/progress/{id}/learn-complete
```
- [ ] Sets learn_completed_at timestamp
- [ ] Updates status to 'practice_pending'

**8. Submit Practice (Upload)**
```http
POST /lesson-presentation/progress/{id}/practice-submit
Content-Type: multipart/form-data

submission_type: upload
file: [image]
```
- [ ] Stores file in storage/practice_submissions/
- [ ] Creates submission record
- [ ] Updates status to 'practice_submitted'
- [ ] Sets color_status to 'purple'

**9. Submit Practice (Drawing)**
```http
POST /lesson-presentation/progress/{id}/practice-submit

submission_type: drawing
drawing_data: [base64 PNG]
```
- [ ] Stores drawing_data
- [ ] Creates submission record
- [ ] Updates progress status

**10. Record Quiz Attempt**
```http
POST /lesson-presentation/progress/{id}/quiz-attempt
{
  "score": 85,
  "quiz_version": 1
}
```
- [ ] Increments quiz_attempts
- [ ] Updates quiz_best_score if higher
- [ ] Score ≥ 80: sets quiz_passed = true
- [ ] Updates color based on attempts (green/yellow/dark_yellow/orange)
- [ ] Attempts ≥ 3 and failed: color = red

---

## Frontend Component Tests

### PracticeSubmission.vue

**Upload Tab:**
- [ ] File picker accepts images only
- [ ] Shows preview after selection
- [ ] Validates file size (5MB max)
- [ ] Submit button disabled when no file selected
- [ ] Shows success notification on submit
- [ ] Shows error notification on failure

**Drawing Tab:**
- [ ] Canvas initializes with white background
- [ ] Pen tool draws correctly
- [ ] Eraser tool removes drawings
- [ ] Color selection works (6 colors)
- [ ] Pen size adjustment works (1-10)
- [ ] Clear button resets canvas
- [ ] Touch events work on mobile/tablet
- [ ] Submit button disabled when canvas is blank
- [ ] Converts canvas to base64 PNG correctly

### StudentLessonList.vue

**Display:**
- [ ] Shows all lessons for grade/subject
- [ ] Displays correct progress percentage
- [ ] Color-coded cards match color_status
- [ ] Lock overlay shows for locked lessons
- [ ] Status badges show correct labels

**Stage Indicators:**
- [ ] Learn: shows completed/active/locked correctly
- [ ] Practice: shows completed/active/available/locked
- [ ] Quiz: shows completed/active/available/locked

**Action Buttons:**
- [ ] "Locked" - disabled, gray
- [ ] "Start Learning" - enabled, primary
- [ ] "Submit Practice" - enabled, primary
- [ ] "Waiting for Teacher" - disabled, gray
- [ ] "Take Quiz" - enabled, primary
- [ ] "Review" - enabled, positive

### TeacherProgressDashboard.vue

**Table Display:**
- [ ] Shows all students for lesson
- [ ] Status badges colored correctly
- [ ] Color indicators show correct hex colors
- [ ] Learn checkmarks show correctly
- [ ] Practice scores display (X/10)
- [ ] Quiz attempts and best scores show

**Grading Interface:**
- [ ] Opens dialog when "Grade" clicked
- [ ] Displays uploaded image correctly
- [ ] Displays drawing correctly
- [ ] Score input validates (0-10)
- [ ] Feedback textarea has 500 char limit
- [ ] Submit button disabled for invalid scores
- [ ] Shows loading state while grading

**Teacher Actions:**
- [ ] Open/Lock toggles correctly
- [ ] View Practice shows submission
- [ ] Grant Attempt increments count
- [ ] Force Pass confirms and updates
- [ ] Reset Progress confirms and clears
- [ ] Open for All works for all students

---

## End-to-End Flow Tests

### Test Scenario 1: Happy Path (Pass on 1st Attempt)

1. **Teacher:**
   - [ ] Opens lesson for student
   - [ ] Verifies student sees lesson as "Ready"

2. **Student:**
   - [ ] Clicks "Start Learning"
   - [ ] Views all slides
   - [ ] Clicks "Complete Learn" on last slide
   - [ ] Sees practice dialog open

3. **Student - Practice:**
   - [ ] Uploads image OR draws solution
   - [ ] Submits practice
   - [ ] Sees "Waiting for teacher" message

4. **Teacher:**
   - [ ] Sees "Grade" button for student
   - [ ] Opens grading dialog
   - [ ] Views practice submission
   - [ ] Gives score ≥ 6 (e.g., 8/10)
   - [ ] Adds feedback
   - [ ] Submits grade

5. **Student:**
   - [ ] Sees "Take Quiz" button
   - [ ] Takes quiz (mock)
   - [ ] Scores ≥ 80%
   - [ ] Lesson card turns GREEN
   - [ ] Status shows "Completed"

### Test Scenario 2: Failed Attempts → Force Pass

1. **Student:**
   - [ ] Completes learn
   - [ ] Submits practice (score 7/10)
   - [ ] Takes quiz 3 times, all < 80%
   - [ ] Lesson card turns RED
   - [ ] Status shows "Failed"

2. **Teacher:**
   - [ ] Sees red status
   - [ ] Clicks "Force Pass"
   - [ ] Confirms action

3. **Verify:**
   - [ ] Lesson card turns GREEN
   - [ ] Status shows "Completed"
   - [ ] force_passed = true in database

### Test Scenario 3: Low Practice Score → Resubmit

1. **Student:**
   - [ ] Submits practice

2. **Teacher:**
   - [ ] Grades with score < 6 (e.g., 4/10)

3. **Student:**
   - [ ] Still sees "Submit Practice" button
   - [ ] Quiz button NOT visible
   - [ ] Can resubmit practice

4. **Teacher:**
   - [ ] Grades resubmission with score ≥ 6

5. **Verify:**
   - [ ] Quiz unlocks for student

### Test Scenario 4: Extra Attempts

1. **Student:**
   - [ ] Fails quiz 3 times
   - [ ] Lesson turns RED

2. **Teacher:**
   - [ ] Grants extra attempt

3. **Student:**
   - [ ] Can take 4th quiz attempt
   - [ ] Passes with ≥ 80%
   - [ ] Lesson turns ORANGE

### Test Scenario 5: Reset Progress

1. **Teacher:**
   - [ ] Clicks "Reset Progress"
   - [ ] Confirms action

2. **Verify:**
   - [ ] All progress cleared
   - [ ] Practice submissions deleted
   - [ ] Lesson returns to "Opened" state
   - [ ] Student can start over

---

## Color Status Verification

Test that color changes correctly:

| Attempts | Score | Expected Color |
|----------|-------|----------------|
| 0 | - | light_blue (opened) |
| 0 | - | blue (learning) |
| 0 | - | purple (practice submitted) |
| 1 | ≥80% | green |
| 2 | ≥80% | yellow |
| 3 | ≥80% | dark_yellow |
| 4 | ≥80% | orange |
| 3 | <80% | red |

---

## File Upload Tests

**Image Upload:**
- [ ] JPG files upload correctly
- [ ] PNG files upload correctly
- [ ] GIF files upload correctly
- [ ] Files > 5MB are rejected
- [ ] Non-image files are rejected
- [ ] Files stored in correct directory structure
- [ ] File URLs are accessible

**Drawing Pad:**
- [ ] Canvas saves as PNG
- [ ] Base64 data is valid
- [ ] Drawing data stores in database
- [ ] Drawing displays correctly when viewed

---

## Edge Cases

- [ ] Student tries to submit practice before completing learn
- [ ] Student tries to take quiz before practice is graded
- [ ] Student tries to take quiz with practice score < 6
- [ ] Teacher tries to grade already-graded practice
- [ ] Multiple teachers grade same submission
- [ ] Student submits practice multiple times
- [ ] Network error during submission
- [ ] Large file upload timeout
- [ ] Canvas drawing with no strokes
- [ ] Empty feedback text
- [ ] Score exactly 6 (boundary test)
- [ ] Score exactly 80% (boundary test)

---

## Performance Tests

- [ ] Load 100+ students in teacher dashboard
- [ ] Load 50+ lessons in student list
- [ ] Upload 5MB image (max size)
- [ ] Complex drawing with many strokes
- [ ] Rapid quiz attempts
- [ ] Concurrent grading by multiple teachers

---

## Browser Compatibility

Test in:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

---

## Accessibility

- [ ] Keyboard navigation works
- [ ] Screen reader announces status changes
- [ ] Color indicators have text labels
- [ ] Form inputs have proper labels
- [ ] Error messages are clear

---

## Security

- [ ] Teacher can only grade their own lessons
- [ ] Student can only access their own progress
- [ ] File uploads are validated server-side
- [ ] SQL injection prevention
- [ ] XSS prevention in feedback text
- [ ] CSRF tokens on all POST requests

---

## Final Checklist

- [ ] All database migrations run successfully
- [ ] All routes are accessible
- [ ] All components render without errors
- [ ] No console errors in browser
- [ ] No PHP errors in logs
- [ ] File storage directory has correct permissions
- [ ] All notifications display correctly
- [ ] Loading states work properly
- [ ] Error handling is graceful
- [ ] Success messages are clear

---

## Known Limitations

1. Quiz system not implemented (will integrate later)
2. Student list in teacher dashboard uses progress data (needs classroom integration)
3. No real-time updates (requires WebSockets/polling)
4. No email notifications for grading
5. No progress analytics/reports
