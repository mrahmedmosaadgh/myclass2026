<template>
  <div class="admin-timing-bar">
    <div class="timing-info">
      <div class="timing-item">
        <span class="timing-label">Time</span>
        <span class="timing-value">{{ store.currentTimeDisplay.value }}</span>
      </div>
      <div class="timing-item">
        <span class="timing-label">Day</span>
        <span class="timing-value">{{ currentDayName }}</span>
      </div>
      <div class="timing-item">
        <span class="timing-label">Stage</span>
        <div class="stage-selector">
          <button
            v-for="s in stages"
            :key="s.id"
            class="stage-btn"
            :class="{ active: store.selectedStage.value === s.id }"
            @click="setStage(s.id)"
          >{{ s.short }}</button>
        </div>
      </div>
      <div class="timing-item">
        <span class="timing-label">Day</span>
        <div class="day-selector">
          <button
            v-for="d in days"
            :key="d.id"
            class="day-btn"
            :class="{ active: store.selectedDay.value === d.id }"
            @click="setDay(d.id)"
          >{{ d.short }}</button>
        </div>
      </div>
    </div>

    <div class="timing-actions">
      <PeriodSummary />
      <button class="action-btn" :class="{ active: store.showTodayOnly.value }" @click="toggleToday">
        📅 Today
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAppStore } from '../composables/useAppStore';
import PeriodSummary from './PeriodSummary.vue';

const store = useAppStore();

const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const stages = [
  { id: 'prim', short: 'P' },
  { id: 'middle', short: 'M' },
  { id: 'sec', short: 'S' }
];

const days = [
  { id: 'd1', short: 'D1' },
  { id: 'd2', short: 'D2' },
  { id: 'd3', short: 'D3' },
  { id: 'd4', short: 'D4' },
  { id: 'd5', short: 'D5' },
  { id: 'd6', short: 'D6' }
];

const currentDayName = computed(() => {
  const dayIndex = store.testTimeEnabled.value ? store.testDayIndex.value : store.currentDayIndex.value;
  return dayNames[dayIndex] || 'Unknown';
});

const setStage = async (stage) => {
  await store.setSelectedStage(stage);
};

const setDay = async (day) => {
  await store.setSelectedDay(day);
};

const toggleToday = async () => {
  const next = !store.showTodayOnly.value;
  await store.setShowTodayOnly(next);

  if (next) {
    const todayId = store.dayIndexToId(new Date().getDay());
    await setDay(todayId);
  }
};
</script>

<style scoped>
.admin-timing-bar {
  background: white;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.timing-info {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.timing-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  min-width: 60px;
}

.timing-label {
  font-size: 0.65rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.timing-value {
  font-weight: 600;
  font-size: 0.85rem;
  color: #1e293b;
}

.stage-selector,
.day-selector {
  display: flex;
  gap: 0.25rem;
}

.stage-btn,
.day-btn {
  padding: 0.25rem 0.4rem;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 0.7rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  min-height: 28px;
  min-width: 28px;
}

.stage-btn:hover,
.day-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.stage-btn.active,
.day-btn.active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.timing-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.action-btn {
  padding: 0.4rem 0.75rem;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  min-height: 32px;
}

.action-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.action-btn.active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

@media (max-width: 640px) {
  .admin-timing-bar {
    padding: 0.5rem;
    gap: 0.5rem;
  }

  .timing-info {
    gap: 0.5rem;
  }

  .timing-item {
    min-width: 50px;
  }

  .timing-label { font-size: 0.6rem; }
  .timing-value { font-size: 0.75rem; }

  .stage-btn,
  .day-btn {
    font-size: 0.65rem;
    min-height: 24px;
    min-width: 24px;
  }

  .action-btn {
    padding: 0.35rem 0.6rem;
    font-size: 0.75rem;
  }
}

@media (prefers-color-scheme: dark) {
  .admin-timing-bar {
    background: #1e293b;
    color: #f1f5f9;
  }

  .timing-value { color: #f1f5f9; }
  .stage-btn,
  .day-btn {
    background: #334155;
    border-color: #475569;
    color: #94a3b8;
  }
  .stage-btn:hover,
  .day-btn:hover {
    background: #475569;
    border-color: #64748b;
  }
  .stage-btn.active,
  .day-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
  }
  .action-btn {
    background: #334155;
    border-color: #475569;
    color: #cbd5e1;
  }
  .action-btn:hover {
    background: #475569;
    border-color: #64748b;
  }

  .action-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
  }
}
</style>
