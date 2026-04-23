<template>
  <div class="answer-key-container">
    <div class="answer-key-header">
      <h3 class="text-h6 text-center q-mb-md">Answer Key</h3>
    </div>

    <AnswerTable :questions="questions" />

    <div v-if="showNotes" class="answer-key-notes q-mt-md">
      <p class="text-caption text-grey-7">
        <em>Note: This answer key should be separated from the exam paper before distribution to students.</em>
      </p>
    </div>
  </div>
</template>

<script setup>
import AnswerTable from './AnswerTable.vue'

const props = defineProps({
  questions: {
    type: Array,
    required: true
  },
  showNotes: {
    type: Boolean,
    default: true
  }
})
</script>

<style scoped>
.answer-key-container {
  page-break-inside: avoid;
  margin: 20px 0;
}

.answer-key-header {
  border-bottom: 2px solid #333;
  padding-bottom: 10px;
  margin-bottom: 15px;
}

.answer-key-notes {
  border-top: 1px dashed #ccc;
  padding-top: 10px;
}
</style>
