<template>
  <div 
    class="question-navigator"
    role="navigation"
    aria-label="Question navigation"
  >
    <div class="navigator-label">
      Jump to question:
    </div>
    <div class="navigator-dots">
      <button
        v-for="(question, index) in questions"
        :key="question.id"
        class="nav-dot"
        :class="{
          'current': index === currentIndex,
          'answered': isQuestionAnswered(question.id)
        }"
        :aria-label="`Go to question ${index + 1}${isQuestionAnswered(question.id) ? ' (answered)' : ''}`"
        :aria-current="index === currentIndex ? 'true' : 'false'"
        @click="handleNavigate(index)"
        @keydown.enter.prevent="handleNavigate(index)"
        @keydown.space.prevent="handleNavigate(index)"
      >
        {{ index + 1 }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { QuizQuestion, AnswerRecord } from '@/types/quiz'

/**
 * QuestionNavigator Component
 * 
 * Provides visual navigation dots for all questions in review mode.
 * Displays answered status and allows direct navigation to any question.
 * 
 * @component
 */

// ============================================================================
// Props Definition
// ============================================================================

interface Props {
  /** Array of all quiz questions */
  questions: QuizQuestion[]
  /** Current question index (0-based) */
  currentIndex: number
  /** Array of answer records to determine which questions are answered */
  answers: AnswerRecord[]
}

const props = defineProps<Props>()

// ============================================================================
// Emits Definition
// ============================================================================

interface Emits {
  /** Emitted when a navigation dot is clicked */
  (e: 'navigate', index: number): void
}

const emit = defineEmits<Emits>()

// ============================================================================
// Methods
// ============================================================================

/**
 * Checks if a question has been answered
 * 
 * @param questionId - The ID of the question to check
 * @returns True if the question has been answered, false otherwise
 */
const isQuestionAnswered = (questionId: string | number): boolean => {
  return props.answers.some(answer => answer.questionId === questionId)
}

/**
 * Handles navigation dot click
 * 
 * @param index - The target question index
 */
const handleNavigate = (index: number): void => {
  emit('navigate', index)
}
</script>

<style scoped>
/* ============================================================================
   Question Navigator Container
   ============================================================================ */
.question-navigator {
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

/* ============================================================================
   Navigator Label
   ============================================================================ */
.navigator-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 0.75rem;
}

/* ============================================================================
   Navigator Dots Container
   ============================================================================ */
.navigator-dots {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

/* ============================================================================
   Navigation Dot Button
   ============================================================================ */
.nav-dot {
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: #ffffff;
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 44px;
  min-height: 44px;
}

/* Hover state */
.nav-dot:hover {
  border-color: #3b82f6;
  background-color: #eff6ff;
  color: #1e40af;
}

/* Focus indicator for accessibility */
.nav-dot:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 3px;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.2);
}

.nav-dot.current:focus-visible {
  outline-color: #1e40af;
  box-shadow: 0 0 0 5px rgba(30, 64, 175, 0.3);
}

.nav-dot.answered:focus-visible {
  outline-color: #10b981;
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2);
}

/* ============================================================================
   Current Question State
   ============================================================================ */
.nav-dot.current {
  border-color: #3b82f6;
  background-color: #3b82f6;
  color: #ffffff;
  font-weight: 600;
}

/* ============================================================================
   Answered Question State
   ============================================================================ */
.nav-dot.answered {
  border-color: #10b981;
  background-color: #d1fae5;
  color: #065f46;
}

/* Current + Answered state */
.nav-dot.answered.current {
  border-color: #10b981;
  background-color: #10b981;
  color: #ffffff;
}

/* ============================================================================
   Responsive Design - Mobile
   ============================================================================ */
@media (max-width: 640px) {
  .question-navigator {
    padding: 0.75rem;
  }

  .navigator-dots {
    justify-content: center;
    gap: 0.375rem;
  }

  .nav-dot {
    width: 2.25rem;
    height: 2.25rem;
    font-size: 0.8125rem;
  }
}

/* ============================================================================
   Responsive Design - Tablet
   ============================================================================ */
@media (min-width: 641px) and (max-width: 1024px) {
  .navigator-dots {
    gap: 0.625rem;
  }
}

/* ============================================================================
   Accessibility - Reduced Motion
   ============================================================================ */
@media (prefers-reduced-motion: reduce) {
  .nav-dot {
    transition: none;
  }
}

/* ============================================================================
   Accessibility - High Contrast Mode
   ============================================================================ */
@media (prefers-contrast: high) {
  .nav-dot {
    border-width: 3px;
  }

  .nav-dot.current {
    border-color: #1e40af;
  }

  .nav-dot.answered {
    border-color: #047857;
  }

  .nav-dot:focus-visible {
    outline-width: 4px;
    outline-style: solid;
  }
}

/* ============================================================================
   Print Styles
   ============================================================================ */
@media print {
  .question-navigator {
    display: none;
  }
}
</style>
