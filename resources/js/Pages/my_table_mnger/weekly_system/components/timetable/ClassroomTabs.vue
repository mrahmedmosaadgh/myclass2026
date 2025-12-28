<template>
  <q-tabs
    v-model="selectedClassroom"
    class="text-primary"
    active-color="primary"
    indicator-color="primary"
    align="left"
    narrow-indicator
    dense
    outside-arrows
    mobile-arrows
  >
    <q-tab
      v-for="classroom in classrooms"
      :key="classroom.id"
      :name="classroom.id"
      :label="classroom.name"
    >
      <q-badge v-if="classroom.grade" color="grey-5" floating>
        {{ classroom.grade?.name || classroom.grade_name }}
      </q-badge>
    </q-tab>
  </q-tabs>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: [Number, String], default: null },
  classrooms: { type: Array, default: () => [] }
})

const emit = defineEmits(['update:modelValue'])

const selectedClassroom = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})
</script>
