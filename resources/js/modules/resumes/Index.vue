<template>
  <q-page class="q-pa-md">
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center q-gutter-md">
        <q-icon name="quiz" size="32px" color="primary" />
        <div>
          <div class="text-h5">Resume Q&A System</div>
          <div class="text-caption">Manage questions and answers. Admins can add/edit questions. Users can answer and upload media.</div>
        </div>
      </q-card-section>
    </q-card>
    <q-card flat bordered class="q-pa-md q-mb-md">
      <QuestionTable
        :questions="questions"
        :loading="loading"
        :is-admin="isAdmin"
        @add="openFormDialog()"
        @edit="onEditQuestion"
        @delete="onDeleteQuestion"
        @view-answers="onViewAnswers"
      />
    </q-card>
    <q-dialog v-model="formDialog">
      <q-card style="min-width:350px;max-width:600px;width:100%">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editQuestion ? 'Edit' : 'Add' }} Question</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <QuestionForm :edit-question="editQuestion" @saved="onFormSaved" />
        </q-card-section>
      </q-card>
    </q-dialog>
    <AnswerTable
      v-if="showAnswers"
      :show="showAnswers"
      :question="selectedQuestion"
      :answers="answers"
      :loading="loadingAnswers"
      @close="showAnswers = false"
      @add="onAddAnswer"
      @edit="onEditAnswer"
      @delete="onDeleteAnswer"
      @save="onSaveAnswer"
    />
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import QuestionForm from './components/QuestionForm.vue';
import QuestionTable from './components/QuestionTable.vue';
import AnswerTable from './components/AnswerTable.vue';
import { fetchQuestions, fetchAnswers, submitQuestion, submitAnswer } from './api/resumeApi';

const $q = useQuasar();
const questions = ref([]);
const loading = ref(false);
const editQuestion = ref(null);
const formDialog = ref(false);
const isAdmin = true; // TODO: Replace with real permission check
const showAnswers = ref(false);
const selectedQuestion = ref(null);
const answers = ref([]);
const loadingAnswers = ref(false);

const refreshQuestions = async () => {
  loading.value = true;
  questions.value = await fetchQuestions();
  loading.value = false;
  editQuestion.value = null;
};

const onEditQuestion = (q) => {
  editQuestion.value = q;
  formDialog.value = true;
};

const openFormDialog = () => {
  editQuestion.value = null;
  formDialog.value = true;
};

const onFormSaved = () => {
  formDialog.value = false;
  refreshQuestions();
};

const onDeleteQuestion = (q) => {
  // TODO: Implement delete API
  $q.notify({ type: 'warning', message: 'Delete question not implemented (mock)' });
  refreshQuestions();
};

const onViewAnswers = async (q) => {
  selectedQuestion.value = q;
  showAnswers.value = true;
  loadingAnswers.value = true;
  answers.value = await fetchAnswers(q.id);
  loadingAnswers.value = false;
};

const onAddAnswer = () => {
  // Handled in AnswerTable
};
const onEditAnswer = (a) => {
  // Handled in AnswerTable
};
const onDeleteAnswer = (a) => {
  // TODO: Implement delete answer API
  $q.notify({ type: 'warning', message: 'Delete answer not implemented (mock)' });
  // Optionally refresh answers
};
const onSaveAnswer = (a) => {
  // TODO: Implement save answer API
  $q.notify({ type: 'positive', message: 'Answer saved (mock)' });
  // Optionally refresh answers
};

onMounted(refreshQuestions);
</script>

<style scoped>
.q-page {
  max-width: 1200px;
  margin: auto;
}
.q-card {
  transition: box-shadow 0.2s;
}
.q-card:hover {
  box-shadow: 0 4px 24px 0 rgba(0,0,0,0.08);
}
</style>
