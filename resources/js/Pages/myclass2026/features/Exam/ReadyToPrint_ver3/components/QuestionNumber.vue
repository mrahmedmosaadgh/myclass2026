<template>
  <span class="question-number" :class="className">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'
import { formatQuestionLabel } from '../utils/questionNumbering'

const props = defineProps({
  index: {
    type: Number,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  },
  className: {
    type: [String, Array, Object],
    default: ''
  }
})

const label = computed(() => formatQuestionLabel(props.index, props.options))
</script>
