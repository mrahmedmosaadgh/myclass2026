# Fix Exam Questions Loss on Save

## Issue
The user reported that after editing and saving an exam, the added questions were lost.

## Analysis
1.  **Frontend**: In `QuExamForm.vue`, the `form.question_ids` array was initialized to an empty array `[]` by default.
2.  **Watcher**: The watcher on `selectedQuestions` responsible for updating `form.question_ids` did not have `{ immediate: true }`.
3.  **Result**: When the component mounted with existing questions, `selectedQuestions` was populated correctly from props, but `form.question_ids` remained `[]`. If the user edited other fields (title, duration) and saved without modifying questions, the form submitted `question_ids: []`.
4.  **Backend**: The `QuExamController::update` method received `[]` (or empty) for `question_ids`. Due to `empty()` check or just `sync([])`, it detached all questions from the exam.

## Fix
*   **Modified `QuExamForm.vue`**: Added `immediate: true` to the `watch(selectedQuestions, ...)` options.
    *   This ensures `form.question_ids` is populated with the existing question IDs immediately upon component mount.
    *   When the form is submitted, the correct list of question IDs is sent to the backend, preserving the questions.

## Result
*   Editing an exam now correctly preserves the associated questions.
