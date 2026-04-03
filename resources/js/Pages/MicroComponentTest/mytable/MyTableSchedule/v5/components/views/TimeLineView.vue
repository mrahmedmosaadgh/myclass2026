<template>
  <div class="timeline-view">
    <div class="timeline-controls">
      <div class="control-group">
        <label class="control-label">Select Stages:</label>
        <div class="stage-checkboxes">
          <label v-for="stage in allStages" :key="stage.id" class="stage-checkbox">
            <input 
              type="checkbox" 
              :value="stage.id" 
              v-model="selectedStages"
              class="checkbox-input"
            >
            <span class="checkbox-label">{{ stage.title }}</span>
          </label>
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label">Day:</label>
        <select v-model="selectedDay" class="day-select">
          <option value="today">Today</option>
          <option value="d1">Monday</option>
          <option value="d2">Tuesday</option>
          <option value="d3">Wednesday</option>
          <option value="d4">Thursday</option>
          <option value="d5">Friday</option>
          <option value="d6">Saturday</option>
        </select>
      </div>
    </div>

    <div class="timeline-container">
      <!-- Header Row -->
      <div class="timeline-header">
        <div class="time-column-header">Time</div>
        <div v-for="stage in displayStages" :key="stage.id" class="stage-header">
          <div class="stage-title">{{ stage.title }}</div>
          <div class="stage-subtitle">{{ getDayLabel(selectedDay) }}</div>
        </div>
      </div>

      <!-- Timeline Grid -->
      <div class="timeline-grid" ref="timelineGrid">
        <!-- Time Grid Background -->
        <div class="time-grid-background">
          <!-- Hour markers -->
          <div v-for="hour in hours" :key="hour" class="hour-marker" :style="{ top: `${getTimePosition(hour * 60)}px` }">
            <div class="hour-line"></div>
            <div class="hour-label">{{ String(hour).padStart(2, '0') }}:00</div>
          </div>
        </div>

        <!-- Horizontal Grid Lines (span across all columns) -->
        <div class="horizontal-grid">
          <div v-for="hour in hours" :key="`grid-${hour}`" class="horizontal-line" :style="{ top: `${getTimePosition(hour * 60)}px` }"></div>
        </div>

        <!-- Stage Columns -->
        <div v-for="stage in displayStages" :key="stage.id" class="stage-column">
          <div class="stage-content">
            <!-- Period Blocks positioned by actual time -->
            <div 
              v-for="timeSlot in timeSlots" 
              :key="`${stage.id}-${timeSlot.id}`"
              class="period-block"
              :class="{
                'current-period': isCurrentPeriod(timeSlot, stage.id, selectedDay),
                'lesson-period': timeSlot.type === 'lesson',
                'break-period': timeSlot.type === 'break',
                'activity-period': timeSlot.type === 'activity',
                'has-content': hasPeriodContent(timeSlot, stage.id, selectedDay)
              }"
              :style="{
                top: `${getTimePosition(timeSlot.startMin)}px`,
                height: `${getTimeHeight(timeSlot.startMin, timeSlot.endMin)}px`
              }"
            >
              <div class="period-content">
                <div class="period-title">{{ getPeriodTitle(timeSlot, stage.id, selectedDay) }}</div>
                <div class="period-subject">{{ getPeriodSubject(timeSlot, stage.id, selectedDay) }}</div>
                <div class="period-teacher">{{ getPeriodTeacher(timeSlot, stage.id, selectedDay) }}</div>
              </div>
              
              <!-- Progress indicator for current period -->
              <div v-if="isCurrentPeriod(timeSlot, stage.id, selectedDay)" class="progress-indicator">
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
      <div class="time-dot"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAppStore } from '../../composables/useAppStore.js';
import { useTimingResolver } from '../../composables/useTimingResolver.js';

const props = defineProps({
  showProgress: { type: Boolean, default: true },
  showCurrentTime: { type: Boolean, default: true },
  autoRefresh: { type: Boolean, default: true }
});

const store = useAppStore();

