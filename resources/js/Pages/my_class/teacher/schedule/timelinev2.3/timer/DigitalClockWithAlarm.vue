<template>  <div class="digital-clock">
    <div class="time">{{ formattedTime }}</div>    <div v-if="alarmActive" class="alarm-indicator">
      <span class="alarm-icon">⏰</span>      <span class="alarm-text">{{ alarmMessage }}</span>
    </div>  </div>
</template>
<script setup>import { ref, onMounted, onUnmounted, computed } from 'vue';
const props = defineProps({
  alarmTime: {    type: String,
    default: null  },
  alarmMessage: {    type: String,
    default: 'Time is up!'  }
});
const emit = defineEmits(['alarm-triggered']);
const currentTime = ref(new Date());const alarmActive = ref(false);
let clockInterval = null;
const formattedTime = computed(() => {  return currentTime.value.toLocaleTimeString([], { 
    hour: '2-digit',     minute: '2-digit', 
    second: '2-digit',    hour12: false 
  });});
const checkAlarm = () => {
  if (!props.alarmTime) return;  
  const now = currentTime.value;  const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
    if (timeStr === props.alarmTime && !alarmActive.value) {
    alarmActive.value = true;    emit('alarm-triggered');
        // Auto-dismiss after 10 seconds
    setTimeout(() => {      alarmActive.value = false;
    }, 10000);  }
};
onMounted(() => {  clockInterval = setInterval(() => {
    currentTime.value = new Date();    checkAlarm();
  }, 1000);});
onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);});
</script>
<style scoped>.digital-clock {
  font-family: 'Courier New', monospace;  background-color: #f0f0f0;
  border-radius: 8px;  padding: 10px;
  display: inline-block;}
.time {
  font-size: 1.5rem;  font-weight: bold;
}
.alarm-indicator {  margin-top: 5px;
  color: #e53e3e;  display: flex;
  align-items: center;  gap: 5px;
  animation: blink 1s infinite;}
@keyframes blink {
  0% { opacity: 1; }  50% { opacity: 0.5; }
  100% { opacity: 1; }
}
</style>
















































