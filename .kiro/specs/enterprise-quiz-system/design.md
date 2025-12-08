# Design Document

## Overview

The Enterprise Quiz System is a comprehensive assessment platform built with Vue 3, TypeScript, and the Composition API. The system provides a modern, accessible, and extensible quiz engine that supports multiple question types, real-time feedback, progress tracking, and comprehensive analytics. The design emphasizes type safety, maintainability, accessibility (WCAG 2.1 AA compliance), and scalability for deployment across educational levels from elementary to university entrance exams.

The system consists of three main layers:
1. **Presentation Layer**: Vue 3 components with TypeScript for type-safe UI rendering
2. **Data Layer**: Laravel backend with normalized database schema for question management
3. **Integration Layer**: API endpoints and event system for quiz lifecycle management

## Architecture

### Component Architecture

```
QuizEngine (Main Component)
├── QuizHeader
│   ├── ProgressIndicator
│   └── QuestionCounter
├── QuestionContent
│   ├── QuestionRenderer
│   │   ├── MultipleChoiceQuestion
│   │   ├── TrueFalseQuestion
│   │   ├── FillBlankQuestion
│   │   ├── MultiSelectQuestion
│   │   └── [Extensible Question Types]
│   ├── OptionsList
│   │   └── OptionItem (with feedback)
│   └── ExplanationPanel
├── QuizFooter
│   ├── NavigationControls
│   └── SubmitButton
└── QuestionNavigator (Review Mode)
```

### Database Architecture

```
question_types
├── id (PK)
├── slug (unique, indexed)
├── name
├── has_options (boolean)
├── supports_hints (boolean)
├── supports_explanation (boolean)
├── created_at
└── updated_at

questions
├── id (PK)
├── question_type_id (FK → question_types.id)
├── question_text (text)
├── grade_level_id (FK → grade_levels.id)
├── subject_id (FK → subjects.id)
├── topic_id (FK → topics.id, nullable)
├── bloom_level (1-6)
├── difficulty_level (1-5)
├── estimated_time_sec (integer)
├── author_id (FK → users.id)
├── status (enum: draft, active, archived, review)
├── usage_count (integer, default 0)
├── avg_success_rate (decimal, nullable)
├── discrimination_index (decimal, nullable)
├── created_at
└── updated_at

question_options
├── id (PK)
├── question_id (FK → questions.id, cascade delete)
├── option_key (varchar: A, B, C, D, etc.)
├── option_text (text)
├── is_correct (boolean)
├── distractor_strength (decimal, nullable)
└── order_index (integer)

question_hints
├── id (PK)
├── question_id (FK → questions.id, cascade delete)
├── hint_text (text)
└── order_index (integer)

question_explanations
├── id (PK)
├── question_id (FK → questions.id, cascade delete)
├── explanation_text (text)
└── revealed_after_attempt (boolean, default true)

quiz_attempts
├── id (PK)
├── user_id (FK → users.id)
├── quiz_id (FK → quizzes.id, nullable)
├── started_at (timestamp)
├── completed_at (timestamp, nullable)
├── total_questions (integer)
├── correct_answers (integer)
├── percentage (decimal)
└── metadata (json)

quiz_attempt_answers
├── id (PK)
├── attempt_id (FK → quiz_attempts.id, cascade delete)
├── question_id (FK → questions.id)
├── selected_option_id (FK → question_options.id, nullable)
├── selected_text (text, nullable)
├── is_correct (boolean)
├── time_spent_sec (integer)
└── answered_at (timestamp)
```

### API Architecture

**Endpoints:**
- `GET /api/quizzes/{id}` - Fetch quiz with questions
- `POST /api/quiz-attempts` - Start a new quiz attempt
- `POST /api/quiz-attempts/{id}/answers` - Submit an answer
- `PUT /api/quiz-attempts/{id}/complete` - Complete quiz attempt
- `GET /api/quiz-attempts/{id}/results` - Fetch quiz results
- `POST /api/questions/import` - Bulk import questions from CSV/Excel
- `GET /api/questions` - List questions with filters
- `POST /api/questions` - Create new question
- `PUT /api/questions/{id}` - Update question
- `DELETE /api/questions/{id}` - Delete question

