<template>
  <div>
    <h2>{{ question.title }}</h2>
    <div class="mb-4">{{ question.body }}</div>
    <form @submit.prevent="submitAnswer" class="mb-4">
      <textarea v-model="answerForm.answer" placeholder="Your answer..." class="block mb-2"></textarea>
      <input type="file" ref="fileInput" @change="onFileChange" accept="audio/*,video/*" class="mb-2" />
      <button type="submit">Submit Answer</button>
    </form>
    <div>
      <h3>Answers</h3>
      <ul>
        <li v-for="ans in question.answers" :key="ans.id">
          <div>{{ ans.answer }}</div>
          <div v-if="ans.media_path">
            <audio v-if="ans.media_type && ans.media_type.startsWith('audio')" :src="`/storage/${ans.media_path}`" controls />
            <video v-else-if="ans.media_type && ans.media_type.startsWith('video')" :src="`/storage/${ans.media_path}`" controls width="200" />
            <a v-else :href="`/storage/${ans.media_path}`" target="_blank">Download</a>
          </div>
        </li>
      </ul>
    </div>
    <!-- <ResumeCommentsPanel :question-id="question.id" /> -->
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
// import ResumeCommentsPanel from '@/components/resume_comments/ResumeCommentsPanel.vue';

const props = defineProps({
  questionId: [String, Number],
  initialQuestion: Object // Optional: pass question data from server
});
const question = ref(props.initialQuestion || { answers: [] });
const answerForm = ref({ answer: '', file: null });

async function fetchQuestion() {
  if (!props.questionId) return;
  const res = await axios.get(`/api/resume-questions/${props.questionId}`);
  question.value = res.data;
}

function onFileChange(e) {
  answerForm.value.file = e.target.files[0];
}

async function submitAnswer() {
  const form = new FormData();
  form.append('answer', answerForm.value.answer);
  if (answerForm.value.file) form.append('file', answerForm.value.file);
  await axios.post(`/api/resume-answers/${question.value.id}`, form);
  answerForm.value = { answer: '', file: null };
  fetchQuestion();
}

onMounted(() => {
  if (!props.initialQuestion) fetchQuestion();
});
</script>
