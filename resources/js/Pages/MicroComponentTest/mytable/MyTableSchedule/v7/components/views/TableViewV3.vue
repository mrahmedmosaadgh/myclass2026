<template>
  <div class="table-view-v3">
    <div class="table-container">
      <table class="schedule-table-v3">
        <thead>
          <tr>
            <th class="header-period" :class="{ 'fixed-col': visibleDays.length > 1 }">Period</th>
            <th
              v-for="day in visibleDays"
              :key="day.dayIndex"
              class="header-day"
              :class="{ 'current-day': isCurrentDay(day.dayIndex) }"
            >
              <div class="day-header">
                <span class="day-title">{{ day.day }}</span>
                <span v-if="isCurrentDay(day.dayIndex)" class="current-badge">Today</span>
              </div>
            </th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="slot in timeSlots"
            :key="slot.id"
            class="period-row"
            :class="{ 'break-row': slot.type === 'break', 'current-period-row': isCurrentPeriod(slot) }"
          >
            <td class="period-cell" :class="{ 'fixed-col': visibleDays.length > 1, 'break-cell': slot.type === 'break', 'current-period-cell': isCurrentPeriod(slot) }">
              <div class="period-info">
                <span class="period-title">{{ slot.title }}</span>
                <span class="time-range">{{ slot.start }} - {{ slot.end }}</span>
                <span v-if="isCurrentPeriod(slot)" class="current-indicator">● NOW</span>
              </div>
            </td>

            <td
              v-for="day in visibleDays"
              :key="`${slot.id}-${day.dayIndex}`"
              class="subject-cell"
              :class="getSubjectCellClass(slot.id, day, slot)"
              :style="getSubjectStyle(slot.id, day, slot)"
              @click="handleCellClick(slot.id, day, slot)"
            >
              <div v-if="getSubject(slot.id, day, slot)" class="subject-content clickable">
                <span class="subject-name">{{ getSubject(slot.id, day, slot) }}</span>
                <span v-if="hasNafs(slot.id, day, slot)" class="nafs-indicator">N</span>
                <span v-if="isPeriodDone(slot.id, day, slot)" class="done-indicator">✓</span>
              </div>
              <span v-else class="empty-cell">—</span>

              <div v-if="isCurrentPeriod(slot) && isCurrentDay(day.dayIndex)" class="cell-progress">
                <div class="progress-fill" :style="{ width: `${periodProgress}%` }"></div>
                <div class="progress-line" :style="{ left: `${periodProgress}%` }"></div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <WeeklyPlanDetailDialog
    v-if="showWeeklyDialog"
    :open="showWeeklyDialog"
    :week-key="dialogWeekKey"
    :class-name="dialogClassName"
    :day-id="dialogDayId"
    :period-id="dialogPeriodId"
    :day-name="dialogDayName"
    :period-title="dialogPeriodTitle"
    @close="showWeeklyDialog = false"
  />
</template>

<script setup>
import { computed, inject, onMounted, onUnmounted, ref } from 'vue';
import { useAppStore } from '../../composables/useAppStore';
import WeeklyPlanDetailDialog from './WeeklyPlanDetailDialog.vue';

const store = useAppStore();
const resolvedTimeSlots = inject('resolvedTimeSlots');

const periodProgress = ref(0);
const alertInterval = ref(null);

const showWeeklyDialog = ref(false);
const dialogWeekKey = ref('');
const dialogClassName = ref('');
const dialogDayId = ref('');
const dialogPeriodId = ref('');
const dialogDayName = ref('');
const dialogPeriodTitle = ref('');

const colorSchemes = {
  default: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16', '#06b6d4', '#a855f7', '#f43f5e', '#22c55e', '#eab308']
};

const currentColorScheme = computed(() => {
  return colorSchemes.default;
});

const subjectColorMap = ref(new Map());

const getClassroomColor = (subject) => {
  if (!subject) return 'transparent';

  if (subjectColorMap.value.has(subject)) {
    return subjectColorMap.value.get(subject);
  }

  const colors = currentColorScheme.value;
  const existingColors = Array.from(subjectColorMap.value.values());
  const availableColors = colors.filter(color => !existingColors.includes(color));

  const colorToUse = availableColors.length > 0 ?
    availableColors[0] :
    colors[subjectColorMap.value.size % colors.length];

  subjectColorMap.value.set(subject, colorToUse);
  return colorToUse;
};

