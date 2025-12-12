<template>
  <q-card>
    <q-card-section>
      <div class="text-subtitle1">{{ question.questionText }}</div>
      <div class="q-mt-md">
        <q-input v-model="answer" dense />
        <q-btn label="Submit" @click="submit" />
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref } from 'vue'
const props = defineProps({ question: Object })
const emit = defineEmits(['answered'])
const answer = ref('')
const attempts = ref(0)
function submit(){ attempts.value++; const correct = String(answer.value).trim() === String(props.question.correctAnswer||'').trim(); const points = Math.max(0, (props.question.maxPoints||5) - (attempts.value-1)); emit('answered', { correct, attempts: attempts.value, points }) }
</script>
