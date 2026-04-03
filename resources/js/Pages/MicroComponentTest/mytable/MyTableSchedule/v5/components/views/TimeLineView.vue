<template>
  <div class="timeline-view">
    <div class="timeline-controls">
      <div class="control-group">
        <label class="control-label">Stage:</label>
        <select v-model="selectedStage" class="stage-select">
          <option value="all">All Stages</option>
          <option value="prim">Primary</option>
          <option value="middle">Middle</option>
          <option value="sec">Secondary</option>
        </select>
      </div>
      
      <div class="control-group">
        <label class="control-label">Day:</label>
        <select v-model="selectedDay" class="day-select">
          <option value="today">Today</option>
          <option value="d1">Monday (D1)</option>
          <option value="d2">Tuesday (D2)</option>
          <option value="d3">Wednesday (D3)</option>
          <option value="d4">Thursday (D4)</option>
          <option value="d5">Friday (D5)</option>
          <option value="d6">Saturday (D6)</option>
        </select>
      </div>
    </div>

    <div class="timeline-container">
      <!-- Header Row -->
      <div class="timeline-header">
        <div class="time-label">Time</div>
        <div v-for="column in headerColumns" :key="column.id" class="day-stage-cell">
          <div class="column-title">{{ column.title }}</div>
          <div class="column-subtitle">{{ column.subtitle }}</div>
        </div>
      </div>

      <!-- Timeline Content -->
      <div class="timeline-content">
        <div v-for="timeSlot in timeSlots" :key="timeSlot.id" class="time-row">
          <!-- Time Label -->
          <div class="time-cell">
            <div class="time-range">{{ timeSlot.start }} - {{ timeSlot.end }}</div>
            <div class="time-duration">{{ formatDuration(timeSlot) }}</div>
          </div>

          <!-- Day/Stage Cells -->
          <div v-for="column in headerColumns" :key="column.id" class="timeline-cell">
            <div 
              class="period-block"
              :class="{
                'current-period': isCurrentPeriod(timeSlot, column),
                'lesson-period': timeSlot.type === 'lesson',
                'break-period': timeSlot.type === 'break',
                'activity-period': timeSlot.type === 'activity'
              }"
            >
              <div class="period-content">
                <div class="period-title">{{ getPeriodTitle(timeSlot, column) }}</div>
                <div class="period-subject">{{ getPeriodSubject(timeSlot, column) }}</div>
                <div class="period-teacher">{{ getPeriodTeacher(timeSlot, column) }}</div>
              </div>
              
              <!-- Progress indicator for current period -->
              <div v-if="isCurrentPeriod(timeSlot, column)" class="progress-indicator">
                <div class="progress-fill" :style="{ height: `${getPeriodProgress(timeSlot)}%` }"></div>
                <div class="progress-line" :style="{ bottom: `${getPeriodProgress(timeSlot)}%` }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Current Time Indicator -->
    <div v-if="showCurrentTimeIndicator" class="current-time-line" :style="getCurrentTimeStyle()">
      <div class="time-label">Now: {{ currentTimeDisplay }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAppStore } from '../../composables/useAppStore.js';
import { useTimingResolver } from '../../composables/useTimingResolver.js';

const props = defineProps({
  // Optional props for customization
  showProgress: { type: Boolean, default: true },
  showCurrentTime: { type: Boolean, default: true },
  autoRefresh: { type: Boolean, default: true }
});

const store = useAppStore();

// State
const selectedStage = ref('all');
const selectedDay = ref('today');
const currentTimeDisplay = ref('');
let timeUpdateInterval = null;