## Components and Interfaces

### TypeScript Interfaces

```typescript
// Core Types
export interface AnswerOption {
  id: string | number
  text: string
  isCorrect: boolean
  rationale?: string
  distractorStrength?: number
}

export interface QuizQuestion {
  id: string | number
  questionNumber: number
  questionTypeId: number
  questionType: QuestionType
  question: string
  answerOptions: AnswerOption[]
  explanation?: string
  hints?: string[]
  bloomLevel?: number
  difficultyLevel?: number
  estimatedTimeSec?: number
  metadata?: Record<string, any>
}

export interface QuestionType {
  id: number
  slug: string
  name: string
  hasOptions: boolean
  supportsHints: boolean
  supportsExplanation: boolean
}

export interface AnswerRecord {
  questionId: string | number
  questionNumber: number
  selectedIndex: number
  selectedOptionId?: string | number
  selectedText?: string
  correct: boolean
  question: string
  correctText: string
  timeSpentSec: number
  answeredAt: Date
}

export interface QuizResult {
  attemptId: string | number
  total: number
  correct: number
  percentage: number
  answers: AnswerRecord[]
  completedAt: Date
  metadata?: Record<string, any>
}

export interface QuizConfig {
  allowReviewMode: boolean
  autoAdvance: boolean
  showRationaleOnCorrect: boolean
  timeLimit?: number
  shuffleQuestions?: boolean
  shuffleOptions?: boolean
}

// Component Props
export interface QuizEngineProps {
  quiz: QuizQuestion[]
  config?: Partial<QuizConfig>
  attemptId?: string | number
}

// Component Emits
export interface QuizEngineEmits {
  'answer-selected': (record: AnswerRecord) => void
  'question-changed': (index: number) => void
  'quiz-completed': (result: QuizResult) => void
  'quiz-review-enter': () => void
  'time-warning': (remainingSeconds: number) => void
}
```

### Vue Components

#### QuizEngine.vue (Main Component)

**Props:**
- `quiz`: Array of QuizQuestion objects (required)
- `config`: QuizConfig object for behavior customization (optional)
- `attemptId`: Unique identifier for tracking this quiz attempt (optional)

**Emits:**
- `answer-selected`: Fired when user selects an answer
- `question-changed`: Fired when current question changes
- `quiz-completed`: Fired when all questions are answered
- `quiz-review-enter`: Fired when entering review mode
- `time-warning`: Fired when time limit is approaching

**State:**
- `currentIndex`: Current question index
- `answers`: Array of AnswerRecord objects
- `startTime`: Quiz start timestamp
- `questionStartTime`: Current question start timestamp
- `timeRemaining`: Seconds remaining (if time limit enabled)

**Computed:**
- `currentQuestion`: Current QuizQuestion object
- `isAnswered`: Boolean indicating if current question is answered
- `selectedIndex`: Index of selected option for current question
- `correctIndex`: Index of correct option for current question
- `progress`: Percentage of quiz completion
- `isLast`: Boolean indicating if on last question

**Methods:**
- `selectOption(index: number)`: Handle option selection
- `goNext()`: Navigate to next question or complete quiz
- `goTo(index: number)`: Navigate to specific question (review mode)
- `submitQuiz()`: Complete and submit quiz
- `calculateTimeSpent()`: Calculate time spent on current question

#### ProgressIndicator.vue

Displays visual progress bar and completion statistics.

**Props:**
- `current`: Current question number
- `total`: Total number of questions
- `percentage`: Completion percentage

#### QuestionRenderer.vue

Dynamically renders question based on type.

**Props:**
- `question`: QuizQuestion object
- `selectedIndex`: Currently selected option index
- `isAnswered`: Boolean indicating if answered
- `showFeedback`: Boolean controlling feedback visibility

**Emits:**
- `select`: Fired when option is selected

#### OptionItem.vue

Renders individual answer option with feedback.

**Props:**
- `option`: AnswerOption object
- `index`: Option index
- `letter`: Option letter (A, B, C, etc.)
- `isSelected`: Boolean indicating if selected
- `isCorrect`: Boolean indicating if correct
- `isAnswered`: Boolean indicating if question is answered
- `showRationale`: Boolean controlling rationale visibility

