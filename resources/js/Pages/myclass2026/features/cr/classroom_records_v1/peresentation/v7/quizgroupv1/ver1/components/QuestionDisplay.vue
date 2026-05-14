<script setup>
defineProps({
  question: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <q-card flat bordered class="question-card">
    <q-card-section>
      <div class="text-caption text-primary text-weight-bold">Sample Group MCQ</div>
      <div class="text-h5 text-weight-bold q-mt-sm">{{ question.question }}</div>
    </q-card-section>
  </q-card>
</template>

<style scoped>
.question-card {
  border-radius: 16px;
}
</style>
