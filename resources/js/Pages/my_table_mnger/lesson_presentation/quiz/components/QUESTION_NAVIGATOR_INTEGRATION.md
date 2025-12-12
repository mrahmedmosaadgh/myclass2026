# QuestionNavigator Component Integration Guide

## Overview

This guide explains how to integrate the standalone `QuestionNavigator` component into the `QuizEngine` component, replacing the inline implementation with the reusable component.

## Current Implementation

The `QuizEngine` component currently has an inline question navigator implementation in the footer section:

```vue
<!-- Current inline implementation in QuizEngine.vue -->
<div 
  v-if="quizConfig.allowReviewMode && answers.length === total" 
  class="question-navigator"
  role="navigation"
  aria-label="Question navigation"
>
  <div class="navigator-label">Jump to question:</div>
  <div class="navigator-dots">
    <button
      v-for="(question, index) in quiz"
      :key="question.id"
      class="nav-dot"
      :class="{
        'current': index === currentIndex,
        'answered': answers.some(a => a.questionId === question.id)
      }"
      :aria-label="`Go to question ${index + 1}`"
      :aria-current="index === currentIndex ? 'true' : 'false'"
      @click="goTo(index)"
    >
      {{ index + 1 }}
    </button>
  </div>
</div>
```

## New Component-Based Implementation

### Step 1: Import the Component

Add the import statement at the top of the `<script setup>` section in `QuizEngine.vue`:

```typescript
import QuestionNavigator from './components/QuestionNavigator.vue'
```

### Step 2: Replace the Inline Implementation

Replace the inline question navigator div with the component:

```vue
<!-- New component-based implementation -->
<QuestionNavigator
  v-if="quizConfig.allowReviewMode && answers.length === total"
  :questions="quiz"
  :current-index="currentIndex"
  :answers="answers"
  @navigate="goTo"
/>
```

### Step 3: Remove Inline Styles (Optional)

If you replace the inline implementation, you can remove the corresponding CSS from the `<style scoped>` section:

```css
/* These styles can be removed if using the component */
.question-navigator { /* ... */ }
.navigator-label { /* ... */ }
.navigator-dots { /* ... */ }
.nav-dot { /* ... */ }
.nav-dot:hover { /* ... */ }
.nav-dot.current { /* ... */ }
.nav-dot.answered { /* ... */ }
.nav-dot.answered.current { /* ... */ }
```

## Complete Integration Example

Here's the complete updated footer section of `QuizEngine.vue`:

```vue
<template>
  <div class="quiz-engine" role="region" aria-label="Quiz Assessment">
    <!-- ... other sections ... -->

    <!-- Quiz Footer Section -->
    <div class="quiz-footer">
      <!-- Navigation Controls -->
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

      <!-- Question Navigator Component (review mode only) -->
      <QuestionNavigator
        v-if="quizConfig.allowReviewMode && answers.length === total"
        :questions="quiz"
        :current-index="currentIndex"
        :answers="answers"
        @navigate="goTo"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import QuestionNavigator from './components/QuestionNavigator.vue'
import type { 
  QuizQuestion, 
  QuizConfig, 
  AnswerRecord, 
  QuizResult 
} from '@/types/quiz'

// ... rest of the component code ...
</script>
```

## Benefits of Component-Based Approach

### 1. Reusability
The `QuestionNavigator` can now be used in other contexts:
- Standalone quiz review pages
- Quiz result pages with navigation
- Quiz preview interfaces
- Admin question management tools

### 2. Maintainability
- Single source of truth for navigator logic
- Easier to test in isolation
- Clearer separation of concerns
- Simpler to update styling or behavior

### 3. Testability
- Can be unit tested independently
- Easier to mock in parent component tests
- Clearer test scenarios

### 4. Documentation
- Self-contained component with its own documentation
- Props and events are explicitly defined
- TypeScript types provide clear contracts

## Migration Checklist

- [ ] Import `QuestionNavigator` component in `QuizEngine.vue`
- [ ] Replace inline navigator div with `<QuestionNavigator>` component
- [ ] Pass required props: `questions`, `current-index`, `answers`
- [ ] Connect `@navigate` event to `goTo` method
- [ ] Test navigation functionality
- [ ] Test answered state indicators
- [ ] Test current question highlighting
- [ ] Test keyboard navigation
- [ ] Test responsive behavior on mobile
- [ ] Remove unused inline navigator styles (optional)
- [ ] Update any related tests

## Backward Compatibility

