<template>
  <div class="table-view-v2">
    <div class="table-container">
      <table class="schedule-table-v2">
        <thead>
          <tr>
            <th class="header-day fixed-col">Day</th>
            <th
              v-for="slot in timeSlots"
              :key="slot.id"
              class="header-period"
              :class="{ 'break-header': slot.type === 'break', 'current-period': isCurrentPeriod(slot) }"
            >
              <div class="period-header">
                <span class="period-title">{{ slot.title }}</span>
                <span class="time-range">{{ slot.start }} - {{ slot.end }}</span>
                <span v-if="isCurrentPeriod(slot)" class="current-indicator">● NOW</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="day in scheduleDays"
            :key="day.dayIndex"
            class="day-row"
            :class="{ active: isCurrentDay(day.dayIndex) }"
          >
            <td class="day-cell fixed-col">
              <div class="day-info">
                <span class="day-name">{{ day.day }}</span>
                <span v-if="isCurrentDay(day.dayIndex)" class="current-badge">Today</span>
              </div>
            </td>
            <td
              v-for="slot in timeSlots"
              :key="`${day.dayIndex}-${slot.id}`"
              class="subject-cell"
              :class="getSubjectCellClass(slot.id, day)"
              :style="getSubjectStyle(slot.id, day)"
            >
              <div v-if="getSubject(slot.id, day)" class="subject-content">
                <span class="subject-name">{{ getSubject(slot.id, day) }}</span>
                <span v-if="hasNafs(slot.id, day)" class="nafs-indicator">N</span>
              </div>
              <span v-else class="empty-cell">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, onUnmounted, ref } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const store = useAppStore();
const resolvedTimeSlots = inject('resolvedTimeSlots');

// Alert system
const alertInterval = ref(null);
const lastAlertPeriod = ref(null);

// Check for 5-minute alerts
const checkForAlerts = () => {
  if (!store.isOnline.value) return;
  
  const current = store.testTimeEnabled.value ? store.testDayIndex.value : store.currentDayIndex.value;
  const now = store.testTimeEnabled.value ? 
    (store.testTimeValue.value.split(':').reduce((h, m) => h * 60 + m * 1, 0) * 60) :
    store.currentTotalSecs.value;
  
  timeSlots.value.forEach(slot => {
    if (slot.type === 'lesson') {
      const end = slot.endMin * 60;
      const timeUntilEnd = end - now;
      
      // Alert 5 minutes before end
      if (timeUntilEnd <= 300 && timeUntilEnd > 0 && lastAlertPeriod.value !== slot.id) {
        lastAlertPeriod.value = slot.id;
        showNotification(`${slot.title} ends in 5 minutes!`);
      }
      
      // Reset alert after period ends
      if (timeUntilEnd <= 0 && lastAlertPeriod.value === slot.id) {
        lastAlertPeriod.value = null;
      }
    }
  });
};

const showNotification = (message) => {
  // Browser notification if permitted
  if ('Notification' in window && Notification.permission === 'granted') {
    new Notification('Schedule Alert', {
      body: message,
      icon: '/my-fly-schedule-app/v5/icon.png'
    });
  }
  
  // Fallback: alert dialog
  if (confirm(message + '\n\nClick OK to acknowledge')) {
    // User acknowledged
  }
};

// Start alert checking
onMounted(() => {
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
  }
  
  // Check every 30 seconds
  alertInterval.value = setInterval(checkForAlerts, 30000);
  checkForAlerts(); // Check immediately
  
  // Pre-populate color map with all subjects
  initializeColorMap();
});

onUnmounted(() => {
  if (alertInterval.value) {
    clearInterval(alertInterval.value);
  }
});

// Initialize color map with all unique subjects
const initializeColorMap = () => {
  const allSubjects = new Set();
  
  // Collect all unique subjects from schedule data
  scheduleDays.value.forEach(day => {
    day.classes.forEach(period => {
      if (period.sub && period.sub.trim()) {
        allSubjects.add(period.sub);
      }
    });
  });
  
  // Assign colors to all subjects
  const colors = currentColorScheme.value;
  let colorIndex = 0;
  
  allSubjects.forEach(subject => {
    if (!subjectColorMap.value.has(subject)) {
      subjectColorMap.value.set(subject, colors[colorIndex % colors.length]);
      colorIndex++;
    }
  });
};

