<template>
  <q-card>
    <q-card-section>
      <div class="text-subtitle1">{{ question.questionText }}</div>
      <div class="q-mt-md">
        <q-btn v-for="(opt,i) in question.options" :key="i" class="q-mb-sm" @click="choose(i)">{{ opt }}</q-btn>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref } from 'vue'
const props = defineProps({ question: Object })
const emit = defineEmits(['answered'])
const attempts = ref(0)
function choose(i){ attempts.value++; const correct = (props.question.correctIndex === i); const points = Math.max(0, (props.question.maxPoints||5) - (attempts.value-1)); emit('answered', { correct, attempts: attempts.value, points }) }
</script>
