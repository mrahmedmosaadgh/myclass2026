<template>
  <transition name="fade">
    <div
      v-if="error"
      class="validation-error"
      role="alert"
      aria-live="polite"
    >
      <svg
        class="validation-error__icon"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 20 20"
        fill="currentColor"
        aria-hidden="true"
      >
        <path
          fill-rule="evenodd"
          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
          clip-rule="evenodd"
        />
      </svg>
      <span class="validation-error__message">{{ error }}</span>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  error?: string | null
  fieldName?: string
}

const props = withDefaults(defineProps<Props>(), {
  error: null,
  fieldName: undefined,
})

const error = computed(() => props.error)
</script>

<style scoped>
.validation-error {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 0.75rem;
  margin-top: 0.5rem;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.375rem;
  color: #991b1b;
  font-size: 0.875rem;
  line-height: 1.25rem;
}

.validation-error__icon {
  flex-shrink: 0;
  width: 1.25rem;
  height: 1.25rem;
  margin-top: 0.125rem;
}

.validation-error__message {
  flex: 1;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .validation-error {
    background-color: #7f1d1d;
    border-color: #991b1b;
    color: #fecaca;
  }
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .validation-error {
    padding: 0.625rem;
    font-size: 0.8125rem;
  }

  .validation-error__icon {
    width: 1rem;
    height: 1rem;
  }
}
</style>
