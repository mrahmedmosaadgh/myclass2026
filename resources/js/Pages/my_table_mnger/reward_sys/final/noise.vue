<template>
  <div class="noise-meter-simple" @click="toggleListening" :class="{ 'is-listening': listening }">
    <!-- Bar Container -->
    <div class="bar-container">
      <!-- Background Track -->
      <div class="bar-track"></div>
      
      <!-- Safe Level Bar -->
      <div 
        class="bar-fill" 
        :style="{ height: barHeight + '%', backgroundColor: barColor }"
      ></div>
      
      <!-- Threshold Line -->
      <div 
        class="threshold-marker" 
        :style="{ bottom: thresholdLinePosition + '%' }"
      ></div>
    </div>

    <!-- Info Text -->
    <div class="meter-info">
      <div class="status-text">{{ listening ? "Listening..." : "Paused" }}</div>
      <div class="db-text">{{ listening ? Math.round(estimatedDbA) : '--' }} dBA</div>
    </div>
    
    <!-- Warning Overlay -->
    <div v-if="isWarning" class="warning-flasher"></div>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted, watch } from "vue";

const listening = ref(false);
const volume = ref(-100); // dBFS
const warningThreshold = ref(50); // Default: warn at 50 dBA (sensory friendly)

let audioContext = null;
let analyser = null;
let microphone = null;
let animationFrame = null;

// Current approximate dBA (rough calibration for typical mics)
const estimatedDbA = computed(() => {
  return Math.max(20, Math.round(volume.value + 70));
});

// Warning state
const isWarning = computed(() => {
  return listening.value && estimatedDbA.value > warningThreshold.value;
});

// Colors
const barColor = computed(() => {
  if (isWarning.value) return "#ff3b30";
  if (estimatedDbA.value > warningThreshold.value - 5) return "#ffcc00"; 
  return "#34c759";
});

const barHeight = computed(() => {
  // Map 20dBA (min) to 110dBA (max) onto 0-100%
  const minDb = 20;
  const maxDb = 110;
  const percent = ((estimatedDbA.value - minDb) / (maxDb - minDb)) * 100;
  return Math.max(0, Math.min(100, percent));
});

const thresholdLinePosition = computed(() => {
  const minDb = 20;
  const maxDb = 110;
  const percent = ((warningThreshold.value - minDb) / (maxDb - minDb)) * 100;
  return Math.max(0, Math.min(100, percent));
});

const startListening = async () => {
  try {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    analyser = audioContext.createAnalyser();
    analyser.fftSize = 512;
    analyser.smoothingTimeConstant = 0.8; // More responsive

    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    microphone = audioContext.createMediaStreamSource(stream);
    microphone.connect(analyser);

    listening.value = true;
    loop();
  } catch (err) {
    console.error("Microphone access denied or error:", err);
    listenError.value = true;
  }
};

const stopListening = () => {
  listening.value = false;
  if (animationFrame) cancelAnimationFrame(animationFrame);
  if (microphone) {
    microphone.disconnect();
    microphone = null;
  }
  if (audioContext) {
    audioContext.close();
    audioContext = null;
  }
};

const toggleListening = () => {
  listening.value ? stopListening() : startListening();
};

const loop = () => {
  if (!analyser || !listening.value) return;

  const bufferLength = analyser.frequencyBinCount;
  const dataArray = new Uint8Array(bufferLength);
  analyser.getByteTimeDomainData(dataArray);

  let sum = 0;
  for (let i = 0; i < bufferLength; i++) {
    const v = (dataArray[i] - 128) / 128;
    sum += v * v;
  }
  const rms = Math.sqrt(sum / bufferLength);
  volume.value = rms > 0 ? 20 * Math.log10(rms) : -100;

  animationFrame = requestAnimationFrame(loop);
};

onUnmounted(() => stopListening());
</script>

<style scoped>
.noise-meter-simple {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 10px;
  border-radius: 20px;
  transition: background-color 0.2s;
  width: fit-content;
  margin: 0 auto;
}

.noise-meter-simple:hover {
  background-color: rgba(0, 0, 0, 0.03);
}

.bar-container {
  position: relative;
  width: 40px;
  height: 120px;
  background-color: #2c2c2e; /* Dark gray background like image */
  border-radius: 999px;
  overflow: hidden;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
}

.bar-track {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #3a3a3c;
  opacity: 0.1;
}

.bar-fill {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: #34c759;
  transition: height 0.1s linear, background-color 0.2s;
  border-radius: 0 0 999px 999px;
}

/* Threshold Line */
.threshold-marker {
  position: absolute;
  left: 0;
  right: 0;
  height: 2px;
  background-color: #ff453a;
  box-shadow: 0 0 4px rgba(255, 69, 58, 0.8);
  z-index: 10;
  opacity: 0.8;
}

.meter-info {
  text-align: center;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.status-text {
  font-size: 0.75rem;
  color: #8e8e93;
  margin-bottom: 2px;
}

.db-text {
  font-size: 0.9rem;
  font-weight: 600;
  color: #333;
  font-variant-numeric: tabular-nums;
}

/* Status variants */
.is-listening .status-text {
  color: #34c759;
}

/* Global warning flash overlay */
.warning-flasher {
  position: absolute;
  inset: 0;
  border-radius: 20px;
  background-color: rgba(255, 59, 48, 0.2);
  pointer-events: none;
  animation: flash 0.5s infinite alternate;
  display: none; /* Hidden by default layout, can be enabled if container position relative */
}

@keyframes flash {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>