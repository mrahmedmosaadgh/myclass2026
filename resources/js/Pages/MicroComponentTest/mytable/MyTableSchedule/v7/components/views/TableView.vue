<template>
  <div class="table-view">
    <div class="table-container">
      <table class="schedule-table">
        <thead>
          <tr>
            <th class="header-period">Period</th>
            <th
              v-for="day in scheduleDays"
              :key="day.dayIndex"
              class="header-day"
              :class="{ active: isCurrentDay(day.dayIndex) }"
            >
              <div class="header-content">
                <span class="day-name">{{ day.day }}</span>
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
            :class="{ 'break-row': slot.type === 'break' }"
          >
            <td class="period-header">
              <div class="period-info">
                <span class="period-title">{{ slot.title }}</span>
                <span class="time-range">{{ slot.start }} - {{ slot.end }}</span>
              </div>
            </td>
            <td
              v-for="day in scheduleDays"
              :key="`${day.dayIndex}-${slot.id}`"
              class="period-cell"
              :class="getCellClass(slot.id, day.dayIndex)"
            >
              <div class="cell-content">
                <span class="subject">{{ getSubject(slot.id, day) }}</span>
                <span v-if="hasNafs(slot.id, day)" class="nafs-badge">Nafs</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const store = useAppStore();
const resolvedTimeSlots = inject('resolvedTimeSlots');

// Debug: Check what we received
console.log('TableView - resolvedTimeSlots:', resolvedTimeSlots);
console.log('TableView - resolvedTimeSlots.value:', resolvedTimeSlots?.value);
console.log('TableView - store.scheduleData:', store.scheduleData.value);

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

const getSubject = (periodNum, day) => {
  const period = day.classes.find(p => p.p === periodNum);
  return period?.sub || '';
};

const hasNafs = (periodNum, day) => {
  const period = day.classes.find(p => p.p === periodNum);
  return period?.nafs || false;
};

const getCellClass = (periodNum, dayIndex) => {
  if (!isCurrentDay(dayIndex)) return '';
  const slot = timeSlots.value.find(s => s.id === periodNum);
  if (!slot) return '';
  const now = store.currentTotalSecs.value;
  const start = slot.startMin * 60;
  const end = slot.endMin * 60;
  if (now >= start && now < end) return 'current';
  if (now >= end) return 'past';
  return '';
};
</script>

<style scoped>
.table-view {
  padding: 1rem;
  overflow-x: auto;
}

.table-container {
  min-width: 600px;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.schedule-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.header-period {
  width: 120px;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 0.75rem;
  text-align: left;
  font-weight: 700;
  color: #1e293b;
}

.header-day {
  min-width: 80px;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 0.5rem;
  text-align: center;
  font-weight: 600;
  color: #475569;
  position: relative;
}

.header-day.active {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.header-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
}

.day-name {
  font-size: 0.8rem;
}

.current-badge {
  background: rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.1rem 0.35rem;
  border-radius: 8px;
  font-size: 0.55rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.period-row {
  border-bottom: 1px solid #f1f5f9;
}

.period-row.break-row {
  background: #f8fafc;
}

.period-header {
  padding: 0.75rem;
  background: #f8fafc;
  border-right: 1px solid #e2e8f0;
  font-weight: 600;
  color: #1e293b;
}

.period-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.period-title {
  font-size: 0.85rem;
}

.time-range {
  font-size: 0.65rem;
  color: #64748b;
}

.period-cell {
  padding: 0.5rem;
  text-align: center;
  border-right: 1px solid #f1f5f9;
  vertical-align: middle;
  position: relative;
}

.period-cell:last-child {
  border-right: none;
}

.period-cell.current {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  font-weight: 600;
}

.period-cell.past {
  background: #f1f5f9;
  color: #64748b;
}

.cell-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
  min-height: 32px;
  justify-content: center;
}

.subject {
  font-size: 0.8rem;
  line-height: 1.2;
}

.nafs-badge {
  background: rgba(245, 158, 11, 0.2);
  color: #d97706;
  padding: 0.1rem 0.3rem;
  border-radius: 4px;
  font-size: 0.55rem;
  font-weight: 600;
}

.period-cell.current .nafs-badge {
  background: rgba(255, 255, 255, 0.3);
  color: white;
}

@media (max-width: 640px) {
  .table-view {
    padding: 0.5rem;
  }

  .header-period {
    width: 100px;
    padding: 0.5rem;
    font-size: 0.75rem;
  }

  .header-day {
    min-width: 60px;
    padding: 0.4rem;
    font-size: 0.7rem;
  }

  .period-header {
    padding: 0.5rem;
    font-size: 0.75rem;
  }

  .period-cell {
    padding: 0.4rem;
    font-size: 0.75rem;
  }

  .subject { font-size: 0.7rem; }
  .time-range { font-size: 0.6rem; }
}

@media (prefers-color-scheme: dark) {
  .table-container {
    background: #1e293b;
  }

  .header-period,
  .period-header {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
  }

  .header-day {
    background: #334155;
    border-color: #475569;
    color: #cbd5e1;
  }

  .period-row {
    border-color: #334155;
  }

  .period-row.break-row {
    background: #334155;
  }

  .period-cell {
    border-color: #334155;
    color: #f1f5f9;
  }

  .period-cell.past {
    background: #334155;
    color: #94a3b8;
  }

  .nafs-badge {
    background: rgba(245, 158, 11, 0.3);
    color: #fbbf24;
  }
}
</style>
