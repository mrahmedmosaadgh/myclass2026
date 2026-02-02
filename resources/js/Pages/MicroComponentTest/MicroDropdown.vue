<template>
  <select
    :value="modelValue"
    @change="onChange"
    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
  </select>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:modelValue']);

function onChange(e) {
  emit('update:modelValue', e.target.value);
}
</script>

<style scoped>
select { padding: 0.5rem; }
</style>
