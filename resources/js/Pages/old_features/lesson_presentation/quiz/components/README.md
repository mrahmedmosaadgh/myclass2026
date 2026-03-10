# Quiz Components

This directory contains reusable components for the Enterprise Quiz System.

## ProgressIndicator Component

A fully accessible progress indicator component that displays quiz completion status with a visual progress bar and question counter.

### Features

- ✅ Visual progress bar with smooth animation
- ✅ Question counter (e.g., "Question 5 of 10")
- ✅ Percentage display (e.g., "50% Complete")
- ✅ Full ARIA support for screen readers
- ✅ WCAG 2.1 AA color contrast compliance
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ Reduced motion support
- ✅ High contrast mode support

### Usage

```vue
<template>
  <ProgressIndicator 
    :current="5" 
    :total="10" 
    :percentage="50" 
  />
</template>

<script setup>
import ProgressIndicator from './components/ProgressIndicator.vue'
</script>
```

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `current` | `number` | Yes | Current question number (1-based) |
| `total` | `number` | Yes | Total number of questions |
| `percentage` | `number` | Yes | Completion percentage (0-100) |

### Accessibility Features

1. **ARIA Attributes**
   - `role="progressbar"` on the progress bar
   - `aria-valuenow`, `aria-valuemin`, `aria-valuemax` for current progress
   - `aria-label` with descriptive text
   - `aria-live="polite"` region for screen reader announcements

2. **Screen Reader Support**
   - Hidden live region announces progress changes
   - Descriptive labels for all visual elements

3. **Keyboard Navigation**
   - No interactive elements (display-only component)

4. **Color Contrast**
   - Question counter: 16.1:1 (AAA)
   - Progress percentage: 4.54:1 (AA)
   - Progress bar: 3.8:1 (AA for graphics)

### Responsive Breakpoints

- **Mobile** (< 640px): Stacked layout, smaller fonts
- **Tablet** (640px - 1024px): Optimized spacing
- **Desktop** (> 1024px): Full-size layout

### Demo

To see the component in action, check out `ProgressIndicatorDemo.vue` which includes:
- Interactive controls to test different states
- Multiple test cases (0%, 50%, 90%, 100%)
- Real-time percentage calculation

### Integration with QuizEngine

The ProgressIndicator component can be integrated into the QuizEngine component:

```vue
<template>
  <div class="quiz-engine">
    <div class="quiz-header">
      <ProgressIndicator 
        :current="currentIndex + 1" 
        :total="quiz.length" 
        :percentage="progress" 
      />
    </div>
    <!-- Rest of quiz content -->
  </div>
</template>

<script setup>
import ProgressIndicator from './components/ProgressIndicator.vue'
import { computed } from 'vue'

const progress = computed(() => {
  const answeredCount = answers.value.length
  return (answeredCount / quiz.value.length) * 100
})
</script>
```

### Requirements Validation

This component satisfies the following requirements from the design document:

- **Requirement 2.1**: Progress bar showing completion percentage ✅
- **Requirement 2.2**: Real-time progress updates ✅
- **Requirement 2.3**: Current question number and total count display ✅
- **Requirement 2.5**: ARIA live regions for screen reader announcements ✅
- **Requirement 8.5**: Color contrast compliance (WCAG 2.1 AA) ✅

### Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

### Performance

- Lightweight component (~2KB gzipped)
- CSS transitions for smooth animations
- No external dependencies
- Optimized for 60fps animations
