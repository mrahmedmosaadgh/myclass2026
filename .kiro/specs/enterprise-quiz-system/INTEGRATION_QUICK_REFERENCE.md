# Integration Quick Reference

## Using QuestionSlide Component

### In Editor Mode (Teacher View)

```vue
<QuestionSlide
  v-model="slideContent"
  mode="edit"
/>
```

### In Player Mode (Student View)

```vue
<QuestionSlide
  :modelValue="slideContent"
  mode="play"
  :quizConfig="{
    allowReviewMode: false,
    autoAdvance: false,
    showRationaleOnCorrect: true,
    timeLimit: 300  // 5 minutes (optional)
  }"
  :attemptId="uniqueAttemptId"
  :legacyMode="'learn'"
  @answer-selected="handleAnswer"
  @quiz-completed="handleCompletion"
/>
```

## Quiz Configuration Options

```typescript
interface QuizConfig {
  allowReviewMode: boolean        // Allow navigation between questions
  autoAdvance: boolean            // Auto-advance on correct answer
  showRationaleOnCorrect: boolean // Show explanation for correct answers
  timeLimit?: number              // Time limit in seconds (optional)
  shuffleQuestions?: boolean      // Randomize question order (optional)
  shuffleOptions?: boolean        // Randomize option order (optional)
}
```

## Event Handlers

### Answer Selected Event

```typescript
const handleAnswerSelected = (record: AnswerRecord) => {
  // record contains:
  // - questionId: string | number
  // - questionNumber: number
  // - selectedIndex: number
  // - selectedOptionId?: string | number
  // - selectedText?: string
  // - correct: boolean
  // - question: string
  // - correctText: string
  // - timeSpentSec: number
  // - answeredAt: Date
  
  // Save to database
  await axios.post('/api/quiz-attempts/answers', {
    question_id: record.questionId,
    selected_option_id: record.selectedOptionId,
    is_correct: record.correct,
    time_spent_sec: record.timeSpentSec
  });
};
```

### Quiz Completed Event

```typescript
const handleQuizCompleted = (result: QuizResult) => {
  // result contains:
  // - attemptId: string | number
  // - total: number
  // - correct: number
  // - percentage: number
  // - answers: AnswerRecord[]
  // - completedAt: Date
  // - metadata?: Record<string, any>
  
  // Save to database
  await axios.post('/api/quiz-attempts', {
    attempt_id: result.attemptId,
    total_questions: result.total,
    correct_answers: result.correct,
    percentage: result.percentage,
    completed_at: result.completedAt,
    answers: result.answers
  });
};
```

## Question Format Conversion

### Legacy Format (Input)

```javascript
{
  id: 'q_abc123',
  type: 'single_choice',
  text: 'What is 2 + 2?',
  options: [
    { id: 'opt_1', text: '3' },
    { id: 'opt_2', text: '4' },
    { id: 'opt_3', text: '5' }
  ],
  correct_answer: 'opt_2',
  explanation: 'Two plus two equals four',
  hints: ['Think about basic addition'],
  timer: 30
}
```

### QuizEngine Format (Output)

```javascript
{
  id: 'q_abc123',
  questionNumber: 1,
  questionTypeId: 1,
  questionType: {
    id: 1,
    slug: 'single_choice',
    name: 'Multiple Choice',
    hasOptions: true,
    supportsHints: true,
    supportsExplanation: true
  },
  question: 'What is 2 + 2?',
  answerOptions: [
    { id: 'opt_1', text: '3', isCorrect: false },
    { id: 'opt_2', text: '4', isCorrect: true },
    { id: 'opt_3', text: '5', isCorrect: false }
  ],
  explanation: 'Two plus two equals four',
  hints: ['Think about basic addition'],
  estimatedTimeSec: 30
}
```

## Migration Commands

### Run Migration

```bash
php artisan migrate --path=database/migrations/2025_11_26_000000_migrate_lesson_questions_to_quiz_system.php
```

### Validate Migration

```bash
php artisan quiz:validate-migration
```

### Check Logs

```bash
tail -f storage/logs/laravel.log | grep "Question Migration"
```

## API Endpoints to Implement

### Save Individual Answer

