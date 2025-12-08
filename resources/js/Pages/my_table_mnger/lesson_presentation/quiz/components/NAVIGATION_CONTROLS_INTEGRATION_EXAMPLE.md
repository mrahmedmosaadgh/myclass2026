# NavigationControls Integration Example

## How to Integrate NavigationControls into QuizEngine

The NavigationControls component has been created as a standalone, reusable component. Here's how to integrate it into the existing QuizEngine component.

## Current Implementation (Inline)

The QuizEngine currently has navigation controls implemented inline:

```vue
<!-- Current implementation in QuizEngine.vue -->
<div class="quiz-footer">
  <div class="navigation-controls">
    <button
      v-if="quizConfig.allowReviewMode"
      class="nav-button prev-button"
      :disabled="currentIndex === 0"
      aria-label="Previous question"
      @click="goTo(currentIndex - 1)"
    >
      ← Previous
    </button>

    <button
      class="nav-button next-button"
      :class="{ 'finish-button': isLast }"
      :disabled="!isAnswered && !quizConfig.allowReviewMode"
      :aria-label="isLast ? 'Finish quiz' : 'Next question'"
      @click="goNext"
    >
      {{ isLast ? 'Finish Quiz' : 'Next Question →' }}
    </button>
  </div>
</div>
```

## Refactored Implementation (Using Component)

Replace the inline navigation controls with the NavigationControls component:

### Step 1: Import the Component

```vue
<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import NavigationControls from './components/NavigationControls.vue'
import type { 
  QuizQuestion, 
  QuizConfig, 
  AnswerRecord, 
  QuizResult 
} from '@/types/quiz'

// ... rest of the script ...
</script>
```

### Step 2: Replace the Template

```vue
<template>
  <div class="quiz-engine">
    <!-- ... quiz header and content ... -->

    <!-- Quiz Footer Section -->
    <div class="quiz-footer">
      <!-- Navigation Controls Component -->
      <NavigationControls
        :allow-review-mode="quizConfig.allowReviewMode"
        :is-answered="isAnswered"
        :is-last="isLast"
        :current-index="currentIndex"
        @previous="goTo(currentIndex - 1)"
        @next="goNext"
        @finish="completeQuiz"
      />

      <!-- Question Navigator (review mode only) -->
      <div 
        v-if="quizConfig.allowReviewMode && answers.length === total" 
        class="question-navigator"
        role="navigation"
        aria-label="Question navigation"
      >
        <!-- ... question navigator content ... -->
      </div>
    </div>
  </div>
</template>
```

### Step 3: Remove Inline Styles (Optional)

Since the NavigationControls component has its own scoped styles, you can remove the navigation-related styles from QuizEngine.vue:

```vue
<style scoped>
/* Remove these styles from QuizEngine.vue: */

/* .navigation-controls { ... } */
/* .nav-button { ... } */
/* .prev-button { ... } */
/* .next-button { ... } */
/* .finish-button { ... } */
/* .nav-button:disabled { ... } */
/* .nav-button:focus-visible { ... } */
</style>
```

## Complete Integration Example

Here's a minimal example showing the integration:

```vue
<template>
  <div class="quiz-engine">
    <!-- Quiz Header -->
    <div class="quiz-header">
      <ProgressIndicator
        :current="currentIndex + 1"
        :total="total"
        :percentage="progress"
      />
    </div>

    <!-- Question Content -->
    <div class="question-content">
      <QuestionRenderer
        :question="currentQuestion"
        :selected-index="selectedIndex"
        :is-answered="isAnswered"
        :show-feedback="isAnswered"
        @select="selectOption"
      />
    </div>

    <!-- Quiz Footer with NavigationControls -->
    <div class="quiz-footer">
      <NavigationControls
        :allow-review-mode="quizConfig.allowReviewMode"
        :is-answered="isAnswered"
        :is-last="isLast"
        :current-index="currentIndex"
        @previous="goTo(currentIndex - 1)"
        @next="goNext"
        @finish="completeQuiz"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import NavigationControls from './components/NavigationControls.vue'
import ProgressIndicator from './components/ProgressIndicator.vue'
import QuestionRenderer from './components/QuestionRenderer.vue'

// Props and state
const props = defineProps<{
  quiz: QuizQuestion[]
  config?: Partial<QuizConfig>
}>()

const currentIndex = ref(0)
const answers = ref<AnswerRecord[]>([])

// Computed properties
const currentQuestion = computed(() => props.quiz[currentIndex.value])
const isAnswered = computed(() => 
  answers.value.some(a => a.questionId === currentQuestion.value.id)
)
const isLast = computed(() => currentIndex.value === props.quiz.length - 1)
const total = computed(() => props.quiz.length)
const progress = computed(() => (answers.value.length / total.value) * 100)

// Methods
const selectOption = (index: number) => {
  // ... selection logic ...
}

const goNext = () => {
  if (isLast.value) {
    completeQuiz()
  } else {
    currentIndex.value++
  }
}

const goTo = (index: number) => {
  if (index >= 0 && index < props.quiz.length) {
    currentIndex.value = index
  }
}

const completeQuiz = () => {
  // ... completion logic ...
}
</script>
```

