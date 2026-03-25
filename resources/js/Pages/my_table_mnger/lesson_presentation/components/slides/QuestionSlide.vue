<template>
  <div class="space-y-6">
    <div v-if="questions.length === 0" class="text-gray-500">
      No questions available for this slide.
    </div>
    <div v-else>
      <div v-for="(q, idx) in questions" :key="q.id || idx" class="p-4 rounded-lg border bg-white">
        <div class="font-medium mb-2">Question {{ idx + 1 }}</div>
        <div class="mb-3" v-html="q.text || q.prompt || ''"></div>
        <div v-if="Array.isArray(q.options)" class="space-y-2">
          <button
            v-for="opt in q.options"
            :key="opt.id || opt.value"
            class="px-4 py-2 rounded border hover:bg-gray-100"
            @click="selectAnswer(q, opt)"
          >
            <span v-if="opt.text" v-html="opt.text"></span>
            <span v-else>{{ opt.label || opt.value }}</span>
          </button>
        </div>
      </div>
      <div class="mt-4">
        <button class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" @click="completeQuiz">
          Continue
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  mode: { type: String, default: 'play' },
  quizConfig: { type: Object, default: () => ({}) },
  attemptId: { type: String, default: '' },
  legacyMode: { type: String, default: '' }
});

const emit = defineEmits(['answer-selected', 'quiz-completed']);

const questions = computed(() => {
  if (Array.isArray(props.modelValue?.questions)) return props.modelValue.questions;
  return [];
});

const selectAnswer = (question, option) => {
  emit('answer-selected', {
    questionId: question.id,
    selectedOptionId: option.id,
    selectedText: option.text || option.label || option.value || '',
    correct: typeof option.correct === 'boolean' ? option.correct : undefined,
    timeSpentSec: undefined,
    answeredAt: new Date().toISOString()
  });
};

const completeQuiz = () => {
  emit('quiz-completed', {
    attemptId: props.attemptId || '',
    total: questions.value.length,
    correct: undefined,
    percentage: undefined,
    completedAt: new Date().toISOString(),
    answers: [],
    metadata: {}
  });
};
</script>

<style scoped>
</style>
