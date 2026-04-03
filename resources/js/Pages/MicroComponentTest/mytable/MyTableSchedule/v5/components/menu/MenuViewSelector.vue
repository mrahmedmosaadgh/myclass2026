<template>
  <div class="menu-view-selector">
    <h3 class="section-title">View Mode</h3>
    <p class="section-desc">Choose how to display your schedule.</p>

    <div class="view-options">
      <button
        v-for="mode in viewModes"
        :key="mode.id"
        class="view-option"
        :class="{ active: store.currentViewMode.value === mode.id }"
        @click="selectMode(mode.id)"
      >
        <span class="view-icon">{{ mode.icon }}</span>
        <div class="view-info">
          <span class="view-label">{{ mode.label }}</span>
          <span class="view-desc">{{ mode.desc }}</span>
        </div>
        <span v-if="store.currentViewMode.value === mode.id" class="check-mark">✓</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { useAppStore } from '../../composables/useAppStore';

const emit = defineEmits(['close']);
const store = useAppStore();

const viewModes = [
  { id: 'card', icon: '🃏', label: 'Card View', desc: 'Cards per day with live period indicator' },
  { id: 'table', icon: '📊', label: 'Table View', desc: 'Full week table with all periods' },
  { id: 'list', icon: '📋', label: 'List View', desc: 'Compact scrollable list layout' },
  { id: 'master', icon: '🏫', label: 'School Timetable', desc: 'Full school timetable with teachers' }
];

const selectMode = (mode) => {
  store.setViewMode(mode);
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
  margin: 0 0 0.25rem 0;
}

.section-desc {
  color: #475569;
  font-size: 0.8rem;
  margin: 0 0 1rem 0;
}

.view-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.view-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
  color: #e2e8f0;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
  min-height: 44px;
}

.view-option:hover {
  background: rgba(59, 130, 246, 0.1);
  border-color: rgba(59, 130, 246, 0.2);
}

.view-option.active {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.4);
}

.view-icon { font-size: 1.5rem; flex-shrink: 0; }

.view-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.view-label { font-weight: 600; font-size: 0.9rem; }
.view-desc { font-size: 0.75rem; color: #94a3b8; }

.check-mark {
  color: #60a5fa;
  font-weight: 700;
  font-size: 1.1rem;
}
</style>
