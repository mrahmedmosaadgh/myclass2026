<script setup>
import QuizElementV2 from './quiz-v2/QuizElementV2.vue'

const props = defineProps({
  element: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="student-quiz-view">
    <QuizElementV2 :element="element" :is-present-mode="true" />
  </div>
</template>

<style scoped>
.student-quiz-view {
  width: 100%;
  min-height: 100%;
  padding: 16px;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
}

/* Override any fixed width/height from the element's canvas positioning */
.student-quiz-view :deep(.quiz-v2-wrapper) {
  width: 100% !important;
  height: auto !important;
  min-height: calc(100vh - 180px);
}
</style>
