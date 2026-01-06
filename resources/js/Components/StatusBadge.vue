<template>
  <q-chip
    :color="badgeColor"
    text-color="white"
    :label="statusLabel"
    size="sm"
  />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: value => [
      'empty',
      'partial',
      'complete',
      'overdue'
    ].includes(value)
  }
});

// Compute the badge color based on status
const badgeColor = computed(() => {
  switch (props.status) {
    case 'empty':
      return 'grey';
    case 'partial':
      return 'amber';
    case 'complete':
      return 'green';
    case 'overdue':
      return 'red';
    default:
      return 'primary';
  }
});

// Compute the status label
const statusLabel = computed(() => {
  switch (props.status) {
    case 'empty':
      return 'Empty';
    case 'partial':
      return 'Partial';
    case 'complete':
      return 'Complete';
    case 'overdue':
      return 'Overdue';
    default:
      return props.status;
  }
});
</script>