**Emits:**
- `click`: Fired when option is clicked

## Data Models

### Laravel Models

#### QuestionType Model

```php
class QuestionType extends Model
{
    protected $fillable = [
        'slug', 'name', 'has_options', 
        'supports_hints', 'supports_explanation'
    ];

    protected $casts = [
        'has_options' => 'boolean',
        'supports_hints' => 'boolean',
        'supports_explanation' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
```

#### Question Model

```php
class Question extends Model
{
    protected $fillable = [
        'question_type_id', 'question_text', 'grade_level_id',
        'subject_id', 'topic_id', 'bloom_level', 'difficulty_level',
        'estimated_time_sec', 'author_id', 'status'
    ];

    protected $casts = [
        'bloom_level' => 'integer',
        'difficulty_level' => 'integer',
        'estimated_time_sec' => 'integer',
        'usage_count' => 'integer',
        'avg_success_rate' => 'decimal:2',
        'discrimination_index' => 'decimal:2',
    ];

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order_index');
    }

    public function hints()
    {
        return $this->hasMany(QuestionHint::class)->orderBy('order_index');
    }

    public function explanation()
    {
        return $this->hasOne(QuestionExplanation::class);
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
```

#### QuizAttempt Model

```php
class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id', 'quiz_id', 'started_at', 'completed_at',
        'total_questions', 'correct_answers', 'percentage', 'metadata'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'percentage' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class, 'attempt_id');
    }

    public function isComplete()
    {
        return !is_null($this->completed_at);
    }

    public function calculateResults()
    {
        $this->correct_answers = $this->answers()->where('is_correct', true)->count();
        $this->percentage = ($this->correct_answers / $this->total_questions) * 100;
        $this->save();
    }
}
```

### Data Validation Rules

**Question Creation:**
- `question_type_id`: required, exists in question_types table
- `question_text`: required, string, max 5000 characters
- `grade_level_id`: required, exists in grade_levels table
- `subject_id`: required, exists in subjects table
- `bloom_level`: nullable, integer, between 1 and 6
- `difficulty_level`: nullable, integer, between 1 and 5
- `status`: required, enum (draft, active, archived, review)

**Question Options (for option-based questions):**
- At least 2 options required
- Exactly one option must be correct for single-choice
- At least one option must be correct for multi-select
- `option_text`: required, string, max 1000 characters

**CSV Import Format:**
- Headers: question_type, grade_level, subject, topic, bloom_level, difficulty, question_text, option_a, option_b, option_c, option_d, correct_answer(s), explanation, hints
- question_type must match existing slug in question_types table
- correct_answer(s) format: Single letter (A) for single-choice, comma-separated (A,C) for multi-select

## Cor
rectness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Property 1: Option selection visual feedback
*For any* quiz question and any answer option, when a student selects that option, the Quiz Engine should apply a distinct visual style to indicate selection
**Validates: Requirements 1.1**

Property 2: Answer submission feedback display
*For any* answer submission, the Quiz Engine should immediately display feedback indicating whether the answer is correct or incorrect
**Validates: Requirements 1.2**

Property 3: Incorrect answer correct option revelation
*For any* incorrect answer submission, the Quiz Engine should reveal the correct answer option with visual distinction
**Validates: Requirements 1.3**

Property 4: Rationale display when available
*For any* question with rationale or explanation text, when feedback is displayed, the Quiz Engine should show the rationale or explanation
**Validates: Requirements 1.4**

Property 5: Accessibility attributes presence
*For any* rendered quiz element, the Quiz Engine should include appropriate ARIA roles, labels, and keyboard navigation support
**Validates: Requirements 1.5**

Property 6: Progress indicator updates
*For any* question answered, the Quiz Engine should update the progress indicator to reflect the new completion percentage
**Validates: Requirements 2.2**

Property 7: Question counter display
*For any* quiz state, the Quiz Engine should display both the current question number and total question count
**Validates: Requirements 2.3**

Property 8: Progress percentage calculation
*For any* quiz state, the percentage displayed should equal (number of answered questions / total questions) × 100
**Validates: Requirements 2.4**

