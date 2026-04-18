<template>
  <div class="schedule-viewer">
    <AdminTimingBar />

    <div class="view-container">
      <Transition name="view" mode="out-in">
        <CardView v-if="viewMode === 'card'" :key="'card'" />
        <TableView v-else-if="viewMode === 'table'" :key="'table'" />
        <TableViewV2 v-else-if="viewMode === 'tablev2'" :key="'tablev2'" />
        <TableViewV3 v-else-if="viewMode === 'tablev3'" :key="'tablev3'" />
        <TimeLineView v-else-if="viewMode === 'timeline'" :key="'timeline'" />
        <ListView v-else-if="viewMode === 'list'" :key="'list'" />
        <MasterTimetableView v-else-if="viewMode === 'master'" :key="'master'" />
        <div v-else class="unknown-view">
          <p>Unknown view mode: {{ viewMode }}</p>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { computed, provide, inject } from 'vue';
import { useAppStore } from './composables/useAppStore';
import { useTimingResolver } from './composables/useTimingResolver';
import AdminTimingBar from './components/AdminTimingBar.vue';
import CardView from './components/views/CardView.vue';
import TableView from './components/views/TableView.vue';
import TableViewV2 from './components/views/TableViewV2.vue';
import TableViewV3 from './components/views/TableViewV3.vue';
import TimeLineView from './components/views/TimeLineView.vue';
import ListView from './components/views/ListView.vue';
import MasterTimetableView from './components/views/MasterTimetableView.vue';

const store = useAppStore();

// Resolve timing slots for this stage/day
const { resolvedTimeSlots } = useTimingResolver(
  store.timingsConfig,
  store.selectedStage,
  store.selectedDay,
  // fallback from schedule_timing.json
  [
    { id: 1, title: 'Period 1', type: 'lesson', start: '09:00', end: '09:30' },
    { id: 2, title: 'Period 2', type: 'lesson', start: '09:30', end: '10:00' },
    { id: 'b1', title: 'First Break', type: 'break', start: '10:00', end: '10:30' },
    { id: 3, title: 'Period 3', type: 'lesson', start: '10:30', end: '11:00' },
    { id: 4, title: 'Period 4', type: 'lesson', start: '11:00', end: '11:30' },
    { id: 'b2', title: 'Second Break', type: 'break', start: '11:30', end: '12:00' },
    { id: 5, title: 'Period 5', type: 'lesson', start: '12:00', end: '12:25' },
    { id: 6, title: 'Period 6', type: 'lesson', start: '12:25', end: '12:50' }
  ]
);

// Debug: Log resolved slots
console.log('Resolved time slots:', resolvedTimeSlots.value);

const viewMode = computed(() => store.currentViewMode.value);

// Provide resolved time slots to child components
provide('resolvedTimeSlots', resolvedTimeSlots);
</script>

<style scoped>
.schedule-viewer {
  padding: 1rem;
  max-width: 1200px;
  margin: 0 auto;
}

.view-container {
  margin-top: 1rem;
}

.unknown-view {
  text-align: center;
  padding: 3rem 1rem;
  color: #64748b;
}

.view-enter-active,
.view-leave-active {
  transition: opacity 0.2s ease;
}

.view-enter-from,
.view-leave-to {
  opacity: 0;
}

@media (max-width: 640px) {
  .schedule-viewer {
    padding: 0.5rem;
  }
}
</style>