// State
const selectedStages = ref(['prim', 'middle', 'sec']); // Default to all stages selected
const selectedDay = ref('today');
const currentTimeDisplay = ref('');
const timelineGrid = ref(null);
let timeUpdateInterval = null;

// Stage definitions
const allStages = [
  { id: 'prim', title: 'Primary' },
  { id: 'middle', title: 'Middle' },
  { id: 'sec', title: 'Secondary' }
];

// Computed properties
const displayStages = computed(() => {
  return allStages.filter(stage => selectedStages.value.includes(stage.id));
});

// Create timing resolver for the timeline
const { resolvedTimeSlots } = useTimingResolver(
  computed(() => store.timingsConfig),
  computed(() => 'prim'), // Use primary timing as fallback
  computed(() => selectedDay.value === 'today' ? getTodayDayId() : selectedDay.value),
  []
);

const timeSlots = computed(() => {
  const slots = resolvedTimeSlots.value;
  
  // If no slots or config not loaded, provide fallback
  if (!slots || slots.length === 0) {
    return [
      { id: 1, title: 'Period 1', type: 'lesson', start: '09:00', end: '09:30', startMin: 540, endMin: 570 },
      { id: 2, title: 'Period 2', type: 'lesson', start: '09:30', end: '10:00', startMin: 570, endMin: 600 },
      { id: 'b1', title: 'First Break', type: 'break', start: '10:00', end: '10:30', startMin: 600, endMin: 630 },
      { id: 3, title: 'Period 3', type: 'lesson', start: '10:30', end: '11:00', startMin: 630, endMin: 660 },
      { id: 4, title: 'Period 4', type: 'lesson', start: '11:00', end: '11:30', startMin: 660, endMin: 690 },
      { id: 'b2', title: 'Second Break', type: 'break', start: '11:30', end: '12:00', startMin: 690, endMin: 720 },
      { id: 5, title: 'Period 5', type: 'lesson', start: '12:00', end: '12:25', startMin: 720, endMin: 745 },
      { id: 6, title: 'Period 6', type: 'lesson', start: '12:25', end: '12:50', startMin: 745, endMin: 770 }
    ];
  }
  
  return slots;
});

// Generate hours for the timeline (6:00 to 22:00)
const hours = computed(() => {
  const hourList = [];
  for (let i = 6; i <= 22; i++) {
    hourList.push(i);
  }
  return hourList;
});

const showCurrentTimeIndicator = computed(() => {
  return props.showCurrentTime && selectedDay.value === 'today';
});

// Time positioning functions
const getTimePosition = (minutes) => {
  // Convert minutes to pixels (2px per minute, starting from 6:00)
  const startOfDay = 6 * 60; // 6:00 AM
  return (minutes - startOfDay) * 2; // 2px per minute for better visibility
};

const getTimeHeight = (startMin, endMin) => {
  return (endMin - startMin) * 2; // 2px per minute for better visibility
};

// Helper functions
const getTodayDayId = () => {
  const dayIndex = new Date().getDay();
  const mapping = [null, 'd1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd1'];
  return mapping[dayIndex] || 'd1';
};

const getDayLabel = (dayId) => {
  if (dayId === 'today') {
    dayId = getTodayDayId();
  }
  const dayLabels = {
    d1: 'Monday',
    d2: 'Tuesday',
    d3: 'Wednesday',
    d4: 'Thursday',
    d5: 'Friday',
    d6: 'Saturday'
  };
  return dayLabels[dayId] || 'Unknown';
};

const getDayIndexFromId = (dayId) => {
  const mapping = { d1: 1, d2: 2, d3: 3, d4: 4, d5: 5, d6: 6 };
  return mapping[dayId] || 1;
};

const getActualDayId = () => {
  return selectedDay.value === 'today' ? getTodayDayId() : selectedDay.value;
};

const getPeriodTitle = (timeSlot, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const scheduleData = store.scheduleData.value;
  
  if (!Array.isArray(scheduleData)) {
    return `${timeSlot.title} from ${timeSlot.start} to ${timeSlot.end}`;
  }
  
  const daySchedule = scheduleData.find(item => item.day === actualDayId || item.dayIndex === getDayIndexFromId(actualDayId));
  
  let subjectInfo = '';
  if (daySchedule && daySchedule.classes) {
    const period = daySchedule.classes.find(p => p.p === timeSlot.id);
    if (period && period.sub) {
      subjectInfo = ` [${period.sub}]`;
    }
  }
  
  return `${timeSlot.title} from ${timeSlot.start} to ${timeSlot.end}${subjectInfo}`;
};

