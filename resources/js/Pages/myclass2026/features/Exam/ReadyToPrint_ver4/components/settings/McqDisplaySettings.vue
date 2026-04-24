<template>
  <div class="options-grid">
    <q-select
      dense
      outlined
      :options="mcqLabelStyles"
      emit-value
      map-options
      v-model="model.mcqOptions.labelStyle"
      label="MCQ option label style"
    />

    <q-toggle
      v-if="model.mcqOptions.labelStyle === 'checkbox'"
      v-model="model.mcqOptions.checkboxShowLabel"
      label="Show letter/number next to checkbox"
    />

    <q-select
      v-if="model.mcqOptions.labelStyle === 'checkbox' && model.mcqOptions.checkboxShowLabel"
      dense
      outlined
      :options="[
        { label: 'Letters (A)', value: 'letter' },
        { label: 'Numbers (1)', value: 'number' },
        { label: 'Custom', value: 'custom' }
      ]"
      emit-value
      map-options
      v-model="model.mcqOptions.checkboxLabelType"
      label="Checkbox label type"
    />

    <q-input
      v-if="model.mcqOptions.labelStyle === 'custom' || (model.mcqOptions.labelStyle === 'checkbox' && model.mcqOptions.checkboxShowLabel && model.mcqOptions.checkboxLabelType === 'custom')"
      dense
      outlined
      v-model="model.mcqOptions.customLabelTemplate"
      label="MCQ label template (use {i}, {n}, {letter})"
    />

    <q-input
      dense
      outlined
      type="number"
      v-model.number="model.mcqOptions.labelFontSizePt"
      label="Label font size (pt) (0 = default)"
    />

    <q-toggle
      v-model="model.mcqOptions.labelBold"
      label="Label bold"
    />

    <q-input
      dense
      outlined
      type="number"
      v-model.number="model.mcqOptions.optionFontSizePt"
      label="Choice font size (pt) (0 = default)"
    />

    <q-toggle
      v-model="model.mcqOptions.optionBold"
      label="Choice bold"
    />

    <q-input
      dense
      outlined
      type="number"
      v-model.number="model.mcqOptions.columns"
      label="MCQ choices per line (1 = each choice on its own line)"
      min="1"
    />

    <q-select
      dense
      outlined
      :options="[
        { label: '1 per line', value: 1 },
        { label: '2 per line', value: 2 },
        { label: '3 per line', value: 3 },
        { label: '4 per line', value: 4 }
      ]"
      emit-value
      map-options
      :model-value="(Number(model.mcqOptions.columns) || 1)"
      label="Quick layout"
      @update:model-value="(v) => { model.mcqOptions.columns = v }"
    />

    <q-input
      dense
      outlined
      type="number"
      v-model.number="model.mcqOptions.optionGapPt"
      label="MCQ option gap (pt)"
    />

    <q-input
      dense
      outlined
      type="number"
      v-model.number="model.mcqOptions.labelGapPt"
      label="MCQ label gap (pt)"
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

const mcqLabelStyles = [
  { label: 'Letters (A)', value: 'letter' },
  { label: 'Numbers (1)', value: 'number' },
  { label: 'Checkbox', value: 'checkbox' },
  { label: 'Custom', value: 'custom' }
]
</script>
