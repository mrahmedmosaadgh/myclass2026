<template>
  <q-chip
    :color="chipColor"
    :text-color="textColor"
    :icon="chipIcon"
    size="sm"
    dense
  >
    {{ displayStatus }}
  </q-chip>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    default: 'draft',
    validator: (val) => ['draft', 'pending', 'active', 'archived', 'empty', 'partial', 'completed'].includes(val)
  }
})

const statusConfig = {
  draft: { color: 'grey-4', textColor: 'grey-8', icon: 'edit_note', label: 'Draft' },
  pending: { color: 'amber-3', textColor: 'amber-10', icon: 'schedule', label: 'Pending' },
  active: { color: 'green-3', textColor: 'green-10', icon: 'check_circle', label: 'Active' },
  archived: { color: 'red-2', textColor: 'red-8', icon: 'archive', label: 'Archived' },
  empty: { color: 'red-2', textColor: 'red-8', icon: 'radio_button_unchecked', label: 'Empty' },
  partial: { color: 'amber-3', textColor: 'amber-10', icon: 'timelapse', label: 'Partial' },
  completed: { color: 'green-3', textColor: 'green-10', icon: 'task_alt', label: 'Completed' }
}

const chipColor = computed(() => statusConfig[props.status]?.color || 'grey-4')
const textColor = computed(() => statusConfig[props.status]?.textColor || 'grey-8')
const chipIcon = computed(() => statusConfig[props.status]?.icon || 'help')
const displayStatus = computed(() => statusConfig[props.status]?.label || props.status)
</script>