```
POST /api/quiz-attempts/answers
Content-Type: application/json

{
  "presentation_id": 123,
  "slide_id": 456,
  "question_id": "q_abc123",
  "selected_option_id": "opt_2",
  "selected_text": null,
  "is_correct": true,
  "time_spent_sec": 15,
  "answered_at": "2025-11-26T10:30:00Z"
}

Response: 201 Created
{
  "id": 789,
  "message": "Answer saved successfully"
}
```

### Save Quiz Attempt

```
POST /api/quiz-attempts
Content-Type: application/json

{
  "presentation_id": 123,
  "slide_id": 456,
  "attempt_id": "attempt_123_456_1732618200000",
  "total_questions": 5,
  "correct_answers": 4,
  "percentage": 80.00,
  "completed_at": "2025-11-26T10:35:00Z",
  "answers": [...],
  "metadata": {
    "startTime": "2025-11-26T10:30:00Z",
    "totalTimeSec": 300
  }
}

Response: 201 Created
{
  "id": 101,
  "message": "Quiz attempt saved successfully"
}
```

## Compatibility Check

```javascript
// Check if questions are compatible with QuizEngine
const isCompatible = (questions) => {
  const compatibleTypes = ['single_choice', 'multiple_choice', 'true_false'];
  return questions.every(q => compatibleTypes.includes(q.type));
};

// Usage
if (isCompatible(slideContent.questions)) {
  // Use QuizEngine
} else {
  // Use UniversalQuestionPlayer
}
```

## Common Issues

### Issue: Questions not rendering

**Check**:
1. Question type is compatible (single_choice, multiple_choice, true_false)
2. Questions array is not empty
3. Each question has required fields (id, type, text, options)

### Issue: Answers not saving

**Check**:
1. API endpoints are implemented
2. User is authenticated
3. Network requests are successful (check browser console)
4. Database tables exist

### Issue: Migration skipped questions

**Check**:
1. Question type is compatible
2. Question is not already migrated
3. Question has valid data structure
4. Check migration logs for specific errors

## TypeScript Types

```typescript
import type {
  QuizQuestion,
  QuizConfig,
  AnswerRecord,
  QuizResult,
  AnswerOption,
  QuestionType
} from '@/types/quiz';
```

## Preview Mode

```vue
<!-- Disable database saves in preview mode -->
<QuestionSlide
  :modelValue="slideContent"
  mode="play"
  :quizConfig="quizConfig"
  @answer-selected="(record) => {
    if (!isPreview) {
      saveAnswer(record);
    }
  }"
  @quiz-completed="(result) => {
    if (!isPreview) {
      saveQuizAttempt(result);
    } else {
      showPreviewResults(result);
    }
  }"
/>
```

## Testing

### Unit Test Example

```javascript
import { mount } from '@vue/test-utils';
import QuestionSlide from './QuestionSlide.vue';

test('renders in play mode with QuizEngine', () => {
  const wrapper = mount(QuestionSlide, {
    props: {
      modelValue: {
        questions: [
          {
            id: 'q1',
            type: 'single_choice',
            text: 'Test question?',
            options: [
              { id: 'a', text: 'Option A' },
              { id: 'b', text: 'Option B' }
            ],
            correct_answer: 'a'
          }
        ]
      },
      mode: 'play'
    }
  });
  
  expect(wrapper.find('.quiz-engine').exists()).toBe(true);
});
```

## Performance Tips

1. **Lazy Load**: Import QuizEngine only when needed
2. **Memoize**: Use computed properties for data transformations
3. **Debounce**: Debounce API calls for answer submissions
4. **Cache**: Cache quiz configurations
5. **Optimize**: Minimize re-renders with proper key usage

## Security Considerations

1. **Validate**: Always validate answers on the server
2. **Sanitize**: Sanitize question text to prevent XSS
3. **Authenticate**: Require authentication for API endpoints
4. **Rate Limit**: Implement rate limiting on answer submissions
5. **Audit**: Log all quiz attempts for auditing

## Support

- Documentation: `QUIZ_SYSTEM_MIGRATION_GUIDE.md`
- Integration Summary: `INTEGRATION_SUMMARY.md`
- Design Document: `.kiro/specs/enterprise-quiz-system/design.md`
- Requirements: `.kiro/specs/enterprise-quiz-system/requirements.md`
