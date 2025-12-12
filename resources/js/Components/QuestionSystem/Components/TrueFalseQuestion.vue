<template>
  <div class="flex gap-4">
    <button
      @click="!submitted && updateAnswer(true)"
      :class="['flex-1 p-4 rounded-lg border text-center font-medium transition-all', getTrueFalseClass(true)]"
    >
      True
    </button>
    <button
      @click="!submitted && updateAnswer(false)"
      :class="['flex-1 p-4 rounded-lg border text-center font-medium transition-all', getTrueFalseClass(false)]"
    >
      False
    </button>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  question: { type: Object, required: true },
  answer: { type: Boolean, default: null },
  submitted: { type: Boolean, default: false }
});

const emit = defineEmits(['update:answer']);

const updateAnswer = (val) => emit('update:answer', val);

const isSelected = (val) => {
  return props.answer === val;
};

const getTrueFalseClass = (val) => {
  const selected = isSelected(val);
  if (!props.submitted) {
    return selected ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:border-blue-300';
  }
  const correct = (props.question.correct_answer === true || props.question.correct_answer === 'true' || props.question.correct_answer === 1) === val;
  if (correct) return 'bg-green-600 text-white border-green-600';
  if (selected && !correct) return 'bg-red-600 text-white border-red-600';
  return 'bg-white text-gray-400 border-gray-200';
};
</script>

<style scoped>
/* Add any specific styling if needed */
</style>
