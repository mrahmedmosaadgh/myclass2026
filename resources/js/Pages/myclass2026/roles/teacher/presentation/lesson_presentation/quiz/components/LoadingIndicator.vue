<template>
  <div
    v-if="isLoading"
    class="loading-indicator"
    role="status"
    aria-live="polite"
    :aria-label="displayMessage"
  >
    <div class="spinner" aria-hidden="true">
      <svg
        class="spinner-icon"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="spinner-track"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        ></circle>
        <path
          class="spinner-path"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
    </div>
    <p class="loading-message">{{ displayMessage }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

interface Props {
  isLoading: boolean
  message?: string
}

const props = withDefaults(defineProps<Props>(), {
  message: undefined,
})

const displayMessage = computed(() => {
  return props.message || t('common.loading')
})
</script>

<style scoped>
.loading-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  gap: 1rem;
}

.spinner {
  width: 3rem;
  height: 3rem;
  color: #3b82f6;
}

.spinner-icon {
  width: 100%;
  height: 100%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.spinner-track {
  opacity: 0.25;
}

.spinner-path {
  opacity: 0.75;
}

.loading-message {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  text-align: center;
}

/* Accessibility - Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  .spinner-icon {
    animation: none;
  }
}
</style>