## Benefits of Using the Component

### 1. **Separation of Concerns**
- Navigation logic is isolated in its own component
- Easier to test and maintain
- Cleaner QuizEngine code

### 2. **Reusability**
- Can be used in other quiz-related components
- Consistent navigation UI across the application
- Easy to create variations

### 3. **Maintainability**
- Changes to navigation only require updating one component
- Easier to add new features (e.g., keyboard shortcuts)
- Better code organization

### 4. **Testing**
- Can test NavigationControls independently
- Easier to write unit tests
- Better test coverage

### 5. **Documentation**
- Component has its own documentation
- Props and events are clearly defined
- Demo available for reference

## Event Handling

The NavigationControls component emits three events:

### 1. `previous` Event
```vue
@previous="goTo(currentIndex - 1)"
```
Emitted when the previous button is clicked. Handle by navigating to the previous question.

### 2. `next` Event
```vue
@next="goNext"
```
Emitted when the next button is clicked. Handle by navigating to the next question or completing the quiz if it's the last question.

### 3. `finish` Event
```vue
@finish="completeQuiz"
```
Emitted when the finish button is clicked (only on the last question). Handle by completing the quiz and showing results.

## Prop Binding

The component requires four props:

```vue
<NavigationControls
  :allow-review-mode="quizConfig.allowReviewMode"  <!-- Boolean: Enable/disable review mode -->
  :is-answered="isAnswered"                        <!-- Boolean: Current question answered -->
  :is-last="isLast"                                <!-- Boolean: Is last question -->
  :current-index="currentIndex"                    <!-- Number: Current question index -->
/>
```

## Styling Considerations

The NavigationControls component uses scoped styles, so it won't conflict with QuizEngine styles. However, you may want to adjust the `.quiz-footer` container styles:

```vue
<style scoped>
.quiz-footer {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e5e7eb;
}

/* No need for navigation-specific styles - they're in the component */
</style>
```

## Migration Checklist

- [ ] Import NavigationControls component
- [ ] Replace inline navigation controls with component
- [ ] Bind required props
- [ ] Connect event handlers
- [ ] Remove inline navigation styles (optional)
- [ ] Test all navigation scenarios
- [ ] Verify accessibility features
- [ ] Test responsive behavior

## Testing After Integration

After integrating the component, test the following scenarios:

1. **Sequential Mode**
   - [ ] Next button disabled when question not answered
   - [ ] Next button enabled after answering
   - [ ] Previous button not visible
   - [ ] Finish button appears on last question

2. **Review Mode**
   - [ ] Previous button visible
   - [ ] Previous button disabled at index 0
   - [ ] Navigation always enabled
   - [ ] Can navigate to any question

3. **Accessibility**
   - [ ] Keyboard navigation works
   - [ ] ARIA labels present
   - [ ] Focus indicators visible
   - [ ] Screen reader announcements

4. **Responsive**
   - [ ] Desktop layout correct
   - [ ] Mobile layout stacks vertically
   - [ ] Touch targets adequate size
   - [ ] Buttons full-width on mobile

## Conclusion

The NavigationControls component is ready for integration and provides a cleaner, more maintainable solution for quiz navigation. The component is fully documented, tested, and follows Vue best practices.
