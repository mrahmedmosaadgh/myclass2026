# NavigationControls Component

## Overview

The `NavigationControls` component provides navigation buttons for moving between quiz questions. It supports both sequential navigation (forward only) and review mode (forward and backward navigation).

## Features

- **Previous Button**: Appears only in review mode, allows backward navigation
- **Next/Finish Button**: Changes text and styling based on whether it's the last question
- **Disabled States**: Automatically disables buttons based on quiz state
- **Accessibility**: Full keyboard navigation and ARIA labels
- **Responsive**: Mobile-optimized with touch-friendly targets (min 44x44px)
- **Visual Feedback**: Hover states and smooth transitions

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `allowReviewMode` | `boolean` | Yes | Whether review mode is enabled (shows previous button) |
| `isAnswered` | `boolean` | Yes | Whether the current question has been answered |
| `isLast` | `boolean` | Yes | Whether the current question is the last question |
| `currentIndex` | `number` | Yes | Current question index (0-based) |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `previous` | None | Emitted when the previous button is clicked |
| `next` | None | Emitted when the next button is clicked |
| `finish` | None | Emitted when the finish button is clicked (last question) |

## Usage

### Basic Usage (Sequential Mode)

```vue
<template>
  <NavigationControls
    :allow-review-mode="false"
    :is-answered="isAnswered"
    :is-last="currentIndex === totalQuestions - 1"
    :current-index="currentIndex"
    @next="goNext"
    @finish="completeQuiz"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue'
import NavigationControls from './NavigationControls.vue'

const currentIndex = ref(0)
const isAnswered = ref(false)
const totalQuestions = 10

const goNext = () => {
  currentIndex.value++
  isAnswered.value = false
}

const completeQuiz = () => {
  console.log('Quiz completed!')
}
</script>
```

### Review Mode Usage

```vue
<template>
  <NavigationControls
    :allow-review-mode="true"
    :is-answered="true"
    :is-last="currentIndex === totalQuestions - 1"
    :current-index="currentIndex"
    @previous="goPrevious"
    @next="goNext"
    @finish="completeQuiz"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue'
import NavigationControls from './NavigationControls.vue'

const currentIndex = ref(5)
const totalQuestions = 10

const goPrevious = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--
  }
}

const goNext = () => {
  if (currentIndex.value < totalQuestions - 1) {
    currentIndex.value++
  }
}

const completeQuiz = () => {
  console.log('Quiz completed!')
}
</script>
```

## Behavior

### Button Visibility

- **Previous Button**: Only visible when `allowReviewMode` is `true`
- **Next Button**: Always visible, changes to "Finish Quiz" when `isLast` is `true`

### Button States

#### Previous Button
- **Enabled**: When `currentIndex > 0`
- **Disabled**: When `currentIndex === 0`

#### Next/Finish Button
- **Enabled**: When `isAnswered` is `true` OR `allowReviewMode` is `true`
- **Disabled**: When `isAnswered` is `false` AND `allowReviewMode` is `false`

### Visual States

#### Next Button
- **Default**: Blue background (`#3b82f6`)
- **Hover**: Darker blue (`#2563eb`)
- **Last Question**: Green background (`#10b981`)
- **Last Question Hover**: Darker green (`#059669`)
- **Disabled**: Gray background with reduced opacity

#### Previous Button
- **Default**: Light gray background (`#f3f4f6`)
- **Hover**: Darker gray (`#e5e7eb`)
- **Disabled**: Gray with reduced opacity

## Accessibility

### ARIA Attributes

- `aria-label="Previous question"` on previous button
- `aria-label="Next question"` or `aria-label="Finish quiz"` on next/finish button

### Keyboard Navigation

- **Tab**: Focus navigation buttons
- **Enter/Space**: Activate focused button
- **Focus Indicators**: Visible outline on focus

### Touch Targets

- Minimum size: 44x44px (WCAG 2.1 AA compliant)
- Adequate spacing between buttons

## Responsive Design

### Desktop (> 640px)
- Horizontal layout with space-between
- Previous button on left, next button on right

### Mobile (≤ 640px)
- Vertical layout (stacked)
- Full-width buttons
- Maintained touch-friendly sizing

## Styling

The component uses scoped styles with the following features:

- **Transitions**: Smooth 0.2s transitions on all interactive states
- **Hover Effects**: Darker backgrounds on hover
- **Focus Indicators**: 2px blue outline with offset
- **Disabled States**: Reduced opacity and not-allowed cursor
- **High Contrast Mode**: Enhanced borders for better visibility
- **Reduced Motion**: Respects user's motion preferences

## Integration with QuizEngine

The NavigationControls component is designed to be used within the QuizEngine component:

```vue
<template>
  <div class="quiz-engine">
    <!-- ... other quiz content ... -->
    
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
```

## Requirements Validation

This component satisfies the following requirements:

- **Requirement 4.1**: Navigation controls for moving between questions
- **Requirement 4.2**: Proper navigation transitions
- **Requirement 8.4**: Touch-friendly sizing and visible focus indicators

## Demo

See `NavigationControlsDemo.vue` for interactive examples of all component states and behaviors.

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Chrome Mobile)
- Supports accessibility features (screen readers, keyboard navigation)

## Notes

- The component emits events rather than directly manipulating state, following Vue best practices
- All styling is scoped to prevent conflicts with other components
- The component is fully typed with TypeScript for type safety
- Responsive design ensures usability across all device sizes
