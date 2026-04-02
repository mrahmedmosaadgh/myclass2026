<template>
  <div class="day-selector">
    <div class="selector-label">Day:</div>
    <div class="day-tabs-container" ref="dayTabsContainer">
      <div class="day-tabs" :class="{ 'has-custom': hasCustomDays }">
        <button
          v-for="day in days"
          :key="day.id"
          @click="selectDay(day.id)"
          :class="['day-tab', { 
            active: modelValue === day.id,
            'is-today': day.isToday,
            'has-custom-timing': day.hasCustomTiming
          }]"
          :aria-label="`Select ${day.label}`"
          :aria-pressed="modelValue === day.id"
        >
          <span class="day-short">{{ day.short }}</span>
          <span v-if="day.isToday" class="today-indicator">●</span>
          <span v-if="day.hasCustomTiming" class="custom-indicator" title="Custom timing">⚙</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: 'd1' },
  stage: { type: String, default: 'prim' },
  todayDayId: { type: String, default: 'd1' },
  customTimingDays: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:modelValue', 'day-change']);

const dayTabsContainer = ref(null);

// Generate days based on current week
const days = computed(() => {
  const customDays = new Set(props.customTimingDays || []);

  return [
    { id: 'd1', label: 'Day 1', short: 'D1', dayIndex: 0, isToday: props.todayDayId === 'd1', hasCustomTiming: customDays.has('d1') },
    { id: 'd2', label: 'Day 2', short: 'D2', dayIndex: 1, isToday: props.todayDayId === 'd2', hasCustomTiming: customDays.has('d2') },
    { id: 'd3', label: 'Day 3', short: 'D3', dayIndex: 2, isToday: props.todayDayId === 'd3', hasCustomTiming: customDays.has('d3') },
    { id: 'd4', label: 'Day 4', short: 'D4', dayIndex: 3, isToday: props.todayDayId === 'd4', hasCustomTiming: customDays.has('d4') },
    { id: 'd5', label: 'Day 5', short: 'D5', dayIndex: 4, isToday: props.todayDayId === 'd5', hasCustomTiming: customDays.has('d5') },
    { id: 'd6', label: 'Day 6', short: 'D6', dayIndex: 5, isToday: props.todayDayId === 'd6', hasCustomTiming: customDays.has('d6') }
  ];
});

const hasCustomDays = computed(() => {
  return days.value.some(day => day.hasCustomTiming);
});

const selectDay = (dayId) => {
  emit('update:modelValue', dayId);
  emit('day-change', dayId);
  
  // Haptic feedback on mobile
  if (navigator.vibrate) {
    navigator.vibrate(50);
  }
  
  // Scroll selected day into view if needed
  scrollToSelectedDay();
};

const scrollToSelectedDay = async () => {
  await nextTick();
  const container = dayTabsContainer.value;
  if (!container) return;
  
  const selectedTab = container.querySelector('.day-tab.active');
  if (selectedTab) {
    const containerRect = container.getBoundingClientRect();
    const tabRect = selectedTab.getBoundingClientRect();
    
    // Check if selected tab is partially out of view
    if (tabRect.left < containerRect.left || tabRect.right > containerRect.right) {
      selectedTab.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
        inline: 'center'
      });
    }
  }
};

// Initialize and scroll to today/selected day
onMounted(() => {
  // Auto-select today if no value is provided
  if (!props.modelValue) {
    const todayDay = days.value.find(day => day.isToday);
    if (todayDay) {
      selectDay(todayDay.id);
    }
  } else {
    scrollToSelectedDay();
  }
});
</script>

<style scoped>
.day-selector {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0;
  background: transparent;
  border-radius: 0;
  min-width: 0;
  flex: 2;
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

.day-tabs-container {
  position: relative;
  flex: 1;
  overflow-x: auto;
  overflow-y: hidden;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE/Edge */
}

.day-tabs-container::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}

.day-tabs {
  display: flex;
  gap: 0.35rem;
  padding: 0.25rem 0;
  min-width: min-content;
  align-items: center;
}

.day-tabs.has-custom {
  padding-bottom: 0.25rem;
}

.day-tab {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  min-width: 50px;
  height: 40px;
  padding: 0.45rem 0.65rem;
  background: white;
  border: 1px solid #dbe3ef;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: 'Segoe UI', system-ui, sans-serif;
  font-weight: 600;
  color: #334155;
  white-space: nowrap;
  flex-shrink: 0;
  overflow: hidden;
}

.day-tab:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #1e293b;
}

.day-tab.active {
  background: #ecfdf5;
  border-color: #86efac;
  color: #047857;
}

.day-tab.is-today:not(.active) {
  border-color: #10b981;
  background: #ecfdf5;
  color: #059669;
}

.day-tab.has-custom-timing:not(.active) {
  border-color: #fcd34d;
  background: #fffdf5;
  color: #b45309;
}

.day-short {
  font-size: 0.8rem;
  font-weight: 700;
  line-height: 1;
  color: inherit;
}

.today-indicator {
  position: absolute;
  top: 2px;
  right: 2px;
  font-size: 0.5rem;
  color: #10b981;
  animation: pulse-green 2s infinite;
}

.day-tab.active .today-indicator {
  color: white;
}

@keyframes pulse-green {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.custom-indicator {
  position: absolute;
  bottom: 2px;
  right: 2px;
  font-size: 0.55rem;
  opacity: 0.55;
}

.day-tab.active .custom-indicator {
  color: rgba(255, 255, 255, 0.8);
}

/* Mobile optimizations */
@media (max-width: 480px) {
  .day-selector {
    flex-direction: column;
    align-items: stretch;
    gap: 0.35rem;
  }
  
  .selector-label {
    align-self: flex-start;
    margin-bottom: 0.1rem;
  }
  
  .day-tabs {
    gap: 0.25rem;
  }
  
  .day-tab {
    min-width: 48px;
    height: 38px;
    padding: 0.35rem 0.45rem;
  }
  
  .day-short {
    font-size: 0.75rem;
  }
  
  .today-indicator {
    font-size: 0.4rem;
  }
  
  .custom-indicator {
    font-size: 0.5rem;
  }
}

@media (max-width: 380px) {
  .day-selector {
    gap: 0.5rem;
  }

  .day-tab {
    min-width: 44px;
    height: 38px;
    padding: 0.25rem 0.4rem;
  }

  .day-short {
    font-size: 0.72rem;
  }

  .custom-indicator {
    display: none;
  }
}

/* Touch feedback */
.day-tab:active {
  transform: scale(0.95);
}

/* Focus styles for accessibility */
.day-tab:focus-visible {
  outline: 2px solid #10b981;
  outline-offset: 2px;
}

/* Scroll hint */
.day-tabs-container::after {
  content: '';
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 20px;
  background: linear-gradient(to right, transparent, rgba(248, 250, 252, 0.9));
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.day-tabs-container.scrolling::after {
  opacity: 1;
}

/* Compact mode for very small screens */
@media (max-width: 320px) {
  .day-tab {
    min-width: 40px;
    height: 36px;
    padding: 0.25rem 0.375rem;
  }
  
  .day-short {
    font-size: 0.65rem;
  }
}
</style>