Property 9: ARIA live region updates
*For any* progress update, the Quiz Engine should update ARIA live regions to announce changes to screen readers
**Validates: Requirements 2.5**

Property 10: Question type foreign key integrity
*For any* question stored in the database, the question_type_id should reference a valid record in the question_types table
**Validates: Requirements 3.5**

Property 11: Navigation transition correctness
*For any* navigation control click, the Quiz Engine should transition to the selected question and update the current question index
**Validates: Requirements 4.2**

Property 12: Answered question indicators
*For any* navigation display, questions that have been answered should be visually indicated as such
**Validates: Requirements 4.3**

Property 13: Answer preservation on navigation
*For any* previously answered question, navigating away and back to that question should preserve the selected answer
**Validates: Requirements 4.4**

Property 14: Score calculation correctness
*For any* completed quiz, the calculated score should equal the count of correct answers, and the percentage should equal (correct answers / total questions) × 100
**Validates: Requirements 5.1**

Property 15: Result counts display
*For any* quiz result display, both the number of correct answers and incorrect answers should be shown
**Validates: Requirements 5.2**

Property 16: Answer breakdown completeness
*For any* quiz result, each answer in the breakdown should include a correctness indicator
**Validates: Requirements 5.3**

Property 17: Result timestamp presence
*For any* generated quiz result, a completion timestamp should be included
**Validates: Requirements 5.4**

Property 18: Result data structure conformance
*For any* emitted quiz result, the data should conform to the QuizResult TypeScript interface
**Validates: Requirements 5.5**

Property 19: Import validation enforcement
*For any* import file processed, invalid question data should be rejected with clear error messages indicating problematic rows
**Validates: Requirements 6.2, 6.4**

Property 20: Question type slug mapping
*For any* imported question, the question_type slug should correctly map to a question_type_id in the database
**Validates: Requirements 6.3**

Property 21: Import metadata completeness
*For any* successfully imported question, all associated metadata (options, hints, explanations) should be created
**Validates: Requirements 6.5**

Property 22: Curriculum metadata storage
*For any* stored question, curriculum alignment fields (grade_level_id, subject_id, topic_id) should be recorded
**Validates: Requirements 7.1**

Property 23: Bloom level storage
*For any* stored question with a Bloom level, the value should be recorded and be between 1 and 6
**Validates: Requirements 7.2**

Property 24: Difficulty and time storage
*For any* stored question, difficulty_level and estimated_time_sec should be recorded if provided
**Validates: Requirements 7.3**

Property 25: Usage statistics updates
*For any* question used in a quiz attempt, the usage_count should increment and avg_success_rate should be recalculated
**Validates: Requirements 7.4**

Property 26: Analytics data updates
*For any* question with sufficient usage data, the discrimination_index should be calculated and updated
**Validates: Requirements 7.5**

Property 27: ARIA attributes completeness
*For any* rendered quiz element, appropriate ARIA roles and labels should be present in the DOM
**Validates: Requirements 8.1**

Property 28: Keyboard navigation support
*For any* answer option, pressing Enter or Space key should select that option
**Validates: Requirements 8.2**

Property 29: State change announcements
*For any* quiz state change, ARIA live regions should be updated to announce the change
**Validates: Requirements 8.3**

Property 30: Focus indicator visibility
*For any* interactive element receiving focus, a visible focus indicator should be displayed
**Validates: Requirements 8.4**

Property 31: Color contrast compliance
*For any* rendered content, color contrast ratios should meet WCAG 2.1 AA standards (4.5:1 for normal text)
**Validates: Requirements 8.5**

Property 32: Configuration enforcement consistency
*For any* quiz configuration setting, the Quiz Engine should enforce the configured behavior throughout the entire quiz session
**Validates: Requirements 9.5**

## Error Handling

### Client-Side Error Handling

**Quiz Loading Errors:**
- Network failures when fetching quiz data
- Invalid quiz data structure
- Missing required question fields
- Strategy: Display user-friendly error message, provide retry option, log error details for debugging

**Answer Submission Errors:**
- Network failures during answer submission
- Invalid answer format
- Server validation errors
- Strategy: Queue answers locally, retry on network recovery, show submission status indicator

