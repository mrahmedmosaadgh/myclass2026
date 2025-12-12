<template>
  <div class="space-y-2">
    <button
      v-for="option in question.options"
      :key="option.id"
      @click="!submitted && toggleOption(option.id)"
      :class="['w-full text-left p-4 rounded-lg border transition-all flex items-center gap-3', getOptionClass(option.id)]"
    >
      <div class="w-5 h-5 border rounded flex items-center justify-center"
           :class="isSelected(option.id) ? 'bg-blue-600 border-blue-600' : 'border-gray-300'">
        <i v-if="isSelected(option.id)" class="fas fa-check text-white text-xs"></i>
      </div>
      <span v-html="option.text"></span>
    </button>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  question: { type: Object, required: true },
  answer: { type: [String, Number, Array, Boolean], default: null },
  submitted: { type: Boolean, default: false }
});

const emit = defineEmits(['update:answer']);

const toggleOption = (id) => {
  const cur = Array.isArray(props.answer) ? [...props.answer] : [];
  const idx = cur.indexOf(id);
  if (idx === -1) cur.push(id);
  else cur.splice(idx, 1);
  emit('update:answer', cur);
};

const isSelected = (val) => {
  if (Array.isArray(props.answer)) return props.answer.includes(val);
  return props.answer === val;
};

const getOptionClass = (id) => {
  const selected = isSelected(id);
  if (!props.submitted) {
    return selected ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'bg-white border-gray-200 hover:border-blue-300';
  }
  const correct = props.question.options.find(o => o.id === id)?.is_correct;
  if (correct) return 'bg-green-50 border-green-500 ring-1 ring-green-500';
  if (selected && !correct) return 'bg-red-50 border-red-500 ring-1 ring-red-500';
  return 'bg-white border-gray-200 opacity-50';
};
</script>

<style scoped>
/* Add any specific styling if needed */
</style>
