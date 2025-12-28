<template>
  <div class="week-selector row items-center q-gutter-sm">
    <q-btn
      flat
      dense
      round
      icon="chevron_left"
      :disable="weekNumber <= 1"
      @click="previousWeek"
    />
    
    <q-select
      v-model="weekNumber"
      :options="weekOptions"
      outlined
      dense
      style="min-width: 150px"
      @update:model-value="$emit('update:modelValue', $event)"
    >
      <template v-slot:prepend>
        <q-icon name="view_week" color="primary" />
      </template>
    </q-select>
    
    <q-btn
      flat
      dense
      round
      icon="chevron_right"
      :disable="weekNumber >= maxWeeks"
      @click="nextWeek"
    />

    <q-btn
      flat
      dense
      color="primary"
      icon="today"
      label="Current"
      @click="goToCurrentWeek"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Number, default: 1 },
  maxWeeks: { type: Number, default: 18 },
  currentWeek: { type: Number, default: 1 }
})

const emit = defineEmits(['update:modelValue'])

const weekNumber = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const weekOptions = computed(() => {
  return Array.from({ length: props.maxWeeks }, (_, i) => ({
    label: `Week ${i + 1}`,
    value: i + 1
  }))
})

const previousWeek = () => {
  if (weekNumber.value > 1) {
    emit('update:modelValue', weekNumber.value - 1)
  }
}

const nextWeek = () => {
  if (weekNumber.value < props.maxWeeks) {
    emit('update:modelValue', weekNumber.value + 1)
  }
}

const goToCurrentWeek = () => {
  emit('update:modelValue', props.currentWeek)
}
</script>
