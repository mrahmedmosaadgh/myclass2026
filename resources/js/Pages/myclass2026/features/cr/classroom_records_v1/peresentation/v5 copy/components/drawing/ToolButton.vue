<script setup>
const props = defineProps({
  icon: String,
  label: String,
  active: Boolean,
  disabled: Boolean,
  small: Boolean
});

const emit = defineEmits(['click']);
</script>

<template>
  <button
    class="tool-button"
    :class="{ active: props.active, disabled: props.disabled, small: props.small }"
    :title="props.label"
    :disabled="props.disabled"
    @click.prevent="emit('click')"
  >
    <slot name="icon">
      <span class="icon" v-if="props.icon">{{ props.icon }}</span>
    </slot>
    <span class="label" v-if="props.label">{{ props.label }}</span>
  </button>
</template>

<style scoped>
.tool-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 6px 10px;
  border-radius: 8px;
  border: 1px solid rgba(148, 163, 184, 0.4);
  background: rgba(255, 255, 255, 0.9);
  color: #0f172a;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.4px;
  cursor: pointer;
  transition: all 0.16s ease;
}

.tool-button:hover:not(.disabled) {
  border-color: #6366f1;
  color: #111827;
  box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
}

.tool-button.active {
  background: linear-gradient(120deg, #4f46e5, #7c3aed);
  color: white;
  border-color: transparent;
  box-shadow: 0 8px 18px rgba(79, 70, 229, 0.35);
}

.tool-button.disabled {
  opacity: 0.4;
  cursor: not-allowed;
  box-shadow: none;
}

.tool-button.small {
  padding: 5px 8px;
  border-radius: 6px;
  font-size: 11px;
}

.icon {
  font-size: 14px;
  line-height: 1;
}

.label {
  white-space: nowrap;
}
</style>
