<template>
  <div class="admin-timing-bar">
    <div class="timing-summary">
      <div class="summary-primary">
        <span class="summary-label">Now</span>
        <span class="summary-value">{{ currentDateLabel }}</span>
      </div>
      <div class="summary-side">
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

        <div class="timing-actions-inline">
          <button
            type="button"
            class="icon-action-btn"
            title="View timing"
            aria-label="View timing"
            @click="$emit('open-timing', selectedDay)"
          >
            👁️
          </button>
          <button
            type="button"
            class="icon-action-btn"
            title="Edit all timing types"
            aria-label="Edit all timing types"
            @click="$emit('open-timing-all')"
          >
            ⚙️
          </button>
        </div>
      </div>
    </div>

    <div class="selectors-grid">
      <div class="selector-card compact">
        <StageSelector
          :model-value="selectedStage"
          @update:modelValue="$emit('update:stage', $event)"
          @stage-change="$emit('update:stage', $event)"
        />
      </div>
      <div class="selector-card compact">
        <DaySelector
          :model-value="selectedDay"
          :stage="selectedStage"
          :today-day-id="todayDayId"
          :custom-timing-days="customTimingDays"
          @update:modelValue="$emit('update:day', $event)"
          @day-change="$emit('day-click', $event)"
        />
      </div>
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
  gap: 0.65rem;
  padding: 0.75rem;
  background: #ffffff;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05);
  margin: 0.65rem 0;
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
  gap: 0.15rem;
  min-width: 0;
}

.summary-label {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #64748b;
}

.summary-value {
  font-size: 0.92rem;
  font-weight: 700;
  color: #1e293b;
}

.summary-side {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  flex: 1;
  min-width: 0;
}

.summary-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
}

.summary-chip {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
  border-radius: 999px;
  padding: 0.38rem 0.68rem;
  font-size: 0.74rem;
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

.timing-actions-inline {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.icon-action-btn {
  width: 34px;
  height: 34px;
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  cursor: pointer;
}

.icon-action-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.selectors-grid {
  display: grid;
  grid-template-columns: 1fr 1.35fr;
  gap: 0.55rem;
  align-items: start;
}

.selector-card.compact {
  padding: 0.7rem 0.8rem;
  border: 1px solid #edf2f7;
  border-radius: 12px;
  background: #f8fafc;
}

@media (max-width: 640px) {
  .admin-timing-bar {
    padding: 0.65rem;
  }

  .timing-summary {
    flex-direction: column;
    align-items: stretch;
  }

  .summary-side {
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .selectors-grid {
    grid-template-columns: 1fr;
  }

  .summary-value {
    font-size: 0.88rem;
  }
}
</style>
