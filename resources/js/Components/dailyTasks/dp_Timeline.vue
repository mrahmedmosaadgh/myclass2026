<template>
  <q-timeline color="secondary">
    <q-timeline-entry
      v-for="task in tasks"
      :key="task.id"
      :title="task.title"
      :subtitle="`${formatTime(task.start_time)} - ${formatTime(task.end_time)}`"
      :color="getTaskColor(task)"
      :icon="getTaskIcon(task)"
      side="left"
    >
      <div class="q-mb-sm text-grey-8" v-if="task.description">
        {{ task.description }}
      </div>
      <div class="row items-center q-gutter-sm">
        <q-btn 
          v-if="task.status !== 'completed'"
          label="إكمال" 
          color="primary" 
          size="sm" 
          @click="$emit('complete', task)" 
        />
        <q-chip v-else color="green-1" text-color="green" icon="check">
          تم الإنجاز
        </q-chip>
        
        <q-btn 
          v-if="isCurrentTask(task) && !isFocusing"
          label="بدء التركيز" 
          color="purple" 
          size="sm" 
          icon="timer"
          @click="$emit('start-focus', task)" 
        />
      </div>
    </q-timeline-entry>
  </q-timeline>
</template>

<script setup>
import { date } from 'quasar'
import { computed } from 'vue'

const props = defineProps({
  tasks: {
    type: Array,
    required: true
  },
  isFocusing: {
    type: Boolean,
    default: false
  }
})

defineEmits(['complete', 'start-focus'])

const formatTime = (timeStr) => {
  if (!timeStr) return ''
  const [hours, minutes] = timeStr.split(':')
  const d = new Date()
  d.setHours(hours)
  d.setMinutes(minutes)
  return date.formatDate(d, 'h:mm a')
}

const getTaskColor = (task) => {
  if (task.status === 'completed') return 'green'
  if (task.status === 'skipped') return 'grey'
  return 'orange'
}

const getTaskIcon = (task) => {
  if (task.status === 'completed') return 'check_circle'
  return 'schedule'
}

const isCurrentTask = (task) => {
  if (!task.start_time || !task.end_time) return false
  const now = new Date()
  const [startH, startM] = task.start_time.split(':')
  const [endH, endM] = task.end_time.split(':')
  
  const start = new Date()
  start.setHours(startH, startM, 0)
  
  const end = new Date()
  end.setHours(endH, endM, 0)
  
  return now >= start && now <= end
}
</script>