const getSubjectColor = (subject) => {
  if (!subject) return 'transparent';
  return getClassroomColor(subject);
};

const scheduleDays = computed(() => {
  return store.scheduleData.value.map(d => ({
    ...d,
    dayIndex: d.dayIndex ?? 0
  }));
});

const visibleDays = computed(() => {
  if (!store.showTodayOnly.value) return scheduleDays.value;

  const selectedDay = store.selectedDay.value;
  const map = { d1: 0, d2: 1, d3: 2, d4: 3, d5: 4, d6: 5 };
  const selectedIndex = map[selectedDay];

  const match = scheduleDays.value.find(d => d.dayIndex === selectedIndex);
  if (match) return [match];

  const current = store.testTimeEnabled.value ? store.testDayIndex.value : store.currentDayIndex.value;
  const fallback = scheduleDays.value.find(d => d.dayIndex === current);
  return fallback ? [fallback] : scheduleDays.value.slice(0, 1);
});

const timeSlots = computed(() => {
  if (resolvedTimeSlots?.value) {
    return resolvedTimeSlots.value;
  }
return [
    { id: 1, title: 'Period 1', type: 'lesson', start: '06:30', end: '07:15', startMin: 390, endMin: 435 },
    { id: 2, title: 'Period 2', type: 'lesson', start: '07:15', end: '08:00', startMin: 435, endMin: 480 },
    { id: 3, title: 'Period 3', type: 'lesson', start: '08:00', end: '08:45', startMin: 480, endMin: 525 },
    { id: 'b1', title: 'Break', type: 'break', start: '08:45', end: '09:15', startMin: 525, endMin: 555 },
    { id: 4, title: 'Period 4', type: 'lesson', start: '09:15', end: '10:00', startMin: 555, endMin: 600 },
    { id: 5, title: 'Period 5', type: 'lesson', start: '10:00', end: '10:35', startMin: 600, endMin: 635 },
    { id: 6, title: 'Period 6', type: 'lesson', start: '10:35', end: '11:10', startMin: 635, endMin: 670 },
    { id: 7, title: 'Period 7', type: 'lesson', start: '11:10', end: '11:45', startMin: 670, endMin: 705 },
    { id: 8, title: 'Period 8', type: 'lesson', start: '11:45', end: '12:20', startMin: 705, endMin: 740 }
];
});

const isCurrentDay = (dayIndex) => {
  const current = store.testTimeEnabled.value ? store.testDayIndex.value : store.currentDayIndex.value;
  return current === dayIndex;
};

const isCurrentPeriod = (slot) => {
  if (!store.testTimeEnabled.value && !isCurrentDay(store.currentDayIndex.value)) {
    return false;
  }

  const now = store.testTimeEnabled.value ?
    (store.testTimeValue.value.split(':').reduce((h, m) => h * 60 + m * 1, 0) * 60) :
    store.currentTotalSecs.value;

  const start = slot.startMin * 60;
  const end = slot.endMin * 60;

  return now >= start && now < end;
};

const getSubject = (periodNum, day, slot) => {
  if (slot.type === 'break' || slot.type === 'activity') return '';
  const period = day.classes.find(p => p.p === periodNum);
  return period?.sub || '';
};

const hasNafs = (periodNum, day, slot) => {
  if (slot.type === 'break' || slot.type === 'activity') return false;
  const period = day.classes.find(p => p.p === periodNum);
  return period?.nafs || false;
};

const isPeriodDone = (periodNum, day, slot) => {
  const subject = getSubject(periodNum, day, slot);
  if (!subject) return false;

  const dayId = `d${day.dayIndex + 1}`;
  const weekKey = store.getWeekKey();
  const weeklyPlanEntry = store.getWeeklyPlanEntry(weekKey, subject, dayId, String(periodNum));

  return weeklyPlanEntry?.done || false;
};

