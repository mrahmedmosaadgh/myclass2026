<script setup>
const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  taskTitle: {
    type: String,
    default: '',
  },
  nextTaskTitle: {
    type: String,
    default: '',
  },
  nextTaskNotes: {
    type: String,
    default: '',
  },
  continueNotes: {
    type: String,
    default: '',
  },
});

const emit = defineEmits([
  'update:nextTaskTitle',
  'update:nextTaskNotes',
  'update:continueNotes',
  'break',
  'continue',
  'done',
  'needs-continue',
]);
</script>

<template>
  <section v-if="visible" class="action-panel">
    <div class="action-head">
      <span class="action-tag">TIMER ENDED</span>
      <h2>Choose the next move for: {{ taskTitle }}</h2>
    </div>

    <div class="action-grid">
      <label class="action-field">
        <span>NEW TASK TITLE</span>
        <input
          :value="nextTaskTitle"
          type="text"
          placeholder="Start a new task"
          @input="emit('update:nextTaskTitle', $event.target.value)"
        >
      </label>

      <label class="action-field">
        <span>NEW TASK NOTES</span>
        <textarea
          :value="nextTaskNotes"
          rows="2"
          placeholder="What should happen next?"
          @input="emit('update:nextTaskNotes', $event.target.value)"
        />
      </label>

      <label class="action-field action-wide">
        <span>CONTINUE LATER NOTES</span>
        <textarea
          :value="continueNotes"
          rows="2"
          placeholder="What still needs to be done or the last step"
          @input="emit('update:continueNotes', $event.target.value)"
        />
      </label>
    </div>

    <div class="action-buttons">
      <button class="action-button" @click="emit('break')">BREAK 5M THEN CONTINUE</button>
      <button class="action-button" @click="emit('continue')">CONTINUE SAME TASK</button>
      <button class="action-button" @click="emit('done')">NEW TASK + MARK OLD DONE</button>
      <button class="action-button danger" @click="emit('needs-continue')">NEEDS CONTINUE</button>
    </div>
  </section>
</template>

<style scoped>
.action-panel {
  border: 1px solid rgba(251, 191, 36, 0.6);
  background: rgba(20, 15, 5, 0.9);
  padding: 1rem;
  font-family: 'Courier New', Courier, monospace;
  color: #f0fdf4;
  box-shadow: inset 0 0 0 1px rgba(251, 191, 36, 0.2), 0 0 20px rgba(251, 191, 36, 0.1);
  border-radius: 2px;
}

.action-head {
  margin-bottom: 1rem;
}

.action-tag {
  color: #fbbf24;
  letter-spacing: 0.18em;
  font-size: 0.8rem;
  font-weight: 600;
  text-shadow: 0 0 8px rgba(251, 191, 36, 0.4);
}

.action-head h2 {
  margin: 0.4rem 0 0;
  font-size: 1rem;
  text-transform: uppercase;
  line-height: 1.5;
}

.action-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.85rem;
}

.action-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.action-wide {
  grid-column: 1 / -1;
}

.action-field span {
  color: #22c55e;
  font-size: 0.75rem;
  letter-spacing: 0.16em;
}

.action-field input,
.action-field textarea {
  width: 100%;
  border: 1px solid rgba(251, 191, 36, 0.4);
  background: rgba(20, 15, 5, 0.95);
  color: #f0fdf4;
  padding: 0.8rem 0.9rem;
  font-family: inherit;
  outline: none;
  resize: vertical;
  border-radius: 2px;
}

.action-field input:focus,
.action-field textarea:focus {
  border-color: #fbbf24;
  box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.15);
}

.action-buttons {
  margin-top: 1rem;
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.action-button {
  border: 1px solid #fbbf24;
  background: transparent;
  color: #f0fdf4;
  padding: 0.75rem 0.9rem;
  font-family: inherit;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
  border-radius: 2px;
}

.action-button:hover:not(:disabled) {
  background: rgba(251, 191, 36, 0.15);
  border-color: #f59e0b;
  box-shadow: 0 0 12px rgba(251, 191, 36, 0.3);
  transform: translateY(-1px);
}

.action-button:active:not(:disabled) {
  transform: translateY(0);
}

.action-button.danger {
  border-color: #ef4444;
  color: #fca5a5;
}

.action-button.danger:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.15);
  border-color: #dc2626;
  box-shadow: 0 0 12px rgba(239, 68, 68, 0.3);
}

@media (max-width: 900px) {
  .action-grid {
    grid-template-columns: 1fr;
  }
}
</style>
