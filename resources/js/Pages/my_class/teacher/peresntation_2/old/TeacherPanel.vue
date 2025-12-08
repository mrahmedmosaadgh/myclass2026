<template>
  <div class="teacher-panel q-pa-sm">
    <h3>Teacher Controls</h3>
    <div class="controls q-mt-sm">
      <button @click="$emit('start')">Start Activity</button>
      <button @click="$emit('pause')">Pause</button>
      <button @click="$emit('next')">Next</button>
    </div>
    <div class="feedback q-mt-sm">
      <textarea v-model="feedbackText" placeholder="Write feedback for students"></textarea>
      <button @click="sendFeedback">Send Feedback</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
const emit = defineEmits(['start','pause','next','feedback']);
const feedbackText = ref('');
function sendFeedback() {
  emit('feedback', feedbackText.value);
  feedbackText.value = '';
}
</script>

<style scoped>
.teacher-panel textarea { width:100%; min-height:80px }
.teacher-panel button { margin-right:8px }
</style>
