# Implementation Plan

## Database Architecture Note

**Schema Design Decision:**
- `question_options` - Separate table (enables analytics on distractor_strength, option performance)
- `hints` - JSON column in questions table (simple array, always fetched with question)
- `explanation` - JSON column in questions table (simple object: {text, revealed_after_attempt})

This hybrid approach balances normalization (where analytics matter) with simplicity (where it doesn't).

## Phase 1: Database Foundation

- [x] 1. Set up database schema and migrations





  - [x] 1.1 Create migration for question_types table with seed data


    - Define table structure (id, slug, name, has_options, supports_hints, supports_explanation)
    - Add seed data for initial question types (multiple_choice, multi_select, true_false, fill_blank, short_answer, essay)
    - Add unique index on slug column
    - _Requirements: 3.5_
  
  - [x] 1.2 Create migration for   table with indexes


    - Define table structure with curriculum fields (grade_level_id, subject_id, topic_id)
    - Add cognitive model fields (bloom_level, difficulty_level, estimated_time_sec)
    - Add analytics fields (usage_count, avg_success_rate, discrimination_index)
    - Add metadata fields (author_id, status)
    - Add JSON columns (hints, explanation) for storing hints array and explanation object
    - Create indexes on foreign keys and frequently queried columns
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 1.4_
  
  - [x] 1.3 Create migration for question_options table


    - Define table structure (question_id, option_key, option_text, is_correct, distractor_strength, order_index)
    - Add foreign key constraint with cascade delete
    - Add index on question_id
    - _Requirements: 3.1, 3.2, 3.3, 3.4_
  
  - [x] 1.4 Create migration for quiz_attempts table


    - Define table structure (user_id, quiz_id, started_at, completed_at, total_questions, correct_answers, percentage, metadata)
    - Add indexes on user_id and completed_at
    - _Requirements: 5.1, 5.4_
  
  - [x] 1.5 Create migration for quiz_attempt_answers table


    - Define table structure (attempt_id, question_id, selected_option_id, selected_text, is_correct, time_spent_sec, answered_at)
    - Add foreign key constraints with cascade delete
    - Add indexes on attempt_id and question_id
    - _Requirements: 5.3_

## Phase 2: Laravel Backend Models

- [x] 2. Create Laravel models and relationships





  - [x] 2.1 Implement QuestionType model with relationships


    - Define fillable fields (slug, name, has_options, supports_hints, supports_explanation)
    - Add boolean casts for has_options, supports_hints, supports_explanation
    - Add questions relationship (hasMany)
    - _Requirements: 3.5_
  
  - [x] 2.2 Implement Question model with relationships


    - Define fillable fields for all question attributes including hints and explanation
    - Add casts for bloom_level, difficulty_level, estimated_time_sec, usage_count, avg_success_rate, discrimination_index
    - Add array cast for hints JSON column
    - Add array cast for explanation JSON column (stores {text: string, revealed_after_attempt: boolean})
    - Add questionType relationship (belongsTo)
    - Add options relationship (hasMany with orderBy)
    - Add gradeLevel, subject, topic, author relationships (belongsTo)
    - Add scopes for filtering (byType, byStatus, active, byGradeLevel, bySubject)
    - Add accessor methods for hints and explanation if needed
    - _Requirements: 7.1, 7.2, 7.3, 1.4_
  
  - [x] 2.3 Implement QuestionOption model


    - Define fillable fields (question_id, option_key, option_text, is_correct, distractor_strength, order_index)
    - Add boolean cast for is_correct
    - Add decimal cast for distractor_strength
    - Add question relationship (belongsTo)
    - _Requirements: 3.1, 3.2, 3.3, 3.4_
  
  - [x] 2.4 Implement QuizAttempt model with methods


    - Define fillable fields (user_id, quiz_id, started_at, completed_at, total_questions, correct_answers, percentage, metadata)
    - Add datetime casts for started_at, completed_at
    - Add integer casts for total_questions, correct_answers
    - Add decimal cast for percentage
    - Add array cast for metadata
    - Add user relationship (belongsTo)
    - Add answers relationship (hasMany)
    - Implement isComplete() method
    - Implement calculateResults() method
    - _Requirements: 5.1, 5.4_
  
  - [x] 2.5 Implement QuizAttemptAnswer model


    - Define fillable fields (attempt_id, question_id, selected_option_id, selected_text, is_correct, time_spent_sec, answered_at)
    - Add boolean cast for is_correct
    - Add integer cast for time_spent_sec
    - Add datetime cast for answered_at
    - Add attempt relationship (belongsTo)
    - Add question relationship (belongsTo)
    - Add selectedOption relationship (belongsTo)
    - _Requirements: 5.3_

- [ ]* 2.6 Write property test for question storage
  - **Property 22: Curriculum metadata storage**
  - **Validates: Requirements 7.1**

- [ ]* 2.7 Write property test for Bloom level validation
  - **Property 23: Bloom level storage**
  - **Validates: Requirements 7.2**

## Phase 3: TypeScript Type Definitions

- [x] 3. Create TypeScript interfaces and types





  - [x] 3.1 Create quiz types file (resources/js/types/quiz.ts)


    - Define AnswerOption interface (id, text, isCorrect, rationale, distractorStrength)
    - Define QuizQuestion interface (id, questionNumber, questionTypeId, questionType, question, answerOptions, explanation, hints, bloomLevel, difficultyLevel, estimatedTimeSec, metadata)
    - Define QuestionType interface (id, slug, name, hasOptions, supportsHints, supportsExplanation)
    - Define AnswerRecord interface (questionId, questionNumber, selectedIndex, selectedOptionId, selectedText, correct, question, correctText, timeSpentSec, answeredAt)
    - Define QuizResult interface (attemptId, total, correct, percentage, answers, completedAt, metadata)
    - Define QuizConfig interface (allowReviewMode, autoAdvance, showRationaleOnCorrect, timeLimit, shuffleQuestions, shuffleOptions)
    - _Requirements: 10.2_
  
  - [x] 3.2 Define component prop and emit types


    - Create QuizEngineProps interface (quiz, config, attemptId)
    - Create QuizEngineEmits interface (answer-selected, question-changed, quiz-completed, quiz-review-enter, time-warning)
    - Add JSDoc comments for all interfaces
    - _Requirements: 10.3, 10.4, 10.5_

## Phase 4: Core QuizEngine Component

- [x] 4. Implement QuizEngine main component




  - [x] 4.1 Create QuizEngine.vue component structure


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/QuizEngine.vue
    - Set up script setup with TypeScript
    - Import quiz types from types/quiz.ts
    - Define props with type validation (quiz, config, attemptId)
    - Define emits with type definitions
    - Initialize reactive state (currentIndex, answers, startTime, questionStartTime, timeRemaining)
    - Add ARIA region with aria-label="Quiz Assessment"
    - _Requirements: 10.1, 10.3, 10.4_
  
  - [x] 4.2 Implement computed properties


    - Implement currentQuestion computed (returns current QuizQuestion)
    - Implement isAnswered computed (checks if current question has answer)
    - Implement selectedIndex computed (returns selected option index or null)
    - Implement correctIndex computed (finds correct option index)
    - Implement progress computed (calculates completion percentage)
    - Implement isLast computed (checks if on last question)
    - Implement total computed (returns total question count)
    - _Requirements: 2.4_
  
  - [x] 4.3 Implement core quiz methods


    - Implement selectOption(index: number) method
      - Check if already answered and review mode disabled
      - Create AnswerRecord with all required fields
      - Track time spent on question
      - Update or add answer to answers array
      - Emit answer-selected event
      - Handle auto-advance if enabled and correct
    - Implement goNext() method
      - Navigate to next question or complete quiz
      - Emit question-changed or quiz-completed event
    - Implement goTo(index: number) method
      - Validate review mode or answered status
      - Update currentIndex
      - Emit question-changed event
    - Implement calculateTimeSpent() helper method
    - _Requirements: 1.1, 1.2, 4.2_
  
  - [x] 4.4 Add event emission logic


    - Emit answer-selected event with AnswerRecord
    - Emit question-changed event with index
    - Emit quiz-completed event with QuizResult
    - Emit quiz-review-enter event when all answered in review mode
    - Add watch for answers.length to trigger review mode
    - _Requirements: 5.5_
  
  - [x] 4.5 Add template structure


    - Create quiz-header section with progress indicator
    - Create question-content section with question text
    - Create options-list with option items
    - Create quiz-footer with navigation controls
    - Add conditional question-navigator for review mode
    - _Requirements: 1.1, 2.1, 4.1_

- [ ]* 4.6 Write property test for option selection
  - **Property 1: Option selection visual feedback**
  - **Validates: Requirements 1.1**

- [ ]* 4.7 Write property test for progress calculation
  - **Property 8: Progress percentage calculation**
  - **Validates: Requirements 2.4**

- [ ]* 4.8 Write property test for answer preservation
  - **Property 13: Answer preservation on navigation**
  - **Validates: Requirements 4.4**

- [ ]* 4.9 Write property test for score calculation
  - **Property 14: Score calculation correctness**
  - **Validates: Requirements 5.1**

## Phase 5: Progress Tracking Components

- [x] 5. Create QuizHeader component with progress tracking





  - [x] 5.1 Implement ProgressIndicator component


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/ProgressIndicator.vue
    - Add props (current: number, total: number, percentage: number)
    - Create progress bar with role="progressbar"
    - Add aria-valuenow, aria-valuemin, aria-valuemax attributes
    - Display "Question X of Y" counter
    - Display "Z% Complete" text
    - Add aria-live="polite" region for screen reader announcements
    - _Requirements: 2.1, 2.2, 2.3, 2.5_
  
  - [x] 5.2 Style progress indicator with responsive design


    - Implement smooth progress bar fill animation (transition: width 0.4s ease)
    - Add mobile-optimized layout (stack vertically on small screens)
    - Ensure color contrast compliance (WCAG 2.1 AA - 4.5:1 ratio)
    - Use semantic colors (green for progress, gray for background)
    - Add responsive font sizes
    - _Requirements: 8.5_

- [ ]* 5.3 Write property test for progress updates
  - **Property 6: Progress indicator updates**
  - **Validates: Requirements 2.2**

- [ ]* 5.4 Write property test for question counter
  - **Property 7: Question counter display**
  - **Validates: Requirements 2.3**

## Phase 6: Question Rendering Components

- [x] 6. Create QuestionRenderer component




  - [x] 6.1 Implement base QuestionRenderer component


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/QuestionRenderer.vue
    - Add props (question: QuizQuestion, selectedIndex: number | null, isAnswered: boolean, showFeedback: boolean)
    - Add emit for 'select' event
    - Render question text with v-html for HTML/math support
    - Add question-header div with question number
    - Add question-text div with formatted content
    - Implement dynamic component loading based on questionType.slug
    - _Requirements: 1.1_
  
  - [x] 6.2 Implement MultipleChoiceQuestion component


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/MultipleChoiceQuestion.vue
    - Render options list with letter labels (A, B, C, D)
    - Use OptionItem component for each option
    - Handle option selection via emit
    - Display feedback on answer submission
    - Add role="listbox" and aria-label
    - _Requirements: 3.1, 8.1_
  
  - [x] 6.3 Implement TrueFalseQuestion component


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/TrueFalseQuestion.vue
    - Render True/False options (2 options only)
    - Use OptionItem component
    - Handle selection and feedback
    - Add ARIA attributes
    - _Requirements: 3.2, 8.1_
  
  - [x] 6.4 Implement FillBlankQuestion component


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/FillBlankQuestion.vue
    - Render text input for answer
    - Add input validation
    - Handle text submission
    - Display feedback with correct answer comparison
    - Add aria-label for input
    - _Requirements: 3.3_
  
  - [x] 6.5 Implement MultiSelectQuestion component


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/MultiSelectQuestion.vue
    - Render checkboxes for multiple selection
    - Handle multiple option selection (array of indices)
    - Validate all correct options selected
    - Display partial credit feedback
    - Add role="group" and aria-label
    - _Requirements: 3.4_

- [ ]* 6.6 Write property test for feedback display 
  - **Property 2: Answer submission feedback display**
  - **Validates: Requirements 1.2**

- [ ]* 6.7 Write property test for correct answer revelation
  - **Property 3: Incorrect answer correct option revelation**
  - **Validates: Requirements 1.3**

## Phase 7: Option Item Component

- [x] 7. Create OptionItem component with feedback




  - [x] 7.1 Implement OptionItem component


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/OptionItem.vue
    - Add props (option: AnswerOption, index: number, letter: string, isSelected: boolean, isCorrect: boolean, isAnswered: boolean, showRationale: boolean)
    - Add emit for 'click' event
    - Create option-label div with letter (A, B, C, D)
    - Create option-text div with option text
    - Add click handler
    - Add keyboard event handlers (@keydown.enter, @keydown.space)
    - Implement visual states with CSS classes (default, selected, correct, incorrect, unselected-correct)
    - Add transition animations (border-color, background-color)
    - _Requirements: 1.1, 8.2_
  
  - [x] 7.2 Implement feedback display


    - Add feedback div with v-if for isAnswered
    - Show checkmark icon (✓) for correct answers
    - Show X icon (✗) for incorrect answers
    - Display rationale text when available (option.rationale)
    - Add fade-in transition for feedback appearance
    - Style feedback with appropriate colors (green for correct, red for incorrect)
    - _Requirements: 1.2, 1.3, 1.4_
  
  - [x] 7.3 Add accessibility features


    - Add role="option" to option item
    - Add aria-selected attribute bound to isSelected
    - Add aria-disabled attribute bound to isAnswered && !allowReviewMode
    - Add tabindex="0" for keyboard navigation
    - Add visible focus indicators with :focus-visible pseudo-class
    - Add aria-label with descriptive text
    - _Requirements: 8.1, 8.2, 8.4_

- [ ]* 7.4 Write property test for rationale display
  - **Property 4: Rationale display when available**
  - **Validates: Requirements 1.4**

- [ ]* 7.5 Write property test for keyboard navigation
  - **Property 28: Keyboard navigation support**
  - **Validates: Requirements 8.2**

## Phase 8: Explanation Panel Component

- [x] 8. Create ExplanationPanel component




  - [x] 8.1 Implement ExplanationPanel component


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/ExplanationPanel.vue
    - Add props (explanation: string | undefined, isVisible: boolean)
    - Display global explanation after answer with v-if
    - Show only when explanation exists and isVisible is true
    - Add "Explanation:" label in bold
    - Style with distinct visual treatment (background color, border, padding)
    - Add fade-in transition
    - _Requirements: 1.4_

## Phase 9: Navigation Controls

- [x] 9. Create QuizFooter with navigation controls





  - [x] 9.1 Implement NavigationControls component


    - Create file at resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/NavigationControls.vue
    - Add props (allowReviewMode: boolean, isAnswered: boolean, isLast: boolean, currentIndex: number)
    - Add emits for 'previous', 'next', 'finish'
    - Create Previous button (v-if allowReviewMode)
      - Disable when currentIndex === 0
      - Add aria-label="Previous question"
    - Create Next button
      - Disable when !isAnswered && !allowReviewMode
      - Show "Next Question" text when not last
      - Show "Finish Quiz" text when isLast
      - Add arrow icon (→) when not last
    - Add click handlers to emit events
    - _Requirements: 4.1, 4.2_
  
  - [x] 9.2 Style navigation controls


    - Implement button hover states (darker background)
    - Add disabled state styling (lighter background, cursor: not-allowed)
    - Ensure touch-friendly sizing for mobile (min 44x44px)
    - Add responsive layout (flex with space-between)
    - Style finish button differently (green background)
    - Add transition effects
    - _Requirements: 8.4_

- [ ]* 9.3 Write property test for navigation transitions
  - **Property 11: Navigation transition correctness**
  - **Validates: Requirements 4.2**

- [x] 10. Create QuestionNavigator component for review mode





  - [x] 10.1 Implement QuestionNavigator component


    - Display navigation dots for all questions
    - Indicate answered questions
    - Highlight current question
    - Handle click navigation
    - Add ARIA navigation attributes
    - _Requirements: 4.1, 4.3_

- [ ]* 10.2 Write property test for answered indicators
  - **Property 12: Answered question indicators**
  - **Validates: Requirements 4.3**

- [x] 11. Implement quiz configuration system





  - [x] 11.1 Add configuration props to QuizEngine


    - Add allowReviewMode prop
    - Add autoAdvance prop
    - Add showRationaleOnCorrect prop
    - Add timeLimit prop
    - Add shuffleQuestions prop
    - Add shuffleOptions prop
    - _Requirements: 9.1, 9.2, 9.3, 9.4_
  
  - [x] 11.2 Implement configuration enforcement logic


    - Enforce review mode restrictions
    - Implement auto-advance behavior
    - Control rationale visibility
    - Implement time limit countdown
    - _Requirements: 9.5_

- [ ]* 11.3 Write property test for configuration enforcement
  - **Property 32: Configuration enforcement consistency**
  - **Validates: Requirements 9.5**

- [x] 12. Create QuizService for backend logic





  - [x] 12.1 Implement QuizService class


    - Implement startAttempt() method
    - Implement submitAnswer() method
    - Implement completeAttempt() method
    - Implement updateQuestionAnalytics() method
    - _Requirements: 5.1, 7.4, 7.5_
  
  - [x] 12.2 Add transaction handling


    - Wrap database operations in transactions
    - Implement rollback on errors
    - _Requirements: Error Handling_

- [ ]* 12.3 Write property test for usage statistics
  - **Property 25: Usage statistics updates**
  - **Validates: Requirements 7.4**

- [ ]* 12.4 Write property test for analytics updates
  - **Property 26: Analytics data updates**
  - **Validates: Requirements 7.5**

- [x] 13. Create API endpoints and controllers





  - [x] 13.1 Implement QuizController


    - Create show() method for fetching quiz
    - Add validation and error handling
    - _Requirements: API Architecture_
  
  - [x] 13.2 Implement QuizAttemptController


    - Create store() method for starting attempt
    - Create submitAnswer() method
    - Create complete() method
    - Create results() method
    - Add authentication middleware
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_
  
  - [x] 13.3 Implement QuestionController


    - Create index() method with filters
    - Create store() method
    - Create update() method
    - Create destroy() method
    - Add authorization checks
    - _Requirements: 7.1, 7.2, 7.3_

- [ ]* 13.4 Write integration test for quiz workflow
  - Test complete quiz from start to finish
  - Verify database records created correctly
  - _Requirements: 5.1, 5.2, 5.3, 5.4_

- [x] 14. Implement question import functionality





  - [x] 14.1 Create QuestionImportService


    - Implement CSV parsing logic
    - Implement Excel parsing logic
    - Add data validation
    - Map question type slugs to IDs
    - _Requirements: 6.1, 6.2, 6.3_
  
  - [x] 14.2 Implement import validation

    - Validate required columns
    - Validate question type slugs
    - Validate option data
    - Generate detailed error reports with row numbers
    - _Requirements: 6.2, 6.4_
  
  - [x] 14.3 Implement bulk question creation

    - Create questions with options in question_options table
    - Store hints as JSON array in questions.hints column
    - Store explanation as JSON object in questions.explanation column
    - Handle partial imports with error logging
    - _Requirements: 6.5_
  
  - [x] 14.4 Create import API endpoint


    - Add file upload handling
    - Add progress tracking
    - Return import results with error details
    - _Requirements: 6.1, 6.4_

- [ ]* 14.5 Write property test for import validation
  - **Property 19: Import validation enforcement**
  - **Validates: Requirements 6.2, 6.4**

- [ ]* 14.6 Write property test for question type mapping
  - **Property 20: Question type slug mapping**
  - **Validates: Requirements 6.3**

- [ ]* 14.7 Write property test for import completeness
  - **Property 21: Import metadata completeness**
  - **Validates: Requirements 6.5**

- [x] 15. Add comprehensive styling and theming





  - [x] 15.1 Create base quiz styles


    - Define color palette with WCAG compliant contrasts
    - Create typography scale
    - Define spacing system
    - Add responsive breakpoints
    - _Requirements: 8.5_
  
  - [x] 15.2 Implement component-specific styles


    - Style QuizEngine container
    - Style progress indicators
    - Style question content
    - Style option items with states
    - Style navigation controls
    - _Requirements: 1.1, 1.2, 1.3_
  
  - [x] 15.3 Add animations and transitions


    - Progress bar fill animation
    - Feedback fade-in animation
    - Option selection transition
    - Page transition effects
    - _Requirements: UI/UX_
  
  - [x] 15.4 Implement responsive design


    - Mobile layout (< 640px)
    - Tablet layout (640px - 1024px)
    - Desktop layout (> 1024px)
    - Touch-friendly targets on mobile
    - _Requirements: Responsive Design_

- [ ]* 15.5 Write property test for color contrast
  - **Property 31: Color contrast compliance**
  - **Validates: Requirements 8.5**

- [x] 16. Implement accessibility features




  - [x] 16.1 Add ARIA attributes throughout


    - Add roles to all interactive elements
    - Add labels to all controls
    - Add live regions for dynamic updates
    - Add descriptions where needed
    - _Requirements: 8.1, 8.3_
  
  - [x] 16.2 Implement keyboard navigation


    - Add keyboard event handlers
    - Implement focus management
    - Add skip links
    - Test tab order
    - _Requirements: 8.2_
  
  - [x] 16.3 Add focus indicators


    - Style focus states for all interactive elements
    - Ensure visibility on all backgrounds
    - Test with keyboard navigation
    - _Requirements: 8.4_

- [ ]* 16.4 Write property test for ARIA attributes
  - **Property 27: ARIA attributes completeness**
  - **Validates: Requirements 8.1**

- [ ]* 16.5 Write property test for state announcements
  - **Property 29: State change announcements**
  - **Validates: Requirements 8.3**

- [ ]* 16.6 Write property test for focus indicators
  - **Property 30: Focus indicator visibility**
  - **Validates: Requirements 8.4**

- [-] 17. Add error handling and validation






  - [x] 17.1 Implement client-side error handling



    - Add try-catch blocks for async operations
    - Display user-friendly error messages
    - Implement retry logic for network failures
    - Add error logging
    - _Requirements: Error Handling_
  
  - [x] 17.2 Implement server-side validation


    - Validate all API inputs
    - Return structured error responses
    - Add database constraint error handling
    - _Requirements: Error Handling_
  
  - [x] 17.3 Add form validation










    - Validate question creation forms
    - Validate import file formats
    - Display validation errors inline
    - _Requirements: 6.2, 6.4_

- [x] 18. Implement caching and performance optimizations





  - [x] 18.1 Add database indexes


    - Create indexes on foreign keys
    - Create composite indexes for common queries
    - _Requirements: Database Architecture_
  
  - [x] 18.2 Implement query caching


    - Cache quiz questions
    - Cache question types
    - Cache user attempts
    - Add cache invalidation logic
    - _Requirements: Caching Strategy_
  
  - [x] 18.3 Optimize frontend performance


    - Lazy load components
    - Optimize bundle size
    - Add code splitting
    - Optimize images
    - _Requirements: Deployment_

- [-] 19. Add internationalization support






  - [x] 19.1 Set up i18n infrastructure


    - Install and configure vue-i18n
    - Create translation files
    - Add language switcher
    - _Requirements: Internationalization_
  
  - [x] 19.2 Translate all UI strings




    - Translate quiz interface
    - Translate error messages
    - Translate validation messages
    - _Requirements: Internationalization_
  
  - [ ] 19.3 Add RTL support
    - Use logical CSS properties
    - Mirror UI for RTL languages
    - Test with Arabic
    - _Requirements: Internationalization_

- [ ] 20. Create documentation and examples
  - [ ] 20.1 Write component documentation
    - Document QuizEngine props and emits
    - Document all child components
    - Add JSDoc comments
    - _Requirements: 10.5_
  
  - [ ] 20.2 Create usage examples
    - Create basic quiz example
    - Create review mode example
    - Create timed quiz example
    - Create import example
    - _Requirements: Documentation_
  
  - [ ] 20.3 Write API documentation
    - Document all endpoints
    - Add request/response examples
    - Document error codes
    - _Requirements: API Architecture_

- [x] 21. Integrate with existing lesson presentation system





  - [x] 21.1 Update QuestionSlide component


    - Replace existing quiz logic with QuizEngine
    - Map existing question data to new format
    - Preserve backward compatibility
    - _Requirements: Integration_
  
  - [x] 21.2 Update lesson player


    - Integrate QuizEngine into lesson flow
    - Handle quiz results in lesson context
    - Save quiz attempts to database
    - _Requirements: Integration_
  
  - [x] 21.3 Migrate existing questions


    - Create migration script for existing questions
    - Map to new database schema
    - Validate migrated data
    - _Requirements: Integration_
  
  - [x] 21.4 Add Quiz Selection UI to Lesson Editor








    - Add quiz selector dropdown in lesson editor header (next to grade selector)
    - Create API endpoint to fetch available quizzes (GET /api/quizzes)
    - Display quiz list with quiz name and question count
    - Add "Create New Quiz" option that opens quiz creation modal
    - Save selected quiz_id when lesson is saved
    - Update LessonPlayer to display selected quiz information in quiz section
    - Replace "Not Set" text with actual quiz name when quiz_id exists
    - Add "Start Quiz" button that launches QuizEngine component with selected quiz
    - Handle case when quiz_id is null (show "No quiz assigned" message)
    - _Requirements: Integration_

- [ ] 22. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
