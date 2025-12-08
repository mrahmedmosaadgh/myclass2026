<template>
  <q-dialog
    v-model="dialogModel"
    persistent
    transition-show="scale"
    transition-hide="scale"
  >
    <q-card style="min-width: 400px; max-width: 500px">
      <!-- Header -->
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">
          <q-icon name="mic" class="q-mr-sm" />
          Voice Recorder
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <!-- Recording Interface -->
      <q-card-section class="text-center">
        <!-- Recording Status -->
        <div class="recording-status q-mb-lg">
          <div
            class="recording-indicator"
            :class="{ 'recording': isRecording, 'paused': isPaused }"
          >
            <q-icon
              :name="getStatusIcon()"
              :size="isRecording ? '4em' : '3em'"
              :color="getStatusColor()"
            />
          </div>
          
          <div class="q-mt-md">
            <div class="text-h6">{{ getStatusText() }}</div>
            <div class="text-subtitle2 text-grey-6">
              {{ formatTime(recordingTime) }}
            </div>
          </div>
        </div>

        <!-- Waveform Visualization -->
        <div
          v-if="isRecording || recordedAudio"
          class="waveform-container q-mb-md"
        >
          <canvas
            ref="waveformCanvas"
            class="waveform-canvas"
          ></canvas>
        </div>

        <!-- Recording Controls -->
        <div class="recording-controls q-mb-md">
          <q-btn-group>
            <!-- Record/Stop Button -->
            <q-btn
              v-if="!isRecording && !recordedAudio"
              color="negative"
              icon="fiber_manual_record"
              label="Start Recording"
              size="lg"
              @click="startRecording"
              :disable="!hasPermission"
            />
            
            <q-btn
              v-else-if="isRecording && !isPaused"
              color="orange"
              icon="pause"
              label="Pause"
              size="lg"
              @click="pauseRecording"
            />
            
            <q-btn
              v-else-if="isRecording && isPaused"
              color="negative"
              icon="fiber_manual_record"
              label="Resume"
              size="lg"
              @click="resumeRecording"
            />
            
            <q-btn
              v-if="isRecording"
              color="grey-7"
              icon="stop"
              label="Stop"
              size="lg"
              @click="stopRecording"
              class="q-ml-sm"
            />
          </q-btn-group>
        </div>

        <!-- Playback Controls -->
        <div
          v-if="recordedAudio && !isRecording"
          class="playback-controls q-mb-md"
        >
          <q-btn-group>
            <q-btn
              :icon="isPlaying ? 'pause' : 'play_arrow'"
              :label="isPlaying ? 'Pause' : 'Play'"
              color="primary"
              @click="togglePlayback"
            />
            <q-btn
              icon="replay"
              label="Re-record"
              color="orange"
              @click="resetRecording"
            />
          </q-btn-group>
        </div>

        <!-- Permission Request -->
        <div
          v-if="!hasPermission && showPermissionRequest"
          class="permission-request q-mb-md"
        >
          <q-banner class="bg-orange-1 text-orange-8">
            <template v-slot:avatar>
              <q-icon name="mic_off" />
            </template>
            Microphone access is required to record voice notes.
            <template v-slot:action>
              <q-btn
                flat
                color="orange"
                label="Request Permission"
                @click="requestPermission"
              />
            </template>
          </q-banner>
        </div>

        <!-- Recording Quality Settings -->
        <div class="recording-settings q-mb-md">
          <q-expansion-item
            icon="settings"
            label="Recording Settings"
            header-class="text-grey-7"
          >
            <div class="q-pa-md">
              <div class="q-mb-sm">
                <label class="text-subtitle2">Quality:</label>
                <q-select
                  v-model="recordingQuality"
                  :options="qualityOptions"
                  outlined
                  dense
                  emit-value
                  map-options
                />
              </div>
              
              <div class="q-mb-sm">
                <label class="text-subtitle2">Format:</label>
                <q-select
                  v-model="recordingFormat"
                  :options="formatOptions"
                  outlined
                  dense
                  emit-value
                  map-options
                />
              </div>
            </div>
          </q-expansion-item>
        </div>
      </q-card-section>

      <!-- Actions -->
      <q-card-actions align="right">
        <q-btn
          flat
          label="Cancel"
          color="grey-7"
          @click="cancelRecording"
        />
        <q-btn
          :label="recordedAudio ? 'Save Recording' : 'Close'"
          color="primary"
          @click="saveRecording"
          :disable="!recordedAudio"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useQuasar } from 'quasar';

