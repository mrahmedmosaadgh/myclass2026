<template>
  <div>
    <h2>All Resume Answers</h2>
    <div v-if="loading">Loading...</div>
    <ul v-else>
      <li v-for="ans in answers" :key="ans.id">
        <div>{{ ans.answer }}</div>
        <div v-if="ans.media_path">
          <audio v-if="ans.media_type && ans.media_type.startsWith('audio')" :src="`/storage/${ans.media_path}`" controls />
          <video v-else-if="ans.media_type && ans.media_type.startsWith('video')" :src="`/storage/${ans.media_path}`" controls width="200" />
          <a v-else :href="`/storage/${ans.media_path}`" target="_blank">Download</a>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const answers = ref([]);
const loading = ref(true);

async function fetchAnswers() {
  loading.value = true;
  const res = await axios.get('/api/resume-answers');
  answers.value = res.data;
  loading.value = false;
}

onMounted(fetchAnswers);
</script>
