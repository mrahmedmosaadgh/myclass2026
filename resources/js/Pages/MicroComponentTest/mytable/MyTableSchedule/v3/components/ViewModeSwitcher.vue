<template>
  <div class="view-mode-switcher">
    <div class="switcher-container">
      <button
        v-for="mode in visibleViewModes()"
        :key="mode.id"
        @click="selectMode(mode.id)"
        :class="['mode-btn', { active: currentMode === mode.id }]"
        :aria-label="`Switch to ${mode.label} view`"
      >
        <span class="mode-icon">{{ mode.icon }}</span>
        <span class="mode-label">{{ mode.label }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  defaultMode: { type: String, default: 'card' },
  availableModes: {
    type: Array,
    default: () => ['card', 'table', 'list', 'master']
  }
});

const emit = defineEmits(['mode-change']);

const viewModes = [
  { id: 'card', icon: '📋', label: 'Cards' },
  { id: 'table', icon: '📊', label: 'Table' },
  { id: 'list', icon: '📝', label: 'List' },
  { id: 'master', icon: '🏫', label: 'School' }
];

const visibleViewModes = () => viewModes.filter(mode => props.availableModes.includes(mode.id));

const currentMode = ref(props.defaultMode);

// Load saved preference from localStorage
const loadSavedMode = () => {
  const saved = localStorage.getItem('schedule-app-view-mode');
  if (saved && visibleViewModes().find(m => m.id === saved)) {
    currentMode.value = saved;
  }
};

const selectMode = (modeId) => {
  if (!visibleViewModes().find(mode => mode.id === modeId)) {
    return;
  }

  currentMode.value = modeId;
  localStorage.setItem('schedule-app-view-mode', modeId);
  emit('mode-change', modeId);
};

watch(() => props.defaultMode, (newMode) => {
  if (newMode && visibleViewModes().find(m => m.id === newMode)) {
    currentMode.value = newMode;
  }
});

watch(() => props.availableModes, (newModes) => {
  if (!newModes.includes(currentMode.value)) {
    currentMode.value = newModes[0] || 'card';
    emit('mode-change', currentMode.value);
  }
}, { deep: true });

// Initialize on mount
loadSavedMode();
</script>

<style scoped>
.view-mode-switcher {
  width: 100%;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
}

.switcher-container {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
}

.mode-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 0.75rem 1rem;
  min-width: 60px;
  min-height: 60px;
  background: transparent;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: 'Segoe UI', system-ui, sans-serif;
  color: #475569;
}

.mode-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: translateY(-1px);
  color: #334155;
}

.mode-btn.active {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-color: #3b82f6;
  color: white;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.mode-icon {
  font-size: 1.25rem;
  line-height: 1;
}

.mode-label {
  font-size: 0.75rem;
  font-weight: 600;
  line-height: 1;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: inherit;
}

/* Mobile optimizations */
@media (max-width: 480px) {
  .view-mode-switcher {
    padding: 0.5rem;
  }
  
  .switcher-container {
    gap: 0.25rem;
  }
  
  .mode-btn {
    min-width: 50px;
    min-height: 50px;
    padding: 0.5rem 0.75rem;
  }
  
  .mode-icon {
    font-size: 1rem;
  }
  
  .mode-label {
    font-size: 0.65rem;
  }
}

/* Touch feedback */
.mode-btn:active {
  transform: scale(0.95);
}

/* Focus styles for accessibility */
.mode-btn:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>
