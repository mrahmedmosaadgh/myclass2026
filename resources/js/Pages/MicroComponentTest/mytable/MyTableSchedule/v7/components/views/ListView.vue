<template>
  <div class="list-view">
    <div class="list-container">
      <div
        v-for="day in scheduleDays"
        :key="day.dayIndex"
        class="day-section"
        :class="{ active: isCurrentDay(day.dayIndex) }"
      >
        <div class="day-header">
          <h3 class="day-name">{{ day.day }}</h3>
          <span v-if="isCurrentDay(day.dayIndex)" class="current-badge">Today</span>
        </div>

        <div class="periods-list">
          <div
            v-for="period in day.classes"
            :key="period.p"
            class="period-row"
            :class="getPeriodClass(period)"
          >
            <div class="period-time">
              <span class="period-number">P{{ period.p }}</span>
              <span class="time-range">{{ getTimeRange(period.p) }}</span>
            </div>
            <div class="period-content">
              <span class="subject">{{ period.sub || 'Free Period' }}</span>
              <span v-if="period.nafs" class="nafs-badge">Nafs</span>
            </div>
            <div class="period-status">
              <span v-if="isCurrentPeriod(period.p, day.dayIndex)" class="live-indicator">● Live</span>
              <span v-else-if="isPastPeriod(period.p, day.dayIndex)" class="past-indicator">✓</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const store = useAppStore();
const resolvedTimeSlots = inject('resolvedTimeSlots');

const scheduleDays = computed(() => {
  return store.scheduleData.value.map(d => ({
    ...d,
    dayIndex: d.dayIndex ?? 0
  }));
});

const isCurrentDay = (dayIndex) => {
  const current = store.testTimeEnabled.value ? store.testDayIndex.value : store.currentDayIndex.value;
  return current === dayIndex;
};

const getPeriodClass = (period) => {
  if (isCurrentPeriod(period.p, period.dayIndex)) return 'current';
  if (isPastPeriod(period.p, period.dayIndex)) return 'past';
  return 'future';
};

const isCurrentPeriod = (periodNum, dayIndex) => {
  if (!isCurrentDay(dayIndex)) return false;
  const slot = resolvedTimeSlots.value.find(s => s.id === periodNum);
  if (!slot) return false;
  const now = store.currentTotalSecs.value;
  const start = slot.startMin * 60;
  const end = slot.endMin * 60;
  return now >= start && now < end;
};

const isPastPeriod = (periodNum, dayIndex) => {
  if (!isCurrentDay(dayIndex)) return false;
  const slot = resolvedTimeSlots.value.find(s => s.id === periodNum);
  if (!slot) return false;
  return store.currentTotalSecs.value >= slot.endMin * 60;
};

const getTimeRange = (periodNum) => {
  const slot = resolvedTimeSlots.value.find(s => s.id === periodNum);
  if (!slot) return '—';
  return `${slot.start} - ${slot.end}`;
};
</script>

<style scoped>
.list-view {
  padding: 1rem;
}

.list-container {
  max-width: 800px;
  margin: 0 auto;
}

.day-section {
  background: white;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: all 0.2s;
}

.day-section.active {
  border: 2px solid #3b82f6;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.2);
}

.day-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.day-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.current-badge {
  background: #3b82f6;
  color: white;
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.periods-list {
  padding: 0.5rem;
}

.period-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 0.25rem;
  transition: all 0.15s;
}

.period-row.current {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.period-row.past {
  background: #f1f5f9;
  color: #64748b;
}

.period-row.future {
  background: white;
  color: #1e293b;
}

.period-time {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 60px;
}

.period-number {
  font-weight: 700;
  font-size: 0.9rem;
}

.time-range {
  font-size: 0.65rem;
  opacity: 0.7;
}

.period-content {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.subject {
  font-weight: 600;
  font-size: 0.95rem;
}

.nafs-badge {
  background: rgba(245, 158, 11, 0.2);
  color: #d97706;
  padding: 0.15rem 0.4rem;
  border-radius: 6px;
  font-size: 0.6rem;
  font-weight: 600;
}

.period-status {
  display: flex;
  align-items: center;
  min-width: 50px;
  justify-content: flex-end;
}

.live-indicator {
  color: #10b981;
  font-size: 1rem;
  animation: pulse 2s infinite;
}

.past-indicator {
  color: #10b981;
  font-size: 0.9rem;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@media (max-width: 640px) {
  .list-view {
    padding: 0.5rem;
  }

  .day-section {
    margin-bottom: 1rem;
  }

  .day-header {
    padding: 0.75rem 1rem;
  }

  .day-name { font-size: 1rem; }

  .period-row {
    padding: 0.6rem 0.75rem;
    gap: 0.75rem;
  }

  .period-time { min-width: 50px; }
  .period-number { font-size: 0.8rem; }
  .time-range { font-size: 0.6rem; }
  .subject { font-size: 0.85rem; }
}

@media (prefers-color-scheme: dark) {
  .day-section {
    background: #1e293b;
    color: #f1f5f9;
  }

  .day-header {
    background: #334155;
    border-color: #475569;
  }

  .day-name { color: #f1f5f9; }

  .period-row.future {
    background: #1e293b;
    color: #f1f5f9;
  }

  .period-row.past {
    background: #334155;
    color: #94a3b8;
  }

  .nafs-badge {
    background: rgba(245, 158, 11, 0.3);
    color: #fbbf24;
  }
}
</style>