// Props and Emits
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'voice-recorded']);

// Composables
const $q = useQuasar();

// State
const dialogModel = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const isRecording = ref(false);
const isPaused = ref(false);
const isPlaying = ref(false);
const recordingTime = ref(0);
const hasPermission = ref(false);
const showPermissionRequest = ref(false);
const recordedAudio = ref(null);
const mediaRecorder = ref(null);
const audioChunks = ref([]);
const stream = ref(null);
const audioContext = ref(null);
const analyser = ref(null);
const waveformCanvas = ref(null);
const animationFrame = ref(null);

// Settings
const recordingQuality = ref('high');
const recordingFormat = ref('webm');

const qualityOptions = [
  { label: 'Low (8kHz)', value: 'low' },
  { label: 'Medium (16kHz)', value: 'medium' },
  { label: 'High (44kHz)', value: 'high' }
];

const formatOptions = [
  { label: 'WebM', value: 'webm' },
  { label: 'MP4', value: 'mp4' },
  { label: 'WAV', value: 'wav' }
];

// Computed
const getStatusIcon = () => {
  if (isRecording.value && !isPaused.value) return 'fiber_manual_record';
  if (isPaused.value) return 'pause';
  if (recordedAudio.value) return 'check_circle';
  return 'mic';
};

const getStatusColor = () => {
  if (isRecording.value && !isPaused.value) return 'negative';
  if (isPaused.value) return 'orange';
  if (recordedAudio.value) return 'positive';
  return 'grey-7';
};

const getStatusText = () => {
  if (isRecording.value && !isPaused.value) return 'Recording...';
  if (isPaused.value) return 'Paused';
  if (recordedAudio.value) return 'Recording Complete';
  return 'Ready to Record';
};

// Methods
const requestPermission = async () => {
  try {
    const mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
    hasPermission.value = true;
    showPermissionRequest.value = false;
    
    // Stop the stream immediately as we just needed permission
    mediaStream.getTracks().forEach(track => track.stop());
    
    $q.notify({
      type: 'positive',
      message: 'Microphone access granted',
      position: 'top'
    });
  } catch (error) {
    console.error('Permission denied:', error);
    $q.notify({
      type: 'negative',
      message: 'Microphone access denied',
      position: 'top'
    });
  }
};

const startRecording = async () => {
  try {
    stream.value = await navigator.mediaDevices.getUserMedia({
      audio: {
        sampleRate: getQualitySampleRate(),
        channelCount: 1,
        echoCancellation: true,
        noiseSuppression: true
      }
    });

    // Setup audio context for visualization
    audioContext.value = new (window.AudioContext || window.webkitAudioContext)();
    analyser.value = audioContext.value.createAnalyser();
    const source = audioContext.value.createMediaStreamSource(stream.value);
    source.connect(analyser.value);
    analyser.value.fftSize = 256;

    // Setup media recorder
    const options = {
      mimeType: getMimeType(),
      audioBitsPerSecond: getQualityBitrate()
    };

    mediaRecorder.value = new MediaRecorder(stream.value, options);
    audioChunks.value = [];

    mediaRecorder.value.ondataavailable = (event) => {
      if (event.data.size > 0) {
        audioChunks.value.push(event.data);
      }
    };

    mediaRecorder.value.onstop = () => {
      const audioBlob = new Blob(audioChunks.value, { type: getMimeType() });
      recordedAudio.value = audioBlob;
      
      // Cleanup
      if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
      }
      if (audioContext.value) {
        audioContext.value.close();
      }
    };

    mediaRecorder.value.start(100); // Collect data every 100ms
    isRecording.value = true;
    isPaused.value = false;
    recordingTime.value = 0;

    // Start timer and visualization
    startTimer();
    startVisualization();

  } catch (error) {
    console.error('Error starting recording:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to start recording',
      position: 'top'
    });
  }
};

const pauseRecording = () => {
  if (mediaRecorder.value && isRecording.value) {
    mediaRecorder.value.pause();
    isPaused.value = true;
  }
};

const resumeRecording = () => {
  if (mediaRecorder.value && isPaused.value) {
    mediaRecorder.value.resume();
    isPaused.value = false;
  }
};

