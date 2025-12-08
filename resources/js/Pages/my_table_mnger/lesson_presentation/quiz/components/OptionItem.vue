<template>
  <div
    class="option-item"
    :class="optionClasses"
    role="option"
    :aria-selected="isSelected"
    :aria-disabled="isAnswered"
    :aria-label="getAriaLabel()"
    tabindex="0"
    @click="handleClick"
    @keydown.enter.prevent="handleClick"
    @keydown.space.prevent="handleClick"
  >
    <!-- Option Label (A, B, C, D) -->
    <div class="option-label">{{ letter }}</div>

    <!-- Option Text -->
    <div class="option-text">{{ option.text }}</div>

    <!-- Feedback Icons -->
    <div v-if="isAnswered" class="feedback-icon">
      <span v-if="isCorrect" class="correct-icon">✓</span>
      <span v-else-if="isSelected" class="incorrect-icon">✗</span>
    </div>

    <!-- Rationale (shown after answer if available) -->
    <div v-if="isAnswered && showRationale && option.rationale" class="rationale">
      <div class="rationale-label">{{ rationale() }}</div>
      <div class="rationale-text">{{ option.rationale }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useQuizI18n } from '../composables/useQuizI18n'
import type { AnswerOption } from '@/types/quiz'

/**
 * OptionItem Component
 * 
 * Renders a single answer option with visual feedback and accessibility support.
 * Supports keyboard navigation and displays rationale when available.
 */

// Initialize i18n
const { 
  rationale, 
  a11yCorrectOption, 
  a11yIncorrectOption,
  option: optionText 
} = useQuizI18n()

interface Props {
  option: AnswerOption
  index: number
  letter: string
  isSelected: boolean
  isCorrect: boolean
  isAnswered: boolean
  showRationale: boolean
}

interface Emits {
  (e: 'click'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

/**
 * Compute CSS classes based on option state
 */
const optionClasses = computed(() => {
  return {
    'selected': props.isSelected && !props.isAnswered,
    'correct': props.isAnswered && props.isCorrect,
    'incorrect': props.isAnswered && props.isSelected && !props.isCorrect,
    'unselected-correct': props.isAnswered && !props.isSelected && props.isCorrect,
    'disabled': props.isAnswered
  }
})

/**
 * Generate accessible label for the option
 */
const getAriaLabel = () => {
  let label = `${optionText(props.letter)}: ${props.option.text}`
  
  if (props.isAnswered && props.isCorrect) {
    label += ` - ${a11yCorrectOption()}`
  } else if (props.isAnswered && props.isSelected && !props.isCorrect) {
    label += ` - ${a11yIncorrectOption()}`
  }
  
  return label
}

/**
 * Handle click/keyboard selection
 */
const handleClick = () => {
  if (!props.isAnswered) {
    emit('click')
  }
}
</script>

<style scoped>
.option-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  margin-bottom: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  background-color: #ffffff;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.option-item:hover:not(.disabled) {
  border-color: #3b82f6;
  background-color: #eff6ff;
  transform: translateX(4px);
}

.option-item:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 3px;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.2);
}

.option-item.correct:focus-visible {
  outline-color: #10b981;
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2);
}

.option-item.incorrect:focus-visible {
  outline-color: #ef4444;
  box-shadow: 0 0 0 5px rgba(239, 68, 68, 0.2);
}

.option-item.selected:focus-visible {
  outline-color: #2563eb;
  box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.3);
}

/* Selected state (before answer submission) */
.option-item.selected {
  border-color: #3b82f6;
  background-color: #dbeafe;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Correct answer state */
.option-item.correct {
  border-color: #10b981;
  background-color: #d1fae5;
  cursor: default;
}

/* Incorrect answer state */
.option-item.incorrect {
  border-color: #ef4444;
  background-color: #fee2e2;
  cursor: default;
}

/* Unselected correct answer (show user what they missed) */
.option-item.unselected-correct {
  border-color: #10b981;
  background-color: #f0fdf4;
  cursor: default;
}

/* Disabled state */
.option-item.disabled {
  cursor: not-allowed;
  opacity: 0.9;
}

.option-label {
  flex-shrink: 0;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f3f4f6;
  border-radius: 50%;
  font-weight: 600;
  color: #6b7280;
  transition: all 0.2s ease;
}

.option-item.selected .option-label {
  background-color: #3b82f6;
  color: #ffffff;
}

.option-item.correct .option-label {
  background-color: #10b981;
  color: #ffffff;
}

.option-item.incorrect .option-label {
  background-color: #ef4444;
  color: #ffffff;
}

.option-item.unselected-correct .option-label {
  background-color: #10b981;
  color: #ffffff;
}

.option-text {
  flex: 1;
  font-size: 1rem;
  line-height: 1.5;
  color: #374151;
}

.feedback-icon {
  flex-shrink: 0;
  font-size: 1.5rem;
  animation: fadeIn 0.3s ease;
}

.correct-icon {
  color: #10b981;
}

.incorrect-icon {
  color: #ef4444;
}

.rationale {
  width: 100%;
  margin-top: 0.75rem;
  padding: 0.75rem;
  background-color: rgba(255, 255, 255, 0.5);
  border-radius: 0.375rem;
  border-left: 3px solid #6b7280;
  animation: fadeIn 0.4s ease;
}

.rationale-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.rationale-text {
  font-size: 0.875rem;
  color: #4b5563;
  line-height: 1.5;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Design */
@media (max-width: 640px) {
  .option-item {
    padding: 0.75rem;
    gap: 0.75rem;
  }
  
  .option-label {
    width: 1.75rem;
    height: 1.75rem;
    font-size: 0.875rem;
  }
  
  .option-text {
    font-size: 0.9375rem;
  }
  
  .feedback-icon {
    font-size: 1.25rem;
  }
}

/* Touch-friendly targets for mobile */
@media (hover: none) and (pointer: coarse) {
  .option-item {
    min-height: 44px;
    padding: 1rem;
  }
}

/* ============================================================================
   Accessibility - High Contrast Mode
   ============================================================================ */
@media (prefers-contrast: high) {
  .option-item {
    border-width: 3px;
  }

  .option-item:focus-visible {
    outline-width: 4px;
    outline-style: solid;
  }
}
</style>
