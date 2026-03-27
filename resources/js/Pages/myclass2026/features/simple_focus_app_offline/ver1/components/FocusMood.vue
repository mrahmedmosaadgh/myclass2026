<template>
  <Teleport to="body">
    <div v-if="visible" class="focus-mood-overlay" @click.self="exitFocusMood">
      <div class="focus-mood-content">
        <!-- Task Title -->
        <h1 class="focus-task-title">{{ taskTitle }}</h1>
        
        <!-- Timer Display -->
        <div class="focus-timer-display" :class="timerColorClass">
          {{ timerLabel }}
        </div>
        
        <!-- Progress Bar -->
        <div class="focus-progress-bar">
          <div 
            class="focus-progress-fill" 
            :class="timerColorClass"
            :style="{ width: `${progress}%` }"
          />
        </div>
        
        <!-- Last Minute Warning -->
        <div v-if="isLastMinute" class="focus-last-minute">
          ⚠️ FINAL MINUTE ⚠️
        </div>
        
        <!-- Minimal Controls -->
        <div class="focus-controls">
          <button class="focus-control-btn" @click="exitFocusMood">
            EXIT FOCUS [ESC]
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  visible: Boolean,
  taskTitle: String,
  timerLabel: String,
  progress: Number,
  remainingSeconds: Number,
  timerStatus: String,
});

const emit = defineEmits(['exit']);

// Computed properties for dynamic styling
const isLastMinute = computed(() => props.remainingSeconds <= 60 && props.remainingSeconds > 0);

const timerColorClass = computed(() => {
  if (props.timerStatus === 'completed') return 'timer-complete';
  if (isLastMinute.value) return 'timer-last-minute';
  
  const percentage = props.progress;
  if (percentage >= 66) return 'timer-green';
  if (percentage >= 33) return 'timer-yellow';
  return 'timer-red';
});

// Handle ESC key to exit focus mood
function handleKeydown(e) {
  if (e.key === 'Escape' && props.visible) {
    exitFocusMood();
  }
}

function exitFocusMood() {
  emit('exit');
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<style scoped>
.focus-mood-overlay {
  position: fixed;
  inset: 0;
  background: #000;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  font-family: 'Courier New', Courier, monospace;
}

.focus-mood-content {
  text-align: center;
  max-width: 90vw;
  width: 100%;
}

.focus-task-title {
  font-size: clamp(2rem, 6vw, 4rem);
  color: #f0fdf4;
  margin: 0 0 2rem 0;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-weight: 300;
  text-shadow: 0 0 20px rgba(240, 253, 244, 0.3);
}

.focus-timer-display {
  font-size: clamp(4rem, 15vw, 12rem);
  font-weight: 600;
  letter-spacing: 0.1em;
  margin: 0 0 2rem 0;
  transition: all 0.5s ease;
  text-shadow: 0 0 40px currentColor;
}

.focus-progress-bar {
  width: min(800px, 80vw);
  height: 8px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  overflow: hidden;
  margin: 0 auto 2rem;
}

.focus-progress-fill {
  height: 100%;
  transition: all 0.3s ease;
  border-radius: 4px;
}

.focus-last-minute {
  font-size: clamp(1.2rem, 3vw, 2rem);
  color: #ef4444;
  font-weight: 600;
  letter-spacing: 0.15em;
  margin: 0 0 2rem 0;
  animation: pulse-warning 1s infinite;
  text-shadow: 0 0 20px rgba(239, 68, 68, 0.8);
}

.focus-controls {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
}

.focus-control-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.6);
  padding: 0.8rem 1.5rem;
  font-family: inherit;
  font-size: 0.9rem;
  letter-spacing: 0.1em;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.focus-control-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.9);
}

/* Timer color states */
.timer-green {
  color: #22c55e;
}

.timer-yellow {
  color: #fbbf24;
}

.timer-red {
  color: #ef4444;
}

.timer-last-minute {
  color: #ef4444;
  animation: pulse-timer 1s infinite;
}

.timer-complete {
  color: #4ade80;
  animation: pulse-complete 2s infinite;
}

/* Animations */
@keyframes pulse-warning {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.05);
  }
}

@keyframes pulse-timer {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

@keyframes pulse-complete {
  0%, 100% {
    opacity: 1;
    text-shadow: 0 0 40px currentColor;
  }
  50% {
    opacity: 0.9;
    text-shadow: 0 0 60px currentColor;
  }
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .focus-task-title {
    margin: 0 0 1.5rem 0;
  }
  
  .focus-timer-display {
    margin: 0 0 1.5rem 0;
  }
  
  .focus-progress-bar {
    margin: 0 auto 1.5rem;
  }
  
  .focus-last-minute {
    margin: 0 0 1.5rem 0;
  }
  
  .focus-controls {
    bottom: 1rem;
  }
}
</style>
