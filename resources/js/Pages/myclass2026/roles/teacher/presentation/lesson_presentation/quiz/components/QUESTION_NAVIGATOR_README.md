# QuestionNavigator Component

## Overview

The `QuestionNavigator` component provides visual navigation dots for all questions in a quiz when review mode is enabled. It displays the answered status of each question and allows students to jump directly to any question by clicking on its corresponding navigation dot.

## Features

- **Visual Navigation**: Displays numbered dots for each question in the quiz
- **Answered Indicators**: Visually distinguishes answered questions from unanswered ones
- **Current Question Highlight**: Clearly indicates which question is currently active
- **Direct Navigation**: Allows clicking on any dot to jump to that question
- **Accessibility**: Full ARIA support with keyboard navigation
- **Responsive Design**: Adapts to mobile, tablet, and desktop screens
- **Touch-Friendly**: Minimum 44x44px touch targets for mobile devices

## Usage

### Basic Usage

```vue
<template>
  <QuestionNavigator
    :questions="quizQuestions"
    :current-index="currentQuestionIndex"
    :answers="studentAnswers"
    @navigate="handleNavigate"
  />
</template>

<script setup lang="ts">
import QuestionNavigator from './components/QuestionNavigator.vue'
import type { QuizQuestion, AnswerRecord } from '@/types/quiz'

const quizQuestions = ref<QuizQuestion[]>([...])
const currentQuestionIndex = ref(0)
const studentAnswers = ref<AnswerRecord[]>([])

const handleNavigate = (index: number) => {
  currentQuestionIndex.value = index
  // Additional navigation logic
}
</script>
```

### Integration with QuizEngine

The QuestionNavigator is already integrated into the QuizEngine component and appears automatically when:
1. Review mode is enabled (`config.allowReviewMode = true`)
2. All questions have been answered

```vue
<!-- Inside QuizEngine.vue -->
<div 
  v-if="quizConfig.allowReviewMode && answers.length === total" 
  class="question-navigator-container"
>
  <QuestionNavigator
    :questions="quiz"
    :current-index="currentIndex"
    :answers="answers"
    @navigate="goTo"
  />
</div>
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `questions` | `QuizQuestion[]` | Yes | Array of all quiz questions |
| `currentIndex` | `number` | Yes | Current question index (0-based) |
| `answers` | `AnswerRecord[]` | Yes | Array of answer records to determine which questions are answered |

### Prop Details

#### `questions`
An array of `QuizQuestion` objects representing all questions in the quiz. Each question must have a unique `id` property.

```typescript
interface QuizQuestion {
  id: string | number
  questionNumber: number
  questionTypeId: number
  questionType: QuestionType
  question: string
  answerOptions: AnswerOption[]
  // ... other properties
}
```

#### `currentIndex`
Zero-based index of the currently displayed question. This determines which navigation dot is highlighted as "current".

#### `answers`
Array of `AnswerRecord` objects representing all answered questions. The component uses this to determine which dots should be marked as "answered".

```typescript
interface AnswerRecord {
  questionId: string | number
  questionNumber: number
  selectedIndex: number
  correct: boolean
  // ... other properties
}
```

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `navigate` | `number` | Emitted when a navigation dot is clicked. Payload is the target question index (0-based) |

### Event Details

#### `navigate`
Fired when a user clicks on any navigation dot. The parent component should handle this event to update the current question.

```vue
<QuestionNavigator
  :questions="questions"
  :current-index="currentIndex"
  :answers="answers"
  @navigate="(index) => currentIndex = index"
/>
```

## Visual States

### Default State
- **Border**: Light gray (#d1d5db)
- **Background**: White (#ffffff)
- **Text**: Gray (#6b7280)

### Hover State
- **Border**: Blue (#3b82f6)
- **Background**: Light blue (#eff6ff)
- **Text**: Dark blue (#1e40af)

### Current Question State
- **Border**: Blue (#3b82f6)
- **Background**: Blue (#3b82f6)
- **Text**: White (#ffffff)
- **Font Weight**: 600 (semi-bold)

### Answered Question State
- **Border**: Green (#10b981)
- **Background**: Light green (#d1fae5)
- **Text**: Dark green (#065f46)

### Current + Answered State
- **Border**: Green (#10b981)
- **Background**: Green (#10b981)
- **Text**: White (#ffffff)

## Accessibility

### ARIA Attributes

The component implements comprehensive ARIA attributes for screen reader support:

```html
<div 
  class="question-navigator"
  role="navigation"
  aria-label="Question navigation"
>
  <button
    class="nav-dot"
    aria-label="Go to question 1 (answered)"
    aria-current="true"
  >
    1
  </button>