const getPeriodSubject = (timeSlot, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const scheduleData = store.scheduleData.value;
  
  // Ensure scheduleData is an array
  if (!Array.isArray(scheduleData)) {
    return '';
  }
  
  const daySchedule = scheduleData.find(item => item.day === actualDayId || item.dayIndex === getDayIndexFromId(actualDayId));
  
  if (daySchedule && daySchedule.classes) {
    const period = daySchedule.classes.find(p => p.p === timeSlot.id);
    if (period && period.sub) {
      // Add stage prefix to differentiate between stages
      return `${stageId.toUpperCase()}: ${period.sub}`;
    }
  }
  
  return '';
};

const getPeriodTeacher = (timeSlot, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const schoolTimetable = store.schoolTimetable.value;
  const stageData = schoolTimetable.stages?.[stageId];
  
  // Handle the nested structure with teachers and assignments
  if (stageData && stageData.teachers && Array.isArray(stageData.teachers)) {
    for (const teacher of stageData.teachers) {
      const assignments = teacher.assignments?.[actualDayId];
      if (assignments && assignments[timeSlot.id]) {
        return teacher.name;
      }
    }
  }
  
  return '';
};

const hasPeriodContent = (timeSlot, stageId, dayId) => {
  const title = getPeriodTitle(timeSlot, stageId, dayId);
  const subject = getPeriodSubject(timeSlot, stageId, dayId);
  const teacher = getPeriodTeacher(timeSlot, stageId, dayId);
  return title || subject || teacher;
};

const isCurrentPeriod = (timeSlot, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const todayDayId = getTodayDayId();
  const now = new Date();
  const currentMinutes = now.getHours() * 60 + now.getMinutes();
  
  return actualDayId === todayDayId && 
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
  
  return {
    top: `${60 + getTimePosition(currentMinutes)}px` // 60px for header
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
  position: relative;
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

.view-mode-select,
.stage-select,
.day-select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  font-size: 0.875rem;
  min-width: 120px;
}

/* Stage Checkboxes */
.stage-checkboxes {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.stage-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  background: white;
  transition: all 0.2s ease;
}

.stage-checkbox:hover {
  border-color: #3b82f6;
  background: #f0f9ff;
}

.checkbox-input {
  width: 16px;
  height: 16px;
  accent-color: #3b82f6;
}

.checkbox-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  user-select: none;
}

/* Timeline Container */
.timeline-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: relative;
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

.time-column-header {
  width: 80px;
  padding: 1rem 0.5rem;
  font-weight: 600;
  color: #374151;
  border-right: 2px solid #e2e8f0;
  background: #f8fafc;
  text-align: center;
}

.stage-header {
  flex: 1;
  min-width: 200px;
  padding: 0.75rem 0.5rem;
  text-align: center;
  border-right: 1px solid #e2e8f0;
}

