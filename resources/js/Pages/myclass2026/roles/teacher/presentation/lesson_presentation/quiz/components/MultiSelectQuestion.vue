<template>
  <div class="multi-select-question">
    <!-- Instructions -->
    <div class="instructions" role="note" aria-label="Question instructions">
      <span class="instructions-icon" aria-hidden="true">ℹ️</span>
      <span class="instructions-text">Select all correct answers</span>
    </div>

    <!-- Options List -->
    <div 
      class="options-container" 
      role="group"
      :aria-label="`Question ${question.questionNumber}: Select all that apply`"
    >
      <div
        v-for="(option, index) in question.answerOptions"
        :key="option.id"
        class="checkbox-option"
        :class="getOptionClasses(index)"
        @click="toggleOption(index)"
      >
        <!-- Checkbox -->
        <div class="checkbox-container">
          <input
            :id="`option-${question.id}-${index}`"
            type="checkbox"
            :checked="selectedIndices.includes(index)"
            :disabled="isAnswered"
            class="checkbox-input"
            :aria-label="`Option ${getOptionLetter(index)}: ${option.text}`"
            :aria-checked="selectedIndices.includes(index)"
            :aria-describedby="isAnswered ? `option-feedback-${question.id}-${index}` : undefined"
            role="checkbox"
            @change="toggleOption(index)"
          />
          <label 
            :for="`option-${question.id}-${index}`"
            class="checkbox-label"
            aria-hidden="true"
          >
            {{ getOptionLetter(index) }}
          </label>
        </div>

        <!-- Option Text -->
        <div class="option-text">{{ option.text }}</div>

        <!-- Feedback Icons -->
        <div 
          v-if="isAnswered" 
          class="feedback-icon"
          :id="`option-feedback-${question.id}-${index}`"
          role="status"
          aria-live="polite"
        >
          <span v-if="option.isCorrect && selectedIndices.includes(index)" class="correct-icon" aria-label="Correct answer selected">✓</span>
          <span v-else-if="option.isCorrect && !selectedIndices.includes(index)" class="missed-icon" aria-label="Correct answer not selected">○</span>
          <span v-else-if="!option.isCorrect && selectedIndices.includes(index)" class="incorrect-icon" aria-label="Incorrect answer selected">✗</span>
        </div>

        <!-- Rationale -->
        <div v-if="isAnswered && showFeedback && option.rationale" class="rationale">
          <div class="rationale-label">Explanation:</div>
          <div class="rationale-text">{{ option.rationale }}</div>
        </div>
      </div>
    </div>

    <!-- Submit Button -->
    <button
      v-if="!isAnswered"
      type="button"
      class="submit-button"
      :disabled="selectedIndices.length === 0"
      :aria-label="`Submit ${selectedIndices.length} selected answer${selectedIndices.length !== 1 ? 's' : ''} for question ${question.questionNumber}`"
      @click="handleSubmit"
    >
      Submit Answer{{ selectedIndices.length > 1 ? 's' : '' }}
      <span v-if="selectedIndices.length > 0" class="selected-count" aria-hidden="true">
        ({{ selectedIndices.length }} selected)
      </span>
    </button>

    <!-- Feedback Section -->
    <div 
      v-if="isAnswered && showFeedback" 
      class="feedback-section"
      role="alert"
      aria-live="polite"
    >
      <!-- Score Display -->
      <div class="score-display" :class="{ 'perfect': isAllCorrect, 'partial': !isAllCorrect }">
        <span class="score-icon">{{ isAllCorrect ? '✓' : '⚠' }}</span>
        <span class="score-text">
          {{ isAllCorrect ? 'Perfect!' : 'Partial Credit' }}
        </span>
        <span class="score-detail">
          {{ correctSelections }} of {{ totalCorrectOptions }} correct answers selected
        </span>
      </div>

      <!-- Global Explanation -->
      <div v-if="question.explanation" class="explanation-panel">
        <div class="explanation-label">📚 Explanation:</div>
        <div class="explanation-text">{{ question.explanation }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import type { QuizQuestion } from '@/types/quiz'

/**
 * MultiSelectQuestion Component
 * 
 * Renders a multi-select question where multiple answers can be correct.
 * Displays checkboxes for selection and provides partial credit feedback.
 * 
 * Requirements: 3.4
 */

interface Props {
  question: QuizQuestion
  selectedIndex: number | null
  isAnswered: boolean
  showFeedback: boolean
}

interface Emits {
  (e: 'select', indices: number[]): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const selectedIndices = ref<number[]>([])

/**
 * Total number of correct options
 */
const totalCorrectOptions = computed(() => {
  return props.question.answerOptions.filter(opt => opt.isCorrect).length
})

/**
 * Number of correctly selected options
 */
const correctSelections = computed(() => {
  return selectedIndices.value.filter(index => 
    props.question.answerOptions[index].isCorrect
  ).length
})

/**
 * Check if all correct answers are selected and no incorrect ones
 */
const isAllCorrect = computed(() => {
  const correctIndices = props.question.answerOptions
    .map((opt, idx) => opt.isCorrect ? idx : -1)
    .filter(idx => idx !== -1)
  
  return correctIndices.length === selectedIndices.value.length &&
    correctIndices.every(idx => selectedIndices.value.includes(idx))
})

/**
 * Convert option index to letter (0 -> A, 1 -> B, etc.)
 */
const getOptionLetter = (index: number): string => {
  return String.fromCharCode(65 + index)
}

/**
 * Get CSS classes for an option based on its state
 */
const getOptionClasses = (index: number) => {
  if (!props.isAnswered) {
    return {
      'selected': selectedIndices.value.includes(index)
    }
  }
  
  const option = props.question.answerOptions[index]
  const isSelected = selectedIndices.value.includes(index)
  
  return {
    'correct': option.isCorrect && isSelected,
    'incorrect': !option.isCorrect && isSelected,
    'missed': option.isCorrect && !isSelected,
    'disabled': true
  }
}

/**
 * Toggle option selection
 */
const toggleOption = (index: number) => {
  if (props.isAnswered) return
  
  const currentIndex = selectedIndices.value.indexOf(index)
  if (currentIndex > -1) {
    selectedIndices.value.splice(currentIndex, 1)
  } else {
    selectedIndices.value.push(index)
  }
}

/**
 * Submit the selected answers
 */
const handleSubmit = () => {
  if (selectedIndices.value.length === 0 || props.isAnswered) return
  
  // Emit the array of selected indices
  emit('select', [...selectedIndices.value])
}
</script>

<style scoped>
.multi-select-question {
  width: 100%;
}

.instructions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  background-color: #fef3c7;
  border: 1px solid #fbbf24;
  border-radius: 0.5rem;
}

.instructions-icon {
  font-size: 1.25rem;
}

.instructions-text {
  font-size: 0.9375rem;
  font-weight: 500;
  color: #92400e;
}

.options-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.checkbox-option {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  background-color: #ffffff;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.checkbox-option:hover:not(.disabled) {
  border-color: #3b82f6;
  background-color: #eff6ff;
  transform: translateX(4px);
}

.checkbox-option.selected {
  border-color: #3b82f6;
  background-color: #dbeafe;
}

.checkbox-option.correct {
  border-color: #10b981;
  background-color: #d1fae5;
  cursor: default;
}

.checkbox-option.incorrect {
  border-color: #ef4444;
  background-color: #fee2e2;
  cursor: default;
}

.checkbox-option.missed {
  border-color: #f59e0b;
  background-color: #fef3c7;
  cursor: default;
}

.checkbox-option.disabled {
  cursor: not-allowed;
}

.checkbox-option:focus-within {
  outline: 3px solid #3b82f6;
  outline-offset: 2px;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.15);
}

