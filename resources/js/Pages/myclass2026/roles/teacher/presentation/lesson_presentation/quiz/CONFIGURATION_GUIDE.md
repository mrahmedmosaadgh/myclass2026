# Quiz Configuration System

## Overview

The QuizEngine component supports comprehensive configuration options that allow you to customize quiz behavior for different assessment scenarios.

## Configuration Options

### `allowReviewMode` (boolean)
- **Default:** `false`
- **Description:** Enables students to navigate freely between questions and review their answers
- **When enabled:**
  - Previous/Next navigation buttons are available
  - Students can jump to any question using the question navigator
  - Students can change their answers after submission
- **When disabled:**
  - Students can only move forward through questions
  - Once an answer is submitted, it cannot be changed
  - Sequential completion is enforced

### `autoAdvance` (boolean)
- **Default:** `false`
- **Description:** Automatically advances to the next question when a correct answer is selected
- **Behavior:**
  - 1-second delay after correct answer before advancing
  - Allows student to see feedback before moving on
  - Only triggers on correct answers
  - Does not apply to the last question

### `showRationaleOnCorrect` (boolean)
- **Default:** `true`
- **Description:** Controls whether rationale text is shown for correct answers
- **When enabled:**
  - Rationale is displayed for both correct and incorrect answers
  - Helps reinforce learning even when answer is correct
- **When disabled:**
  - Rationale only shown for incorrect answers
  - Reduces visual clutter for correct responses

### `timeLimit` (number | undefined)
- **Default:** `undefined`
- **Description:** Sets a time limit for the entire quiz in seconds
- **Behavior:**
  - Countdown timer displayed in quiz header
  - Time warnings emitted at 5 minutes, 2 minutes, and 1 minute
  - Quiz auto-completes when time runs out
  - Timer turns red when less than 2 minutes remain
- **Example:** `timeLimit: 1800` (30 minutes)

### `shuffleQuestions` (boolean)
- **Default:** `false`
- **Description:** Randomizes the order of questions in the quiz
- **Behavior:**
  - Questions are shuffled when quiz loads
  - Each student gets a different question order
  - Question numbers are updated to reflect new order
  - Useful for reducing cheating in group settings

### `shuffleOptions` (boolean)
- **Default:** `false`
- **Description:** Randomizes the order of answer options for each question
- **Behavior:**
  - Options are shuffled independently for each question
  - Each student gets different option orders
  - Option labels (A, B, C, D) remain consistent
  - Useful for reducing pattern-based guessing

## Usage Examples

### Basic Quiz (No Configuration)
```vue
<QuizEngine :quiz="questions" />
```

### Practice Mode (Review Enabled)
```vue
<QuizEngine 
  :quiz="questions"
  :config="{
    allowReviewMode: true,
    showRationaleOnCorrect: true
  }"
/>
```

### Timed Assessment (30 minutes)
```vue
<QuizEngine 
  :quiz="questions"
  :config="{
    allowReviewMode: false,
    timeLimit: 1800,
    shuffleQuestions: true,
    shuffleOptions: true
  }"
/>
```

### Quick Drill (Auto-Advance)
```vue
<QuizEngine 
  :quiz="questions"
  :config="{
    autoAdvance: true,
    showRationaleOnCorrect: false
  }"
/>
```

### High-Stakes Exam
```vue
<QuizEngine 
  :quiz="questions"
  :config="{
    allowReviewMode: false,
    autoAdvance: false,
    showRationaleOnCorrect: false,
    timeLimit: 3600,
    shuffleQuestions: true,
    shuffleOptions: true
  }"
/>
```

## Event Handling

The QuizEngine emits events that you can use to track quiz progress:

```vue
<QuizEngine 
  :quiz="questions"
  :config="config"
  @answer-selected="handleAnswer"
  @question-changed="handleNavigation"
  @quiz-completed="handleCompletion"
  @quiz-review-enter="handleReviewMode"
  @time-warning="handleTimeWarning"
/>
```

### Event Handlers

```typescript
const handleAnswer = (record: AnswerRecord) => {
  console.log('Answer submitted:', record)
  // Save answer to backend
}

const handleNavigation = (index: number) => {
  console.log('Navigated to question:', index + 1)
}

const handleCompletion = (result: QuizResult) => {
  console.log('Quiz completed:', result)
  // Submit results to backend
  // Show results page
}

const handleReviewMode = () => {
  console.log('All questions answered - review mode available')
}

const handleTimeWarning = (remainingSeconds: number) => {
  console.log('Time warning:', remainingSeconds, 'seconds remaining')
  // Show notification to user
}
```

## Testing

A test page is available at `QuizEngineTest.vue` that allows you to:
- Toggle all configuration options
- See real-time event logging
- Reset the quiz to test different configurations
- Verify that all features work correctly

## Implementation Details

### Question Shuffling
- Uses Fisher-Yates shuffle algorithm for unbiased randomization
- Shuffling occurs once when quiz loads
- Question numbers are updated to reflect new order
- Original question IDs are preserved for answer tracking

### Option Shuffling
- Each question's options are shuffled independently
- Correct answer tracking is maintained regardless of order
- Option labels (A, B, C, D) are assigned after shuffling

### Timer Management
- Timer starts when component mounts
- Updates every second using `setInterval`
- Automatically cleaned up on component unmount
- Timer stops when quiz is completed
- Warnings emitted at configurable thresholds

### Review Mode
- Enabled when `allowReviewMode` is true
- Question navigator appears after all questions answered
- Previous button becomes available
- Students can modify previous answers
- Navigation restrictions are lifted

## Best Practices

1. **Practice Quizzes:** Enable `allowReviewMode` and `showRationaleOnCorrect`
2. **Formative Assessments:** Use `autoAdvance` for quick feedback
3. **Summative Assessments:** Disable review mode, enable shuffling, set time limit
4. **Adaptive Learning:** Use event handlers to track performance and adjust difficulty
5. **Accessibility:** Always provide sufficient time limits for students with accommodations

## Configuration Validation

The component validates configuration at runtime:
- Invalid time limits are ignored
- Boolean flags default to `false` if not provided
- Partial configuration objects are merged with defaults
- Type safety is enforced through TypeScript interfaces
