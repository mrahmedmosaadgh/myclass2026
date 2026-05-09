<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';

const props = defineProps({
  cameraId: {
    type: String,
    default: null
  },
  paused: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['decode', 'error', 'init']);

const readerId = 'qr-reader-elem-' + Math.floor(Math.random() * 1000000);
let html5QrCode = null;
const isScanning = ref(false);

onMounted(() => {
  html5QrCode = new Html5Qrcode(readerId);
  const config = { fps: 10, qrbox: { width: 300, height: 300 } };
  
  const camConfig = props.cameraId ? props.cameraId : { facingMode: "environment" };

  html5QrCode.start(
    camConfig,
    config,
    (decodedText) => {
      if (!props.paused) {
        emit('decode', decodedText);
      }
    },
    (errorMessage) => {
      // Fires constantly when nothing is found
    }
  ).then(() => {
    isScanning.value = true;
    emit('init', html5QrCode);
  }).catch(err => {
    emit('error', err);
  });
});

onUnmounted(() => {
  if (html5QrCode && html5QrCode.isScanning) {
    html5QrCode.stop().then(() => {
      html5QrCode.clear();
    });
  }
});
</script>

<template>
  <div class="relative w-full h-full bg-gray-900 rounded-xl overflow-hidden shadow-inner">
    <div :id="readerId" class="w-full h-full"></div>
    
    <div v-if="paused" class="absolute inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center">
      <div class="bg-white/90 text-gray-900 px-4 py-2 rounded-lg shadow font-semibold">
        Scanner Paused
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Override default html5-qrcode styling which lacks constraints */
:deep([id^="qr-reader-elem-"]) {
  width: 100% !important;
  border: none !important;
}
:deep([id^="qr-reader-elem-"] video) {
  object-fit: cover !important;
  width: 100% !important;
  height: 100% !important;
}
</style>
