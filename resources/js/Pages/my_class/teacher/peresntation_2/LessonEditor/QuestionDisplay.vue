<template>
  <div>
    <component :is="questionComponent" :question="question" @answered="onAnswered" />
  </div>
</template>

<script setup>
import QuestionFactory from './QuestionTypes/QuestionFactory.js'
const props = defineProps({ question: Object })
const emit = defineEmits(['answered'])
const questionComponent = QuestionFactory.resolve(props.question.type)
function onAnswered(payload){ emit('answered', payload) }
</script>
