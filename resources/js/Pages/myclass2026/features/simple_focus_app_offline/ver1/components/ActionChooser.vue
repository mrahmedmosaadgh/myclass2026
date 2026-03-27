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
  border: 1px solid rgba(34, 197, 94, 0.5);
  background: rgba(0, 0, 0, 0.82);
  padding: 1rem;
  font-family: 'Courier New', Courier, monospace;
  color: #d1fae5;
}

.action-head {
  margin-bottom: 1rem;
}

.action-tag {
  color: #fbbf24;
  letter-spacing: 0.18em;
  font-size: 0.8rem;
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
  border: 1px solid rgba(34, 197, 94, 0.35);
  background: rgba(1, 6, 12, 0.95);
  color: #ecfdf5;
  padding: 0.8rem 0.9rem;
  font-family: inherit;
  outline: none;
  resize: vertical;
}

.action-field input:focus,
.action-field textarea:focus {
  border-color: #22c55e;
}

.action-buttons {
  margin-top: 1rem;
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.action-button {
  border: 1px solid #22c55e;
  background: transparent;
  color: #d1fae5;
  padding: 0.75rem 0.9rem;
  font-family: inherit;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  cursor: pointer;
}

.action-button:hover {
  background: rgba(34, 197, 94, 0.1);
}

.action-button.danger {
  border-color: #f59e0b;
  color: #fde68a;
}

@media (max-width: 900px) {
  .action-grid {
    grid-template-columns: 1fr;
  }
}
</style>
