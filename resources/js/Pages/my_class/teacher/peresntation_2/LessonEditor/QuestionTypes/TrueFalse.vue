<template>
  <q-card>
    <q-card-section>
      <div class="text-subtitle1">{{ question.questionText }}</div>
      <div class="q-mt-md">
        <q-btn label="True" @click="choose(true)" />
        <q-btn label="False" @click="choose(false)" />
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref } from 'vue'
const props = defineProps({ question: Object })
const emit = defineEmits(['answered'])
const attempts = ref(0)
function choose(v){ attempts.value++; const correct = (props.question.correct === v); const points = Math.max(0, (props.question.maxPoints||5) - (attempts.value-1)); emit('answered', { correct, attempts: attempts.value, points }) }
</script>
