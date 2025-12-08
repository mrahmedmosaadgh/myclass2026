<template>
  <div class="voice-player">
    <div class="row items-center q-gutter-sm">
      <!-- Play/Pause Button -->
      <q-btn
        :icon="isPlaying ? 'pause' : 'play_arrow'"
        round
        color="primary"
        size="sm"
        @click="togglePlay"
        :loading="loading"
      />

      <!-- Progress Bar -->
      <div class="col">
        <q-linear-progress
          :value="progress"
          color="primary"
          track-color="grey-3"
          size="8px"
          rounded
          class="cursor-pointer"
          @click="seekTo"
        />
      </div>

      <!-- Time Display -->
      <div class="text-caption text-grey-7 time-display">
        {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
      </div>

      <!-- Volume Control -->
      <q-btn
        :icon="isMuted ? 'volume_off' : 'volume_up'"
        flat
        round
        size="sm"
        @click="toggleMute"
      />

      <!-- Download Button -->
      <q-btn
        icon="download"
        flat
        round
        size="sm"
        @click="downloadAudio"
      >
        <q-tooltip>Download Audio</q-tooltip>
      </q-btn>

      <!-- Speed Control -->
      <q-btn
        flat
        round
        size="sm"
        @click="cycleSpeed"
        class="speed-btn"
      >
        {{ playbackRate }}x
        <q-tooltip>Playback Speed</q-tooltip>
      </q-btn>
    </div>

    <!-- Waveform Visualization (Optional) -->
    <div
      v-if="showWaveform"
      class="waveform-container q-mt-sm"
      ref="waveformRef"
    >
      <canvas
        ref="canvasRef"
        class="waveform-canvas"
        @click="seekToWaveform"
      ></canvas>
    </div>

    <!-- Audio Element -->
    <audio
      ref="audioRef"
      :src="audioUrl"
      preload="metadata"
      @loadedmetadata="onLoadedMetadata"
      @timeupdate="onTimeUpdate"
      @ended="onEnded"
      @error="onError"
    ></audio>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useQuasar } from 'quasar';

// Props
const props = defineProps({
  audioUrl: {
    type: String,
    required: true
  },
  showWaveform: {
    type: Boolean,
    default: false
  },
  autoplay: {
    type: Boolean,
    default: false
  }
});

// Composables
const $q = useQuasar();

// Refs
const audioRef = ref(null);
const canvasRef = ref(null);
const waveformRef = ref(null);

// State
const isPlaying = ref(false);
const loading = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const isMuted = ref(false);
const playbackRate = ref(1);
const waveformData = ref([]);

// Available playback speeds
const playbackSpeeds = [0.5, 0.75, 1, 1.25, 1.5, 2];

// Computed
const progress = computed(() => {
  return duration.value > 0 ? currentTime.value / duration.value : 0;
});

