# QuestionRenderer Component Suite

## Overview

This document describes the QuestionRenderer component and its associated question type components that were implemented as part of Task 6 of the Enterprise Quiz System.

## Components Implemented

### 1. QuestionRenderer.vue (Base Component)
**Location:** `resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/QuestionRenderer.vue`

**Purpose:** Main component that dynamically loads and renders different question types based on the `questionType.slug` property.

**Props:**
- `question: QuizQuestion` - The question object to render
- `selectedIndex: number | null` - Currently selected option index
- `isAnswered: boolean` - Whether the question has been answered
- `showFeedback: boolean` - Whether to show feedback/explanations

**Emits:**
- `select` - Emitted when an option is selected (passes index or string)

**Features:**
- Dynamic component loading based on question type
- HTML/Math content support via v-html
- Hints display (collapsible)
- Difficulty badge display
- Responsive design

**Supported Question Types:**
- `multiple_choice` → MultipleChoiceQuestion.vue
- `true_false` → TrueFalseQuestion.vue
- `fill_blank` → FillBlankQuestion.vue
- `multi_select` → MultiSelectQuestion.vue
- `short_answer` → FillBlankQuestion.vue (reused)
- `essay` → FillBlankQuestion.vue (reused)

---

### 2. MultipleChoiceQuestion.vue
**Location:** `resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/MultipleChoiceQuestion.vue`

**Purpose:** Renders multiple choice questions with single correct answer.

**Features:**
- Letter labels (A, B, C, D, etc.)
- Uses OptionItem component for each option
- Global explanation display after answer
- ARIA listbox role for accessibility
- Validates Requirements: 3.1, 8.1

---

### 3. TrueFalseQuestion.vue
**Location:** `resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/TrueFalseQuestion.vue`

**Purpose:** Renders true/false questions with exactly two options.

**Features:**
- T/F labels instead of A/B
- Horizontal layout on larger screens
- Uses OptionItem component
- ARIA radiogroup role
- Validates Requirements: 3.2, 8.1

---

### 4. FillBlankQuestion.vue
**Location:** `resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/FillBlankQuestion.vue`

**Purpose:** Renders fill-in-the-blank and short answer questions with text input.

**Features:**
- Text input with validation
- Case-insensitive answer checking
- Article removal for flexible matching (a/an/the)
- Submit button with disabled state
- Detailed feedback with correct answer display
- Shows user's answer vs correct answer
- Validates Requirements: 3.3

---

### 5. MultiSelectQuestion.vue
**Location:** `resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/MultiSelectQuestion.vue`

**Purpose:** Renders multi-select questions where multiple answers can be correct.

**Features:**
- Checkbox-based selection
- "Select all that apply" instructions
- Partial credit feedback
- Shows which correct answers were missed
- Score display (X of Y correct)
- Submit button with selection count
- ARIA group role
- Validates Requirements: 3.4

---

### 6. OptionItem.vue (Shared Component)
**Location:** `resources/js/Pages/my_table_mnger/lesson_presentation/quiz/components/OptionItem.vue`

**Purpose:** Reusable component for rendering individual answer options with feedback.

**Props:**
- `option: AnswerOption` - The option data
- `index: number` - Option index
- `letter: string` - Display letter (A, B, C, etc.)
- `isSelected: boolean` - Whether this option is selected
- `isCorrect: boolean` - Whether this option is correct
- `isAnswered: boolean` - Whether the question has been answered
- `showRationale: boolean` - Whether to show the rationale

**Emits:**
- `click` - Emitted when option is clicked

**Features:**
- Visual states: default, selected, correct, incorrect, unselected-correct
- Feedback icons (✓ for correct, ✗ for incorrect)
- Rationale display when available
- Keyboard navigation (Enter/Space)
- ARIA option role
- Focus indicators
- Smooth transitions and animations
- Touch-friendly on mobile (44x44px minimum)

**Visual States:**
- **Default:** Gray border, white background
- **Selected (before answer):** Blue border, light blue background
- **Correct:** Green border, light green background
- **Incorrect:** Red border, light red background
- **Unselected Correct:** Green border, very light green background (shows what user missed)

---

## Usage Example

