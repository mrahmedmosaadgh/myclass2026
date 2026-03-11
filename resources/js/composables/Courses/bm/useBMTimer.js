import { ref, onUnmounted } from 'vue';

export function useBMTimer() {
  const timeElapsedMs = ref(0);
  const isRunning = ref(false);
  let intervalId = null;

  const startTimer = () => {
    if (isRunning.value) return;
    isRunning.value = true;
    const startTime = Date.now() - timeElapsedMs.value;
    
    intervalId = setInterval(() => {
      timeElapsedMs.value = Date.now() - startTime;
    }, 100); // update every 100ms
  };

  const stopTimer = () => {
    isRunning.value = false;
    clearInterval(intervalId);
  };

  const resetTimer = () => {
    stopTimer();
    timeElapsedMs.value = 0;
  };

  onUnmounted(() => {
    stopTimer();
  });

  return {
    timeElapsedMs,
    isRunning,
    startTimer,
    stopTimer,
    resetTimer
  };
}
