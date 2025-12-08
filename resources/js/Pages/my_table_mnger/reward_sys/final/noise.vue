<template>
  <div class="noise-meter" :class="{ listening, warning: isWarning }">
    <div class="meter-container">
      <!-- Circular meter -->
      <div class="circular-meter">
        <svg viewBox="0 0 200 200">
          <circle class="meter-bg" cx="100" cy="100" r="90" stroke-width="20" fill="none" />
          <circle
            class="meter-fill"
            cx="100"
            cy="100"
            r="90"
            stroke-width="20"
            fill="none"
            :stroke="meterColor"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="dashOffset"
            transform="rotate(-90 100 100)"
          />
        </svg>
        <div class="level-text" :class="{ warning: isWarning }">
          {{ Math.round(estimatedDbA) }} <small>dBA</small>
        </div>
      </div>

      <!-- Warning message -->
      <transition name="fade">
        <div v-if="isWarning" class="warning-banner">
          <p>Too Loud!</p>
        </div>
      </transition>

      <!-- Bar meter -->
      <div class="bar-meter">
        <div
          class="bar"
          :style="{ height: barHeight + '%', backgroundColor: barColor }"
        ></div>
        <!-- Threshold line -->
        <div
          class="threshold-line"
          :style="{ bottom: thresholdLinePosition + '%' }"
        ></div>
      </div>

      <div class="info">
        <p v-if="!listening">Click "Start" to begin listening</p>
        <p v-else class="listening-text">Listening...</p>
        <p class="db-value">{{ estimatedDbA.toFixed(0) }} dBA</p>
      </div>

      <!-- Threshold Setting -->
      <div class="threshold-control">
        <label for="threshold">Warning threshold:</label>
        <input
          type="range"
          id="threshold"
          min="50"
          max="100"
          v-model.number="warningThreshold"
          class="slider"
        />
        <span class="threshold-value">{{ warningThreshold }} dBA</span>
      </div>
    </div>

    <button @click="toggleListening" class="control-btn">
      {{ listening ? "Stop" : "Start" }} Listening
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from "vue";

const listening = ref(false);
const volume = ref(-100); // dBFS
const warningThreshold = ref(80); // Default: warn at 80 dBA

let audioContext = null;
let analyser = null;
let microphone = null;
let animationFrame = null;

const circumference = 2 * Math.PI * 90;

// Current approximate dBA (rough calibration for typical mics)
const estimatedDbA = computed(() => {
  return Math.max(20, Math.round(volume.value + 70));
});

// Warning state
const isWarning = computed(() => {
  return listening.value && estimatedDbA.value > warningThreshold.value;
});

// Visual feedback colors
const meterColor = computed(() => {
  if (isWarning.value) return "#ff3b30";
  if (volume.value > -25) return "#ff9500";
  if (volume.value > -40) return "#ffcc00";
  return "#34c759";
});

const barColor = computed(() => {
  return isWarning.value ? "#ff3b30" : meterColor.value;
});

const dashOffset = computed(() => {
  const normalized = Math.min(1, estimatedDbA.value / 110); // 0–110 dBA scale
  return circumference * (1 - normalized);
});

const barHeight = computed(() => {
  return Math.min(100, (estimatedDbA.value / 110) * 100);
});

// Position of the red threshold line on the bar
const thresholdLinePosition = computed(() => {
  return (warningThreshold.value / 110) * 100;
});

const startListening = async () => {
  try {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    analyser = audioContext.createAnalyser();
    analyser.fftSize = 512;
    analyser.smoothingTimeConstant = 0.9;

    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    microphone = audioContext.createMediaStreamSource(stream);
    microphone.connect(analyser);

    listening.value = true;
    loop();
  } catch (err) {
    alert("Microphone access required!");
    console.error(err);
  }
};

const stopListening = () => {
  listening.value = false;
  if (animationFrame) cancelAnimationFrame(animationFrame);
  if (microphone) microphone.disconnect();
  if (audioContext) audioContext.close();
};

const toggleListening = () => {
  listening.value ? stopListening() : startListening();
};

const loop = () => {
  if (!analyser) return;

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

  if (listening.value) {
    animationFrame = requestAnimationFrame(loop);
  }
};

onUnmounted(() => stopListening());
</script>

<style scoped>
.noise-meter {
  font-family: system-ui, sans-serif;
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  background: #121212;
  color: white;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
  transition: all 0.3s;
}

.noise-meter.warning {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0%, 100% { box-shadow: 0 10px 30px rgba(255, 59, 48, 0.4); }
  50% { box-shadow: 0 10px 50px rgba(255, 59, 48, 0.8); }
}

.meter-bg { stroke: #333; }
.meter-fill { stroke-linecap: round; transition: stroke-dashoffset 0.15s ease-out, stroke 0.3s; }

.level-text {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  font-size: 2.8rem;
  font-weight: bold;
}
.level-text.warning { color: #ff3b30; }

.warning-banner {
  position: absolute;
  top: -10px; left: 50%;
  transform: translateX(-50%);
  background: #ff3b30;
  color: white;
  padding: 8px 20px;
  border-radius: 30px;
  font-weight: bold;
  font-size: 1.1rem;
  animation: bounce 0.6s infinite alternate;
}

@keyframes bounce {
  from { transform: translateX(-50%) translateY(0); }
  to { transform: translateX(-50%) translateY(-8px); }
}

.bar-meter {
  position: relative;
  width: 60px;
  height: 200px;
  background: #333;
  margin: 2rem auto;
  border-radius: 30px;
  overflow: hidden;
}

.bar {
  position: absolute;
  bottom: 0; left: 0;
  width: 100%;
  transition: height 0.15s ease-out, background-color 0.3s;
}

.threshold-line {
  position: absolute;
  left: 0; right: 0;
  height: 4px;
  background: #ff3b30;
  opacity: 0.7;
  box-shadow: 0 0 10px #ff3b30;
  pointer-events: none;
}

.threshold-control {
  margin: 1.5rem 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  flex-wrap: wrap;
}

.threshold-control label {
  font-size: 0.95rem;
  min-width: 140px;
}

.slider {
  flex: 1;
  max-width: 200px;
}

.threshold-value {
  font-weight: bold;
  color: #ff3b30;
  min-width: 60px;
}

.control-btn {
  padding: 14px 36px;
  font-size: 1.2rem;
  background: #00ffc3;
  color: black;
  border: none;
  border-radius: 50px;
  cursor: pointer;
  font-weight: bold;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>