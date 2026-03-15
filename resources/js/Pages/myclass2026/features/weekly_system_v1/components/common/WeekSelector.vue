<template>
  <q-select
    v-model="selectedWeek"
    :options="weekOptions"
    option-value="value"
    option-label="label"
    emit-value
    map-options
    outlined
    dense
    :label="label || 'Week'"
    :style="width"
    v-bind="$attrs"
  >
    <template v-slot:prepend>
      <q-icon name="calendar_today" />
    </template>
  </q-select>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: [Number, String],
  label: {
    type: String,
    default: 'Week'
  },
  width: {
    type: String,
    default: '200px'
  },
  maxWeeks: {
    type: Number,
    default: 18
  }
})

const emit = defineEmits(['update:modelValue', 'week-change'])

const selectedWeek = ref(props.modelValue || null)

const weekOptions = computed(() => {
  const weeks = []
  for (let i = 1; i <= props.maxWeeks; i++) {
    weeks.push({
      value: i,
      label: `Week ${i}`
    })
  }
  return weeks
})

watch(selectedWeek, (newValue) => {
  emit('update:modelValue', newValue)
  emit('week-change', newValue)
})

watch(() => props.modelValue, (newValue) => {
  selectedWeek.value = newValue
})
</script>
