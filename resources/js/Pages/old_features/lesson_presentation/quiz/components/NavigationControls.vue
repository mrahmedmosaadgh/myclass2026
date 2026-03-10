<template>
  <div class="navigation-controls">
    <!-- Previous Button (only in review mode) -->
    <button
      v-if="allowReviewMode"
      class="nav-button prev-button"
      :disabled="currentIndex === 0"
      :aria-label="previous()"
      @click="emit('previous')"
      @keydown.left.prevent="emit('previous')"
    >
      {{ isRtl ? `${previous()} ←` : `← ${previous()}` }}
    </button>

    <!-- Next/Finish Button -->
    <button
      class="nav-button next-button"
      :class="{ 'finish-button': isLast }"
      :disabled="!isAnswered && !allowReviewMode"
      :aria-label="isLast ? finish() : next()"
      @click="handleNextClick"
      @keydown.right.prevent="!isLast && handleNextClick()"
      @keydown.enter.prevent="handleNextClick"
    >
      <template v-if="isLast">
        {{ finish() }}
      </template>
      <template v-else>
        {{ isRtl ? `${next()} ←` : `${next()} →` }}
      </template>
    </button>
  </div>
</template>

<script setup lang="ts">
import { useQuizI18n } from '../composables/useQuizI18n'

/**
 * NavigationControls Component
 * 
 * Provides navigation buttons for moving between quiz questions.
 * Supports both sequential navigation and review mode with backward navigation.
 * 
 * @component
 */

// Initialize i18n
const { next, previous, finish, isRtl } = useQuizI18n()

// ============================================================================
// Props Definition
// ============================================================================

interface Props {
  /** Whether review mode is enabled (allows backward navigation) */
  allowReviewMode: boolean
  /** Whether the current question has been answered */
  isAnswered: boolean
  /** Whether the current question is the last question */
  isLast: boolean
  /** Current question index (0-based) */
  currentIndex: number
}

const props = defineProps<Props>()

// ============================================================================
// Emits Definition
// ============================================================================

interface Emits {
  /** Emitted when the previous button is clicked */
  (e: 'previous'): void
  /** Emitted when the next button is clicked */
  (e: 'next'): void
  /** Emitted when the finish button is clicked */
  (e: 'finish'): void
}

const emit = defineEmits<Emits>()

// ============================================================================
// Methods
// ============================================================================

/**
 * Handles the next/finish button click
 * Emits either 'next' or 'finish' based on whether this is the last question
 */
const handleNextClick = (): void => {
  if (props.isLast) {
    emit('finish')
  } else {
    emit('next')
  }
}
</script>

<style scoped>
/* ============================================================================
   Navigation Controls Container
   ============================================================================ */
.navigation-controls {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

/* ============================================================================
   Navigation Buttons - Base Styles
   ============================================================================ */
.nav-button {
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  font-weight: 500;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 44px;
  min-width: 44px;
}

/* Focus indicator for accessibility */
.nav-button:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 3px;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.2);
}

.finish-button:focus-visible {
  outline-color: #10b981;
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2);
}

.prev-button:focus-visible {
  outline-color: #6b7280;
  box-shadow: 0 0 0 5px rgba(107, 114, 128, 0.2);
}

/* ============================================================================
   Previous Button Styles
   ============================================================================ */
.prev-button {
  background-color: #f3f4f6;
  color: #4b5563;
}

.prev-button:hover:not(:disabled) {
  background-color: #e5e7eb;
}

/* ============================================================================
   Next Button Styles
   ============================================================================ */
.next-button {
  background-color: #3b82f6;
  color: #ffffff;
  margin-left: auto;
}

.next-button:hover:not(:disabled) {
  background-color: #2563eb;
}

/* ============================================================================
   Finish Button Styles (green variant)
   ============================================================================ */
.finish-button {
  background-color: #10b981;
}

.finish-button:hover:not(:disabled) {
  background-color: #059669;
}

/* ============================================================================
   Disabled State
   ============================================================================ */
.nav-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background-color: #e5e7eb;
  color: #9ca3af;
}

/* ============================================================================
   Responsive Design - Mobile
   ============================================================================ */
@media (max-width: 640px) {
  .navigation-controls {
    flex-direction: column;
  }

  .next-button {
    margin-left: 0;
  }

  .nav-button {
    width: 100%;
  }
}

/* ============================================================================
   Accessibility - Reduced Motion
   ============================================================================ */
@media (prefers-reduced-motion: reduce) {
  .nav-button {
    transition: none;
  }
}

/* ============================================================================
   Accessibility - High Contrast Mode
   ============================================================================ */
@media (prefers-contrast: high) {
  .nav-button {
    border: 2px solid currentColor;
  }

  .nav-button:focus-visible {
    outline-width: 4px;
    outline-style: solid;
  }
}
</style>
