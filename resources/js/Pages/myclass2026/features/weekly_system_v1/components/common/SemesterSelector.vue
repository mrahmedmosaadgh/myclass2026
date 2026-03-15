<template>
  <q-select
    v-model="selectedSemester"
    :options="semesterOptions"
    option-value="value"
    option-label="label"
    emit-value
    map-options
    outlined
    dense
    :label="label || 'Semester'"
    :style="width"
    v-bind="$attrs"
  >
    <template v-slot:prepend>
      <q-icon name="school" />
    </template>
  </q-select>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: [Number, String],
  label: {
    type: String,
    default: 'Semester'
  },
  width: {
    type: String,
    default: '200px'
  }
})

const emit = defineEmits(['update:modelValue', 'semester-change'])

const selectedSemester = ref(props.modelValue || null)

const semesterOptions = [
  { value: 1, label: 'Semester 1' },
  { value: 2, label: 'Semester 2' }
]

watch(selectedSemester, (newValue) => {
  emit('update:modelValue', newValue)
  emit('semester-change', newValue)
})

watch(() => props.modelValue, (newValue) => {
  selectedSemester.value = newValue
})
</script>
