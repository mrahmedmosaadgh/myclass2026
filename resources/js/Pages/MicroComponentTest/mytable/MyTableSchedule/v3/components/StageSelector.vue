<template>
  <div class="stage-selector">
    <div class="selector-label">Stage:</div>
    <div class="stage-tabs">
      <button
        v-for="stage in stages"
        :key="stage.id"
        @click="selectStage(stage.id)"
        :class="['stage-tab', { active: modelValue === stage.id }]"
        :aria-label="`Select ${stage.label} stage`"
        :aria-pressed="modelValue === stage.id"
      >
        <span class="stage-icon">{{ stage.icon }}</span>
        <span class="stage-label">{{ stage.label }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: 'prim' }
});

const emit = defineEmits(['update:modelValue', 'stage-change']);

const stages = [
  { id: 'prim', label: 'Primary', icon: '🏫' },
  { id: 'middle', label: 'Middle', icon: '🎓' },
  { id: 'sec', label: 'Secondary', icon: '📚' }
];

const selectStage = (stageId) => {
  emit('update:modelValue', stageId);
  emit('stage-change', stageId);
  
  // Haptic feedback on mobile
  if (navigator.vibrate) {
    navigator.vibrate(50);
  }
};
</script>

<style scoped>
.stage-selector {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0;
  background: transparent;
  border-radius: 0;
  min-width: 0;
  flex: 1;
  overflow: hidden;
}

.selector-label {
  font-size: 0.74rem;
  font-weight: 600;
  color: #475569;
  white-space: nowrap;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.stage-tabs {
  display: flex;
  gap: 0.35rem;
  flex: 1;
  min-width: 0;
  overflow-x: auto;
  overflow-y: hidden;
  scrollbar-width: none;
}

.stage-tabs::-webkit-scrollbar {
  display: none;
}

.stage-tab {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  padding: 0.65rem 0.85rem;
  background: white;
  border: 1px solid #dbe3ef;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: 'Segoe UI', system-ui, sans-serif;
  font-weight: 600;
  color: #334155;
  min-width: 0;
  flex: 1;
  overflow: hidden;
}

.stage-tab:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #1e293b;
}

.stage-tab.active {
  background: #eff6ff;
  border-color: #93c5fd;
  color: #1d4ed8;
}

.stage-icon {
  font-size: 0.95rem;
  line-height: 1;
}

.stage-label {
  font-size: 0.8rem;
  font-weight: 600;
  line-height: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: inherit;
}

/* Mobile optimizations */
@media (max-width: 480px) {
  .stage-selector {
    flex-direction: column;
    align-items: stretch;
    gap: 0.35rem;
  }
  
  .selector-label {
    align-self: flex-start;
    margin-bottom: 0.1rem;
  }
  
  .stage-tabs {
    gap: 0.25rem;
  }
  
  .stage-tab {
    min-width: 78px;
    padding: 0.55rem 0.55rem;
    gap: 0.375rem;
  }
  
  .stage-icon {
    font-size: 0.9rem;
    flex-shrink: 0;
  }
  
  .stage-label {
    font-size: 0.75rem;
  }
}

@media (max-width: 380px) {
  .stage-selector {
    gap: 0.5rem;
  }

  .stage-tab {
    min-width: 64px;
    padding: 0.625rem 0.5rem;
  }

  .stage-icon {
    display: none;
  }

  .stage-label {
    font-size: 0.72rem;
  }
}

/* Touch feedback */
.stage-tab:active {
  transform: scale(0.95);
}

/* Focus styles for accessibility */
.stage-tab:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Compact mode for smaller containers */
@media (max-width: 320px) {
  .stage-tab {
    padding: 0.5rem 0.625rem;
  }
  
  .stage-icon {
    font-size: 0.875rem;
  }
  
  .stage-label {
    font-size: 0.65rem;
  }
}
</style>
