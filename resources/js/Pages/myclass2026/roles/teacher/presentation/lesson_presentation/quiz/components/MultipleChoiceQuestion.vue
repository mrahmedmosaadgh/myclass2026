<template>
  <div class="multiple-choice-question">
    <ol 
      class="options-list" 
      role="listbox"
      :aria-label="`Question ${question.questionNumber} options`"
    >
      <OptionItem
        v-for="(option, index) in question.answerOptions"
        :key="option.id"
        :option="option"
        :index="index"
        :letter="getOptionLetter(index)"
        :is-selected="selectedIndex === index"
        :is-correct="option.isCorrect"
        :is-answered="isAnswered"
        :show-rationale="showFeedback && (option.isCorrect || selectedIndex === index)"
        @click="handleSelect(index)"
      />
    </ol>

    <!-- Global Explanation (shown after answer if available) -->
    <div 
      v-if="isAnswered && showFeedback && question.explanation" 
      class="explanation-panel"
    >
      <div class="explanation-label">📚 Explanation:</div>
      <div class="explanation-text">{{ question.explanation }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { QuizQuestion } from '@/types/quiz'
import OptionItem from './OptionItem.vue'

/**
 * MultipleChoiceQuestion Component
 * 
 * Renders a multiple choice question with single correct answer.
 * Displays options with letter labels (A, B, C, D) and provides
 * feedback after answer submission.
 * 
 * Requirements: 3.1, 8.1
 */

interface Props {
  question: QuizQuestion
  selectedIndex: number | null
  isAnswered: boolean
  showFeedback: boolean
}

interface Emits {
  (e: 'select', index: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

/**
 * Convert option index to letter (0 -> A, 1 -> B, etc.)
 */
const getOptionLetter = (index: number): string => {
  return String.fromCharCode(65 + index) // 65 is ASCII code for 'A'
}

/**
 * Handle option selection
 */
const handleSelect = (index: number) => {
  if (!props.isAnswered) {
    emit('select', index)
  }
}
</script>

<style scoped>
.multiple-choice-question {
  width: 100%;
}

.options-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.explanation-panel {
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #f0f9ff;
  border: 2px solid #3b82f6;
  border-radius: 0.5rem;
  animation: fadeIn 0.4s ease;
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
  .explanation-panel {
    padding: 0.875rem;
  }
  
  .explanation-label {
    font-size: 0.9375rem;
  }
  
  .explanation-text {
    font-size: 0.875rem;
  }
}
</style>
