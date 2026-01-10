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
import { useI18n } from 'vue-i18n'

const props = defineProps({
  status: {
    type: String,
    default: 'draft',
    validator: (val) => ['draft', 'pending', 'active', 'archived', 'empty', 'partial', 'completed'].includes(val)
  }
})

const { t } = useI18n()

const statusConfig = {
  draft: { color: 'grey-4', textColor: 'grey-8', icon: 'edit_note' },
  pending: { color: 'amber-3', textColor: 'amber-10', icon: 'schedule' },
  active: { color: 'green-3', textColor: 'green-10', icon: 'check_circle' },
  archived: { color: 'red-2', textColor: 'red-8', icon: 'archive' },
  empty: { color: 'red-2', textColor: 'red-8', icon: 'radio_button_unchecked' },
  partial: { color: 'amber-3', textColor: 'amber-10', icon: 'timelapse' },
  completed: { color: 'green-3', textColor: 'green-10', icon: 'task_alt' }
}

const chipColor = computed(() => statusConfig[props.status]?.color || 'grey-4')
const textColor = computed(() => statusConfig[props.status]?.textColor || 'grey-8')
const chipIcon = computed(() => statusConfig[props.status]?.icon || 'help')
const displayStatus = computed(() => {
    if (statusConfig[props.status]) {
        return t(`common.status.${props.status}`)
    }
    return props.status
})
</script>
