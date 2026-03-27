<script setup>
const props = defineProps({
  taskTitle: {
    type: String,
    default: 'NO ACTIVE TASK',
  },
  timerLabel: {
    type: String,
    default: '00:00',
  },
  timerStatus: {
    type: String,
    default: 'idle',
  },
  progress: {
    type: Number,
    default: 0,
  },
  lastAction: {
    type: String,
    default: 'Ready',
  },
});

const emit = defineEmits(['pause', 'resume', 'reset']);
</script>

<template>
  <section class="timer-panel">
    <div class="timer-head">
      <span class="timer-tag">ACTIVE TASK</span>
      <span class="timer-status">{{ timerStatus.toUpperCase() }}</span>
    </div>

    <h1 class="timer-task">{{ taskTitle }}</h1>

    <div class="timer-display">{{ timerLabel }}</div>
    <div class="timer-ruler">
      <div class="timer-fill" :style="{ width: `${progress}%` }" />
    </div>

    <p class="timer-action">{{ lastAction }}</p>

    <div class="timer-actions">
      <button class="timer-button" @click="emit('pause')">PAUSE</button>
      <button class="timer-button" @click="emit('resume')">RESUME</button>
      <button class="timer-button danger" @click="emit('reset')">RESET</button>
    </div>
  </section>
</template>

<style scoped>
.timer-panel {
  border: 1px solid rgba(34, 197, 94, 0.55);
  background: rgba(0, 0, 0, 0.84);
  padding: 1.25rem;
  text-align: center;
  color: #d1fae5;
  font-family: 'Courier New', Courier, monospace;
}

.timer-head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 0.85rem;
}

.timer-tag,
.timer-status {
  color: #22c55e;
  font-size: 0.8rem;
  letter-spacing: 0.18em;
}

.timer-task {
  margin: 0;
  font-size: clamp(1.4rem, 4vw, 3rem);
  letter-spacing: 0.16em;
  color: #f0fdf4;
  text-transform: uppercase;
  line-height: 1.2;
}

.timer-display {
  margin-top: 0.95rem;
  font-size: clamp(3rem, 12vw, 6rem);
  color: #4ade80;
  text-shadow: 0 0 16px rgba(74, 222, 128, 0.35);
  letter-spacing: 0.12em;
}

.timer-ruler {
  margin: 1rem auto 0;
  width: min(720px, 100%);
  height: 10px;
  border: 1px solid rgba(34, 197, 94, 0.4);
  background: rgba(1, 6, 12, 0.95);
  overflow: hidden;
}

.timer-fill {
  height: 100%;
  background: linear-gradient(90deg, #22c55e, #86efac);
  transition: width 0.3s linear;
}

.timer-action {
  min-height: 1.5rem;
  margin: 0.9rem 0 0;
  color: #86efac;
  letter-spacing: 0.08em;
}

.timer-actions {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.timer-button {
  border: 1px solid #22c55e;
  background: transparent;
  color: #d1fae5;
  padding: 0.8rem 1rem;
  font-family: inherit;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  cursor: pointer;
}

.timer-button:hover {
  background: rgba(34, 197, 94, 0.12);
}

.timer-button.danger {
  border-color: #ef4444;
  color: #fca5a5;
}

.timer-button.danger:hover {
  background: rgba(239, 68, 68, 0.12);
}
</style>
