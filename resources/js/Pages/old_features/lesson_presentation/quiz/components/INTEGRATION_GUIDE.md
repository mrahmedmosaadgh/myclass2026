# Integration Guide: ProgressIndicator Component

This guide shows how to integrate the new `ProgressIndicator` component into the existing `QuizEngine` component.

## Current Implementation

The `QuizEngine.vue` currently has progress tracking built directly into the template:

```vue
<!-- Current implementation in QuizEngine.vue -->
<div class="quiz-header">
  <div 
    class="progress-bar-container"
    role="progressbar"
    :aria-valuenow="progress"
    aria-valuemin="0"
    aria-valuemax="100"
    :aria-label="`Quiz progress: ${Math.round(progress)}% complete`"
  >
    <div class="progress-info">
      <span class="question-counter">
        Question {{ currentIndex + 1 }} of {{ total }}
      </span>
      <span class="progress-percentage">
        {{ Math.round(progress) }}% Complete
      </span>
    </div>
    <div class="progress-bar">
      <div 
        class="progress-bar-fill" 
        :style="{ width: `${progress}%` }"
      />
    </div>
  </div>
</div>
```

## Refactored Implementation

Replace the above code with the new `ProgressIndicator` component:

### Step 1: Import the Component

Add the import at the top of the `<script setup>` section:

```vue
<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import ProgressIndicator from './components/ProgressIndicator.vue'
import type { 
  QuizQuestion, 
  QuizConfig, 
  AnswerRecord, 
  QuizResult 
} from '@/types/quiz'

// ... rest of the script
</script>
```

### Step 2: Update the Template

Replace the progress tracking section with:

```vue
<div class="quiz-header">
  <ProgressIndicator 
    :current="currentIndex + 1" 
    :total="total" 
    :percentage="progress" 
  />

  <!-- Time Remaining (if time limit is set) -->
  <div 
    v-if="timeRemaining !== undefined" 
    class="time-remaining"
    :class="{ 'time-warning': timeRemaining < 120 }"
  >
    <span>Time Remaining: {{ Math.floor(timeRemaining / 60) }}:{{ String(timeRemaining % 60).padStart(2, '0') }}</span>
  </div>
</div>
```

### Step 3: Remove Redundant Styles

You can now remove these CSS rules from `QuizEngine.vue` as they're handled by the component:

```css
/* These can be removed: */
.progress-bar-container { }
.progress-info { }
.question-counter { }
.progress-percentage { }
.progress-bar { }
.progress-bar-fill { }
```

Keep the `.quiz-header` and `.time-remaining` styles as they're still needed.

## Benefits of Refactoring

1. **Separation of Concerns**: Progress tracking logic is isolated in its own component
2. **Reusability**: The ProgressIndicator can be used in other quiz contexts
3. **Maintainability**: Easier to update and test progress tracking independently
4. **Consistency**: Ensures consistent progress display across the application
5. **Testing**: Component can be tested in isolation

## Complete Example

Here's the complete refactored `quiz-header` section:

```vue
<template>
  <div 
    class="quiz-engine" 
    role="region" 
    aria-label="Quiz Assessment"
    aria-live="polite"
  >
    <!-- Quiz Header Section -->
    <div class="quiz-header">
      <!-- Progress Indicator Component -->
      <ProgressIndicator 
        :current="currentIndex + 1" 
        :total="total" 
        :percentage="progress" 
      />

      <!-- Time Remaining (if time limit is set) -->
      <div 
        v-if="timeRemaining !== undefined" 
        class="time-remaining"
        :class="{ 'time-warning': timeRemaining < 120 }"
      >
        <span>Time Remaining: {{ Math.floor(timeRemaining / 60) }}:{{ String(timeRemaining % 60).padStart(2, '0') }}</span>
      </div>
    </div>

    <!-- Rest of quiz content... -->
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import ProgressIndicator from './components/ProgressIndicator.vue'
import type { 
  QuizQuestion, 
  QuizConfig, 
  AnswerRecord, 
  QuizResult 
} from '@/types/quiz'

// ... existing code ...

const currentIndex = ref<number>(0)
const answers = ref<AnswerRecord[]>([])

const progress = computed<number>(() => {
  const answeredCount = answers.value.length
  const totalQuestions = props.quiz.length
  return totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0
})

const total = computed<number>(() => {
  return props.quiz.length
})

// ... rest of the code ...
</script>

<style scoped>
.quiz-header {
  margin-bottom: 2rem;
}

.time-remaining {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background-color: #dbeafe;
  border-radius: 0.375rem;
  text-align: center;
  font-weight: 500;
  color: #1e40af;
}

.time-remaining.time-warning {
  background-color: #fee2e2;
  color: #991b1b;
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* ... rest of styles ... */
</style>
```

## Testing the Integration

After integrating the component, test the following:

1. **Visual Display**: Progress bar updates correctly as questions are answered
2. **Accessibility**: Screen readers announce progress changes
3. **Responsive Design**: Layout adapts on mobile, tablet, and desktop
4. **Animations**: Progress bar fills smoothly (0.4s transition)
5. **Edge Cases**: 
   - First question (0% complete)
   - Last question (100% complete)
   - Single question quiz
   - Empty quiz (edge case handling)

## Backward Compatibility

The refactored implementation maintains 100% backward compatibility:
- Same visual appearance
- Same accessibility features
- Same responsive behavior
- Same computed properties and methods

The only difference is the internal structure, which is now more modular and maintainable.