**Navigation Errors:**
- Attempting to navigate to non-existent question
- Invalid question index
- Strategy: Validate navigation bounds, prevent invalid navigation, log error

**Time Limit Errors:**
- Timer synchronization issues
- Browser tab inactive causing timer drift
- Strategy: Use server-side time tracking as source of truth, warn user of time discrepancies

### Server-Side Error Handling

**Question Import Errors:**
- Invalid file format
- Missing required columns
- Data validation failures
- Duplicate questions
- Strategy: Return detailed error report with row numbers, allow partial import with error log

**Database Errors:**
- Foreign key constraint violations
- Unique constraint violations
- Connection failures
- Strategy: Wrap in transactions, rollback on failure, return specific error codes

**Analytics Calculation Errors:**
- Insufficient data for discrimination index
- Division by zero in percentage calculations
- Strategy: Use null for insufficient data, handle edge cases gracefully

**Authentication/Authorization Errors:**
- Unauthorized quiz access
- Expired session during quiz
- Strategy: Redirect to login, preserve quiz state for resume, clear error messages

### Error Response Format

```typescript
interface ErrorResponse {
  error: {
    code: string
    message: string
    details?: Record<string, any>
    timestamp: string
  }
}

// Example error codes:
// QUIZ_NOT_FOUND
// INVALID_ANSWER_FORMAT
// IMPORT_VALIDATION_FAILED
// UNAUTHORIZED_ACCESS
// DATABASE_ERROR
```

## Testing Strategy

### Unit Testing

**Component Unit Tests:**
- Test individual Vue components in isolation
- Mock props, emits, and external dependencies
- Verify correct rendering for various prop combinations
- Test user interactions (clicks, keyboard events)
- Verify accessibility attributes are present
- Tools: Vitest, Vue Test Utils

**Model Unit Tests:**
- Test Laravel model methods
- Test relationships and scopes
- Test data casting and validation
- Verify database constraints
- Tools: PHPUnit, Laravel Testing

**Service Unit Tests:**
- Test business logic in service classes
- Test calculation methods (score, percentage, analytics)
- Test data transformation logic
- Mock database interactions
- Tools: PHPUnit

**Example Unit Tests:**
```typescript
// QuizEngine component test
describe('QuizEngine', () => {
  it('should display progress bar on load', () => {
    const wrapper = mount(QuizEngine, {
      props: { quiz: mockQuizData }
    })
    expect(wrapper.find('.progress-bar').exists()).toBe(true)
  })

  it('should highlight selected option', async () => {
    const wrapper = mount(QuizEngine, {
      props: { quiz: mockQuizData }
    })
    await wrapper.find('.option-item').trigger('click')
    expect(wrapper.find('.option-item.selected').exists()).toBe(true)
  })
})
```

### Property-Based Testing

**Property-Based Testing Library:** fast-check (for TypeScript/JavaScript)

**Configuration:** Each property-based test should run a minimum of 100 iterations to ensure comprehensive coverage across random inputs.

**Test Tagging:** Each property-based test must include a comment explicitly referencing the correctness property using this format: `**Feature: enterprise-quiz-system, Property {number}: {property_text}**`

**Property Test Examples:**

