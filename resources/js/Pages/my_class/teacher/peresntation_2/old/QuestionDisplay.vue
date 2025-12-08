<template>
  <div class="question-display q-pa-md q-mt-md" style="border:1px solid #ddd; background:#fff">
    <div><strong>{{question.questionText}}</strong></div>
    <div v-if="question.type==='MultipleChoice'">
      <div v-for="opt in question.options" :key="opt" style="margin-top:6px">
        <button @click="answer(opt)">{{opt}}</button>
      </div>
    </div>
    <div v-else-if="question.type==='OpenEnded'">
      <input v-model="response" />
      <button @click="answer(response)">Submit</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
const props = defineProps({ question: Object });
const emit = defineEmits(['answered']);
const response = ref('');

function playSound(url) {
  if (!url) return;
  const a = new Audio(url);
  a.play().catch(() => {});
}

function answer(value) {
  const q = props.question;
  const correct = q.type === 'MultipleChoice' ? value === q.correctAnswer : String(value).trim() === String(q.correctAnswer).trim();
  const pts = correct ? (q.points?.correct || 0) : (q.points?.wrong || 0);
  playSound(correct ? q.sounds?.correct : q.sounds?.wrong);
  emit('answered', { questionId: q.id, correct, points: pts });
}
</script>

<style scoped>
.question-display button { margin-right:6px; }
</style>
