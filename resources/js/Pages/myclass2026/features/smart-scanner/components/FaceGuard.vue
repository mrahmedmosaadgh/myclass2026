<script setup>
import { onMounted, ref, watch } from 'vue';
import * as faceapi from 'face-api.js';

const props = defineProps({
  videoElement: {
    type: Object, // HTMLVideoElement
    default: null
  },
  active: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['face-detected', 'face-lost', 'error', 'ready']);

const isLoaded = ref(false);
const isDetecting = ref(false);
const detectionInterval = ref(null);

onMounted(async () => {
  try {
    // Models should be downloaded and placed in public/models
    // We only load tiny face detector for speed
    await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
    isLoaded.value = true;
    emit('ready');
    
    if (props.active && props.videoElement) {
      startDetection();
    }
  } catch (e) {
    console.warn("Face-api models missing! Download weights to public/models", e);
    emit('error', 'Models not found');
  }
});

const startDetection = () => {
  if (detectionInterval.value) clearInterval(detectionInterval.value);
  
  detectionInterval.value = setInterval(async () => {
    if (!isLoaded.value || !props.active || !props.videoElement) return;
    if (props.videoElement.paused || props.videoElement.ended) return;
    
    const detection = await faceapi.detectSingleFace(
      props.videoElement, 
      new faceapi.TinyFaceDetectorOptions()
    );
    
    if (detection) {
      if (!isDetecting.value) {
        isDetecting.value = true;
        emit('face-detected', detection);
      }
    } else {
      if (isDetecting.value) {
        isDetecting.value = false;
        emit('face-lost');
      }
    }
  }, 500); // Check every 500ms
};

const stopDetection = () => {
  if (detectionInterval.value) clearInterval(detectionInterval.value);
  isDetecting.value = false;
};

watch(() => props.active, (newVal) => {
  if (newVal) {
    startDetection();
  } else {
    stopDetection();
  }
});

watch(() => props.videoElement, (newVal) => {
  if (newVal && props.active) {
    startDetection();
  }
});

defineExpose({ isDetecting });
</script>

<template>
  <div v-if="!isLoaded" class="bg-blue-50 text-blue-800 text-xs px-2 py-1 rounded inline-flex items-center">
    <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-blue-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    Loading face detector...
  </div>
</template>
