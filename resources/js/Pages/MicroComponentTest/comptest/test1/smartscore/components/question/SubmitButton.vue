<template>
  <button
    class="submit-button"
    :class="{
      'submit-button-disabled': disabled,
      'submit-button-loading': loading,
      'submit-button-primary': variant === 'primary',
      'submit-button-secondary': variant === 'secondary'
    }"
    @click="handleClick"
    :disabled="disabled || loading"
  >
    <span v-if="loading" class="loading-spinner">
      <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 4.955 3.59 9.05 8.25 10.25.78.17 1.53.24 2.25.24v-3.84a7.962 7.962 0 01-2.25-1.25z"></path>
      </svg>
    </span>
    <span v-else>{{ text }}</span>
  </button>
</template>

<script setup>
const props = defineProps({
  text: {
    type: String,
    default: 'Submit'
  },
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary'].includes(value)
  }
});

const emit = defineEmits(['click']);

const handleClick = () => {
  if (!props.disabled && !props.loading) {
    emit('click');
  }
};
</script>

<style scoped>
.submit-button {
  padding: 12px 24px;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.submit-button-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
}

.submit-button-primary:hover {
  background-color: #2563eb;
}

.submit-button-primary:active {
  background-color: #1d4ed8;
}

.submit-button-secondary {
  background-color: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.submit-button-secondary:hover {
  background-color: #e5e7eb;
}

.submit-button-secondary:active {
  background-color: #d1d5db;
}

.submit-button-disabled,
.submit-button-loading {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading-spinner {
  display: inline-flex;
  align-items: center;
}
</style>