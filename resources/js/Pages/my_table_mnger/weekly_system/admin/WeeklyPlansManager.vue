<template>
  <Head title="Weekly Plans Manager" />
  
  
  <div class="q-pa-md">
    <WeeklyPlanMenu />
    
    <!-- Page Header -->
    <div class="row items-center q-mb-lg">
      <div class="col">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="view_week" class="q-mr-sm" color="primary" />
          Weekly Plans Management
        </h4>
        <p class="text-grey-7 q-mb-none">
          Generate, monitor and print weekly plans for teachers
        </p>
      </div>
    </div>

    <!-- Tabs Container -->
    <q-card flat bordered class="overflow-hidden">
      <q-tabs
        v-model="tab"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
      >
        <q-tab name="sync" icon="sync" label="1- Generate & Sync" />
        <q-tab name="monitor" icon="analytics" label="2- Monitor Progress" />
        <q-tab name="print" icon="print" label="3- Print Weekly" />
        <q-tab name="classroom" icon="meeting_room" label="4- By Classroom" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="tab" animated>
        <!-- TAB 1: GENERATE & SYNC -->
        <q-tab-panel name="sync" class="q-pa-md">
          <WeeklyPlanSyncDashboard 
            v-if="selectedCopyId && selectedAcademicYearId"
            :copy-id="selectedCopyId" 
            :week-number="weekNumber"
            :academic-year-id="selectedAcademicYearId"
            :semester-number="semesterNumber"
          />
          <q-card v-else flat bordered class="text-center q-pa-xl">
            <q-icon name="info" size="64px" color="grey-5" />
            <p class="text-h6 text-grey-7 q-mt-md">Please configure Active Schedule Copy</p>
            <p class="text-grey-6">Go to Timetable Editor to activate a schedule copy first.</p>
          </q-card>
        </q-tab-panel>

        <!-- TAB 2: MONITOR PROGRESS -->
        <q-tab-panel name="monitor" class="q-pa-none">
          <WeeklyPlanStats 
            ref="statsRef"
            :initial-copy-id="selectedCopyId" 
            :initial-week="weekNumber" 
          />
        </q-tab-panel>

        <!-- TAB 3: PRINT -->
        <q-tab-panel name="print" class="q-pa-none">
          <WeeklyPlanPrinter 
            :initial-copy-id="selectedCopyId" 
            :initial-week="weekNumber" 
          />
        </q-tab-panel>

        <!-- TAB 4: BY CLASSROOM -->
        <q-tab-panel name="classroom" class="q-pa-none">
          <WeeklyPlanClassroomView 
            :initial-copy-id="selectedCopyId" 
            :initial-week="weekNumber" 
          />
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import WeeklyPlanMenu from '@/Pages/my_table_mnger/weekly_system/WeeklyPlanMenu.vue'
import WeeklyPlanSyncDashboard from '@/Pages/my_table_mnger/weekly_system/components/weekly-plans/WeeklyPlanSyncDashboard.vue'
import WeeklyPlanStats from '@/Pages/my_table_mnger/weekly_system/admin/WeeklyPlanStats.vue'
import WeeklyPlanPrinter from '@/Pages/my_table_mnger/weekly_system/admin/WeeklyPlanPrinter.vue'
import WeeklyPlanClassroomView from '@/Pages/my_table_mnger/weekly_system/admin/WeeklyPlanClassroomView.vue'

// Tab state
const tab = ref('sync')

// Shared state (passed as initial props)
const selectedCopyId = ref(4) // TODO: Get from active schedule copy
const selectedAcademicYearId = ref(1) // TODO: Get from active academic year  
const semesterNumber = ref(1) // TODO: Get from current semester
const weekNumber = ref(1)
const statsRef = ref(null)

const refreshStats = () => {
    if (statsRef.value) {
        statsRef.value.refresh()
    }
}

onMounted(() => {
    // Basic initialization if needed
    const now = new Date()
    const startOfYear = new Date(now.getFullYear(), 0, 1)
    const currentWeek = Math.ceil(((now - startOfYear) / 86400000 + startOfYear.getDay() + 1) / 7)
    weekNumber.value = currentWeek > 18 ? 1 : currentWeek
})
</script>

<style scoped>
h4 {
  font-size: 1.5rem;
}
.q-tab-panel {
    min-height: 400px;
}
</style>

