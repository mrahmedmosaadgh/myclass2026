<template>
  <component
    :is="templateComponent"
    :questions="questions"
    :mcq-options="mcqOptions"
  />
</template>

<script setup>
import { computed } from 'vue'
import AnswerKeyCompactChoiceTable from './answerKey/templates/AnswerKeyCompactChoiceTable.vue'

const props = defineProps({
  questions: { type: Array, required: true },
  mcqOptions: { type: Object, default: () => ({}) },
  template: { type: String, default: 'compact_choice_table' }
})

const templateComponent = computed(() => {
  if (props.template === 'compact_choice_table') return AnswerKeyCompactChoiceTable
  return AnswerKeyCompactChoiceTable
})
</script>