```typescript
import fc from 'fast-check'

/**
 * Feature: enterprise-quiz-system, Property 8: Progress percentage calculation
 * For any quiz state, the percentage displayed should equal 
 * (number of answered questions / total questions) × 100
 */
test('progress percentage calculation is correct', () => {
  fc.assert(
    fc.property(
      fc.integer({ min: 1, max: 50 }), // total questions
      fc.integer({ min: 0, max: 50 }), // answered questions
      (total, answered) => {
        fc.pre(answered <= total) // precondition
        
        const quiz = generateQuiz(total)
        const engine = new QuizEngine(quiz)
        
        // Answer random questions
        for (let i = 0; i < answered; i++) {
          engine.selectOption(i, 0)
        }
        
        const expectedPercentage = (answered / total) * 100
        expect(engine.progress).toBeCloseTo(expectedPercentage, 2)
      }
    ),
    { numRuns: 100 }
  )
})

/**
 * Feature: enterprise-quiz-system, Property 13: Answer preservation on navigation
 * For any previously answered question, navigating away and back to that 
 * question should preserve the selected answer
 */
test('navigation preserves answers', () => {
  fc.assert(
    fc.property(
      fc.array(fc.record({
        question: fc.string(),
        options: fc.array(fc.string(), { minLength: 2, maxLength: 5 })
      }), { minLength: 3, maxLength: 20 }),
      fc.integer({ min: 0, max: 4 }), // selected option index
      (questions, selectedIndex) => {
        const quiz = questions.map((q, i) => ({
          questionNumber: i + 1,
          question: q.question,
          answerOptions: q.options.map(text => ({ text, isCorrect: false }))
        }))
        
        const engine = new QuizEngine(quiz)
        
        // Answer first question
        engine.selectOption(selectedIndex % quiz[0].answerOptions.length)
        const originalAnswer = engine.answers[0]
        
        // Navigate away and back
        engine.goNext()
        engine.goTo(0)
        
        // Verify answer preserved
        expect(engine.selectedIndex).toBe(originalAnswer.selectedIndex)
      }
    ),
    { numRuns: 100 }
  )
})

/**
 * Feature: enterprise-quiz-system, Property 14: Score calculation correctness
 * For any completed quiz, the calculated score should equal the count of 
 * correct answers, and the percentage should equal (correct / total) × 100
 */
test('score calculation is correct', () => {
  fc.assert(
    fc.property(
      fc.array(fc.boolean(), { minLength: 1, maxLength: 50 }), // correct/incorrect pattern
      (correctPattern) => {
        const quiz = correctPattern.map((isCorrect, i) => ({
          questionNumber: i + 1,
          question: `Question ${i + 1}`,
          answerOptions: [
            { text: 'Correct', isCorrect: true },
            { text: 'Wrong', isCorrect: false }
          ]
        }))
        
        const engine = new QuizEngine(quiz)
        
        // Answer all questions according to pattern
        correctPattern.forEach((shouldBeCorrect, i) => {
          engine.currentIndex = i
          engine.selectOption(shouldBeCorrect ? 0 : 1)
        })
        
        const result = engine.submitQuiz()
        const expectedCorrect = correctPattern.filter(x => x).length
        const expectedPercentage = (expectedCorrect / correctPattern.length) * 100
        
        expect(result.correct).toBe(expectedCorrect)
        expect(result.percentage).toBeCloseTo(expectedPercentage, 2)
      }
    ),
    { numRuns: 100 }
  )
})
```

**Property Test Generators:**

```typescript
// Custom generators for quiz data
const questionGenerator = fc.record({
  questionNumber: fc.integer({ min: 1, max: 100 }),
  question: fc.string({ minLength: 10, maxLength: 200 }),
  answerOptions: fc.array(
    fc.record({
      text: fc.string({ minLength: 1, maxLength: 100 }),
      isCorrect: fc.boolean()
    }),
    { minLength: 2, maxLength: 6 }
  ).map(options => {
    // Ensure at least one correct answer
    if (!options.some(o => o.isCorrect)) {
      options[0].isCorrect = true
    }
    return options
  })
})

const quizGenerator = fc.array(questionGenerator, { 
  minLength: 1, 
  maxLength: 30 
})
```

### Integration Testing

**API Integration Tests:**
- Test complete quiz workflow from start to completion
- Test question import process end-to-end
- Test analytics calculation pipeline
- Verify database transactions and rollbacks
- Tools: PHPUnit with database transactions

**Component Integration Tests:**
- Test QuizEngine with real child components
- Test data flow between parent and child components
- Test event emission and handling
- Verify component lifecycle hooks
- Tools: Vitest, Vue Test Utils

**Example Integration Test:**
```php
class QuizWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_quiz_workflow()
    {
        $user = User::factory()->create();
        $questions = Question::factory()->count(5)->create();
        
        // Start quiz attempt
        $response = $this->actingAs($user)
            ->postJson('/api/quiz-attempts', [
                'question_ids' => $questions->pluck('id')
            ]);
        
        $attemptId = $response->json('id');
        
        // Submit answers
        foreach ($questions as $index => $question) {
            $this->postJson("/api/quiz-attempts/{$attemptId}/answers", [
                'question_id' => $question->id,
                'selected_option_id' => $question->options->first()->id
            ]);
        }
        
        // Complete quiz
        $response = $this->putJson("/api/quiz-attempts/{$attemptId}/complete");
        
        $response->assertOk();
        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $attemptId,
            'completed_at' => now()
        ]);
    }
}
```

