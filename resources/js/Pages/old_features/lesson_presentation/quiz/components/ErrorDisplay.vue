<template>
  <div
    v-if="hasError"
    class="error-display"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
  >
    <div class="error-content">
      <!-- Error Icon -->
      <div class="error-icon" aria-hidden="true">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="currentColor"
          class="icon"
        >
          <path
            fill-rule="evenodd"
            d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z"
            clip-rule="evenodd"
          />
        </svg>
      </div>

      <!-- Error Message -->
      <div class="error-message">
        <h3 class="error-title">{{ errorTitle }}</h3>
        <p class="error-description">{{ errorMessage }}</p>

        <!-- Error Details (only in dev mode) -->
        <details v-if="showDetails && errorDetails" class="error-details">
          <summary>{{ t('quiz.import.technicalDetails') }}</summary>
          <pre>{{ errorDetails }}</pre>
        </details>
      </div>

      <!-- Action Buttons -->
      <div class="error-actions">
        <button
          v-if="canRetry"
          class="retry-button"
          @click="handleRetry"
          :disabled="isRetrying"
        >
          <span v-if="!isRetrying">{{ t('quiz.import.retry') }}</span>
          <span v-else>{{ t('quiz.import.retrying') }}</span>
        </button>

        <button class="dismiss-button" @click="handleDismiss">
          {{ t('quiz.import.dismiss') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { ErrorDetails } from '@/composables/useErrorHandler'
import { useI18n } from 'vue-i18n'

// Initialize i18n
const { t } = useI18n()

interface Props {
  error: ErrorDetails | null
  canRetry?: boolean
  isRetrying?: boolean
  showDetails?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  canRetry: false,
  isRetrying: false,
  showDetails: false,
})

interface Emits {
  (e: 'retry'): void
  (e: 'dismiss'): void
}

const emit = defineEmits<Emits>()

const hasError = computed(() => props.error !== null)

const errorTitle = computed(() => {
  if (!props.error) return ''

  const titles: Record<string, string> = {
    NETWORK_ERROR: t('quiz.errors.connectionError'),
    TIMEOUT_ERROR: t('quiz.errors.timeoutError'),
    VALIDATION_ERROR: t('quiz.errors.validationError'),
    UNAUTHORIZED_ACCESS: t('quiz.errors.unauthorizedAccess'),
    NOT_FOUND: t('quiz.errors.notFound'),
    UNKNOWN_ERROR: t('quiz.errors.unknownError'),
  }

  return titles[props.error.code] || t('quiz.errors.unknownError')
})

const errorMessage = computed(() => {
  return props.error?.message || t('quiz.errors.unexpectedError')
})

const errorDetails = computed(() => {
  if (!props.error?.details) return null
  return JSON.stringify(props.error.details, null, 2)
})

const handleRetry = () => {
  emit('retry')
}

const handleDismiss = () => {
  emit('dismiss')
}
</script>

<style scoped>
.error-display {
  margin: 1rem 0;
  padding: 1rem;
  background-color: #fef2f2;
  border: 2px solid #ef4444;
  border-radius: 0.5rem;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.error-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.error-icon {
  flex-shrink: 0;
  width: 3rem;
  height: 3rem;
  color: #ef4444;
  margin: 0 auto;
}

.icon {
  width: 100%;
  height: 100%;
}

.error-message {
  flex: 1;
}

.error-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #991b1b;
}

.error-description {
  margin: 0;
  color: #7f1d1d;
  line-height: 1.5;
}

.error-details {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #ffffff;
  border: 1px solid #fca5a5;
  border-radius: 0.375rem;
}

.error-details summary {
  cursor: pointer;
  font-weight: 500;
  color: #991b1b;
  user-select: none;
}

.error-details summary:hover {
  color: #7f1d1d;
}

.error-details pre {
  margin-top: 0.5rem;
  padding: 0.5rem;
  background-color: #fef2f2;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  overflow-x: auto;
  color: #7f1d1d;
}

.error-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
}

.retry-button,
.dismiss-button {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 44px;
  min-width: 100px;
}

.retry-button {
  background-color: #ef4444;
  color: #ffffff;
}

.retry-button:hover:not(:disabled) {
  background-color: #dc2626;
}

.retry-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.dismiss-button {
  background-color: #f3f4f6;
  color: #4b5563;
}

.dismiss-button:hover {
  background-color: #e5e7eb;
}

.retry-button:focus-visible,
.dismiss-button:focus-visible {
  outline: 3px solid #ef4444;
  outline-offset: 2px;
}

/* Responsive Design */
@media (max-width: 640px) {
  .error-display {
    padding: 0.75rem;
  }

  .error-actions {
    flex-direction: column;
  }

  .retry-button,
  .dismiss-button {
    width: 100%;
  }
}

/* Accessibility - Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  .error-display {
    animation: none;
  }
}
</style>
