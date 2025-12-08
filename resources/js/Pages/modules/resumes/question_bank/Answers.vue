<template>
  <div>
    <h2>Resume Answers</h2>
    <q-list bordered separator>
      <q-item v-for="ans in answers" :key="ans.id">
        <q-item-section>{{ ans.answer }}</q-item-section>
        <q-item-section side>{{ ans.media_type }}</q-item-section>
      </q-item>
    </q-list>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const answers = ref([]);

async function fetchAnswers() {
  const res = await axios.get('/api/resume-answers');
  answers.value = res.data;
}

onMounted(fetchAnswers);
</script>
