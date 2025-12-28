<template>
  <div class="completion-progress-bar">
    <div class="row items-center q-gutter-sm">
      <!-- Teacher Info -->
      <div class="col-auto teacher-info">
        <q-avatar size="32px" color="primary" text-color="white">
          {{ teacherInitials }}
        </q-avatar>
      </div>
      <div class="col teacher-name">
        <div class="text-body2 text-weight-medium">{{ teacher.teacher_name }}</div>
        <div class="text-caption text-grey-6">{{ completedCount }}/{{ totalCount }} completed</div>
      </div>

      <!-- Progress Bar -->
      <div class="col-12 col-sm-6">
        <q-linear-progress
          :value="percentage / 100"
          size="20px"
          :color="progressColor"
          track-color="grey-3"
          rounded
          class="progress-bar"
        >
          <div class="absolute-full flex flex-center">
            <span class="text-caption text-weight-bold" :class="textColorClass">
              {{ percentage }}%
            </span>
          </div>
        </q-linear-progress>
      </div>

      <!-- Status Indicators -->
      <div class="col-auto status-chips">
        <q-chip
          v-if="teacher.completed > 0"
          dense
          size="sm"
          color="green-2"
          text-color="green-9"
          icon="check_circle"
        >
          {{ teacher.completed }}
        </q-chip>
        <q-chip
          v-if="teacher.partial > 0"
          dense
          size="sm"
          color="amber-2"
          text-color="amber-9"
          icon="timelapse"
        >
          {{ teacher.partial }}
        </q-chip>
        <q-chip
          v-if="teacher.empty > 0"
          dense
          size="sm"
          color="red-1"
          text-color="red-8"
          icon="radio_button_unchecked"
        >
          {{ teacher.empty }}
        </q-chip>
      </div>

      <!-- Action -->
      <div class="col-auto">
        <q-btn
          flat
          dense
          round
          icon="visibility"
          color="primary"
          @click="$emit('view', teacher)"
        >
          <q-tooltip>View Details</q-tooltip>
        </q-btn>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  teacher: {
    type: Object,
    required: true
  }
})

defineEmits(['view'])

const totalCount = computed(() => props.teacher.total || 0)
const completedCount = computed(() => props.teacher.completed || 0)
const percentage = computed(() => props.teacher.percentage || 0)

const teacherInitials = computed(() => {
  const name = props.teacher.teacher_name || ''
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const progressColor = computed(() => {
  const p = percentage.value
  if (p >= 80) return 'green'
  if (p >= 50) return 'amber'
  if (p >= 20) return 'orange'
  return 'red'
})

const textColorClass = computed(() => {
  const p = percentage.value
  if (p >= 50) return 'text-white'
  return 'text-grey-8'
})
</script>

<style scoped>
.completion-progress-bar {
  padding: 12px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  transition: all 0.2s ease;
}

.completion-progress-bar:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.progress-bar {
  position: relative;
}

.status-chips {
  display: flex;
  gap: 4px;
}

@media (max-width: 600px) {
  .status-chips {
    display: none;
  }
}
</style>
