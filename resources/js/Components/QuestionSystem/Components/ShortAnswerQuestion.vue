<template>
  <div class="space-y-2">
    <input
      type="text"
      :value="answer || ''"
      @input="!submitted && updateAnswer($event.target.value)"
      class="w-full p-4 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
      :disabled="submitted"
      placeholder="Type your answer..."
    />
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

const updateAnswer = (val) => emit('update:answer', val);
</script>

<style scoped>
/* Add any specific styling if needed */
</style>
