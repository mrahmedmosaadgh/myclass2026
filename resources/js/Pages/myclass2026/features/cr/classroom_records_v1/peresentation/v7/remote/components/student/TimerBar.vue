<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
  endTime: { type: Number, required: true }
});

const timeLeft = ref(0);
const duration = ref(0);
let interval = null;

const percentage = computed(() => {
  if (duration.value === 0) return 100;
  return Math.max(0, (timeLeft.value / duration.value) * 100);
});

const barColor = computed(() => {
  if (percentage.value < 20) return '#ef4444'; // Red
  if (percentage.value < 50) return '#f59e0b'; // Amber
  return '#10b981'; // Green
});

const updateTimer = () => {
  const now = Date.now();
  timeLeft.value = Math.max(0, props.endTime - now);
  
  if (timeLeft.value === 0) {
    clearInterval(interval);
  }
};

onMounted(() => {
  const now = Date.now();
  duration.value = props.endTime - now;
  updateTimer();
  interval = setInterval(updateTimer, 100);
});

onUnmounted(() => {
  clearInterval(interval);
});
</script>

<template>
  <div class="timer-container">
    <div class="timer-meta">
      <span class="label">Time Remaining</span>
      <span class="seconds">{{ Math.ceil(timeLeft / 1000) }}s</span>
    </div>
    <div class="timer-bar-bg">
      <div 
        class="timer-bar-fill"
        :style="{ 
          width: percentage + '%',
          backgroundColor: barColor
        }"
      ></div>
    </div>
  </div>
</template>

<style scoped>
.timer-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.timer-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.timer-meta .label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
}

.timer-meta .seconds {
  font-size: 1rem;
  font-weight: 800;
  color: #1e293b;
}

.timer-bar-bg {
  width: 100%;
  height: 10px;
  background: #e2e8f0;
  border-radius: 5px;
  overflow: hidden;
}

.timer-bar-fill {
  height: 100%;
  transition: width 0.1s linear, background-color 0.3s;
}
</style>
