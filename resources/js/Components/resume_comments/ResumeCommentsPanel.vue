<template>
  <div>
    <CommentComposer :question-id="questionId" @submitted="fetchComments" />
    <CommentThread :comments="comments" :question-id="questionId" @refresh="fetchComments" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import CommentComposer from '@/components/resume_comments/CommentComposer.vue';
import CommentThread from '@/components/resume_comments/CommentThread.vue';

const props = defineProps({
  questionId: [String, Number],
});

const comments = ref([]);

async function fetchComments() {
  const res = await axios.get('/api/resume-comments', { params: { question_id: props.questionId } });
  comments.value = res.data;
}

onMounted(fetchComments);
</script>