const getSubjectCellClass = (periodNum, day, slot) => {
  const classes = [];

  if (slot.type === 'break' || slot.type === 'activity') {
    classes.push('break');
    if (isCurrentDay(day.dayIndex) && isCurrentPeriod(slot)) classes.push('current');
    return classes;
  }

  const subject = getSubject(periodNum, day, slot);

  if (subject) {
    classes.push('has-subject');
  } else {
    classes.push('empty');
  }

  if (isCurrentDay(day.dayIndex)) {
    const now = store.currentTotalSecs.value;
    const start = slot.startMin * 60;
    const end = slot.endMin * 60;
    if (now >= start && now < end) {
      classes.push('current');
    } else if (now >= end) {
      classes.push('past');
    }
  }

  return classes;
};

const getSubjectStyle = (periodNum, day, slot) => {
  if (slot.type === 'break' || slot.type === 'activity') return {};

  const subject = getSubject(periodNum, day, slot);
  if (!subject) return {};

  const color = getSubjectColor(subject);
  return {
    backgroundColor: color,
    color: '#ffffff'
  };
};

const handleCellClick = (periodId, day, slot) => {
  if (slot.type === 'break' || slot.type === 'activity') return;

  const subject = getSubject(periodId, day, slot);
  if (!subject) return;

  const dayId = `d${day.dayIndex + 1}`;
  const weekKey = store.getWeekKey();

  dialogWeekKey.value = weekKey;
  dialogClassName.value = subject;
  dialogDayId.value = dayId;
  dialogPeriodId.value = String(periodId);
  dialogDayName.value = day.day;
  dialogPeriodTitle.value = slot?.title || `Period ${periodId}`;

  showWeeklyDialog.value = true;
};

const updateTimeProgress = () => {
  const now = store.testTimeEnabled.value ?
    (store.testTimeValue.value.split(':').reduce((h, m) => h * 60 + m * 1, 0) * 60) :
    store.currentTotalSecs.value;

  periodProgress.value = 0;

  timeSlots.value.forEach(slot => {
    if (slot.type === 'lesson' && isCurrentPeriod(slot)) {
      const startSecs = slot.startMin * 60;
      const endSecs = slot.endMin * 60;
      const totalDuration = endSecs - startSecs;
      const elapsed = now - startSecs;

      periodProgress.value = Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
    }
  });
};

const initializeColorMap = () => {
  const allSubjects = new Set();

  scheduleDays.value.forEach(day => {
    day.classes.forEach(period => {
      if (period.sub && period.sub.trim()) {
        allSubjects.add(period.sub);
      }
    });
  });

  const colors = currentColorScheme.value;
  let colorIndex = 0;

  allSubjects.forEach(subject => {
    if (!subjectColorMap.value.has(subject)) {
      subjectColorMap.value.set(subject, colors[colorIndex % colors.length]);
      colorIndex++;
    }
  });
};

onMounted(() => {
  initializeColorMap();

  const progressInterval = setInterval(updateTimeProgress, 1000);
  updateTimeProgress();

  alertInterval.value = progressInterval;
});

onUnmounted(() => {
  if (alertInterval.value) {
    clearInterval(alertInterval.value);
  }
});
</script>

<style scoped>
.table-view-v3 {
  padding: 1rem;
  overflow-x: auto;
  max-width: 100vw;
}

.table-container {
  min-width: 900px;
  max-width: 100%;
  background: white;
  border-radius: 12px;
  overflow: visible;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  position: relative;
}

.schedule-table-v3 {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.85rem;
}

.fixed-col {
  position: sticky;
  left: 0;
  z-index: 10;
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
}

.header-period.fixed-col {
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
}

.period-cell.fixed-col {
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
}

.header-period {
  width: 160px;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 0.75rem;
  text-align: left;
  font-weight: 700;
  color: #1e293b;
}

.header-day {
  min-width: 120px;
  max-width: 150px;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 0.5rem;
  text-align: center;
  font-weight: 600;
  color: #475569;
}

.header-day.current-day {
  background: #dbeafe;
  border-bottom: 2px solid #3b82f6;
}

.day-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
}

.day-title {
  font-size: 0.8rem;
}