// Color schemes
const colorSchemes = {
  default: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16', '#06b6d4', '#a855f7', '#f43f5e', '#22c55e', '#eab308'],
  pastel: ['#93c5fd', '#fca5a5', '#86efac', '#fcd34d', '#c4b5fd', '#f9a8d4', '#5eead4', '#fdba74', '#a5b4fc', '#bef264', '#67e8f9', '#d8b4fe', '#fda4af', '#86efac', '#fde047'],
  vibrant: ['#2563eb', '#dc2626', '#059669', '#d97706', '#7c3aed', '#db2777', '#0d9488', '#ea580c', '#4f46e5', '#65a30d', '#0891b2', '#9333ea', '#e11d48', '#16a34a', '#ca8a04'],
  monochrome: ['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#e2e8f0', '#f1f5f9', '#f8fafc', '#0f172a', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#e2e8f0']
};

// Get current color scheme
const currentColorScheme = computed(() => {
  // Default to 'default' scheme if not set
  return colorSchemes.default;
});

// Persistent color mapping for subjects
const subjectColorMap = ref(new Map());

// Assign consistent colors to classrooms
const getClassroomColor = (subject) => {
  if (!subject) return 'transparent';
  
  // Check if we already have a color for this subject
  if (subjectColorMap.value.has(subject)) {
    return subjectColorMap.value.get(subject);
  }
  
  // Assign a new color for this subject
  const colors = currentColorScheme.value;
  const existingColors = Array.from(subjectColorMap.value.values());
  const availableColors = colors.filter(color => !existingColors.includes(color));
  
  // If all colors are used, cycle back to the beginning
  const colorToUse = availableColors.length > 0 ? 
    availableColors[0] : 
    colors[subjectColorMap.value.size % colors.length];
  
  // Store the color for this subject
  subjectColorMap.value.set(subject, colorToUse);
  
  return colorToUse;
};

// Get color for a specific subject cell
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

// Fallback time slots in case inject fails
const timeSlots = computed(() => {
  if (resolvedTimeSlots?.value) {
    return resolvedTimeSlots.value;
  }
  // Fallback static slots
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

const getSubject = (periodNum, day) => {
  const period = day.classes.find(p => p.p === periodNum);
  return period?.sub || '';
};

const hasNafs = (periodNum, day) => {
  const period = day.classes.find(p => p.p === periodNum);
  return period?.nafs || false;
};

const getSubjectCellClass = (periodNum, day) => {
  const subject = getSubject(periodNum, day);
  const classes = [];
  
  if (subject) {
    classes.push('has-subject');
  } else {
    classes.push('empty');
  }
  
  // Add live indicator for current period
  if (isCurrentDay(day.dayIndex)) {
    const slot = timeSlots.value.find(s => s.id === periodNum);
    if (slot) {
      const now = store.currentTotalSecs.value;
      const start = slot.startMin * 60;
      const end = slot.endMin * 60;
      if (now >= start && now < end) {
        classes.push('current');
      } else if (now >= end) {
        classes.push('past');
      }
    }
  }
  
  return classes;
};

const getSubjectStyle = (periodNum, day) => {
  const subject = getSubject(periodNum, day);
  if (!subject) return {};
  
  const color = getSubjectColor(subject);
  return {
    backgroundColor: color,
    color: 'white'
  };
};
</script>

<style scoped>
.table-view-v2 {
  padding: 1rem;
  overflow-x: auto;
}

.table-container {
  min-width: 800px;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  position: relative;
}

.schedule-table-v2 {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

/* Fixed column styles */
.fixed-col {
  position: sticky;
  left: 0;
  z-index: 10;
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
}

.header-day.fixed-col {
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
}

.day-cell.fixed-col {
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
}

.header-day {
  width: 100px;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 0.75rem;
  text-align: left;
  font-weight: 700;
  color: #1e293b;
}

.header-period {
  min-width: 90px;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 0.5rem;
  text-align: center;
  font-weight: 600;
  color: #475569;
}

.header-period.break-header {
  background: #f1f5f9;
}

.header-period.current-period {
  background: #dbeafe;
  border-bottom: 2px solid #3b82f6;
}

.period-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
}

.period-title {
  font-size: 0.75rem;
}

.time-range {
  font-size: 0.6rem;
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

.day-row {
  border-bottom: 1px solid #f1f5f9;
  transition: background-color 0.2s;
}

.day-row.active {
  background: #f0f9ff;
}

.day-cell {
  padding: 0.75rem;
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
  font-weight: 600;
  color: #1e293b;
}

.day-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.day-name {
  font-size: 0.85rem;
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
  align-self: flex-start;
}

.subject-cell {
  padding: 0.5rem;
  text-align: center;
  border-right: 1px solid #f1f5f9;
  vertical-align: middle;
  position: relative;
  transition: all 0.2s;
}

.subject-cell:last-child {
  border-right: none;
}

.subject-cell.empty {
  background: #fafafa;
  color: #cbd5e1;
}

.subject-cell.has-subject {
  font-weight: 600;
}

.subject-cell.current {
  box-shadow: inset 0 0 0 2px #3b82f6;
  transform: scale(1.05);
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

.empty-cell {
  color: #cbd5e1;
  font-size: 0.9rem;
}

@media (max-width: 640px) {
  .table-view-v2 {
    padding: 0.5rem;
  }

  .table-container {
    min-width: 600px;
  }

  .header-day { 
    width: 80px; 
    padding: 0.5rem;
  }

  .day-cell {
    padding: 0.5rem;
  }

  .day-name { font-size: 0.75rem; }

  .subject-cell {
    padding: 0.3rem;
  }

  .subject-name { font-size: 0.65rem; }
}

@media (prefers-color-scheme: dark) {
  .table-container {
    background: #1e293b;
  }

  .header-day,
  .day-cell {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
  }

  .header-period {
    background: #334155;
    border-color: #475569;
    color: #cbd5e1;
  }

  .header-period.break-header {
    background: #475569;
  }

  .day-row {
    border-color: #334155;
  }

  .day-row.active {
    background: #1e3a8a;
  }

  .subject-cell {
    border-color: #334155;
  }

  .subject-cell.empty {
    background: #334155;
    color: #64748b;
  }

  .nafs-indicator {
    background: rgba(0, 0, 0, 0.2);
  }
}
</style>
