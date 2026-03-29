<template>
  <table class="schedule-table">
    <thead>
      <tr>
        <th>Day</th>
        <th 
          v-for="(slot, i) in timeSlots" 
          :key="slot.id || i"
          :class="{
            'is-break-header': slot.type === 'break',
            'is-activity-header': slot.type === 'activity'
          }"
        >
          <span class="period-num text-xs">{{ slot.title || `P${slot.id || i + 1}` }}</span>
          <span class="period-time">({{ slot.start }} - {{ slot.end }})</span>
        </th>
      </tr>
    </thead>
    <tbody>
      <ScheduleRow 
        v-for="dayRow in filteredSchedule" 
        :key="dayRow.dayIndex"
        :day-row="dayRow"
        :time-slots="timeSlots"
        :is-current-day="dayRow.dayIndex === currentDayIndex"
        :current-total-secs="currentTotalSecs"
        @play-alert="$emit('play-alert')"
        @notify="(title, body) => $emit('notify', title, body)"
        @active-period-update="handleActivePeriodUpdate"
      />
    </tbody>
  </table>
</template>

<script setup>
import { computed } from 'vue';
import ScheduleRow from './ScheduleRow.vue';

const props = defineProps({
  scheduleData: { type: Array, required: true },
  timeSlots: { type: Array, required: true },
  isShowingAllDays: { type: Boolean, default: true },
  currentDayIndex: { type: Number, required: true },
  currentTotalSecs: { type: Number, required: true }
});

const emit = defineEmits(['play-alert', 'notify', 'active-period-update']);

const filteredSchedule = computed(() => {
  if (props.isShowingAllDays) return props.scheduleData;
  
  // Find current day, fallback to 0 (Sunday) if weekend
  let activeDay = props.currentDayIndex;
  // Based on strict 0-4 matching in original, can be customized later
  if (activeDay > 4) activeDay = 0; 

  return props.scheduleData.filter(day => day.dayIndex === activeDay);
});

const handleActivePeriodUpdate = (info) => {
  emit('active-period-update', info);
};
</script>

<style scoped>
.schedule-table {
  width: 100%;
  max-width: 1000px;
  border-collapse: collapse;
  background: white;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  border: 2px solid #222;
  transition: all 0.3s ease;
}

th {
  background-color: #4a4a4a;
  color: white;
  font-size: 0.9rem;
  line-height: 1.4;
  border: 1px solid #222;
  text-align: center;
  padding: 12px 5px;
  width: 11%;
}

.is-break-header {
  background-color: #3b82f6; /* Blue for breaks */
}

.is-activity-header {
  background-color: #f59e0b; /* Orange for activities */
}

.period-num { font-size: 1.1rem; font-weight: bold; display: block; }
.period-time { font-size: 0.75rem; color: #ccc; }
</style>
