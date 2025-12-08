<template>
  <div class="radio-inputs">
    <label
      v-for="option in options"
      :key="option.value"
      class="radio"
    >
      <input
        type="radio"
        :name="name"
        :value="option.value"
        :checked="modelValue === option.value"
        @change="$emit('update:modelValue', option.value)"
      />
      <span class="name">{{ option.label }}</span>
    </label>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    required: true
  },
  options: {
    type: Array,
    required: true
  },
  name: {
    type: String,
    default: 'radio-group'
  }
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
.radio-inputs {
  position: relative;
  display: flex;
  flex-wrap: wrap;
  border-radius: 1rem;
  background: linear-gradient(145deg, #f3f4f6, #ffffff);
  box-sizing: border-box;
  box-shadow:
    0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06),
    inset 0 2px 4px rgba(255, 255, 255, 0.9);
  padding: 0.5rem;
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
  font-size: 14px;
  gap: 0.5rem;
}

.radio-inputs .radio {
  flex: 1 1 auto;
  text-align: center;
  position: relative;
  min-width: 120px;
}

.radio-inputs .radio input {
  display: none;
}

.radio-inputs .radio .name {
  display: flex;
  cursor: pointer;
  align-items: center;
  justify-content: center;
  border-radius: 0.8rem;
  border: 2px solid transparent;
  padding: 0.8rem 1.2rem;
  color: #4b5563;
  font-weight: 500;
  font-size: 0.95rem;
  letter-spacing: 0.01em;
  background: linear-gradient(145deg, #ffffff, #f9fafb);
  box-shadow:
    3px 3px 6px rgba(0, 0, 0, 0.1),
    -3px -3px 6px rgba(255, 255, 255, 0.8);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

/* Hover state */
.radio-inputs .radio:hover .name {
  color: #2563eb;
  transform: translateY(-1px);
  box-shadow:
    4px 4px 8px rgba(0, 0, 0, 0.1),
    -4px -4px 8px rgba(255, 255, 255, 0.8);
}

/* Selected state */
.radio-inputs .radio input:checked + .name {
  background: linear-gradient(145deg, #3b82f6, #2563eb);
  color: white;
  font-weight: 600;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
  box-shadow:
    inset 2px 2px 5px rgba(0, 0, 0, 0.2),
    inset -2px -2px 5px rgba(255, 255, 255, 0.1),
    0 2px 4px rgba(59, 130, 246, 0.3);
  transform: translateY(1px);
}

/* Animation for selection */
.radio-inputs .radio input:checked + .name {
  animation: select 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Glow effect */
.radio-inputs .radio input:checked + .name::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: inherit;
  background: radial-gradient(
    circle at center,
    rgba(255, 255, 255, 0.8) 0%,
    rgba(255, 255, 255, 0) 80%
  );
  opacity: 0;
  animation: glow 0.6s ease-out forwards;
}

/* Ripple effect */
.radio-inputs .radio .name::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 5px;
  height: 5px;
  background: rgba(255, 255, 255, 0.5);
  opacity: 0;
  border-radius: 100%;
  transform: scale(1, 1) translate(-50%);
  transform-origin: 50% 50%;
}

.radio-inputs .radio input:checked + .name::after {
  animation: ripple 0.6s ease-out forwards;
}

@keyframes select {
  0% {
    transform: scale(0.95) translateY(1px);
  }
  50% {
    transform: scale(1.05) translateY(-2px);
  }
  100% {
    transform: scale(1) translateY(1px);
  }
}

@keyframes glow {
  0% {
    opacity: 0;
    transform: scale(0.8);
  }
  50% {
    opacity: 0.3;
  }
  100% {
    opacity: 0;
    transform: scale(2);
  }
}

@keyframes ripple {
  0% {
    transform: scale(0, 0) translate(-50%, -50%);
    opacity: 0.6;
  }
  100% {
    transform: scale(20, 20) translate(-50%, -50%);
    opacity: 0;
  }
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .radio-inputs {
    padding: 0.4rem;
    gap: 0.4rem;
  }

  .radio-inputs .radio .name {
    padding: 0.6rem 1rem;
    font-size: 0.875rem;
  }

  .radio-inputs .radio {
    min-width: 100px;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .radio-inputs {
    background: linear-gradient(145deg, #1f2937, #111827);
    box-shadow:
      0 4px 6px -1px rgba(0, 0, 0, 0.2),
      0 2px 4px -1px rgba(0, 0, 0, 0.1),
      inset 0 2px 4px rgba(255, 255, 255, 0.1);
  }

  .radio-inputs .radio .name {
    color: #e5e7eb;
    background: linear-gradient(145deg, #374151, #1f2937);
    box-shadow:
      3px 3px 6px rgba(0, 0, 0, 0.2),
      -3px -3px 6px rgba(255, 255, 255, 0.05);
  }

  .radio-inputs .radio:hover .name {
    color: #60a5fa;
  }

  .radio-inputs .radio input:checked + .name {
    background: linear-gradient(145deg, #2563eb, #1d4ed8);
    box-shadow:
      inset 2px 2px 5px rgba(0, 0, 0, 0.3),
      inset -2px -2px 5px rgba(255, 255, 255, 0.05),
      0 2px 4px rgba(37, 99, 235, 0.2);
  }
}
</style>


