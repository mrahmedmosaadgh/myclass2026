<template>
  <q-item clickable v-ripple @click="$emit('toggle', task)" :class="{ 'bg-green-1': task.status === 'completed' }">
    <q-item-section avatar>
      <q-checkbox :model-value="task.status === 'completed'" @update:model-value="$emit('toggle', task)" />
    </q-item-section>

    <q-item-section>
      <q-item-label>{{ task.title }}</q-item-label>
      <q-item-label caption>
        {{ formatTime(task.start_time) }} - {{ formatTime(task.end_time) }}
      </q-item-label>
      <q-item-label caption v-if="task.description" class="text-italic text-grey-8">
        {{ task.description }}
      </q-item-label>
    </q-item-section>

    <q-item-section side>
      <q-badge v-if="task.status === 'completed'" color="green" label="منجز" />
      <q-badge v-else color="orange" label="قيد الانتظار" />
    </q-item-section>
  </q-item>
</template>

<script setup>
import { date } from 'quasar'

const props = defineProps({
  task: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle'])

const formatTime = (timeStr) => {
  if (!timeStr) return ''
  // Assuming timeStr is HH:mm:ss or HH:mm
  const [hours, minutes] = timeStr.split(':')
  const d = new Date()
  d.setHours(hours)
  d.setMinutes(minutes)
  return date.formatDate(d, 'h:mm a')
}
</script>
