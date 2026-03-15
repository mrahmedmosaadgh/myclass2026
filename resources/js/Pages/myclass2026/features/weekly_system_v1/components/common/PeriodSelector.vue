<template>
  <q-select
    v-model="selectedPeriod"
    :options="periodOptions"
    option-value="value"
    option-label="label"
    emit-value
    map-options
    outlined
    dense
    :label="label || 'Period'"
    :style="width"
    v-bind="$attrs"
  >
    <template v-slot:prepend>
      <q-icon name="access_time" />
    </template>
  </q-select>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: [Number, String],
  label: {
    type: String,
    default: 'Period'
  },
  width: {
    type: String,
    default: '150px'
  },
  maxPeriods: {
    type: Number,
    default: 12
  }
})

const emit = defineEmits(['update:modelValue', 'period-change'])

const selectedPeriod = ref(props.modelValue || null)

const periodOptions = computed(() => {
  const periods = []
  for (let i = 1; i <= props.maxPeriods; i++) {
    periods.push({
      value: i,
      label: `Period ${i}`
    })
  }
  return periods
})

watch(selectedPeriod, (newValue) => {
  emit('update:modelValue', newValue)
  emit('period-change', newValue)
})

watch(() => props.modelValue, (newValue) => {
  selectedPeriod.value = newValue
})
</script>
