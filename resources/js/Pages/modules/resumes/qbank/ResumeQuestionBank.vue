<template>
  <div>vvvvvvvvvvvvvvvvv
    <h1>Resume Question Bank</h1>
    <form @submit.prevent="createQuestion" class="mb-4">
      <input v-model="form.title" placeholder="Question Title" required class="block mb-2" />
      <textarea v-model="form.body" placeholder="Question Body" class="block mb-2"></textarea>
      <select v-model="form.type" class="block mb-2">
        <option value="text">Text</option>
        <option value="audio">Audio</option>
        <option value="video">Video</option>
        <option value="file">File</option>
      </select>
      <button type="submit">Add Question</button>
    </form>
    <ul>
      <li v-for="q in questions" :key="q.id">
        <router-link :to="`/resume-questions/${q.id}`">{{ q.title }}</router-link>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const questions = ref([]);
const form = ref({ title: '', body: '', type: 'text' });

async function fetchQuestions() {
  const res = await axios.get('/api/resume-questions');
  questions.value = res.data;
}

async function createQuestion() {
  await axios.post('/api/resume-questions', form.value);
  form.value = { title: '', body: '', type: 'text' };
  fetchQuestions();
}

onMounted(fetchQuestions);
</script>
