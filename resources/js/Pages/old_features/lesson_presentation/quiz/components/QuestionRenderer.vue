<template>
  <div class="question-renderer">
    <!-- Question Header -->
    <div class="question-header">
      <span class="question-number">Question {{ question.questionNumber }}</span>
      <span v-if="question.difficultyLevel" class="difficulty-badge">
        Difficulty: {{ question.difficultyLevel }}/5
      </span>
    </div>

    <!-- Question Text -->
    <div class="question-text" v-html="question.question"></div>

    <!-- Hints (if available and not answered) -->
    <div v-if="question.hints && question.hints.length > 0 && !isAnswered" class="hints-section">
      <details>
        <summary>💡 Show Hints</summary>
        <ul class="hints-list">
          <li v-for="(hint, index) in question.hints" :key="index">{{ hint }}</li>
        </ul>
      </details>
    </div>

    <!-- Dynamic Question Type Component -->
    <component
      :is="questionComponent"
      :question="question"
      :selected-index="selectedIndex"
      :is-answered="isAnswered"
      :show-feedback="showFeedback"
      @select="handleSelect"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue'
import type { QuizQuestion } from '@/types/quiz'

/**
 * QuestionRenderer Component
 * 
 * Renders a quiz question with dynamic component loading based on question type.
 * Supports HTML/math content in question text and provides hints when available.
 */

interface Props {
  question: QuizQuestion
  selectedIndex: number | null
  isAnswered: boolean
  showFeedback: boolean
}

interface Emits {
  (e: 'select', index: number | string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

/**
 * Dynamically load the appropriate question type component based on questionType.slug
 */
const questionComponent = computed(() => {
  const slug = props.question.questionType.slug
  
  // Map question type slugs to component names
  const componentMap: Record<string, any> = {
    'multiple_choice': defineAsyncComponent(() => 
      import('./MultipleChoiceQuestion.vue')
    ),
    'true_false': defineAsyncComponent(() => 
      import('./TrueFalseQuestion.vue')
    ),
    'fill_blank': defineAsyncComponent(() => 
      import('./FillBlankQuestion.vue')
    ),
    'multi_select': defineAsyncComponent(() => 
      import('./MultiSelectQuestion.vue')
    ),
    'short_answer': defineAsyncComponent(() => 
      import('./FillBlankQuestion.vue') // Reuse fill blank for short answer
    ),
    'essay': defineAsyncComponent(() => 
      import('./FillBlankQuestion.vue') // Reuse fill blank for essay
    )
  }
  
  return componentMap[slug] || componentMap['multiple_choice']
})

/**
 * Handle option selection from child components
 */
const handleSelect = (index: number | string) => {
  emit('select', index)
}
</script>

<style scoped>
.question-renderer {
  width: 100%;
  padding: 1.5rem;
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e5e7eb;
}

.question-number {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
}

.difficulty-badge {
  font-size: 0.875rem;
  padding: 0.25rem 0.75rem;
  background-color: #f3f4f6;
  color: #6b7280;
  border-radius: 9999px;
}

.question-text {
  font-size: 1.125rem;
  line-height: 1.75;
  color: #374151;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
  border-left: 4px solid #3b82f6;
}

/* Support for math/HTML content */
.question-text :deep(p) {
  margin-bottom: 0.5rem;
}

.question-text :deep(code) {
  background-color: #e5e7eb;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-family: monospace;
}

.hints-section {
  margin-bottom: 1.5rem;
}

.hints-section details {
  background-color: #fef3c7;
  border: 1px solid #fbbf24;
  border-radius: 0.5rem;
  padding: 0.75rem;
}

.hints-section summary {
  cursor: pointer;
  font-weight: 500;
  color: #92400e;
  user-select: none;
}

.hints-section summary:hover {
  color: #78350f;
}

.hints-list {
  margin-top: 0.75rem;
  margin-left: 1.5rem;
  list-style-type: disc;
}

.hints-list li {
  margin-bottom: 0.5rem;
  color: #92400e;
}

/* Responsive Design */
@media (max-width: 640px) {
  .question-renderer {
    padding: 1rem;
  }
  
  .question-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .question-text {
    font-size: 1rem;
    padding: 0.75rem;
  }
}
</style>
