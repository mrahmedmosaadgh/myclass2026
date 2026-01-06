<template>
  <div>
    <q-select
      v-model="selectedWeek"
      :options="weeks"
      option-value="id"
      option-label="name"
      emit-value
      map-options
      outlined
      label="Select Week"
      style="min-width: 200px"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    required: false,
    default: null
  },
  weeks: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'week-selected']);

const selectedWeek = ref(props.modelValue);

// Watch for changes and emit update event
watch(
  () => selectedWeek.value,
  (newVal) => {
    if (newVal) {
      emit('update:modelValue', newVal);
      emit('week-selected', newVal); // Emit an event when a week is selected
    }
  }
);
</script>