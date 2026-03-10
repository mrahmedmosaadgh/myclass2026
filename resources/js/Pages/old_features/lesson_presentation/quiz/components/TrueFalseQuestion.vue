<template>
  <div class="true-false-question">
    <div 
      class="options-container" 
      role="radiogroup"
      :aria-label="`Question ${question.questionNumber}: True or False`"
    >
      <OptionItem
        v-for="(option, index) in trueFalseOptions"
        :key="option.id"
        :option="option"
        :index="index"
        :letter="index === 0 ? 'T' : 'F'"
        :is-selected="selectedIndex === index"
        :is-correct="option.isCorrect"
        :is-answered="isAnswered"
        :show-rationale="showFeedback && (option.isCorrect || selectedIndex === index)"
        @click="handleSelect(index)"
      />
    </div>

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
import { computed } from 'vue'
import type { QuizQuestion } from '@/types/quiz'
import OptionItem from './OptionItem.vue'

/**
 * TrueFalseQuestion Component
 * 
 * Renders a true/false question with exactly two options.
 * Uses T/F labels instead of A/B for clarity.
 * 
 * Requirements: 3.2, 8.1
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
 * Ensure we only show the first two options (True/False)
 * In case the question has more options, we limit to 2
 */
const trueFalseOptions = computed(() => {
  return props.question.answerOptions.slice(0, 2)
})

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
.true-false-question {
  width: 100%;
}

.options-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
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

/* Make True/False options more prominent */
.options-container :deep(.option-item) {
  min-height: 60px;
}

@media (min-width: 768px) {
  .options-container {
    flex-direction: row;
    gap: 1rem;
  }
  
  .options-container :deep(.option-item) {
    flex: 1;
  }
}
</style>
