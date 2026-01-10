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

    <!-- Shared Filters -->
    <WeeklyPlansFilterBar />

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
        <q-tab name="classroom" icon="meeting_room" label="3- By Classroom" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="tab" animated>
        <!-- TAB 1: GENERATE & SYNC -->
        <q-tab-panel name="sync" class="q-pa-md">
          <WeeklyPlanSyncDashboard 
            v-if="store.selectedCopyId && store.selectedAcademicYearId"
            :copy-id="store.selectedCopyId" 
            :week-number="store.weekNumber"
            :academic-year-id="store.selectedAcademicYearId"
            :semester-number="store.semesterNumber"
          />
          <q-card v-else flat bordered class="text-center q-pa-xl">
            <q-icon name="info" size="64px" color="grey-5" />
            <p class="text-h6 text-grey-7 q-mt-md">Please configure Active Schedule Copy</p>
            <p class="text-grey-6">Select a schedule copy from the filter bar above.</p>
          </q-card>
        </q-tab-panel>

        <!-- TAB 2: MONITOR PROGRESS -->
        <q-tab-panel name="monitor" class="q-pa-none">
          <WeeklyPlanStats 
            ref="statsRef"
          />
        </q-tab-panel>

        <!-- TAB 3: BY CLASSROOM -->
        <q-tab-panel name="classroom" class="q-pa-none">
          <WeeklyPlanClassroomView 
          />
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import WeeklyPlanMenu from '@/Pages/my_table_mnger/weekly_system/WeeklyPlanMenu.vue'
import WeeklyPlanSyncDashboard from '@/Pages/my_table_mnger/weekly_system/components/weekly-plans/WeeklyPlanSyncDashboard.vue'
import WeeklyPlanStats from '@/Pages/my_table_mnger/weekly_system/admin/WeeklyPlanStats.vue'
import WeeklyPlanClassroomView from '@/Pages/my_table_mnger/weekly_system/admin/WeeklyPlanClassroomView.vue'
import WeeklyPlansFilterBar from './components/WeeklyPlansFilterBar.vue'
import { useWeeklyPlansStore } from '@/Stores/useWeeklyPlansStore'

// Tab state
const tab = ref('sync')
const store = useWeeklyPlansStore()
const statsRef = ref(null)

const refreshStats = () => {
    if (statsRef.value) {
        statsRef.value.refresh()
    }
}
</script>

<style scoped>
h4 {
  font-size: 1.5rem;
}
.q-tab-panel {
    min-height: 400px;
}
</style>