</div>
```

### Keyboard Navigation

- **Tab**: Move focus between navigation dots
- **Enter/Space**: Navigate to the focused question
- **Shift+Tab**: Move focus backward

### Focus Indicators

Visible focus indicators are provided for keyboard users:
- 2px solid blue outline (#3b82f6)
- 2px offset from the element

### Screen Reader Announcements

Each navigation dot includes:
- Question number
- Answered status (if applicable)
- Current status (if applicable)

Example: "Go to question 3 (answered)"

## Responsive Design

### Mobile (< 640px)
- Smaller dots: 2.25rem × 2.25rem
- Reduced gap: 0.375rem
- Centered alignment
- Reduced padding

### Tablet (641px - 1024px)
- Standard dots: 2.5rem × 2.5rem
- Medium gap: 0.625rem

### Desktop (> 1024px)
- Standard dots: 2.5rem × 2.5rem
- Standard gap: 0.5rem

### Touch Targets

All navigation dots meet WCAG 2.1 Level AA requirements:
- Minimum size: 44px × 44px
- Adequate spacing between targets

## Styling

### CSS Variables

The component uses standard Tailwind CSS colors. You can customize by overriding the scoped styles:

```vue
<style scoped>
.nav-dot {
  /* Override default styles */
  border-color: var(--custom-border-color);
  background-color: var(--custom-bg-color);
}
</style>
```

### Custom Themes

To apply custom themes, you can use CSS custom properties:

```css
.question-navigator {
  --nav-dot-border: #your-color;
  --nav-dot-bg: #your-color;
  --nav-dot-text: #your-color;
  --nav-dot-current: #your-color;
  --nav-dot-answered: #your-color;
}
```

## Performance Considerations

### Rendering Optimization

The component uses `v-for` with `:key` binding to ensure efficient rendering:

```vue
<button
  v-for="(question, index) in questions"
  :key="question.id"
  class="nav-dot"
>
  {{ index + 1 }}
</button>
```

### Large Question Sets

For quizzes with many questions (50+), consider:
1. Implementing virtual scrolling
2. Showing only a subset of dots with pagination
3. Adding a "Jump to question" dropdown for direct access

## Browser Support

- Chrome/Edge: ✓ Full support
- Firefox: ✓ Full support
- Safari: ✓ Full support
- Mobile browsers: ✓ Full support with touch optimization

## Examples

### Example 1: Basic Integration

```vue
<template>
  <div class="quiz-container">
    <QuestionDisplay :question="currentQuestion" />
    
    <QuestionNavigator
      v-if="showNavigator"
      :questions="allQuestions"
      :current-index="currentIndex"
      :answers="answers"
      @navigate="goToQuestion"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import QuestionNavigator from './QuestionNavigator.vue'

const currentIndex = ref(0)
const answers = ref([])
const allQuestions = ref([...])

const showNavigator = computed(() => {
  return answers.value.length === allQuestions.value.length
})

const goToQuestion = (index: number) => {
  currentIndex.value = index
}
</script>
```

### Example 2: With Custom Styling

```vue
<template>
  <QuestionNavigator
    class="custom-navigator"
    :questions="questions"
    :current-index="currentIndex"
    :answers="answers"
    @navigate="handleNavigate"
  />
</template>

<style>
.custom-navigator .nav-dot {
  border-radius: 50%; /* Make dots circular */
  font-size: 1rem;
}

.custom-navigator .nav-dot.current {
  transform: scale(1.2); /* Enlarge current dot */
}
</style>
```

### Example 3: Conditional Display

```vue
<template>
  <QuestionNavigator
    v-if="config.allowReviewMode && allQuestionsAnswered"
    :questions="questions"
    :current-index="currentIndex"
    :answers="answers"
    @navigate="navigateToQuestion"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue'

const allQuestionsAnswered = computed(() => {
  return answers.value.length === questions.value.length
})
</script>
```

## Testing

### Unit Tests

```typescript
import { mount } from '@vue/test-utils'
import QuestionNavigator from './QuestionNavigator.vue'

describe('QuestionNavigator', () => {
  it('renders correct number of dots', () => {
    const wrapper = mount(QuestionNavigator, {
      props: {
        questions: mockQuestions,
        currentIndex: 0,
        answers: []
      }
    })
    
    expect(wrapper.findAll('.nav-dot')).toHaveLength(mockQuestions.length)
  })

  it('highlights current question', () => {
    const wrapper = mount(QuestionNavigator, {
      props: {
        questions: mockQuestions,
        currentIndex: 2,
        answers: []
      }
    })
    
    const dots = wrapper.findAll('.nav-dot')
    expect(dots[2].classes()).toContain('current')
  })

  it('emits navigate event on click', async () => {
    const wrapper = mount(QuestionNavigator, {
      props: {
        questions: mockQuestions,
        currentIndex: 0,
        answers: []
      }
    })
    
    await wrapper.findAll('.nav-dot')[3].trigger('click')
    expect(wrapper.emitted('navigate')).toBeTruthy()
    expect(wrapper.emitted('navigate')[0]).toEqual([3])
  })
})
```

## Troubleshooting

### Issue: Dots not showing answered state

**Solution**: Ensure the `answers` array contains `AnswerRecord` objects with matching `questionId` values:

```typescript
// Correct
const answer: AnswerRecord = {
  questionId: question.id, // Must match question.id
  // ... other properties
}
```

### Issue: Navigation not working

**Solution**: Ensure you're handling the `navigate` event:

```vue
<QuestionNavigator
  @navigate="(index) => currentIndex = index"
/>
```

### Issue: Current question not highlighted

**Solution**: Verify `currentIndex` is 0-based and within bounds:

```typescript
// Correct
currentIndex.value = 0 // First question
currentIndex.value = questions.length - 1 // Last question
```

## Related Components

- **QuizEngine**: Main quiz component that integrates QuestionNavigator
- **NavigationControls**: Previous/Next button navigation
- **ProgressIndicator**: Visual progress bar for quiz completion

## Requirements Validation

This component satisfies the following requirements from the design document:

- **Requirement 4.1**: ✓ Displays navigation controls for moving between questions
- **Requirement 4.3**: ✓ Indicates which questions have been answered
- **Requirement 8.1**: ✓ Includes appropriate ARIA roles and labels
- **Requirement 8.2**: ✓ Supports keyboard navigation (Enter/Space keys)
- **Requirement 8.4**: ✓ Provides visible focus indicators

## License

Part of the Enterprise Quiz System. See main project license.