.stage-title {
  font-weight: 700;
  color: #1f2937;
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stage-subtitle {
  color: #6b7280;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

/* Timeline Grid */
.timeline-grid {
  position: relative;
  display: flex;
  min-height: 1920px; /* 16 hours * 120px per hour (2px per minute) */
  background: linear-gradient(to bottom, #ffffff, #f8fafc);
}

/* Horizontal Grid Lines */
.horizontal-grid {
  position: absolute;
  left: 80px; /* Start after time column */
  right: 0;
  top: 0;
  bottom: 0;
  pointer-events: none;
}

.horizontal-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 1px;
  background: #f1f5f9;
  z-index: 0;
}

/* Time Grid Background */
.time-grid-background {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 80px;
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
}

.hour-marker {
  position: absolute;
  left: 0;
  right: 0;
  height: 2px;
}

.hour-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 1px;
  background: #d1d5db;
  z-index: 0;
}

.hour-label {
  position: absolute;
  left: 4px;
  top: -8px;
  font-size: 0.625rem;
  color: #6b7280;
  font-weight: 600;
  background: #f8fafc;
  padding: 2px 4px;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
  z-index: 10;
  white-space: nowrap;
}

/* Stage Columns */
.stage-column {
  flex: 1;
  min-width: 200px;
  border-right: 1px solid #f1f5f9;
  position: relative;
}

.stage-column:last-child {
  border-right: none;
}

.stage-content {
  position: relative;
  height: 100%;
}

/* Period Blocks - Positioned by actual time */
.period-block {
  position: absolute;
  left: 8px;
  right: 8px;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s ease;
  cursor: pointer;
  z-index: 1;
  min-height: 20px; /* Minimum height for very short periods */
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.period-block.has-content {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.period-block.break-period {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.period-block.activity-period {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.period-block.current-period {
  box-shadow: 0 0 0 3px #ef4444, 0 0 20px rgba(239, 68, 68, 0.5);
  z-index: 5;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 3px #ef4444, 0 0 20px rgba(239, 68, 68, 0.5);
  }
  50% {
    box-shadow: 0 0 0 3px #ef4444, 0 0 30px rgba(239, 68, 68, 0.7);
  }
}

.period-block:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
  z-index: 3;
}

.period-content {
  padding: 6px;
  font-size: 0.7rem;
  line-height: 1.2;
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 100%;
  word-wrap: break-word;
  overflow: hidden;
}

.period-time {
  font-weight: 600;
  font-size: 0.625rem;
  opacity: 0.9;
  margin-bottom: 2px;
}

.period-title {
  font-weight: 600;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 0.65rem;
}

.period-subject {
  opacity: 0.9;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 0.6875rem;
}

.period-teacher {
  opacity: 0.8;
  font-size: 0.625rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
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
  left: -80px;
  top: -10px;
  background: #ef4444;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.625rem;
  font-weight: 600;
  white-space: nowrap;
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

.time-dot {
  position: absolute;
  left: -4px;
  top: -3px;
  width: 8px;
  height: 8px;
  background: #ef4444;
  border-radius: 50%;
  box-shadow: 0 0 6px rgba(239, 68, 68, 0.6);
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
  
  .time-column-header {
    background: #1e293b;
    color: #e2e8f0;
    border-right-color: #334155;
  }
  
  .stage-header {
    border-right-color: #334155;
  }
  
  .stage-title {
    color: #f1f5f9;
  }
  
  .stage-subtitle {
    color: #94a3b8;
  }
  
  .timeline-grid {
    background: linear-gradient(to bottom, #0f172a, #1e293b);
  }
  
  .time-grid-background {
    background: #1e293b;
    border-right-color: #334155;
  }
  
  .hour-line {
    background: #334155;
  }
  
  .hour-label {
    color: #94a3b8;
  }
  
  .stage-column {
    border-right-color: #334155;
  }
  
  .view-mode-select,
  .stage-select,
  .day-select {
    background: #1e293b;
    border-color: #475569;
    color: #e2e8f0;
  }
  
  .stage-checkbox {
    background: #1e293b;
    border-color: #475569;
  }
  
  .stage-checkbox:hover {
    border-color: #3b82f6;
    background: #1e3a8a;
  }
  
  .checkbox-label {
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
  
  .time-column-header {
    width: 60px;
    font-size: 0.75rem;
  }
  
  .time-grid-background {
    width: 60px;
  }
  
  .hour-label {
    font-size: 0.5rem;
    left: 2px;
  }
  
  .stage-header {
    min-width: 150px;
    padding: 0.5rem;
  }
  
  .stage-column {
    min-width: 150px;
  }
  
  .period-content {
    padding: 0.25rem;
    font-size: 0.625rem;
  }
  
  .period-time {
    font-size: 0.5rem;
  }
  
  .period-title {
    font-size: 0.5625rem;
  }
  
  .period-subject {
    font-size: 0.5rem;
  }
  
  .period-teacher {
    font-size: 0.4375rem;
  }
}
</style>
