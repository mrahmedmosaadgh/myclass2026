<template>
  <div class="q-pa-md">
    <h1>Resume System</h1>
    <q-tabs v-model="tab" class="text-primary q-mb-md">
      <q-tab name="question-bank" label="Question Bank" />
      <q-tab name="answers" label="Answers" />
      <q-tab name="comments" label="Comments" />
    </q-tabs>
    <div v-if="tab === 'question-bank'">
      <QuestionBank />
    </div>
    <div v-else-if="tab === 'answers'">
      <Answers />
    </div>
    <div v-else-if="tab === 'comments'">
      <Comments />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import QuestionBank from './question_bank/QuestionBank.vue';
import Answers from './question_bank/Answers.vue';
import Comments from './question_bank/Comments.vue';
const tab = ref('question-bank');
const pageTitle = 'Resume System';
</script>

<style scoped>
.q-pa-md {
  padding: 16px;
}

.text-primary {
  color: #1976d2;
}

.q-mb-md {
  margin-bottom: 16px;
}

.q-tabs {
  border-bottom: 1px solid #e0e0e0;
}

.q-tab {
  font-weight: 500;
  padding: 12px 16px;
}

.q-tab:hover {
  background-color: #f5f5f5;
}

.q-tab.q-tabs__tab--active {
  color: #1976d2;
  border-bottom: 2px solid #1976d2;
}

.q-tab.q-tabs__tab--active:hover {
  background-color: transparent;
}

.q-card {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.q-card-section {
  padding: 16px;
}

.q-btn {
  min-width: 120px;
}

.q-dialog .q-card {
  border-radius: 8px;
}

.q-file__input {
  padding: 8px 12px;
}

.q-banner {
  border-radius: 8px;
}

.q-tooltip {
  font-size: 12px;
  padding: 6px 10px;
  border-radius: 6px;
  backdrop-filter: blur(8px);
}
</style>





