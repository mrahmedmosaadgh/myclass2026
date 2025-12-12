<template>
  <q-page class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="text-h5">Resume Questions Manager</div>
        <q-btn color="primary" label="Add Question" @click="openQuestionDialog()" class="q-mb-md" />
        <q-table
          :rows="questions"
          :columns="questionColumns"
          row-key="id"
          :loading="loadingQuestions"
          :expand="true"
          @request="fetchQuestions"
        >
          <template v-slot:body-cell-actions="props">
            <q-btn flat icon="edit" @click="editQuestion(props.row)" />
            <q-btn flat icon="delete" color="negative" @click="deleteQuestion(props.row)" />
            <q-btn flat icon="list" color="secondary" @click="toggleExpand(props.key)" />
          </template>
          <template v-slot:body-row-expand="props">
            <q-table
              :rows="answers[props.row.id] || []"
              :columns="answerColumns"
              row-key="id"
              :loading="loadingAnswers[props.row.id]"
            >
              <template v-slot:body-cell-actions="aProps">
                <q-btn flat icon="edit" @click="editAnswer(props.row.id, aProps.row)" />
                <q-btn flat icon="delete" color="negative" @click="deleteAnswer(props.row.id, aProps.row)" />
              </template>
              <template v-slot:top="">
                <q-btn color="primary" label="Add Answer" @click="addAnswer(props.row.id)" />
              </template>
            </q-table>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
    <question-form
      v-model="questionDialog.visible"
      :question="questionDialog.data"
      @save="onQuestionSaved"
    />
    <answer-form
      v-model="answerDialog.visible"
      :answer="answerDialog.data"
      :question-id="answerDialog.questionId"
      @save="onAnswerSaved"
    />
    <q-spinner v-if="loadingQuestions" color="primary" size="2em" />
    <q-snackbar v-model="snackbar.visible" :color="snackbar.color">{{ snackbar.message }}</q-snackbar>
  </q-page>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import resumeApi from '@/modules/resumes/resumes_questions/resumeApi.js';
import QuestionForm from '@/Pages/modules/resumes/qbank/QuestionForm.vue';
import AnswerForm from '@/modules/resumes/resumes_questions/AnswerForm.vue';

const $q = useQuasar();
const questions = ref([]);
const answers = reactive({});
const loadingQuestions = ref(false);
const loadingAnswers = reactive({});
const snackbar = reactive({ visible: false, message: '', color: 'positive' });

const questionColumns = [
  { name: 'title', label: 'Title', field: 'title', align: 'left' },
  { name: 'type', label: 'Type', field: 'type', align: 'left' },
  { name: 'category', label: 'Category', field: 'category', align: 'left' },
  { name: 'language', label: 'Language', field: 'language', align: 'left' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
];
const answerColumns = [
  { name: 'user', label: 'User', field: 'user_id', align: 'left' },
  { name: 'answer', label: 'Answer', field: 'answer_text', align: 'left' },
  { name: 'media', label: 'Media', field: 'media_links', align: 'left' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
];

const questionDialog = reactive({ visible: false, data: null });
const answerDialog = reactive({ visible: false, data: null, questionId: null });

function showSnackbar(message, color = 'positive') {
  snackbar.message = message;
  snackbar.color = color;
  snackbar.visible = true;
}

async function fetchQuestions() {
  loadingQuestions.value = true;
  try {
    questions.value = await resumeApi.getQuestions();
  } catch (e) {
    showSnackbar('Failed to load questions', 'negative');
  } finally {
    loadingQuestions.value = false;
  }
}

async function fetchAnswers(questionId) {
  loadingAnswers[questionId] = true;
  try {
    answers[questionId] = await resumeApi.getAnswers(questionId);
  } catch (e) {
    showSnackbar('Failed to load answers', 'negative');
  } finally {
    loadingAnswers[questionId] = false;
  }
}

function openQuestionDialog(question = null) {
  questionDialog.data = question ? { ...question } : null;
  questionDialog.visible = true;
}
function editQuestion(question) {
  openQuestionDialog(question);
}
function deleteQuestion(question) {
  $q.dialog({
    title: 'Confirm',
    message: 'Delete this question?',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await resumeApi.deleteQuestion(question.id);
      fetchQuestions();
      showSnackbar('Question deleted');
    } catch (e) {
      showSnackbar('Delete failed', 'negative');
    }
  });
}
function toggleExpand(questionId) {
  if (!answers[questionId]) fetchAnswers(questionId);
}
function onQuestionSaved() {
  questionDialog.visible = false;
  fetchQuestions();
}
function addAnswer(questionId) {
  answerDialog.data = null;
  answerDialog.questionId = questionId;
  answerDialog.visible = true;
}
function editAnswer(questionId, answer) {
  answerDialog.data = { ...answer };
  answerDialog.questionId = questionId;
  answerDialog.visible = true;
}
function deleteAnswer(questionId, answer) {
  $q.dialog({
    title: 'Confirm',
    message: 'Delete this answer?',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await resumeApi.deleteAnswer(answer.id);
      fetchAnswers(questionId);
      showSnackbar('Answer deleted');
    } catch (e) {
      showSnackbar('Delete failed', 'negative');
    }
  });
}
function onAnswerSaved() {
  answerDialog.visible = false;
  if (answerDialog.questionId) fetchAnswers(answerDialog.questionId);
}
onMounted(fetchQuestions);
</script>

<style scoped>
.q-table .q-btn {
  margin-right: 4px;
}
</style>
