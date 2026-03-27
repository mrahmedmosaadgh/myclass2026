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
  border: 1px solid rgba(74, 222, 128, 0.3);
  background: rgba(10, 10, 10, 0.9);
  padding: 1.2rem;
  font-family: 'Courier New', Courier, monospace;
  color: #f0fdf4;
  box-shadow: inset 0 0 0 1px rgba(74, 222, 128, 0.1), 0 0 20px rgba(0, 0, 0, 0.5);
  border-radius: 2px;
}

.composer-head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.prompt {
  color: #4ade80;
  letter-spacing: 0.08em;
  font-weight: 600;
  text-shadow: 0 0 8px rgba(74, 222, 128, 0.3);
}

.composer-hint {
  color: #86efac;
  opacity: 0.85;
  font-size: 0.75rem;
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
  color: #4ade80;
  font-weight: 500;
}

.composer-field input,
.composer-field textarea {
  width: 100%;
  border: 1px solid rgba(74, 222, 128, 0.4);
  background: rgba(5, 10, 15, 0.95);
  color: #f0fdf4;
  padding: 0.85rem 0.95rem;
  outline: none;
  font-family: inherit;
  font-size: 0.9rem;
  border-radius: 2px;
  transition: border-color 0.2s, box-shadow 0.2s;
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
  border: 1px solid #4ade80;
  background: #4ade80;
  color: #000;
  font-family: inherit;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  padding: 0.8rem 1rem;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
  border-radius: 2px;
  box-shadow: 0 0 12px rgba(74, 222, 128, 0.3);
}

.composer-button:hover:not(:disabled) {
  background: #22c55e;
  border-color: #22c55e;
  box-shadow: 0 0 20px rgba(74, 222, 128, 0.5);
  transform: translateY(-1px);
}

.composer-button:active:not(:disabled) {
  transform: translateY(0);
}

.composer-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.composer-current {
  color: #86efac;
  font-size: 0.82rem;
  letter-spacing: 0.08em;
  opacity: 0.9;
  font-weight: 500;
}

@media (max-width: 900px) {
  .composer-grid {
    grid-template-columns: 1fr;
  }
}
</style>