// Methods
const togglePlay = async () => {
  if (!audioRef.value) return;

  try {
    if (isPlaying.value) {
      audioRef.value.pause();
    } else {
      loading.value = true;
      await audioRef.value.play();
    }
  } catch (error) {
    console.error('Error playing audio:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to play audio',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
};

const seekTo = (event) => {
  if (!audioRef.value || duration.value === 0) return;

  const rect = event.target.getBoundingClientRect();
  const clickX = event.clientX - rect.left;
  const percentage = clickX / rect.width;
  const newTime = percentage * duration.value;
  
  audioRef.value.currentTime = newTime;
};

const seekToWaveform = (event) => {
  if (!audioRef.value || duration.value === 0) return;

  const rect = canvasRef.value.getBoundingClientRect();
  const clickX = event.clientX - rect.left;
  const percentage = clickX / rect.width;
  const newTime = percentage * duration.value;
  
  audioRef.value.currentTime = newTime;
};

const toggleMute = () => {
  if (!audioRef.value) return;
  
  isMuted.value = !isMuted.value;
  audioRef.value.muted = isMuted.value;
};

const cycleSpeed = () => {
  const currentIndex = playbackSpeeds.indexOf(playbackRate.value);
  const nextIndex = (currentIndex + 1) % playbackSpeeds.length;
  playbackRate.value = playbackSpeeds[nextIndex];
  
  if (audioRef.value) {
    audioRef.value.playbackRate = playbackRate.value;
  }
};

const downloadAudio = () => {
  const link = document.createElement('a');
  link.href = props.audioUrl;
  link.download = `voice-note-${Date.now()}.wav`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const formatTime = (seconds) => {
  if (!seconds || isNaN(seconds)) return '0:00';
  
  const mins = Math.floor(seconds / 60);
  const secs = Math.floor(seconds % 60);
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const drawWaveform = () => {
  if (!canvasRef.value || !waveformData.value.length) return;

  const canvas = canvasRef.value;
  const ctx = canvas.getContext('2d');
  const width = canvas.width;
  const height = canvas.height;

  // Clear canvas
  ctx.clearRect(0, 0, width, height);

  // Draw waveform
  ctx.fillStyle = '#1976d2';
  const barWidth = width / waveformData.value.length;

  waveformData.value.forEach((amplitude, index) => {
    const barHeight = amplitude * height;
    const x = index * barWidth;
    const y = (height - barHeight) / 2;
    
    ctx.fillRect(x, y, barWidth - 1, barHeight);
  });

  // Draw progress overlay
  if (progress.value > 0) {
    ctx.fillStyle = 'rgba(25, 118, 210, 0.3)';
    ctx.fillRect(0, 0, width * progress.value, height);
  }
};

const generateWaveformData = async () => {
  if (!props.showWaveform || !audioRef.value) return;

  try {
    // This is a simplified waveform generation
    // In a real app, you'd use Web Audio API to analyze the audio
    const sampleCount = 100;
    const data = [];
    
    for (let i = 0; i < sampleCount; i++) {
      // Generate random waveform data for demo
      data.push(Math.random() * 0.8 + 0.1);
    }
    
    waveformData.value = data;
    
    await nextTick();
    drawWaveform();
  } catch (error) {
    console.error('Error generating waveform:', error);
  }
};

// Event Handlers
const onLoadedMetadata = () => {
  duration.value = audioRef.value.duration;
  
  if (props.autoplay) {
    togglePlay();
  }
  
  if (props.showWaveform) {
    generateWaveformData();
  }
};

const onTimeUpdate = () => {
  currentTime.value = audioRef.value.currentTime;
  
  if (props.showWaveform) {
    drawWaveform();
  }
};

const onEnded = () => {
  isPlaying.value = false;
  currentTime.value = 0;
  
  if (props.showWaveform) {
    drawWaveform();
  }
};

const onError = (error) => {
  console.error('Audio error:', error);
  loading.value = false;
  $q.notify({
    type: 'negative',
    message: 'Error loading audio file',
    position: 'top'
  });
};

// Watchers
watch(() => audioRef.value?.paused, (paused) => {
  isPlaying.value = !paused;
});

watch(() => props.audioUrl, () => {
  if (audioRef.value) {
    audioRef.value.load();
  }
});

// Lifecycle
onMounted(() => {
  if (canvasRef.value && props.showWaveform) {
    const canvas = canvasRef.value;
    canvas.width = canvas.offsetWidth;
    canvas.height = 60;
  }
});

onUnmounted(() => {
  if (audioRef.value) {
    audioRef.value.pause();
  }
});
</script>

<style scoped>
.voice-player {
  background-color: #f5f5f5;
  border-radius: 8px;
  padding: 12px;
  border: 1px solid #e0e0e0;
}

.time-display {
  min-width: 80px;
  text-align: center;
}

.speed-btn {
  min-width: 40px;
  font-size: 12px;
  font-weight: bold;
}

.waveform-container {
  height: 60px;
  border-radius: 4px;
  overflow: hidden;
  background-color: #fafafa;
  border: 1px solid #e0e0e0;
}

.waveform-canvas {
  width: 100%;
  height: 100%;
  cursor: pointer;
}

.q-linear-progress {
  cursor: pointer;
}
</style>
