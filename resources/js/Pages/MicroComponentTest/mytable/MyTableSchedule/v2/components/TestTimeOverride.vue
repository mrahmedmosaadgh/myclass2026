<template>
  <div class="test-time-override" :class="{ active: enabled }">
    <div class="override-header">
      <label class="toggle-row">
        <input
          :checked="enabled"
          type="checkbox"
          class="toggle-input"
          @change="updateEnabled($event.target.checked)"
        />
        <span class="toggle-slider"></span>
        <span class="toggle-label">Test Time Override</span>
      </label>

      <button
        v-if="enabled"
        type="button"
        class="reset-btn"
        @click="resetToRealtime"
      >
        Real Time
      </button>
    </div>

    <div v-if="enabled" class="override-controls">
      <label class="field-group">
        <span class="field-label">Day</span>
        <select
          class="field-input"
          :value="dayIndex"
          @change="updateDayIndex(Number($event.target.value))"
        >
          <option v-for="day in dayOptions" :key="day.value" :value="day.value">
            {{ day.label }}
          </option>
        </select>
      </label>

      <label class="field-group">
        <span class="field-label">Time</span>
        <input
          class="field-input"
          :type="useManualTimeInput ? 'text' : 'time'"
          :step="useManualTimeInput ? undefined : '60'"
          :inputmode="useManualTimeInput ? 'numeric' : undefined"
          :placeholder="useManualTimeInput ? 'HH:MM' : undefined"
          :value="timeValue"
          @input="handleTimeInput($event.target.value)"
          @blur="handleTimeBlur($event.target.value)"
        />
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  enabled: { type: Boolean, default: false },
  dayIndex: { type: Number, default: 0 },
  timeValue: { type: String, default: '09:00' }
});

const emit = defineEmits(['update:enabled', 'update:dayIndex', 'update:timeValue']);

const useManualTimeInput = ref(false);

const dayOptions = [
  { value: 0, label: 'Sunday / D1' },
  { value: 1, label: 'Monday / D2' },
  { value: 2, label: 'Tuesday / D3' },
  { value: 3, label: 'Wednesday / D4' },
  { value: 4, label: 'Thursday / D5' },
  { value: 5, label: 'Friday / D6' },
  { value: 6, label: 'Saturday' }
];

const updateEnabled = (value) => {
  emit('update:enabled', value);
};

const updateDayIndex = (value) => {
  emit('update:dayIndex', value);
};

const updateTimeValue = (value) => {
  emit('update:timeValue', value || '09:00');
};

const normalizeTimeValue = (value) => {
  const trimmed = String(value || '').trim();
  const match = trimmed.match(/^(\d{1,2}):(\d{2})$/);

  if (!match) {
    return null;
  }

  const hours = Number(match[1]);
  const minutes = Number(match[2]);

  if (Number.isNaN(hours) || Number.isNaN(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
    return null;
  }

  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
};

const handleTimeInput = (value) => {
  if (!useManualTimeInput.value) {
    updateTimeValue(value);
    return;
  }

  emit('update:timeValue', value);
};

const handleTimeBlur = (value) => {
  if (!useManualTimeInput.value) {
    return;
  }

  const normalized = normalizeTimeValue(value);
  updateTimeValue(normalized || '09:00');
};

const resetToRealtime = () => {
  emit('update:enabled', false);
};

onMounted(() => {
  useManualTimeInput.value = window.matchMedia('(pointer: coarse)').matches || window.innerWidth <= 640;
});
</script>

<style scoped>
.test-time-override {
  margin: 0.75rem 0;
  padding: 0.75rem 1rem;
  background: #ffffff;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
}

.test-time-override.active {
  border-color: #3b82f6;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.override-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.toggle-row {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  min-width: 0;
}

.toggle-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.toggle-slider {
  position: relative;
  width: 44px;
  height: 24px;
  border-radius: 9999px;
  background: #cbd5e1;
  transition: background 0.2s ease;
  flex-shrink: 0;
}

.toggle-slider::after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #ffffff;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.25);
  transition: transform 0.2s ease;
}

.toggle-input:checked + .toggle-slider {
  background: #2563eb;
}

.toggle-input:checked + .toggle-slider::after {
  transform: translateX(20px);
}

.toggle-label {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1e293b;
}

.reset-btn {
  padding: 0.45rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #f8fafc;
  color: #334155;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
}

.override-controls {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
  margin-top: 0.75rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  min-width: 0;
}

.field-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #475569;
}

.field-input {
  width: 100%;
  min-height: 42px;
  padding: 0.65rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #ffffff;
  color: #1e293b;
  font-size: 0.9rem;
}

@media (max-width: 520px) {
  .test-time-override {
    padding: 0.65rem 0.75rem;
  }

  .override-header {
    flex-wrap: wrap;
    align-items: flex-start;
  }

  .override-controls {
    grid-template-columns: 1fr;
  }

  .toggle-label {
    font-size: 0.82rem;
  }

  .reset-btn {
    width: 100%;
  }
}
</style>
