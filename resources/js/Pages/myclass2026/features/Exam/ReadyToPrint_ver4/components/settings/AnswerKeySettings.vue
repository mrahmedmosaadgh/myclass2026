<template>
  <div class="options-grid">
    <q-toggle
      v-model="model.answerKey.enabled"
      label="Show Answer Key at end"
    />

    <q-select
      v-if="model.answerKey.enabled"
      dense
      outlined
      :options="templates"
      emit-value
      map-options
      v-model="model.answerKey.template"
      label="Answer key template"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Object, required: true }
})

const emit = defineEmits(['update:modelValue'])

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v)
})

const templates = [
  { label: 'Compact choice table (# / Correct Choice)', value: 'compact_choice_table' }
]
</script>
