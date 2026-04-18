<template>
  <div class="period-summary" :class="statusClass">
    <div class="summary-main">
      <div class="summary-left">
        <div class="badge">{{ badgeText }}</div>
        <div class="title-row">
          <div class="title">{{ titleText }}</div>
          <div v-if="timeRangeText" class="time-range">{{ timeRangeText }}</div>
        </div>
        <div v-if="subtitleText" class="subtitle">{{ subtitleText }}</div>
      </div>

      <div class="summary-right">
        <div class="time-left">
          <span class="time-left-label">Left</span>
          <span class="time-left-value">{{ timeLeftText }}</span>
        </div>
      </div>
    </div>

    <div class="progress" v-if="showProgress">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: `${progressPercent}%` }"></div>
      </div>
      <div class="progress-meta">
        <span class="progress-meta-left">{{ progressLeftText }}</span>
        <span v-if="nextText" class="progress-meta-right">Next: {{ nextText }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import { useAppStore } from '../composables/useAppStore';

const store = useAppStore();
const resolvedTimeSlots = inject('resolvedTimeSlots', null);

const parseTimeToMin = (t) => {
  if (!t) return 0;
  const [h, m] = String(t).split(':').map(Number);
  return (Number.isFinite(h) ? h : 0) * 60 + (Number.isFinite(m) ? m : 0);
};

const withMinutes = (slot) => {
  if (!slot) return slot;
  const startMin = Number.isFinite(slot.startMin) ? slot.startMin : parseTimeToMin(slot.start);
  const endMin = Number.isFinite(slot.endMin) ? slot.endMin : parseTimeToMin(slot.end);
  return { ...slot, startMin, endMin };
};

const dayIndexForSelected = computed(() => {
  const map = { d1: 0, d2: 1, d3: 2, d4: 3, d5: 4, d6: 5 };
  const selected = store.selectedDay.value;
  return typeof map[selected] === 'number' ? map[selected] : 0;
});

const nowSecs = computed(() => {
  if (store.testTimeEnabled.value) {
    const [hh = 0, mm = 0] = String(store.testTimeValue.value).split(':').map(Number);
    return (hh * 3600) + (mm * 60);
  }
  return store.currentTotalSecs.value;
});

const slots = computed(() => {
  const list = resolvedTimeSlots?.value ? resolvedTimeSlots.value : [];
  return list.map(withMinutes);
});

const currentSlot = computed(() => {
  const nowMin = Math.floor(nowSecs.value / 60);
  return slots.value.find(s => nowMin >= s.startMin && nowMin < s.endMin) || null;
});

const nextSlot = computed(() => {
  const nowMin = Math.floor(nowSecs.value / 60);
  return slots.value.find(s => s.startMin > nowMin) || null;
});

const subjectNow = computed(() => {
  const slot = currentSlot.value;
  if (!slot || slot.type === 'break' || slot.type === 'activity') return '';

  const day = store.scheduleData.value.find(d => (d.dayIndex ?? 0) === dayIndexForSelected.value);
  if (!day) return '';

  const period = (day.classes || []).find(p => p.p === slot.id);
  return period?.sub || '';
});

const progressPercent = computed(() => {
  const slot = currentSlot.value;
  if (!slot) return 0;

  const now = nowSecs.value;
  const startSecs = slot.startMin * 60;
  const endSecs = slot.endMin * 60;
  const total = Math.max(1, endSecs - startSecs);
  const elapsed = now - startSecs;
  return Math.min(100, Math.max(0, (elapsed / total) * 100));
});

const timeLeftSecs = computed(() => {
  const slot = currentSlot.value;
  if (!slot) return 0;
  const endSecs = slot.endMin * 60;
  return Math.max(0, endSecs - nowSecs.value);
});

const timeLeftText = computed(() => {
  const slot = currentSlot.value;
  if (!slot) return '—';

  const mins = Math.floor(timeLeftSecs.value / 60);
  const secs = Math.floor(timeLeftSecs.value % 60);

  if (mins >= 60) {
    const hh = Math.floor(mins / 60);
    const mm = mins % 60;
    return `${hh}:${String(mm).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  }

  return `${mins}:${String(secs).padStart(2, '0')}`;
});

const showProgress = computed(() => !!currentSlot.value);

const badgeText = computed(() => {
  const slot = currentSlot.value;
  if (!slot) return 'No active period';
  if (slot.type === 'break') return 'Break';
  return 'Now';
});

const titleText = computed(() => {
  const slot = currentSlot.value;
  if (!slot) return 'Schedule';
  if (slot.type === 'break') return slot.title || 'Break';
  const base = slot.title || `Period ${slot.id}`;
  return base;
});

const subtitleText = computed(() => {
  const slot = currentSlot.value;
  if (!slot) {
    const next = nextSlot.value;
    if (!next) return 'No upcoming periods';
    return `Next: ${next.title || `Period ${next.id}`}`;
  }

  if (slot.type === 'break') {
    return nextSlot.value ? `Next: ${nextSlot.value.title || `Period ${nextSlot.value.id}`}` : '';
  }

  return subjectNow.value ? `Subject: ${subjectNow.value}` : '';
});

const timeRangeText = computed(() => {
  const slot = currentSlot.value;
  if (!slot) return '';
  if (!slot.start || !slot.end) return '';
  return `${slot.start} - ${slot.end}`;
});

const nextText = computed(() => {
  const next = nextSlot.value;
  if (!next) return '';
  if (next.type === 'break') return next.title || 'Break';
  return next.title || `Period ${next.id}`;
});

const progressLeftText = computed(() => {
  const slot = currentSlot.value;
  if (!slot) return '';
  return `${Math.round(progressPercent.value)}%`;
});

const statusClass = computed(() => {
  const slot = currentSlot.value;
  if (!slot) return 'is-idle';
  if (slot.type === 'break') return 'is-break';
  if (timeLeftSecs.value <= 300) return 'is-ending';
  return 'is-lesson';
});
</script>

<style scoped>
.period-summary {
  border-radius: 14px;
  padding: 0.85rem 0.9rem;
  color: #0f172a;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
  min-width: 320px;
}

.period-summary.is-lesson {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.18) 0%, rgba(16, 185, 129, 0.14) 100%), rgba(255, 255, 255, 0.85);
}

.period-summary.is-break {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.18) 0%, rgba(236, 72, 153, 0.12) 100%), rgba(255, 255, 255, 0.85);
}

.period-summary.is-ending {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.18) 0%, rgba(245, 158, 11, 0.12) 100%), rgba(255, 255, 255, 0.85);
}

.period-summary.is-idle {
  background: rgba(255, 255, 255, 0.75);
}

.summary-main {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.summary-left {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  min-width: 0;
}

.badge {
  align-self: flex-start;
  font-size: 0.65rem;
  font-weight: 800;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  padding: 0.18rem 0.5rem;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.08);
  color: #0f172a;
}

.title-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.75rem;
}

.title {
  font-size: 1.05rem;
  font-weight: 800;
  line-height: 1.1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.time-range {
  font-size: 0.75rem;
  font-weight: 700;
  color: rgba(15, 23, 42, 0.7);
  flex-shrink: 0;
}

.subtitle {
  font-size: 0.85rem;
  font-weight: 700;
  color: rgba(15, 23, 42, 0.78);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.summary-right {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  flex-shrink: 0;
}

.time-left {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.1rem;
}

.time-left-label {
  font-size: 0.65rem;
  font-weight: 800;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  color: rgba(15, 23, 42, 0.6);
}

.time-left-value {
  font-size: 1.15rem;
  font-weight: 900;
  font-variant-numeric: tabular-nums;
}

.progress {
  margin-top: 0.7rem;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.progress-bar {
  height: 10px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.08);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, #3b82f6, #10b981);
}

.period-summary.is-break .progress-fill {
  background: linear-gradient(90deg, #f59e0b, #ec4899);
}

.period-summary.is-ending .progress-fill {
  background: linear-gradient(90deg, #ef4444, #f59e0b);
}

.progress-meta {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  font-size: 0.72rem;
  font-weight: 800;
  color: rgba(15, 23, 42, 0.65);
}

.progress-meta-right {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 60%;
}

@media (max-width: 640px) {
  .period-summary {
    min-width: 0;
    width: 100%;
  }

  .title {
    font-size: 1rem;
  }

  .time-left-value {
    font-size: 1.05rem;
  }
}

@media (prefers-color-scheme: dark) {
  .period-summary {
    color: #e2e8f0;
    background: rgba(30, 41, 59, 0.78);
    border-color: rgba(255, 255, 255, 0.08);
  }

  .period-summary.is-lesson {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.22) 0%, rgba(16, 185, 129, 0.18) 100%), rgba(30, 41, 59, 0.78);
  }

  .period-summary.is-break {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.22) 0%, rgba(236, 72, 153, 0.16) 100%), rgba(30, 41, 59, 0.78);
  }

  .period-summary.is-ending {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.22) 0%, rgba(245, 158, 11, 0.16) 100%), rgba(30, 41, 59, 0.78);
  }

  .badge {
    background: rgba(226, 232, 240, 0.12);
    color: #e2e8f0;
  }

  .time-range,
  .subtitle,
  .time-left-label,
  .progress-meta {
    color: rgba(226, 232, 240, 0.75);
  }

  .progress-bar {
    background: rgba(226, 232, 240, 0.14);
  }
}
</style>
