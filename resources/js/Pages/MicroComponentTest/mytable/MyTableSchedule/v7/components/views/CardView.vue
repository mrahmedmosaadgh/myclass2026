<template>
  <div class="card-view">
    <div class="cards-grid">
      <div
        v-for="day in scheduleDays"
        :key="day.dayIndex"
        class="day-card"
        :class="{ active: isCurrentDay(day.dayIndex) }"
      >
        <div class="card-header">
          <h3 class="day-name">{{ day.day }}</h3>
          <span v-if="isCurrentDay(day.dayIndex)" class="current-badge">Today</span>
        </div>

        <div class="periods-list">
          <div
            v-for="period in day.classes"
            :key="period.p"
            class="period-item"
            :class="getPeriodClass(period)"
          >
            <div class="period-time">
              <span class="period-number">P{{ period.p }}</span>
              <span class="time-range">{{ getTimeRange(period.p) }}</span>
            </div>
            <div class="period-content">
              <span class="subject">{{ period.sub || 'Free' }}</span>
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
.card-view {
  padding: 1rem;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1rem;
}

.day-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: all 0.2s;
}

.day-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.day-card.active {
  border: 2px solid #3b82f6;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.2);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.day-name {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.current-badge {
  background: #3b82f6;
  color: white;
  padding: 0.15rem 0.5rem;
  border-radius: 12px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.periods-list {
  padding: 0.5rem;
}

.period-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  margin-bottom: 0.25rem;
  transition: all 0.15s;
}

.period-item.current {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.period-item.past {
  background: #f1f5f9;
  color: #64748b;
}

.period-item.future {
  background: white;
  color: #1e293b;
}

.period-time {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 50px;
}

.period-number {
  font-weight: 700;
  font-size: 0.8rem;
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
  font-size: 0.85rem;
}

.nafs-badge {
  background: rgba(245, 158, 11, 0.2);
  color: #d97706;
  padding: 0.1rem 0.35rem;
  border-radius: 4px;
  font-size: 0.6rem;
  font-weight: 600;
}

.period-status {
  display: flex;
  align-items: center;
  min-width: 40px;
  justify-content: flex-end;
}

.live-indicator {
  color: #10b981;
  font-size: 0.9rem;
  animation: pulse 2s infinite;
}

.past-indicator {
  color: #10b981;
  font-size: 0.8rem;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@media (max-width: 640px) {
  .cards-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .card-view {
    padding: 0.5rem;
  }

  .period-item {
    padding: 0.4rem 0.5rem;
    gap: 0.5rem;
  }

  .period-time { min-width: 45px; }
  .period-number { font-size: 0.75rem; }
  .time-range { font-size: 0.6rem; }
  .subject { font-size: 0.8rem; }
}

@media (prefers-color-scheme: dark) {
  .day-card {
    background: #1e293b;
    color: #f1f5f9;
  }

  .card-header {
    background: #334155;
    border-bottom-color: #475569;
  }

  .day-name { color: #f1f5f9; }

  .period-item.future {
    background: #1e293b;
    color: #f1f5f9;
  }

  .period-item.past {
    background: #334155;
    color: #94a3b8;
  }

  .nafs-badge {
    background: rgba(245, 158, 11, 0.3);
    color: #fbbf24;
  }
}
</style>
