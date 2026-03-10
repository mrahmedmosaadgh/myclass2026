<template>
  <div class="fill-blank-question">
    <!-- Answer Input -->
    <div class="input-container">
      <label 
        :for="`answer-input-${question.id}`" 
        class="input-label"
      >
        Your Answer:
      </label>
      <input
        :id="`answer-input-${question.id}`"
        v-model="userAnswer"
        type="text"
        class="answer-input"
        :class="inputClasses"
        :disabled="isAnswered"
        :aria-label="`Answer input for question ${question.questionNumber}`"
        :aria-describedby="isAnswered ? `feedback-${question.id}` : undefined"
        :aria-invalid="isAnswered && !isCorrect"
        :aria-required="true"
        placeholder="Type your answer here..."
        @keydown.enter="handleSubmit"
      />
      
      <!-- Submit Button (only shown if not answered) -->
      <button
        v-if="!isAnswered"
        type="button"
        class="submit-button"
        :disabled="!userAnswer.trim()"
        :aria-label="`Submit answer for question ${question.questionNumber}`"
        @click="handleSubmit"
      >
        Submit Answer
      </button>
    </div>

    <!-- Feedback Section -->
    <div 
      v-if="isAnswered && showFeedback" 
      :id="`feedback-${question.id}`"
      class="feedback-section"
      role="alert"
      aria-live="polite"
    >
      <!-- Correctness Indicator -->
      <div class="feedback-header" :class="{ 'correct': isCorrect, 'incorrect': !isCorrect }">
        <span class="feedback-icon">{{ isCorrect ? '✓' : '✗' }}</span>
        <span class="feedback-text">
          {{ isCorrect ? 'Correct!' : 'Incorrect' }}
        </span>
      </div>

      <!-- Show correct answer if wrong -->
      <div v-if="!isCorrect && correctAnswer" class="correct-answer-display">
        <div class="correct-answer-label">Correct Answer:</div>
        <div class="correct-answer-text">{{ correctAnswer }}</div>
      </div>

      <!-- Show user's answer for reference -->
      <div v-if="!isCorrect" class="user-answer-display">
        <div class="user-answer-label">Your Answer:</div>
        <div class="user-answer-text">{{ userAnswer }}</div>
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
 * FillBlankQuestion Component
 * 
 * Renders a fill-in-the-blank or short answer question with text input.
 * Validates answer and displays feedback with correct answer comparison.
 * 
 * Requirements: 3.3
 */

interface Props {
  question: QuizQuestion
  selectedIndex: number | null
  isAnswered: boolean
  showFeedback: boolean
}

interface Emits {
  (e: 'select', answer: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const userAnswer = ref('')
const isCorrect = ref(false)

/**
 * Get the correct answer from the question options
 * For fill-in-blank, the first option marked as correct is the answer
 */
const correctAnswer = computed(() => {
  const correctOption = props.question.answerOptions.find(opt => opt.isCorrect)
  return correctOption?.text || ''
})

/**
 * Compute input CSS classes based on state
 */
const inputClasses = computed(() => {
  if (!props.isAnswered) return {}
  
  return {
    'input-correct': isCorrect.value,
    'input-incorrect': !isCorrect.value
  }
})

/**
 * Validate and submit the answer
 */
const handleSubmit = () => {
  if (!userAnswer.value.trim() || props.isAnswered) return
  
  // Check if answer is correct (case-insensitive comparison)
  const normalizedUserAnswer = userAnswer.value.trim().toLowerCase()
  const normalizedCorrectAnswer = correctAnswer.value.trim().toLowerCase()
  
  // Check for exact match or if correct answer accepts multiple variations
  isCorrect.value = normalizedUserAnswer === normalizedCorrectAnswer ||
    checkAnswerVariations(normalizedUserAnswer, normalizedCorrectAnswer)
  
  // Emit the answer as a string
  emit('select', userAnswer.value.trim())
}

/**
 * Check for common answer variations (e.g., with/without articles, plurals)
 */
const checkAnswerVariations = (userAns: string, correctAns: string): boolean => {
  // Remove common articles
  const removeArticles = (str: string) => 
    str.replace(/^(a|an|the)\s+/i, '').trim()
  
  const userWithoutArticles = removeArticles(userAns)
  const correctWithoutArticles = removeArticles(correctAns)
  
  return userWithoutArticles === correctWithoutArticles
}
</script>

<style scoped>
.fill-blank-question {
  width: 100%;
}

.input-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.input-label {
  font-size: 1rem;
  font-weight: 500;
  color: #374151;
}

.answer-input {
  width: 100%;
  padding: 0.875rem 1rem;
  font-size: 1rem;
  border: 2px solid #d1d5db;
  border-radius: 0.5rem;
  background-color: #ffffff;
  transition: all 0.2s ease;
}

.answer-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.answer-input:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 2px;
  border-color: #2563eb;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.2);
}

.answer-input:disabled {
  background-color: #f3f4f6;
  cursor: not-allowed;
}

.answer-input.input-correct {
  border-color: #10b981;
  background-color: #d1fae5;
}

.answer-input.input-incorrect {
  border-color: #ef4444;
  background-color: #fee2e2;
}

.submit-button {
  align-self: flex-start;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  font-weight: 500;
  color: #ffffff;
  background-color: #3b82f6;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
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

.submit-button:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 3px;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.2);
}

.feedback-section {
  padding: 1rem;
  border-radius: 0.5rem;
  animation: fadeIn 0.4s ease;
}

.feedback-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 0.375rem;
  margin-bottom: 1rem;
}

.feedback-header.correct {
  background-color: #d1fae5;
  border: 2px solid #10b981;
}

.feedback-header.incorrect {
  background-color: #fee2e2;
  border: 2px solid #ef4444;
}

.feedback-icon {
  font-size: 1.5rem;
}

.feedback-header.correct .feedback-icon {
  color: #10b981;
}

.feedback-header.incorrect .feedback-icon {
  color: #ef4444;
}

.feedback-text {
  font-size: 1.125rem;
  font-weight: 600;
}

.feedback-header.correct .feedback-text {
  color: #065f46;
}

.feedback-header.incorrect .feedback-text {
  color: #991b1b;
}

.correct-answer-display,
.user-answer-display {
  padding: 0.75rem;
  margin-bottom: 0.75rem;
  border-radius: 0.375rem;
}

.correct-answer-display {
  background-color: #d1fae5;
  border-left: 4px solid #10b981;
}

.user-answer-display {
  background-color: #fee2e2;
  border-left: 4px solid #ef4444;
}

.correct-answer-label,
.user-answer-label {
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.correct-answer-label {
  color: #065f46;
}

.user-answer-label {
  color: #991b1b;
}

.correct-answer-text,
.user-answer-text {
  font-size: 1rem;
  font-weight: 500;
}

.correct-answer-text {
  color: #047857;
}

.user-answer-text {
  color: #dc2626;
}

.explanation-panel {
  margin-top: 1rem;
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
  .answer-input {
    padding: 0.75rem;
    font-size: 0.9375rem;
  }
  
  .submit-button {
    width: 100%;
    padding: 0.875rem;
  }
  
  .feedback-header {
    flex-direction: column;
    align-items: flex-start;
  }
}

/* Touch-friendly targets for mobile */
@media (hover: none) and (pointer: coarse) {
  .answer-input {
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
  .answer-input {
    border-width: 3px;
  }

  .answer-input:focus-visible,
  .submit-button:focus-visible {
    outline-width: 4px;
    outline-style: solid;
  }
}
</style>
