<script setup>
const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  notes: {
    type: String,
    default: '',
  },
  activeTaskTitle: {
    type: String,
    default: 'NO ACTIVE TASK',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:title', 'update:notes', 'create']);
</script>

<template>
  <section class="composer-panel">
    <div class="composer-head">
      <span class="prompt">C:\FOCUS\NEW_TASK&gt;</span>
      <span class="composer-hint">Type a task, press ENTER, start 10m automatically</span>
    </div>

    <div class="composer-grid">
      <label class="composer-field">
        <span>TASK</span>
        <input
          :value="title"
          type="text"
          :disabled="disabled"
          placeholder="Focus task title"
          @input="emit('update:title', $event.target.value)"
          @keydown.enter.prevent="emit('create')"
        >
      </label>

      <label class="composer-field">
        <span>NOTES</span>
        <textarea
          :value="notes"
          :disabled="disabled"
          rows="3"
          placeholder="Last step, blocker, or small note"
          @input="emit('update:notes', $event.target.value)"
        />
      </label>
    </div>

    <div class="composer-actions">
      <button class="composer-button" :disabled="disabled" @click="emit('create')">
        START TASK + 10M
      </button>
      <span class="composer-current">CURRENT: {{ activeTaskTitle }}</span>
    </div>
  </section>
</template>

<style scoped>
.composer-panel {
  border: 1px solid rgba(34, 197, 94, 0.5);
  background: rgba(0, 0, 0, 0.7);
  padding: 1rem;
  font-family: 'Courier New', Courier, monospace;
  color: #d1fae5;
  box-shadow: inset 0 0 0 1px rgba(34, 197, 94, 0.08);
}

.composer-head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.prompt {
  color: #22c55e;
  letter-spacing: 0.08em;
}

.composer-hint {
  color: #86efac;
  opacity: 0.8;
}

.composer-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 0.9rem;
}

.composer-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.composer-field span {
  font-size: 0.75rem;
  letter-spacing: 0.18em;
  color: #22c55e;
}

.composer-field input,
.composer-field textarea {
  width: 100%;
  border: 1px solid rgba(34, 197, 94, 0.35);
  background: rgba(1, 6, 12, 0.95);
  color: #ecfdf5;
  padding: 0.85rem 0.95rem;
  outline: none;
  font-family: inherit;
  resize: vertical;
}

.composer-field input:focus,
.composer-field textarea:focus {
  border-color: #22c55e;
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.15);
}

.composer-actions {
  margin-top: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.composer-button {
  border: 1px solid #22c55e;
  background: #22c55e;
  color: #000;
  font-family: inherit;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  padding: 0.8rem 1rem;
  cursor: pointer;
}

.composer-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.composer-current {
  color: #86efac;
  font-size: 0.82rem;
  letter-spacing: 0.08em;
}

@media (max-width: 900px) {
  .composer-grid {
    grid-template-columns: 1fr;
  }
}
</style>