### End-to-End Testing

**User Journey Tests:**
- Student takes complete quiz from start to finish
- Teacher creates and imports questions
- Student reviews quiz results
- Tools: Playwright or Cypress

**Accessibility Testing:**
- Screen reader compatibility
- Keyboard-only navigation
- Color contrast verification
- Tools: axe-core, Pa11y

### Performance Testing

**Load Testing:**
- Concurrent quiz attempts
- Large question bank queries
- Bulk import operations
- Tools: Apache JMeter, k6

**Metrics to Monitor:**
- Quiz load time < 2 seconds
- Answer submission response < 500ms
- Import processing < 5 seconds per 100 questions
- Database query time < 100ms

## Implementation Notes

### Vue 3 Composition API Patterns

**Composable for Quiz Logic:**
```typescript
// useQuizEngine.ts
export function useQuizEngine(quiz: Ref<QuizQuestion[]>, config: QuizConfig) {
  const currentIndex = ref(0)
  const answers = ref<AnswerRecord[]>([])
  const startTime = ref<Date>(new Date())
  
  const currentQuestion = computed(() => quiz.value[currentIndex.value])
  const progress = computed(() => {
    const answeredCount = answers.value.length
    return (answeredCount / quiz.value.length) * 100
  })
  
  function selectOption(index: number) {
    // Implementation
  }
  
  function goNext() {
    // Implementation
  }
  
  return {
    currentIndex,
    answers,
    currentQuestion,
    progress,
    selectOption,
    goNext
  }
}
```

### Laravel Service Pattern

**QuizService:**
```php
class QuizService
{
    public function startAttempt(User $user, array $questionIds): QuizAttempt
    {
        return DB::transaction(function () use ($user, $questionIds) {
            return QuizAttempt::create([
                'user_id' => $user->id,
                'started_at' => now(),
                'total_questions' => count($questionIds),
                'metadata' => ['question_ids' => $questionIds]
            ]);
        });
    }
    
    public function submitAnswer(QuizAttempt $attempt, Question $question, $selectedOptionId): QuizAttemptAnswer
    {
        $option = QuestionOption::find($selectedOptionId);
        
        return QuizAttemptAnswer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'selected_option_id' => $selectedOptionId,
            'is_correct' => $option->is_correct,
            'answered_at' => now()
        ]);
    }
    
    public function completeAttempt(QuizAttempt $attempt): QuizResult
    {
        $attempt->update(['completed_at' => now()]);
        $attempt->calculateResults();
        
        // Update question analytics
        $this->updateQuestionAnalytics($attempt);
        
        return new QuizResult($attempt);
    }
}
```

### Database Indexing Strategy

**Critical Indexes:**
```sql
-- Questions table
CREATE INDEX idx_questions_type ON questions(question_type_id);
CREATE INDEX idx_questions_grade_subject ON questions(grade_level_id, subject_id);
CREATE INDEX idx_questions_status ON questions(status);
CREATE INDEX idx_questions_author ON questions(author_id);

-- Question options table
CREATE INDEX idx_options_question ON question_options(question_id);

-- Quiz attempts table
CREATE INDEX idx_attempts_user ON quiz_attempts(user_id);
CREATE INDEX idx_attempts_completed ON quiz_attempts(completed_at);

-- Quiz attempt answers table
CREATE INDEX idx_answers_attempt ON quiz_attempt_answers(attempt_id);
CREATE INDEX idx_answers_question ON quiz_attempt_answers(question_id);
```

### Caching Strategy

**Cache Keys:**
- `quiz:{id}:questions` - Quiz questions with options (TTL: 1 hour)
- `question_types:all` - All question types (TTL: 24 hours)
- `user:{id}:attempts:recent` - Recent quiz attempts (TTL: 5 minutes)

