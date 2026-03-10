<template>
  <div
    v-if="isVisible && explanation"
    class="explanation-panel"
    role="region"
    :aria-label="explanationText()"
    aria-live="polite"
  >
    <div class="explanation-label" id="explanation-label">{{ explanationText() }}</div>
    <div class="explanation-text" aria-labelledby="explanation-label">{{ explanation }}</div>
  </div>
</template>

<script setup lang="ts">
import { useQuizI18n } from '../composables/useQuizI18n'

/**
 * ExplanationPanel Component
 * 
 * Displays a global explanation for a question after the user has answered.
 * Only shown when explanation text exists and isVisible is true.
 * 
 * @component
 */

// Initialize i18n
const { explanation: explanationText } = useQuizI18n()

interface Props {
  /** The explanation text to display */
  explanation?: string
  /** Controls visibility of the explanation panel */
  isVisible: boolean
}

defineProps<Props>()
</script>

<style scoped>
.explanation-panel {
  margin-top: 1.5rem;
  padding: 1rem 1.25rem;
  background-color: #f0f9ff;
  border: 2px solid #3b82f6;
  border-radius: 0.5rem;
  animation: fadeIn 0.4s ease;
}

.explanation-label {
  font-size: 0.9375rem;
  font-weight: 700;
  color: #1e40af;
  margin-bottom: 0.5rem;
}

.explanation-text {
  font-size: 0.9375rem;
  color: #1e3a8a;
  line-height: 1.6;
  white-space: pre-wrap;
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
    padding: 0.875rem 1rem;
    margin-top: 1.25rem;
  }
  
  .explanation-label {
    font-size: 0.875rem;
  }
  
  .explanation-text {
    font-size: 0.875rem;
  }
}

/* Print styles */
@media print {
  .explanation-panel {
    border: 1px solid #3b82f6;
    background-color: #f0f9ff;
    page-break-inside: avoid;
  }
}
</style>
