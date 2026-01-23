<script setup>
import { computed } from 'vue'
import TimetableCell from './TimetableCell.vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const props = defineProps({
  schedules: { type: Array, default: () => [] },
  periodTimes: { type: Object, default: () => ({}) },
  teacherConflicts: { type: Object, default: () => ({}) },
  visibleDays: { type: Array, default: () => [1, 2, 3, 4, 5] }, // Default Sun-Thu
  visiblePeriods: { type: Array, default: () => [1, 2, 3, 4, 5, 6, 7, 8] },
  filterSubjectIds: { type: Array, default: () => [] }, // Empty means show all
  filterTeacherIds: { type: Array, default: () => [] }, // Empty means show all
  filterClassroomIds: { type: Array, default: () => [] },
  readonly: { type: Boolean, default: false },
  showClassroom: { type: Boolean, default: false },
  hideTeacher: { type: Boolean, default: false },
  showFullName: { type: Boolean, default: false },
  rewardSystemDisabled: { type: Boolean, default: false }
})

const emit = defineEmits(['cell-click', 'edit', 'clear', 'open-reward'])

// Using translation for day names (excluding Friday=6 and Saturday=7)
const allDays = [
  { value: 1, label: t('weeklyPlans.fullDays.1'), short: t('weeklyPlans.shortDays.1') },
  { value: 2, label: t('weeklyPlans.fullDays.2'), short: t('weeklyPlans.shortDays.2') },
  { value: 3, label: t('weeklyPlans.fullDays.3'), short: t('weeklyPlans.shortDays.3') },
  { value: 4, label: t('weeklyPlans.fullDays.4'), short: t('weeklyPlans.shortDays.4') },
  { value: 5, label: t('weeklyPlans.fullDays.5'), short: t('weeklyPlans.shortDays.5') },
  { value: 6, label: t('weeklyPlans.fullDays.6'), short: t('weeklyPlans.shortDays.6') },
  { value: 7, label: t('weeklyPlans.fullDays.7'), short: t('weeklyPlans.shortDays.7') }
]

// Filter days based onvisibleDays prop
const filteredDays = computed(() => {
    return allDays.filter(day => props.visibleDays.includes(day.value))
})

const periods = computed(() => props.visiblePeriods)