.checkbox-option.correct:focus-within {
  outline-color: #10b981;
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.15);
}

.checkbox-option.incorrect:focus-within {
  outline-color: #ef4444;
  box-shadow: 0 0 0 5px rgba(239, 68, 68, 0.15);
}

.checkbox-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.checkbox-input {
  width: 1.25rem;
  height: 1.25rem;
  cursor: pointer;
  accent-color: #3b82f6;
}

.checkbox-input:disabled {
  cursor: not-allowed;
}

.checkbox-input:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 2px;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.checkbox-label {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  background-color: #f3f4f6;
  border-radius: 50%;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
}

.checkbox-option.selected .checkbox-label {
  background-color: #3b82f6;
  color: #ffffff;
}

.checkbox-option.correct .checkbox-label {
  background-color: #10b981;
  color: #ffffff;
}

.checkbox-option.incorrect .checkbox-label {
  background-color: #ef4444;
  color: #ffffff;
}

.checkbox-option.missed .checkbox-label {
  background-color: #f59e0b;
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

.missed-icon {
  color: #f59e0b;
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

.submit-button {
  width: 100%;
  padding: 0.875rem 1.5rem;
  font-size: 1rem;
  font-weight: 500;
  color: #ffffff;
  background-color: #3b82f6;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.submit-button:hover:not(:disabled) {
  background-color: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.submit-button:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
  opacity: 0.6;
}

.selected-count {
  font-size: 0.875rem;
  opacity: 0.9;
}

.submit-button:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 3px;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.2);
}

