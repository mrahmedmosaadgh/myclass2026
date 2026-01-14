<template>
  <div class="timer-container" :class="timerClass">
    <q-icon name="timer" size="24px" class="q-mr-sm" />
    <div class="timer-display">{{ formattedTime }}</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  remainingSeconds: {
    type: Number,
    required: true
  },
  autoSubmit: {
    type: Function,
    required: true
  }
});

const emit = defineEmits(['time-warning', 'time-expired']);

const timeRemaining = ref(props.remainingSeconds);
let intervalId = null;
const warnings = ref({
  fiveMin: false,
  oneMin: false
});

const formattedTime = computed(() => {
  const minutes = Math.floor(timeRemaining.value / 60);
  const seconds = Math.floor(timeRemaining.value % 60);
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

const timerClass = computed(() => {
  if (timeRemaining.value <= 60) {
    return 'timer-critical';
  } else if (timeRemaining.value <= 300) {
    return 'timer-warning';
  }
  return 'timer-normal';
});

const checkTime = () => {
  timeRemaining.value--;

  if (timeRemaining.value <= 0) {
    clearInterval(intervalId);
    emit('time-expired');
    $q.notify({
      type: 'negative',
      message: 'Time expired! Submitting exam...',
      icon: 'alarm',
      timeout: 3000
    });
    props.autoSubmit();
    return;
  }

  // Warning at 5 minutes
  if (timeRemaining.value === 300 && !warnings.value.fiveMin) {
    warnings.value.fiveMin = true;
    emit('time-warning', 5);
    $q.notify({
      type: 'warning',
      message: '5 minutes remaining',
      icon: 'warning',
      timeout: 5000
    });
  }

  // Warning at 1 minute
  if (timeRemaining.value === 60 && !warnings.value.oneMin) {
    warnings.value.oneMin = true;
    emit('time-warning', 1);
    $q.notify({
      type: 'warning',
      message: '1 minute remaining - please submit soon!',
      icon: 'warning',
      timeout: 5000,
      position: 'top'
    });
  }
};

onMounted(() => {
  intervalId = setInterval(checkTime, 1000);
});

onBeforeUnmount(() => {
  if (intervalId) {
    clearInterval(intervalId);
  }
});
</script>

<style scoped>
.timer-container {
  display: flex;
  align-items: center;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: bold;
  font-size: 18px;
  transition: all 0.3s ease;
}

.timer-display {
  font-family: 'Courier New', monospace;
  font-size: 20px;
}

.timer-normal {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.timer-warning {
  background-color: #fff3e0;
  color: #e65100;
}

.timer-critical {
  background-color: #ffebee;
  color: #c62828;
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}
</style>
