<template>
  <div class="question-editor q-pa-sm q-mt-sm" style="border:1px dashed #ccc">
    <div v-for="q in questions" :key="q.id" style="margin-bottom:8px">
      <div><strong>{{q.id}} — {{q.type}}</strong></div>
      <div>{{q.questionText}}</div>
      <div v-if="q.type==='MultipleChoice'">Options: {{q.options.join(', ')}}</div>
    </div>
    <div>
      <button @click="addSample">Add Sample Question</button>
    </div>
  </div>
</template>

<script setup>
import { toRefs } from 'vue';
const props = defineProps({ questions: { type: Array, default: () => [] } });
const emit = defineEmits(['updateQuestions']);

function addSample() {
  const id = 'q' + (props.questions.length + 1);
  const q = {
    id,
    type: 'MultipleChoice',
    questionText: 'New question ' + id,
    options: ['A','B','C'],
    correctAnswer: 'A',
    points: { correct: 5, wrong: -1 }
  };
  const arr = props.questions.concat(q);
  emit('updateQuestions', arr);
}
</script>

<style scoped>
</style>
