<template>
  <div class="q-mb-md">
    <q-card flat bordered class="bg-white q-pa-sm">
      <div class="row q-col-gutter-md items-center justify-end">
        
        <!-- Week Selector (Only remaining filter) -->
        <div class="col-12 col-sm-6 col-md-4">
          <div class="text-caption text-grey-8 q-mb-xs font-medium">Week Selection</div>
          <WeekSelector
            :model-value="store.weekNumber"
            :max-weeks="store.maxWeeks"
            :current-week="store.currentWeek"
            @update:model-value="store.setWeekNumber"
          />
        </div>

      </div>
    </q-card>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useWeeklyPlansStore } from '@/Stores/useWeeklyPlansStore'
import WeekSelector from '@/Pages/my_table_mnger/weekly_system/components/weekly-plans/WeekSelector.vue'

const store = useWeeklyPlansStore()

onMounted(() => {
    store.calculateCurrentWeek()
    // No need to fetch copies, MainSchoolData handles school/copy context
})
</script>

<style scoped>
.font-medium {
  font-weight: 500;
}
</style>