.feedback-section {
  margin-top: 1.5rem;
  animation: fadeIn 0.4s ease;
}

.score-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

.score-display.perfect {
  background-color: #d1fae5;
  border: 2px solid #10b981;
}

.score-display.partial {
  background-color: #fef3c7;
  border: 2px solid #f59e0b;
}

.score-icon {
  font-size: 2rem;
}

.score-display.perfect .score-icon {
  color: #10b981;
}

.score-display.partial .score-icon {
  color: #f59e0b;
}

.score-text {
  font-size: 1.25rem;
  font-weight: 600;
}

.score-display.perfect .score-text {
  color: #065f46;
}

.score-display.partial .score-text {
  color: #92400e;
}

.score-detail {
  font-size: 0.9375rem;
}

.score-display.perfect .score-detail {
  color: #047857;
}

.score-display.partial .score-detail {
  color: #b45309;
}

.explanation-panel {
  padding: 1rem;
  background-color: #f0f9ff;
  border: 2px solid #3b82f6;
  border-radius: 0.5rem;
}

.explanation-label {
  font-size: 1rem;
  font-weight: 600;
  color: #1e40af;
  margin-bottom: 0.5rem;
}

.explanation-text {
  font-size: 0.9375rem;
  color: #1e3a8a;
  line-height: 1.6;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Design */
@media (max-width: 640px) {
  .checkbox-option {
    padding: 0.75rem;
    gap: 0.75rem;
  }
  
  .checkbox-label {
    width: 1.75rem;
    height: 1.75rem;
    font-size: 0.875rem;
  }
  
  .option-text {
    font-size: 0.9375rem;
  }
  
  .submit-button {
    flex-direction: column;
    gap: 0.25rem;
  }
}

/* Touch-friendly targets for mobile */
@media (hover: none) and (pointer: coarse) {
  .checkbox-option {
    min-height: 44px;
  }
  
  .submit-button {
    min-height: 44px;
  }
}

/* ============================================================================
   Accessibility - High Contrast Mode
   ============================================================================ */
@media (prefers-contrast: high) {
  .checkbox-option {
    border-width: 3px;
  }

  .checkbox-input:focus-visible,
  .checkbox-option:focus-within,
  .submit-button:focus-visible {
    outline-width: 4px;
    outline-style: solid;
  }
}
</style>