const scheduleMap = computed(() => {
  const map = {}
  props.schedules.forEach(schedule => {
    const key = `${schedule.day}-${schedule.period_number}` || `${schedule.day_number}-${schedule.period_number}`
    // Backwards compatibility with either day/day_number and period/period_number
    // Ideally backend sends day_number, period_number.
    // Let's ensure we use what's available.
    // In TimetableEditor fetchSchedules: result is from /admin/schedules which returns ScheduleResource?
    // Let's check keys.
    const day = schedule.day || schedule.day_number
    const period = schedule.period || schedule.period_number
    if (day && period) {
        map[`${day}-${period}`] = schedule
    }
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
  // If readonly, we might want to prevent emission? 
  // But parent can handle it.
  const schedule = getSchedule(day, period)
  emit('cell-click', { day, period, schedule })
}

const handleEdit = (schedule) => {
  emit('edit', schedule)
}

const handleClear = (schedule) => {
  emit('clear', schedule)
}

const handleOpenReward = (data) => {
  emit('open-reward', data)
}

const getConflictInfo = (schedule) => {
  if (!schedule?.id) return null
  return props.teacherConflicts[schedule.id] || null
}

// Check if a schedule matches theactive content filters (Subject/Teacher)
const matchesFilters = (schedule) => {
    if (!props.filterSubjectIds?.length && !props.filterTeacherIds?.length) return true
    if (!schedule || !schedule.cst_id) return false // Empty slots don't match specific filters

    let matchesSubject = true
    if (props.filterSubjectIds?.length > 0) {
        matchesSubject = props.filterSubjectIds.includes(schedule.cst?.subject_id) || 
                         (schedule.co_subject_id && props.filterSubjectIds.includes(schedule.co_subject_id))
    }

    let matchesTeacher = true
    if (props.filterTeacherIds?.length > 0) {
        matchesTeacher = props.filterTeacherIds.includes(schedule.cst?.teacher_id) ||
                         (schedule.teacher_substitute_id && props.filterTeacherIds.includes(schedule.teacher_substitute_id)) ||
                         (schedule.co_teacher_id && props.filterTeacherIds.includes(schedule.co_teacher_id))
    }

    let matchesClassroom = true
    if (props.filterClassroomIds?.length > 0) {
        matchesClassroom = props.filterClassroomIds.includes(schedule.cst?.classroom_id)
    }

    return matchesSubject && matchesTeacher && matchesClassroom
}
</script>

<template>
  <div class="timetable-grid" :dir="$i18n.locale === 'ar' ? 'rtl' : 'ltr'">
    <!-- Header Row: Periods -->
    <div class="grid-row header-row">
      <div class="grid-cell day-header"></div>
      <div
        v-for="period in periods"
        :key="period"
        class="grid-cell period-header"
      >
        <div class="period-number">{{ period }}</div>
        <div class="period-time text-caption text-grey-6">
          {{ getPeriodTime(period) }}
        </div>
      </div>
    </div>

    <!-- Day Rows (excluding Friday=6 and Saturday=7) -->
    <div
      v-for="day in filteredDays"
      :key="day.value"
      class="grid-row"
    >
      <!-- Day Header -->
      <div class="grid-cell day-header">
        <span class="day-name">{{ day.label }}</span>
        <span class="day-short">{{ day.short }}</span>
      </div>

      <!-- Period Cells -->
      <div
        v-for="period in periods"
        :key="`${day.value}-${period}`"
        class="grid-cell schedule-cell"
        :class="{ 'dimmed-cell': !matchesFilters(getSchedule(day.value, period)) }"
      >
        <TimetableCell
          :schedule="getSchedule(day.value, period)"
          :conflict-info="getConflictInfo(getSchedule(day.value, period))"
          :readonly="readonly"
          :show-classroom="showClassroom"
          :hide-teacher="hideTeacher"
          :show-full-name="showFullName"
          :reward-system-disabled="rewardSystemDisabled"
          @click="handleCellClick(day.value, period)"
          @edit="handleEdit"
          @clear="handleClear"
          @open-reward="handleOpenReward"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.timetable-grid {
  display: flex;
  flex-direction: column;
  gap: 2px;
  background: #f5f5f5;
  border-radius: 8px;
  padding: 8px;
  overflow-x: auto;
  direction: ltr;
}

.timetable-grid[dir="rtl"] {
  direction: rtl;
}

.grid-row {
  display: grid;
  /* Changed to have periods as columns: 100px for day header + n periods */
  grid-template-columns: 100px repeat(v-bind('periods.length'), 1fr);
  gap: 2px;
  min-height: 80px;
}

.header-row .grid-cell {
  background: var(--q-primary);
  color: white;
  font-weight: 600;
}

.grid-cell {
  background: white;
  border-radius: 4px;
  min-width: 100px;
  min-height: 60px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2px;
  overflow: hidden;
  position: relative;
}

.period-header {
  background: #e3f2fd;
  font-weight: 600;
  min-width: 100px;
}

.period-number {
  font-size: 1.1rem;
  font-weight: bold;
  color: var(--q-primary);
}

.day-header {
  background: #f8f9fa;
  color: #333;
  font-size: 0.9rem;
  border-right: 2px solid #ddd;
}

[dir="rtl"] .day-header {
  border-right: none;
  border-left: 2px solid #ddd;
}

.day-name {
  display: block;
}

.day-short {
  display: none;
}

.schedule-cell {
  padding: 4px;
  position: relative;
}

/* Style for handling long text in cells */
.schedule-cell:hover::after {
  content: attr(title);
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  z-index: 100;
  white-space: nowrap;
  font-size: 12px;
  pointer-events: none;
  visibility: hidden;
  opacity: 0;
  transition: opacity 0.3s, visibility 0.3s;
}

.schedule-cell:hover .schedule-content {
  position: relative;
  z-index: 2;
}

.schedule-cell:hover::after {
  visibility: visible;
  opacity: 1;
}

.schedule-content {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  width: 100%;
  text-align: center;
  font-size: 12px;
}

@media (max-width: 768px) {
  .grid-row {
    grid-template-columns: 80px repeat(v-bind('periods.length'), 1fr);
  }

  .day-name {
    display: none;
  }

  .day-short {
    display: block;
  }
  
  .grid-cell {
    min-width: 80px;
    min-height: 60px;
  }
}

/* RTL specific styles */
[dir="rtl"] .grid-row {
  direction: rtl;
}

.dimmed-cell {
    opacity: 0.3;
    filter: grayscale(1);
    pointer-events: none; /* Optional: prevent interaction with filtered out cells */
}

/* Improve aesthetics */
.timetable-grid {
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e0e0e0;
}

.grid-row {
    gap: 4px; /* Increases spacing */
}

.header-row .grid-cell {
    background: #f8f9fa;
    color: #444;
    border-bottom: 2px solid #ddd;
    box-shadow: none;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.period-header {
    background: #f8f9fa; /* Consistent header bg */
    border-bottom: 2px solid #ddd;
}

.grid-cell {
    box-shadow: 0 1px 2px rgba(0,0,0,0.05); /* Subtle shadow for cells */
    border: 1px solid #f0f0f0;
}
</style>