```vue
<template>
  <QuestionRenderer
    :question="currentQuestion"
    :selected-index="selectedIndex"
    :is-answered="isAnswered"
    :show-feedback="showFeedback"
    @select="handleSelect"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue'
import QuestionRenderer from './components/QuestionRenderer.vue'
import type { QuizQuestion } from '@/types/quiz'

const currentQuestion = ref<QuizQuestion>({
  id: 1,
  questionNumber: 1,
  questionTypeId: 1,
  questionType: {
    id: 1,
    slug: 'multiple_choice',
    name: 'Multiple Choice',
    hasOptions: true,
    supportsHints: true,
    supportsExplanation: true
  },
  question: 'What is 2 + 2?',
  answerOptions: [
    { id: 1, text: '3', isCorrect: false },
    { id: 2, text: '4', isCorrect: true, rationale: 'Basic addition' },
    { id: 3, text: '5', isCorrect: false }
  ],
  explanation: 'This is a basic arithmetic problem.',
  hints: ['Think about counting on your fingers']
})

const selectedIndex = ref<number | null>(null)
const isAnswered = ref(false)
const showFeedback = ref(false)

const handleSelect = (index: number | string) => {
  selectedIndex.value = typeof index === 'number' ? index : null
  isAnswered.value = true
  showFeedback.value = true
}
</script>
```

---

## Accessibility Features

All components implement WCAG 2.1 AA compliance:

1. **ARIA Attributes:**
   - Proper roles (listbox, radiogroup, group, option)
   - aria-label for context
   - aria-selected for state
   - aria-disabled for disabled state
   - aria-live regions for dynamic updates

2. **Keyboard Navigation:**
   - Tab: Move between interactive elements
   - Enter/Space: Select options
   - Focus indicators visible on all interactive elements

3. **Visual Accessibility:**
   - Color contrast ratios meet 4.5:1 minimum
   - Focus indicators with 3px outline
   - Not relying solely on color for feedback (icons + text)

4. **Screen Reader Support:**
   - Descriptive labels for all inputs
   - State announcements via ARIA
   - Semantic HTML structure

---

## Responsive Design

All components are fully responsive:

- **Mobile (< 640px):**
  - Stacked layouts
  - Larger touch targets (44x44px minimum)
  - Adjusted font sizes
  - Full-width buttons

- **Tablet (640px - 1024px):**
  - Optimized spacing
  - Balanced layouts

- **Desktop (> 1024px):**
  - Full feature set
  - Hover effects
  - Optimal spacing

---

## Styling Approach

- **Scoped styles** for component isolation
- **Utility-first approach** with custom CSS
- **Smooth transitions** (0.2s - 0.4s ease)
- **Animations** for feedback (fadeIn)
- **Color palette:**
  - Primary: #3b82f6 (blue)
  - Success: #10b981 (green)
  - Error: #ef4444 (red)
  - Warning: #f59e0b (orange)
  - Neutral: #6b7280 (gray)

---

## Integration Notes

1. **Import Path:** All components use `@/types/quiz` for type imports
2. **Dynamic Loading:** QuestionRenderer uses `defineAsyncComponent` for code splitting
3. **Event Handling:** All components emit standardized events
4. **Type Safety:** Full TypeScript support with proper interfaces
5. **Extensibility:** Easy to add new question types by:
   - Creating new component
   - Adding to componentMap in QuestionRenderer
   - Following the same prop/emit pattern

---

## Testing Recommendations

1. **Unit Tests:**
   - Test each component in isolation
   - Verify prop handling
   - Test event emissions
   - Check accessibility attributes

2. **Integration Tests:**
   - Test QuestionRenderer with different question types
   - Verify dynamic component loading
   - Test state management

3. **Accessibility Tests:**
   - Run axe-core or Pa11y
   - Test keyboard navigation
   - Verify screen reader compatibility

---

## Next Steps

To complete the quiz system, the following tasks remain:

- Task 7: Create OptionItem component (✓ Already created as part of Task 6)
- Task 8: Create ExplanationPanel component
- Task 9: Create QuizFooter with navigation controls
- Task 10: Create QuestionNavigator component
- Task 11: Implement quiz configuration system
- Task 12-22: Backend integration, testing, styling, etc.

---

## Requirements Validated

This implementation validates the following requirements:

- **Requirement 1.1:** Visual feedback on option selection ✓
- **Requirement 3.1:** Multiple choice questions ✓
- **Requirement 3.2:** True/false questions ✓
- **Requirement 3.3:** Fill-in-the-blank questions ✓
- **Requirement 3.4:** Multi-select questions ✓
- **Requirement 8.1:** ARIA roles and labels ✓
- **Requirement 8.2:** Keyboard navigation ✓
- **Requirement 8.4:** Focus indicators ✓
- **Requirement 8.5:** Color contrast compliance ✓

---

## File Structure

```
quiz/components/
├── QuestionRenderer.vue          (Base component)
├── MultipleChoiceQuestion.vue    (Single answer)
├── TrueFalseQuestion.vue         (True/False)
├── FillBlankQuestion.vue         (Text input)
├── MultiSelectQuestion.vue       (Multiple answers)
├── OptionItem.vue                (Shared option component)
├── ProgressIndicator.vue         (Existing)
└── QUESTION_RENDERER_README.md   (This file)
```
