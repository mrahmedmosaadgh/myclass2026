<template>
  <q-badge :color="badgeColor" :label="displayText" v-bind="$attrs" />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true
  },
  statusMap: {
    type: Object,
    default: () => ({
      // Default status mappings
      draft: { label: 'Draft', color: 'grey-7' },
      active: { label: 'Active', color: 'primary' },
      completed: { label: 'Completed', color: 'positive' },
      locked: { label: 'Locked', color: 'negative' },
      pending: { label: 'Pending', color: 'warning' }
    })
  },
  defaultLabel: {
    type: String,
    default: 'Unknown'
  }
})

const badgeColor = computed(() => {
  return props.statusMap[props.status]?.color || 'grey'
})

const displayText = computed(() => {
  return props.statusMap[props.status]?.label || props.defaultLabel
})
</script>