// Computed properties
const headerColumns = computed(() => {
  const columns = [];
  
  if (selectedStage.value === 'all') {
    columns.push(
      { id: 'prim-d1', title: 'Primary', subtitle: 'Monday', stage: 'prim', day: 'd1' },
      { id: 'prim-d2', title: 'Primary', subtitle: 'Tuesday', stage: 'prim', day: 'd2' },
      { id: 'prim-d3', title: 'Primary', subtitle: 'Wednesday', stage: 'prim', day: 'd3' },
      { id: 'prim-d4', title: 'Primary', subtitle: 'Thursday', stage: 'prim', day: 'd4' },
      { id: 'prim-d5', title: 'Primary', subtitle: 'Friday', stage: 'prim', day: 'd5' },
      { id: 'prim-d6', title: 'Primary', subtitle: 'Saturday', stage: 'prim', day: 'd6' },
      { id: 'middle-d1', title: 'Middle', subtitle: 'Monday', stage: 'middle', day: 'd1' },
      { id: 'middle-d2', title: 'Middle', subtitle: 'Tuesday', stage: 'middle', day: 'd2' },
      { id: 'middle-d3', title: 'Middle', subtitle: 'Wednesday', stage: 'middle', day: 'd3' },
      { id: 'middle-d4', title: 'Middle', subtitle: 'Thursday', stage: 'middle', day: 'd4' },
      { id: 'middle-d5', title: 'Middle', subtitle: 'Friday', stage: 'middle', day: 'd5' },
      { id: 'middle-d6', title: 'Middle', subtitle: 'Saturday', stage: 'middle', day: 'd6' },
      { id: 'sec-d1', title: 'Secondary', subtitle: 'Monday', stage: 'sec', day: 'd1' },
      { id: 'sec-d2', title: 'Secondary', subtitle: 'Tuesday', stage: 'sec', day: 'd2' },
      { id: 'sec-d3', title: 'Secondary', subtitle: 'Wednesday', stage: 'sec', day: 'd3' },
      { id: 'sec-d4', title: 'Secondary', subtitle: 'Thursday', stage: 'sec', day: 'd4' },
      { id: 'sec-d5', title: 'Secondary', subtitle: 'Friday', stage: 'sec', day: 'd5' },
      { id: 'sec-d6', title: 'Secondary', subtitle: 'Saturday', stage: 'sec', day: 'd6' }
    );
  } else {
    const stageLabels = {
      prim: 'Primary',
      middle: 'Middle',
      sec: 'Secondary'
    };
    
    const dayLabels = {
      d1: 'Monday',
      d2: 'Tuesday',
      d3: 'Wednesday',
      d4: 'Thursday',
      d5: 'Friday',
      d6: 'Saturday'
    };
    
    if (selectedDay.value === 'today') {
      // Show today's day for selected stage
      const todayDayId = getTodayDayId();
      columns.push({
        id: `${selectedStage.value}-${todayDayId}`,
        title: stageLabels[selectedStage.value],
        subtitle: dayLabels[todayDayId],
        stage: selectedStage.value,
        day: todayDayId
      });
    } else {
      // Show selected day for selected stage
      columns.push({
        id: `${selectedStage.value}-${selectedDay.value}`,
        title: stageLabels[selectedStage.value],
        subtitle: dayLabels[selectedDay.value],
        stage: selectedStage.value,
        day: selectedDay.value
      });
    }
  }
  
  return columns;
});

const { resolvedTimeSlots } = useTimingResolver(
  computed(() => store.timingsConfig),
  computed(() => selectedStage.value === 'all' ? 'prim' : selectedStage.value),
  computed(() => selectedDay.value === 'today' ? getTodayDayId() : selectedDay.value),
  []
);

const timeSlots = computed(() => resolvedTimeSlots.value);

const showCurrentTimeIndicator = computed(() => {
  return props.showCurrentTime && selectedDay.value === 'today';
});

// Helper functions
const getTodayDayId = () => {
  const dayIndex = new Date().getDay();
  const mapping = [null, 'd1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd1'];
  return mapping[dayIndex] || 'd1';
};

const formatDuration = (slot) => {
  const start = slot.startMin || 0;
  const end = slot.endMin || 0;
  const duration = end - start;
  const hours = Math.floor(duration / 60);
  const minutes = duration % 60;
  
  if (hours > 0) {
    return `${hours}h ${minutes}m`;
  }
  return `${minutes}m`;
};

