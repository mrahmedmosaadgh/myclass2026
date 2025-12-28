<template>
  <q-select
    v-model="model"
    :options="semesters"
    option-value="id"
    option-label="name"
    label="Semester"
    emit-value
    map-options
    outlined
    dense
    :loading="loading"
    :disable="disable"
    clearable
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <template v-slot:prepend>
      <q-icon name="date_range" color="primary" />
    </template>
    <template v-slot:no-option>
      <q-item>
        <q-item-section class="text-grey">No semesters available</q-item-section>
      </q-item>
    </template>
  </q-select>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: [Number, String], default: null },
  semesters: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  disable: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue'])

const model = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})
</script>
