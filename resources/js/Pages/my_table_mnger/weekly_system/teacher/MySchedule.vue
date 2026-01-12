<template>
  <Head :title="t('weeklySystem.mySchedule.title')" />
  <div class="q-pa-md">
    <WeeklyPlanMenu />
    <!-- Page Header -->
    <div class="row items-center q-mb-lg">
      <div class="col">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="calendar_month" class="q-mr-sm" color="primary" />
          {{ t('weeklySystem.mySchedule.title') }}
        </h4>
        <p class="text-grey-7 q-mb-none">
          {{ t('weeklySystem.mySchedule.subtitle') }}
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="row justify-center q-pa-xl">
      <q-spinner-dots size="50px" color="primary" />
    </div>

    <!-- Empty State -->
    <q-card v-else-if="!schedules.length" flat bordered class="text-center q-pa-xl">
      <q-icon name="event_busy" size="64px" color="grey-5" />
      <p class="text-h6 text-grey-7 q-mt-md">{{ t('weeklySystem.schoolBrowser.noDataTitle') }}</p>
      <p class="text-grey-6">{{ t('weeklySystem.mySchedule.emptyState') }}</p>
    </q-card>

    <!-- Teacher Timetable -->
    <div v-else class="timetable-container">
      <!-- Header Row -->
      <div class="grid-row header-row">
        <div class="grid-cell period-header"></div>
        <div v-for="day in days" :key="day.value" class="grid-cell day-header">
          {{ t(`weeklyPlans.shortDays.${day.value}`) }}
        </div>
      </div>

      <!-- Period Rows -->
      <div v-for="period in periods" :key="period" class="grid-row">
        <div class="grid-cell period-header">
          <div class="period-number">{{ period }}</div>
        </div>
        
        <div v-for="day in days" :key="`${day.value}-${period}`" class="grid-cell">
          <div
            v-if="getSchedule(day.value, period)"
            class="schedule-card"
            :style="getScheduleStyle(day.value, period)"
            @click="openWeeklyPlan(day.value, period)"
          >
            <div class="card-content">
              <div class="subject">{{ getSchedule(day.value, period).cst?.subject_name }}</div>
              <div class="classroom">
                <q-icon name="meeting_room" size="xs" />
                {{ getSchedule(day.value, period).cst?.classroom_name }}
              </div>
            </div>
          </div>
          <div v-else class="free-period text-caption text-grey-5">
            {{ t('weeklySystem.mySchedule.free') }}
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <q-card v-if="schedules.length" flat bordered class="q-mt-lg q-pa-md">
      <div class="row q-gutter-lg justify-center">
        <div class="text-center">
          <div class="text-h4 text-primary">{{ totalClasses }}</div>
          <div class="text-caption text-grey-7">{{ t('weeklySystem.mySchedule.totalClasses') }}</div>
        </div>
        <div class="text-center">
          <div class="text-h4 text-secondary">{{ uniqueClassrooms }}</div>
          <div class="text-caption text-grey-7">{{ t('weeklySystem.mySchedule.classrooms') }}</div>
        </div>
        <div class="text-center">
          <div class="text-h4 text-accent">{{ uniqueSubjects }}</div>
          <div class="text-caption text-grey-7">{{ t('weeklySystem.mySchedule.subjects') }}</div>
        </div>
      </div>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useQuasar } from 'quasar'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import WeeklyPlanMenu from '../WeeklyPlanMenu.vue'

const { t } = useI18n()
const $q = useQuasar()

// Data
const schedules = ref([])
const loading = ref(false)

// Days of the week
const days = computed(() => [
  { value: 1, label: t('weeklyPlans.fullDays.1') },
  { value: 2, label: t('weeklyPlans.fullDays.2') },
  { value: 3, label: t('weeklyPlans.fullDays.3') },
  { value: 4, label: t('weeklyPlans.fullDays.4') },
  { value: 5, label: t('weeklyPlans.fullDays.5') }
])

const periods = [1, 2, 3, 4, 5, 6, 7, 8]

// Computed
const scheduleMap = computed(() => {
  const map = {}
  schedules.value.forEach(schedule => {
    const key = `${schedule.day}-${schedule.period_number}`
    map[key] = schedule
  })
  return map
})

const totalClasses = computed(() => schedules.value.length)

const uniqueClassrooms = computed(() => {
  const classrooms = new Set(schedules.value.map(s => s.cst?.classroom_id))
  return classrooms.size
})

const uniqueSubjects = computed(() => {
  const subjects = new Set(schedules.value.map(s => s.cst?.subject_id))
  return subjects.size
})

// Methods
const getSchedule = (day, period) => {
  return scheduleMap.value[`${day}-${period}`]
}

const getScheduleStyle = (day, period) => {
  const schedule = getSchedule(day, period)
  if (!schedule?.cst) return {}
  return {
    backgroundColor: schedule.cst.c_bg || '#e0e0e0',
    color: schedule.cst.c_text || '#333'
  }
}

const fetchSchedule = async () => {
  loading.value = true
  try {
    // This endpoint should return schedules filtered by the logged-in teacher
    const response = await axios.get('/weekly-system/api/teacher/my-schedule')
    schedules.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching schedule:', error)
    $q.notify({ type: 'negative', message: t('weeklySystem.notifications.loadFailed') })
  } finally {
    loading.value = false
  }
}

const openWeeklyPlan = (day, period) => {
  const schedule = getSchedule(day, period)
  if (schedule) {
    router.visit('/my_table_mnger/weekly_system/teacher/my-weekly-plans', {
      data: { schedule_id: schedule.id }
    })
  }
}

// Lifecycle
onMounted(() => {
  fetchSchedule()
})
</script>

<style scoped>
h4 {
  font-size: 1.5rem;
}

.timetable-container {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 8px;
  overflow-x: auto;
}

.grid-row {
  display: grid;
  grid-template-columns: 60px repeat(5, 1fr);
  gap: 4px;
  margin-bottom: 4px;
}

.grid-cell {
  background: white;
  border-radius: 4px;
  min-height: 80px;
  padding: 8px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.header-row .grid-cell {
  background: var(--q-primary);
  color: white;
  font-weight: 600;
  min-height: 50px;
}

.period-header {
  background: #f0f0f0 !important;
  color: var(--q-primary) !important;
}

.period-number {
  font-size: 1.2rem;
  font-weight: bold;
}

.schedule-card {
  width: 100%;
  height: 100%;
  border-radius: 6px;
  padding: 8px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.schedule-card:hover {
  transform: scale(1.02);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.subject-name {
  font-weight: 600;
  font-size: 0.85rem;
}

.classroom-name {
  margin-top: 4px;
}

.free-period {
  font-style: italic;
}
</style>