.current-badge {
  background: #3b82f6;
  color: white;
  padding: 0.1rem 0.4rem;
  border-radius: 8px;
  font-size: 0.55rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.period-row {
  border-bottom: 1px solid #f1f5f9;
  transition: background-color 0.2s;
}

.period-row.current-period-row {
  background: #f0f9ff;
}

.period-cell {
  padding: 0.75rem;
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
  font-weight: 600;
  color: #1e293b;
}

.period-cell.break-cell {
  background: #f1f5f9;
}

.period-cell.current-period-cell {
  background: #dbeafe;
}

.period-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.period-title {
  font-size: 0.8rem;
}

.time-range {
  font-size: 0.65rem;
  opacity: 0.7;
}

.current-indicator {
  font-size: 0.55rem;
  color: #3b82f6;
  font-weight: 700;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.subject-cell {
  padding: 0.5rem;
  text-align: center;
  border-right: 1px solid #f1f5f9;
  vertical-align: middle;
  position: relative;
  transition: all 0.2s;
  min-width: 120px;
  max-width: 150px;
}

.subject-cell:last-child {
  border-right: none;
}

.subject-cell.empty {
  background: #fafafa;
  color: #cbd5e1;
}

.subject-cell.break {
  background: #f1f5f9;
  color: #94a3b8;
}

.subject-cell.has-subject {
  font-weight: 600;
}

.subject-cell.current {
  box-shadow: inset 0 0 0 2px #3b82f6;
  transform: scale(1.03);
  z-index: 1;
}

.subject-cell.past {
  opacity: 0.6;
}

.subject-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.1rem;
}

.subject-content.clickable {
  cursor: pointer;
}

.subject-name {
  font-size: 0.75rem;
  line-height: 1.2;
}

.nafs-indicator {
  background: rgba(255, 255, 255, 0.3);
  color: inherit;
  padding: 0.1rem 0.3rem;
  border-radius: 4px;
  font-size: 0.55rem;
  font-weight: 700;
}

.done-indicator {
  background: #28a745;
  color: white;
  padding: 0.1rem 0.3rem;
  border-radius: 4px;
  font-size: 0.55rem;
  font-weight: 700;
  margin-left: 0.2rem;
}

.empty-cell {
  color: #cbd5e1;
  font-size: 0.9rem;
}

.cell-progress {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  overflow: hidden;
  border-radius: 4px;
}

.progress-fill {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  background: rgba(255, 255, 255, 0.2);
  transition: width 1s linear;
}

.progress-line {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 2px;
  background: rgba(255, 255, 255, 0.8);
  box-shadow: 0 0 4px rgba(255, 255, 255, 0.6);
}

.progress-line::before {
  content: '';
  position: absolute;
  top: 0;
  left: -3px;
  width: 6px;
  height: 6px;
  background: white;
  border-radius: 50%;
  box-shadow: 0 0 3px rgba(255, 255, 255, 0.8);
}

@media (max-width: 640px) {
  .table-view-v3 {
    padding: 0.5rem;
  }

  .table-container {
    min-width: 700px;
    max-width: calc(100vw - 1rem);
  }

  .header-period {
    width: 140px;
    padding: 0.5rem;
    font-size: 0.75rem;
  }

  .header-day {
    min-width: 100px;
  }

  .period-cell {
    padding: 0.5rem;
  }

  .subject-cell {
    padding: 0.3rem;
    min-width: 80px;
  }

  .subject-name {
    font-size: 0.65rem;
    font-weight: 600;
  }

  .day-title {
    font-size: 0.75rem;
  }

  .time-range {
    font-size: 0.55rem;
  }
}

@media (prefers-color-scheme: dark) {
  .table-view-v3 {
    background: #0f172a;
  }

  .table-container {
    background: #1e293b;
  }

  .header-period,
  .period-cell {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
  }

  .header-period.fixed-col,
  .period-cell.fixed-col {
    background: #334155;
    border-color: #475569;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.3);
  }

  .header-day {
    background: #334155;
    border-color: #475569;
    color: #cbd5e1;
  }

  .header-day.current-day {
    background: #1e3a8a;
    border-color: #3b82f6;
  }

  .subject-cell {
    border-color: #334155;
  }

  .subject-cell.empty {
    background: #334155;
    color: #64748b;
  }

  .subject-cell.break {
    background: #475569;
    color: #cbd5e1;
  }

  .nafs-indicator {
    background: rgba(0, 0, 0, 0.3);
  }

  .current-indicator {
    color: #60a5fa;
  }
}
</style>
