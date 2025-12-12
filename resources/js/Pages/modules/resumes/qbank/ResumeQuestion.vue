// Register the new ResumeCommentsPanel in your ResumeQuestion.vue or ResumeAnswer.vue page
<template>
  <div>
    <!-- ...existing question/answer UI... -->
    <ResumeCommentsPanel :question-id="question.id" />
  </div>
</template>

<script setup>
import ResumeCommentsPanel from '@/components/resume_comments/ResumeCommentsPanel.vue';
// ...existing code...
</script>