const getPeriodTitle = (timeSlot, column) => {
  // Get the actual period title from schedule data
  const scheduleData = store.scheduleData.value;
  const daySchedule = scheduleData.find(item => item.day === column.day);
  
  if (daySchedule && daySchedule.schedule) {
    const period = daySchedule.schedule.find(p => p.period === timeSlot.id);
    if (period) {
      return period.title || timeSlot.title;
    }
  }
  
  return timeSlot.title;
};

const getPeriodSubject = (timeSlot, column) => {
  const scheduleData = store.scheduleData.value;
  const daySchedule = scheduleData.find(item => item.day === column.day);
  
  if (daySchedule && daySchedule.schedule) {
    const period = daySchedule.schedule.find(p => p.period === timeSlot.id);
    if (period && period.subject) {
      return period.subject;
    }
  }
  
  return '';
};

const getPeriodTeacher = (timeSlot, column) => {
  const schoolTimetable = store.schoolTimetable.value;
  const stageData = schoolTimetable.stages?.[column.stage];
  
  if (stageData) {
    // Find teacher for this period and day
    const dayData = stageData.find(item => item.day === column.day);
    if (dayData && dayData.schedule) {
      const period = dayData.schedule.find(p => p.period === timeSlot.id);
      if (period && period.teacher) {
        return period.teacher;
      }
    }
  }
  
  return '';
};

const isCurrentPeriod = (timeSlot, column) => {
  if (selectedDay.value !== 'today') return false;
  
  const now = new Date();
  const currentDay = getTodayDayId();
  const currentMinutes = now.getHours() * 60 + now.getMinutes();
  
  return column.day === currentDay && 
         currentMinutes >= timeSlot.startMin && 
         currentMinutes < timeSlot.endMin;
};

const getPeriodProgress = (timeSlot) => {
  const now = new Date();
  const currentMinutes = now.getHours() * 60 + now.getMinutes();
  const start = timeSlot.startMin || 0;
  const end = timeSlot.endMin || 0;
  
  if (currentMinutes < start) return 0;
  if (currentMinutes >= end) return 100;
  
  const elapsed = currentMinutes - start;
  const total = end - start;
  return Math.min(100, Math.max(0, (elapsed / total) * 100));
};

const getCurrentTimeStyle = () => {
  const now = new Date();
  const currentMinutes = now.getHours() * 60 + now.getMinutes();
  
  // Find the current time slot
  let position = 0;
  for (let i = 0; i < timeSlots.value.length; i++) {
    const slot = timeSlots.value[i];
    if (currentMinutes >= slot.startMin && currentMinutes < slot.endMin) {
      const start = slot.startMin || 0;
      const end = slot.endMin || 0;
      const elapsed = currentMinutes - start;
      const total = end - start;
      position = (i * 60) + (elapsed / total * 60); // 60px per row
      break;
    }
  }
  
  return {
    top: `${80 + position}px` // 80px for header
  };
};

const updateCurrentTime = () => {
  const now = new Date();
  currentTimeDisplay.value = now.toLocaleTimeString([], { 
    hour: '2-digit', 
    minute: '2-digit',
    second: '2-digit'
  });
};

// Lifecycle
onMounted(() => {
  updateCurrentTime();
  if (props.autoRefresh) {
    timeUpdateInterval = setInterval(updateCurrentTime, 1000);
  }
});

onUnmounted(() => {
  if (timeUpdateInterval) {
    clearInterval(timeUpdateInterval);
  }
});
</script>

<style scoped>
.timeline-view {
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  overflow: hidden;
}

/* Controls */
.timeline-controls {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.control-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.control-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stage-select,
.day-select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  font-size: 0.875rem;
  min-width: 120px;
}

/* Timeline Container */
.timeline-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Header */
.timeline-header {
  display: flex;
  background: #f1f5f9;
  border-bottom: 2px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 10;
}

.time-label {
  width: 120px;
  padding: 1rem;
  font-weight: 600;
  color: #374151;
  border-right: 2px solid #e2e8f0;
  background: #f8fafc;
}

.day-stage-cell {
  flex: 1;
  min-width: 150px;
  padding: 0.75rem 0.5rem;
  text-align: center;
  border-right: 1px solid #e2e8f0;
}

