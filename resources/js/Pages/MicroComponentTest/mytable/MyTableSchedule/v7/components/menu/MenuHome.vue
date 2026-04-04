<template>
  <div class="menu-home">
    <div class="today-snapshot">
      <h3 class="section-title">Today</h3>
      <div class="snapshot-card">
        <div class="snapshot-row">
          <span class="snapshot-label">Time</span>
          <span class="snapshot-value">{{ store.currentTimeDisplay.value }}</span>
        </div>
        <div class="snapshot-row">
          <span class="snapshot-label">Day</span>
          <span class="snapshot-value">{{ currentDayName }}</span>
        </div>
        <div class="snapshot-row">
          <span class="snapshot-label">Stage</span>
          <span class="snapshot-value">{{ stageLabel }}</span>
        </div>
        <div class="snapshot-row">
          <span class="snapshot-label">View</span>
          <span class="snapshot-value">{{ store.currentViewMode.value }}</span>
        </div>
        <div class="snapshot-row">
          <span class="snapshot-label">Status</span>
          <span class="snapshot-value" :class="store.isOnline.value ? 'online' : 'offline'">
            {{ store.isOnline.value ? '🟢 Online' : '🔴 Offline' }}
          </span>
        </div>
      </div>
    </div>

    <div class="quick-actions">
      <h3 class="section-title">Quick Actions</h3>
      <div class="actions-grid">
        <button class="action-btn" @click="goToToday">
          <span class="action-icon">📅</span>
          <span class="action-label">Go to Today</span>
        </button>
        <button class="action-btn" @click="$emit('close')">
          <span class="action-icon">👁️</span>
          <span class="action-label">View Schedule</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const emit = defineEmits(['close']);
const store = useAppStore();

const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const stageLabels = { prim: 'Primary', middle: 'Middle', sec: 'Secondary' };

const currentDayName = computed(() => dayNames[store.currentDayIndex.value] || 'Unknown');
const stageLabel = computed(() => stageLabels[store.selectedStage.value] || store.selectedStage.value);

const goToToday = () => {
  const todayId = store.dayIndexToId(new Date().getDay());
  store.setSelectedDay(todayId);
  emit('close');
};
</script>

<style scoped>
.section-title {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #64748b;
  margin: 0 0 0.75rem 0;
}

.snapshot-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.snapshot-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.snapshot-row:last-child { border-bottom: none; }

.snapshot-label {
  color: #94a3b8;
  font-size: 0.85rem;
}

.snapshot-value {
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: capitalize;
}

.snapshot-value.online { color: #10b981; }
.snapshot-value.offline { color: #f59e0b; }

.actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.03);
  color: #e2e8f0;
  cursor: pointer;
  transition: all 0.2s;
  min-height: 44px;
}

.action-btn:hover {
  background: rgba(59, 130, 246, 0.15);
  border-color: rgba(59, 130, 246, 0.3);
}

.action-icon { font-size: 1.5rem; }
.action-label { font-size: 0.75rem; font-weight: 600; }
</style>