**Cache Invalidation:**
- Invalidate quiz cache when questions are updated
- Invalidate question types cache when new types are added
- Invalidate user attempts cache when new attempt is created

### Accessibility Implementation

**ARIA Attributes:**
```vue
<div 
  class="quiz-engine" 
  role="region" 
  aria-label="Quiz Assessment"
  aria-live="polite"
>
  <div 
    class="progress-bar" 
    role="progressbar"
    :aria-valuenow="progress"
    aria-valuemin="0"
    aria-valuemax="100"
    :aria-label="`Quiz progress: ${progress}% complete`"
  />
  
  <ol 
    class="options-list" 
    role="listbox"
    :aria-label="`Question ${currentQuestion.questionNumber} options`"
  >
    <li
      v-for="(option, i) in currentQuestion.answerOptions"
      :key="i"
      role="option"
      :aria-selected="selectedIndex === i"
      :aria-disabled="isAnswered"
      tabindex="0"
      @click="selectOption(i)"
      @keydown.enter.prevent="selectOption(i)"
      @keydown.space.prevent="selectOption(i)"
    >
      {{ option.text }}
    </li>
  </ol>
</div>
```

**Keyboard Navigation:**
- Tab: Move focus between interactive elements
- Enter/Space: Select answer option
- Arrow keys: Navigate between options (optional enhancement)
- Escape: Close modals/dialogs

### Responsive Design Considerations

**Breakpoints:**
- Mobile: < 640px (single column, larger touch targets)
- Tablet: 640px - 1024px (optimized spacing)
- Desktop: > 1024px (full feature set)

**Mobile Optimizations:**
- Larger touch targets (minimum 44x44px)
- Simplified navigation (bottom sheet for question navigator)
- Reduced animations for performance
- Optimized image loading

### Internationalization (i18n)

**Translation Keys:**
```typescript
{
  "quiz.progress": "Question {current} of {total}",
  "quiz.complete": "{percentage}% Complete",
  "quiz.submit": "Submit Answer",
  "quiz.next": "Next Question",
  "quiz.finish": "Finish Quiz",
  "quiz.correct": "Correct!",
  "quiz.incorrect": "Incorrect",
  "quiz.explanation": "Explanation:",
  "quiz.results.title": "Quiz Results",
  "quiz.results.score": "You scored {correct} out of {total}",
  "quiz.results.percentage": "{percentage}% correct"
}
```

**RTL Support:**
- Use logical CSS properties (margin-inline-start instead of margin-left)
- Mirror UI elements for RTL languages
- Test with Arabic and Hebrew

## Security Considerations

**Input Validation:**
- Sanitize all user inputs (question text, options, answers)
- Validate file uploads (type, size, content)
- Prevent SQL injection through parameterized queries
- Prevent XSS through proper escaping

**Authentication & Authorization:**
- Verify user authentication for all quiz operations
- Check user permissions for question creation/editing
- Prevent unauthorized access to other users' quiz attempts
- Implement rate limiting for API endpoints

**Data Privacy:**
- Encrypt sensitive data at rest
- Use HTTPS for all communications
- Implement proper session management
- Comply with GDPR/COPPA for student data

**Quiz Integrity:**
- Prevent answer tampering through client-side validation bypass
- Implement server-side answer validation
- Track suspicious behavior (rapid submissions, pattern detection)
- Implement quiz attempt timeout mechanisms

## Deployment Considerations

**Environment Configuration:**
- Separate configurations for development, staging, production
- Environment variables for sensitive data (database credentials, API keys)
- Feature flags for gradual rollout

**Database Migrations:**
- Version-controlled migration files
- Rollback strategy for failed migrations
- Data seeding for question types and initial data

**Asset Optimization:**
- Minify and bundle JavaScript/CSS
- Optimize images and media files
- Implement CDN for static assets
- Enable gzip compression

**Monitoring & Logging:**
- Application performance monitoring (APM)
- Error tracking and alerting
- User analytics and behavior tracking
- Database query performance monitoring

**Backup & Recovery:**
- Automated database backups (daily)
- Quiz attempt data retention policy
- Disaster recovery plan
- Data export functionality for compliance