.column-title {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.875rem;
}

.column-subtitle {
  color: #6b7280;
  font-size: 0.75rem;
  margin-top: 0.125rem;
}

/* Content */
.timeline-content {
  position: relative;
}

.time-row {
  display: flex;
  border-bottom: 1px solid #f1f5f9;
  min-height: 60px;
}

.time-cell {
  width: 120px;
  padding: 0.75rem 0.5rem;
  border-right: 2px solid #e2e8f0;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.time-range {
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.time-duration {
  color: #6b7280;
  font-size: 0.75rem;
  margin-top: 0.125rem;
}

.timeline-cell {
  flex: 1;
  min-width: 150px;
  border-right: 1px solid #f1f5f9;
  position: relative;
}

/* Period Blocks */
.period-block {
  height: 100%;
  padding: 0.5rem;
  position: relative;
  overflow: hidden;
  transition: all 0.2s ease;
}

.period-block:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.period-block.lesson-period {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.period-block.break-period {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.period-block.activity-period {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.period-block.current-period {
  box-shadow: 0 0 0 2px #ef4444, 0 0 8px rgba(239, 68, 68, 0.3);
  z-index: 5;
}

.period-content {
  font-size: 0.75rem;
  line-height: 1.4;
}

.period-title {
  font-weight: 600;
  margin-bottom: 0.125rem;
}

.period-subject {
  opacity: 0.9;
  margin-bottom: 0.125rem;
}

.period-teacher {
  opacity: 0.7;
  font-size: 0.625rem;
}

/* Progress Indicator */
.progress-indicator {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: rgba(255, 255, 255, 0.3);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #ef4444;
  transition: height 1s linear;
}

.progress-line {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: #ef4444;
  box-shadow: 0 0 4px rgba(239, 68, 68, 0.8);
}

.progress-line::before {
  content: '';
  position: absolute;
  right: 0;
  top: -2px;
  width: 6px;
  height: 6px;
  background: #ef4444;
  border-radius: 50%;
  box-shadow: 0 0 3px rgba(239, 68, 68, 0.8);
}

/* Current Time Line */
.current-time-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 2px;
  background: #ef4444;
  z-index: 20;
  pointer-events: none;
}

.current-time-line .time-label {
  position: absolute;
  left: -120px;
  top: -8px;
  background: #ef4444;
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.625rem;
  font-weight: 600;
  white-space: nowrap;
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
  .timeline-view {
    background: #1e293b;
  }
  
  .timeline-container {
    background: #0f172a;
  }
  
  .timeline-header {
    background: #1e293b;
    border-bottom-color: #334155;
  }
  
  .time-label {
    background: #1e293b;
    color: #e2e8f0;
    border-right-color: #334155;
  }
  
  .day-stage-cell {
    border-right-color: #334155;
  }
  
  .column-title {
    color: #f1f5f9;
  }
  
  .column-subtitle {
    color: #94a3b8;
  }
  
  .time-row {
    border-bottom-color: #334155;
  }
  
  .time-cell {
    background: #1e293b;
    border-right-color: #334155;
  }
  
  .time-range {
    color: #e2e8f0;
  }
  
  .time-duration {
    color: #94a3b8;
  }
  
  .timeline-cell {
    border-right-color: #334155;
  }
  
  .stage-select,
  .day-select {
    background: #1e293b;
    border-color: #475569;
    color: #e2e8f0;
  }
  
  .control-label {
    color: #94a3b8;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .timeline-controls {
    flex-direction: column;
  }
  
  .timeline-container {
    overflow-x: auto;
  }
  
  .time-label {
    width: 80px;
    padding: 0.5rem;
    font-size: 0.75rem;
  }
  
  .time-cell {
    width: 80px;
    padding: 0.5rem;
  }
  
  .day-stage-cell {
    min-width: 120px;
    padding: 0.5rem;
  }
  
  .timeline-cell {
    min-width: 120px;
  }
  
  .period-content {
    font-size: 0.625rem;
  }
}
</style>
