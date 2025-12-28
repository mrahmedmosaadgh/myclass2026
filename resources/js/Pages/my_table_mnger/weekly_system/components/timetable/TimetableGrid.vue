<template>
  <div class="timetable-grid">
    <!-- Header Row: Days -->
    <div class="grid-row header-row">
      <div class="grid-cell period-header"></div>
      <div
        v-for="day in days"
        :key="day.value"
        class="grid-cell day-header"
      >
        <span class="day-name">{{ day.label }}</span>
        <span class="day-short">{{ day.short }}</span>
      </div>
    </div>

    <!-- Period Rows -->
    <div
      v-for="period in periods"
      :key="period"
      class="grid-row"
    >
      <!-- Period Header -->
      <div class="grid-cell period-header">
        <div class="period-number">{{ period }}</div>
        <div class="period-time text-caption text-grey-6">
          {{ getPeriodTime(period) }}
        </div>
      </div>

      <!-- Day Cells -->
      <div
        v-for="day in days"
        :key="`${day.value}-${period}`"
        class="grid-cell schedule-cell"
      >
        <TimetableCell
          :schedule="getSchedule(day.value, period)"
          @click="handleCellClick(day.value, period)"
          @edit="handleEdit"
          @clear="handleClear"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import TimetableCell from './TimetableCell.vue'

const props = defineProps({
  schedules: { type: Array, default: () => [] },
  periodTimes: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['cell-click', 'edit', 'clear'])

const days = [
  { value: 1, label: 'Sunday', short: 'Sun' },
  { value: 2, label: 'Monday', short: 'Mon' },
  { value: 3, label: 'Tuesday', short: 'Tue' },
  { value: 4, label: 'Wednesday', short: 'Wed' },
  { value: 5, label: 'Thursday', short: 'Thu' }
]

const periods = [1, 2, 3, 4, 5, 6, 7, 8]

const scheduleMap = computed(() => {
  const map = {}
  props.schedules.forEach(schedule => {
    const key = `${schedule.day_number}-${schedule.period_number}`
    map[key] = schedule
  })
  return map
})

const getSchedule = (day, period) => {
  return scheduleMap.value[`${day}-${period}`] || null
}

const getPeriodTime = (period) => {
  return props.periodTimes[period] || ''
}

const handleCellClick = (day, period) => {
  const schedule = getSchedule(day, period)
  emit('cell-click', { day, period, schedule })
}

const handleEdit = (schedule) => {
  emit('edit', schedule)
}

const handleClear = (schedule) => {
  emit('clear', schedule)
}
</script>

<style scoped>
.timetable-grid {
  display: flex;
  flex-direction: column;
  gap: 2px;
  background: #f5f5f5;
  border-radius: 8px;
  padding: 8px;
  overflow-x: auto;
}

.grid-row {
  display: grid;
  grid-template-columns: 80px repeat(5, 1fr);
  gap: 2px;
}

.header-row .grid-cell {
  background: var(--q-primary);
  color: white;
  font-weight: 600;
}

.grid-cell {
  background: white;
  border-radius: 4px;
  min-height: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 4px;
}

.period-header {
  background: #f0f0f0;
  font-weight: 600;
}

.period-number {
  font-size: 1.1rem;
  font-weight: bold;
  color: var(--q-primary);
}

.day-header {
  padding: 12px 8px;
}

.day-name {
  display: block;
}

.day-short {
  display: none;
}

.schedule-cell {
  padding: 0;
}

@media (max-width: 768px) {
  .grid-row {
    grid-template-columns: 60px repeat(5, 1fr);
  }

  .day-name {
    display: none;
  }

  .day-short {
    display: block;
  }
}
</style>
