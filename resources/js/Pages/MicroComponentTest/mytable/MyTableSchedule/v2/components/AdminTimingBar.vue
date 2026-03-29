<template>
  <div class="admin-timing-bar">
    <div class="timing-summary">
      <div class="summary-primary">
        <span class="summary-label">Now</span>
        <span class="summary-value">{{ currentDateLabel }}</span>
      </div>
      <div class="summary-chips">
        <button type="button" class="summary-chip stage-chip" @click="$emit('focus-stage')">
          {{ stageLabel }}
        </button>
        <button type="button" class="summary-chip day-chip" @click="$emit('day-click', selectedDay)">
          {{ dayLabel }}
        </button>
        <button
          v-if="selectedDay !== todayDayId"
          type="button"
          class="summary-chip today-chip"
          @click="$emit('go-today')"
        >
          Today
        </button>
      </div>
    </div>

    <div class="selectors-grid">
      <StageSelector
        :model-value="selectedStage"
        @update:modelValue="$emit('update:stage', $event)"
        @stage-change="$emit('update:stage', $event)"
      />
      <DaySelector
        :model-value="selectedDay"
        :stage="selectedStage"
        :today-day-id="todayDayId"
        :custom-timing-days="customTimingDays"
        @update:modelValue="$emit('update:day', $event)"
        @day-change="$emit('day-click', $event)"
      />
    </div>

    <div class="timing-actions">
      <button type="button" class="timing-action-btn primary" @click="$emit('open-timing', selectedDay)">
        Timing for {{ dayLabel }}
      </button>
      <button type="button" class="timing-action-btn" @click="$emit('open-timing-all')">
        Same for all days
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import StageSelector from './StageSelector.vue';
import DaySelector from './DaySelector.vue';

const props = defineProps({
  selectedStage: { type: String, required: true },
  selectedDay: { type: String, required: true },
  todayDayId: { type: String, required: true },
  currentDateLabel: { type: String, required: true },
  customTimingDays: { type: Array, default: () => [] }
});

defineEmits(['update:stage', 'update:day', 'go-today', 'open-timing', 'open-timing-all', 'day-click', 'focus-stage']);

const stageLabel = computed(() => {
  return {
    prim: 'Primary',
    middle: 'Middle',
    sec: 'Secondary'
  }[props.selectedStage] || props.selectedStage;
});

const dayLabel = computed(() => {
  return {
    d1: 'Day 1',
    d2: 'Day 2',
    d3: 'Day 3',
    d4: 'Day 4',
    d5: 'Day 5',
    d6: 'Day 6'
  }[props.selectedDay] || props.selectedDay;
});
</script>

<style scoped>
.admin-timing-bar {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 0.85rem;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  border: 1px solid #dbe3ef;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
  margin: 0.75rem 0;
}

.timing-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.summary-primary {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.summary-label {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #64748b;
}

.summary-value {
  font-size: 0.98rem;
  font-weight: 700;
  color: #1e293b;
}

.summary-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.summary-chip {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
  border-radius: 999px;
  padding: 0.45rem 0.8rem;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
}

.stage-chip {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #1d4ed8;
}

.day-chip {
  background: #ecfdf5;
  border-color: #a7f3d0;
  color: #047857;
}

.today-chip {
  background: #f8fafc;
}

.selectors-grid {
  display: grid;
  grid-template-columns: 1fr 1.35fr;
  gap: 0.75rem;
  align-items: start;
}

.timing-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.timing-action-btn {
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #334155;
  border-radius: 10px;
  padding: 0.7rem 1rem;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
}

.timing-action-btn.primary {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-color: #3b82f6;
  color: #ffffff;
}

@media (max-width: 640px) {
  .admin-timing-bar {
    padding: 0.75rem;
  }

  .selectors-grid {
    grid-template-columns: 1fr;
  }

  .timing-actions {
    flex-direction: column;
  }

  .timing-action-btn {
    width: 100%;
  }
}
</style>