The component-based implementation maintains 100% backward compatibility:
- Same visual appearance
- Same behavior and interactions
- Same accessibility features
- Same responsive design
- Same event handling

## Testing After Integration

### Manual Testing

1. **Basic Navigation**
   - Enable review mode
   - Answer all questions
   - Verify navigator appears
   - Click on different question numbers
   - Verify navigation works correctly

2. **Visual States**
   - Verify current question is highlighted
   - Verify answered questions show green state
   - Verify unanswered questions show default state
   - Verify hover states work

3. **Accessibility**
   - Test keyboard navigation (Tab, Enter, Space)
   - Test with screen reader
   - Verify ARIA labels are announced
   - Verify focus indicators are visible

4. **Responsive Design**
   - Test on mobile (< 640px)
   - Test on tablet (640px - 1024px)
   - Test on desktop (> 1024px)
   - Verify touch targets are adequate

### Automated Testing

```typescript
import { mount } from '@vue/test-utils'
import QuizEngine from './QuizEngine.vue'
import QuestionNavigator from './components/QuestionNavigator.vue'

describe('QuizEngine with QuestionNavigator', () => {
  it('renders QuestionNavigator when all questions answered in review mode', () => {
    const wrapper = mount(QuizEngine, {
      props: {
        quiz: mockQuestions,
        config: { allowReviewMode: true }
      }
    })

    // Answer all questions
    mockQuestions.forEach((_, index) => {
      wrapper.vm.selectOption(0)
      if (index < mockQuestions.length - 1) {
        wrapper.vm.goNext()
      }
    })

    await wrapper.vm.$nextTick()

    // Verify QuestionNavigator is rendered
    expect(wrapper.findComponent(QuestionNavigator).exists()).toBe(true)
  })

  it('navigates to question when QuestionNavigator emits navigate event', async () => {
    const wrapper = mount(QuizEngine, {
      props: {
        quiz: mockQuestions,
        config: { allowReviewMode: true }
      }
    })

    // Answer all questions to show navigator
    // ... answer logic ...

    const navigator = wrapper.findComponent(QuestionNavigator)
    await navigator.vm.$emit('navigate', 3)

    expect(wrapper.vm.currentIndex).toBe(3)
  })
})
```

## Troubleshooting

### Issue: Navigator not appearing

**Possible causes:**
1. Review mode not enabled: `config.allowReviewMode` must be `true`
2. Not all questions answered: `answers.length` must equal `quiz.length`
3. Component not imported correctly

**Solution:**
```vue
<script setup lang="ts">
// Ensure import is correct
import QuestionNavigator from './components/QuestionNavigator.vue'

// Verify config
const quizConfig = computed(() => ({
  allowReviewMode: true, // Must be true
  // ... other config
}))
</script>
```

### Issue: Navigation not working

**Possible cause:** Event handler not connected

**Solution:**
```vue
<!-- Ensure @navigate is connected to goTo method -->
<QuestionNavigator
  @navigate="goTo"
/>
```

### Issue: Styling looks different

**Possible cause:** CSS conflicts or missing styles

**Solution:**
The component has scoped styles. If you need custom styling, use deep selectors:

```vue
<style scoped>
:deep(.question-navigator) {
  /* Custom styles */
}
</style>
```

## Alternative: Keep Both Implementations

If you prefer to keep the inline implementation for now, you can use the component in other contexts while maintaining the existing code in `QuizEngine`. This allows for gradual migration:

```vue
<!-- Use inline implementation in QuizEngine -->
<div v-if="quizConfig.allowReviewMode && answers.length === total" 
     class="question-navigator">
  <!-- inline implementation -->
</div>

<!-- Use component in other pages -->
<QuestionNavigator
  :questions="questions"
  :current-index="currentIndex"
  :answers="answers"
  @navigate="handleNavigate"
/>
```

## Next Steps

After successful integration:

1. **Update Documentation**: Update any documentation that references the inline navigator
2. **Update Tests**: Update integration tests to use the component
3. **Code Review**: Have the changes reviewed by team members
4. **Deploy**: Deploy to staging environment for testing
5. **Monitor**: Monitor for any issues after deployment

## Related Documentation

- [QuestionNavigator Component README](./QUESTION_NAVIGATOR_README.md)
- [QuestionNavigator Demo](./QuestionNavigatorDemo.vue)
- [QuizEngine Documentation](../README.md)

## Support

For questions or issues with the integration, please refer to:
- Component documentation
- Demo file for usage examples
- Unit tests for implementation details
