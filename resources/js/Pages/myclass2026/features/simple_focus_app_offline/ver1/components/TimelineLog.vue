<script setup>
import { computed } from 'vue';
import { formatTimelineDate } from '../lib/focusAppStorage';

const props = defineProps({
  entries: {
    type: Array,
    default: () => [],
  },
  activeTaskId: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['resume']);

function toneClass(item) {
  switch (item.tone) {
    case 'success':
      return 'tone-success';
    case 'warning':
      return 'tone-warning';
    case 'danger':
      return 'tone-danger';
    default:
      return 'tone-neutral';
  }
}

const emptyState = computed(() => props.entries.length === 0);
</script>

<template>
  <section class="timeline-panel">
    <div class="timeline-head">
      <span class="timeline-tag">TIMELINE</span>
      <span class="timeline-count">{{ entries.length }} EVENTS</span>
    </div>

    <div v-if="emptyState" class="timeline-empty">
      No events yet. Start the first task to build the log.
    </div>

    <div v-else class="timeline-list">
      <button
        v-for="entry in entries"
        :key="entry.id"
        class="timeline-item"
        :class="[toneClass(entry), { active: entry.taskId && entry.taskId === activeTaskId }]"
        :disabled="!entry.taskId"
        @click="entry.taskId && emit('resume', entry.taskId)"
      >
        <div class="timeline-left">
          <span class="timeline-dot" />
          <div>
            <div class="timeline-title">{{ entry.label }}</div>
            <div class="timeline-detail">{{ entry.detail || '—' }}</div>
          </div>
        </div>
        <div class="timeline-meta">
          <span>{{ formatTimelineDate(entry.timestamp) }}</span>
          <span v-if="entry.taskId" class="timeline-task">RESUME</span>
        </div>
      </button>
    </div>
  </section>
</template>

<style scoped>
.timeline-panel {
  border: 1px solid rgba(74, 222, 128, 0.45);
  background: rgba(10, 10, 10, 0.85);
  padding: 1rem;
  font-family: 'Courier New', Courier, monospace;
  color: #f0fdf4;
  box-shadow: inset 0 0 0 1px rgba(74, 222, 128, 0.1), 0 0 20px rgba(0, 0, 0, 0.3);
  border-radius: 2px;
}

.timeline-head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 0.85rem;
}

.timeline-tag,
.timeline-count {
  color: #4ade80;
  font-size: 0.8rem;
  letter-spacing: 0.18em;
  font-weight: 500;
}

.timeline-empty {
  border: 1px dashed rgba(74, 222, 128, 0.4);
  padding: 1rem;
  color: #86efac;
  text-align: center;
  background: rgba(5, 10, 15, 0.5);
  border-radius: 2px;
}

.timeline-list {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.timeline-item {
  width: 100%;
  text-align: left;
  border: 1px solid rgba(74, 222, 128, 0.3);
  background: rgba(5, 10, 15, 0.95);
  color: #f0fdf4;
  padding: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  border-radius: 2px;
}

.timeline-item:disabled {
  cursor: default;
  opacity: 0.6;
}

.timeline-item:hover:not(:disabled) {
  border-color: #4ade80;
  background: rgba(10, 20, 30, 0.95);
  box-shadow: 0 0 12px rgba(74, 222, 128, 0.15);
  transform: translateY(-1px);
}

.timeline-item.active {
  border-color: #22c55e;
  background: rgba(34, 197, 94, 0.08);
  box-shadow: inset 0 0 0 1px rgba(34, 197, 94, 0.25), 0 0 16px rgba(34, 197, 94, 0.2);
}

.timeline-left {
  display: flex;
  gap: 0.75rem;
}

.timeline-dot {
  width: 10px;
  height: 10px;
  margin-top: 0.35rem;
  border-radius: 999px;
  background: #4ade80;
  box-shadow: 0 0 16px rgba(74, 222, 128, 0.6);
  flex: none;
}

.timeline-title {
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.timeline-detail {
  color: #86efac;
  margin-top: 0.25rem;
  white-space: pre-wrap;
}

.timeline-meta {
  margin-top: 0.5rem;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  font-size: 0.78rem;
  color: #86efac;
  letter-spacing: 0.08em;
}

.timeline-task {
  color: #fbbf24;
}

.tone-success {
  border-left: 3px solid #22c55e;
}

.tone-warning {
  border-left: 3px solid #fbbf24;
}

.tone-danger {
  border-left: 3px solid #ef4444;
}

.tone-neutral {
  border-left: 3px solid rgba(34, 197, 94, 0.4);
}
</style>