const stopRecording = () => {
  if (mediaRecorder.value && isRecording.value) {
    mediaRecorder.value.stop();
    isRecording.value = false;
    isPaused.value = false;
    
    if (animationFrame.value) {
      cancelAnimationFrame(animationFrame.value);
    }
  }
};

const resetRecording = () => {
  recordedAudio.value = null;
  recordingTime.value = 0;
  audioChunks.value = [];
};

const togglePlayback = () => {
  if (!recordedAudio.value) return;

  if (isPlaying.value) {
    // Stop playback
    isPlaying.value = false;
  } else {
    // Start playback
    const audio = new Audio(URL.createObjectURL(recordedAudio.value));
    audio.play();
    isPlaying.value = true;
    
    audio.onended = () => {
      isPlaying.value = false;
    };
  }
};

const saveRecording = () => {
  if (recordedAudio.value) {
    emit('voice-recorded', recordedAudio.value);
  }
  dialogModel.value = false;
};

const cancelRecording = () => {
  if (isRecording.value) {
    stopRecording();
  }
  resetRecording();
  dialogModel.value = false;
};

const startTimer = () => {
  const startTime = Date.now();
  const timer = setInterval(() => {
    if (!isRecording.value) {
      clearInterval(timer);
      return;
    }
    if (!isPaused.value) {
      recordingTime.value = Math.floor((Date.now() - startTime) / 1000);
    }
  }, 1000);
};

const startVisualization = () => {
  if (!analyser.value || !waveformCanvas.value) return;

  const canvas = waveformCanvas.value;
  const ctx = canvas.getContext('2d');
  const bufferLength = analyser.value.frequencyBinCount;
  const dataArray = new Uint8Array(bufferLength);

  const draw = () => {
    if (!isRecording.value) return;

    animationFrame.value = requestAnimationFrame(draw);
    
    analyser.value.getByteFrequencyData(dataArray);
    
    ctx.fillStyle = '#f5f5f5';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    const barWidth = (canvas.width / bufferLength) * 2.5;
    let barHeight;
    let x = 0;
    
    for (let i = 0; i < bufferLength; i++) {
      barHeight = (dataArray[i] / 255) * canvas.height;
      
      ctx.fillStyle = `rgb(25, 118, 210)`;
      ctx.fillRect(x, canvas.height - barHeight, barWidth, barHeight);
      
      x += barWidth + 1;
    }
  };
  
  draw();
};

const getQualitySampleRate = () => {
  const rates = { low: 8000, medium: 16000, high: 44100 };
  return rates[recordingQuality.value] || 44100;
};

const getQualityBitrate = () => {
  const rates = { low: 32000, medium: 64000, high: 128000 };
  return rates[recordingQuality.value] || 128000;
};

const getMimeType = () => {
  const types = {
    webm: 'audio/webm',
    mp4: 'audio/mp4',
    wav: 'audio/wav'
  };
  return types[recordingFormat.value] || 'audio/webm';
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

// Lifecycle
onMounted(async () => {
  // Check for microphone permission
  try {
    const permissions = await navigator.permissions.query({ name: 'microphone' });
    hasPermission.value = permissions.state === 'granted';
    
    if (permissions.state === 'prompt') {
      showPermissionRequest.value = true;
    }
  } catch (error) {
    showPermissionRequest.value = true;
  }

  // Setup canvas
  await nextTick();
  if (waveformCanvas.value) {
    const canvas = waveformCanvas.value;
    canvas.width = 300;
    canvas.height = 100;
  }
});

onUnmounted(() => {
  if (isRecording.value) {
    stopRecording();
  }
  if (animationFrame.value) {
    cancelAnimationFrame(animationFrame.value);
  }
});
</script>

<style scoped>
.recording-indicator {
  transition: all 0.3s ease;
}

.recording-indicator.recording {
  animation: pulse 1.5s infinite;
}

.recording-indicator.paused {
  opacity: 0.6;
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.7; }
  100% { transform: scale(1); opacity: 1; }
}

.waveform-container {
  height: 100px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
  background-color: #f5f5f5;
}

.waveform-canvas {
  width: 100%;
  height: 100%;
}

.recording-controls .q-btn {
  min-width: 120px;
}

.playback-controls .q-btn {
  min-width: 100px;
}
</style>
