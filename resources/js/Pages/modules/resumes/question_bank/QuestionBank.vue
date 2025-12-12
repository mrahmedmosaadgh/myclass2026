<template>
  <div>
    <h2>Resume Question Bank</h2>
    <form @submit.prevent="createQuestion" class="q-mb-md">
      <q-input v-model="form.title" label="Question Title" outlined class="q-mb-sm" required />
      <q-input v-model="form.body" label="Question Body" type="textarea" outlined class="q-mb-sm" />
      <q-select v-model="form.type" :options="typeOptions" label="Type" outlined class="q-mb-sm" />
      <q-btn type="submit" color="primary" label="Add Question" />
    </form>
    <q-list bordered separator>
      <q-item v-for="q in questions" :key="q.id" clickable @click="selectQuestion(q)">
        <q-item-section>{{ q.title }}</q-item-section>
        <q-item-section side>{{ q.type }}</q-item-section>
      </q-item>
    </q-list>
    <q-dialog v-model="showEdit" persistent>
      <q-card style="min-width:400px">
        <q-card-section>
          <q-input v-model="editForm.title" label="Edit Title" outlined class="q-mb-sm" />
          <q-input v-model="editForm.body" label="Edit Body" type="textarea" outlined class="q-mb-sm" />
          <q-select v-model="editForm.type" :options="typeOptions" label="Type" outlined class="q-mb-sm" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn color="primary" label="Save" @click="saveEdit" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
// import { QInput, QSelect, QBtn, QList, QItem, QItemSection, QDialog, QCard, QCardSection, QCardActions } from 'quasar';
import axios from 'axios';

const questions = ref([]);
const form = ref({ title: '', body: '', type: 'text' });
const typeOptions = [
  { label: 'Text', value: 'text' },
  { label: 'Audio', value: 'audio' },
  { label: 'Video', value: 'video' },
  { label: 'File', value: 'file' }
];
const showEdit = ref(false);
const editForm = ref({ id: null, title: '', body: '', type: 'text' });

async function fetchQuestions() {
  const res = await axios.get('/api/resume-questions');
  questions.value = res.data;
}

async function createQuestion() {
  await axios.post('/api/resume-questions', form.value);
  form.value = { title: '', body: '', type: 'text' };
  fetchQuestions();
}

function selectQuestion(q) {
  editForm.value = { ...q };
  showEdit.value = true;
}

async function saveEdit() {
  await axios.put(`/api/resume-questions/${editForm.value.id}`, editForm.value);
  showEdit.value = false;
  fetchQuestions();
}

onMounted(fetchQuestions);
</script>
