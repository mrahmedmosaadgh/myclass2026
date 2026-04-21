<script setup>
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';
import { useQuasar } from 'quasar';
import { Html5Qrcode } from 'html5-qrcode';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: 'Scan QR Code' },
  subtitle: { type: String, default: '' },
  allowManualInput: { type: Boolean, default: true },
  initialMode: { type: String, default: 'camera' } // 'camera' | 'manual'
});

const emit = defineEmits(['update:modelValue', 'scanned']);

const $q = useQuasar();

const mode = ref(props.initialMode);
const scanState = ref('idle'); // idle | waiting | received | error
const lastRaw = ref('');
const statusText = ref('');
const manualValue = ref('');

const readerId = computed(() => `qr-scan-dialog-reader-${Math.random().toString(36).slice(2)}`);
let html5QrCode = null;

function close() {
  emit('update:modelValue', false);
}

function setMode(newMode) {
  mode.value = newMode;
}

function resetStatus() {
  scanState.value = props.modelValue ? 'waiting' : 'idle';
  lastRaw.value = '';
  statusText.value = '';
  manualValue.value = '';
}

async function startCamera() {
  scanState.value = 'waiting';
  statusText.value = 'Initializing camera…';

  await nextTick();

  try {
    html5QrCode = new Html5Qrcode(readerId.value);
  } catch (e) {
    scanState.value = 'error';
    statusText.value = 'Failed to initialize scanner.';
    return;
  }

  const qrConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

  const onScanSuccess = (decodedText) => {
    const raw = String(decodedText || '').trim();
    if (!raw) return;

    scanState.value = 'received';
    lastRaw.value = raw;
    statusText.value = 'Received.';
    emit('scanned', raw);
  };

  try {
    await html5QrCode.start({ facingMode: 'environment' }, qrConfig, onScanSuccess);
    statusText.value = 'Waiting for scan…';
  } catch (err) {
    try {
      await html5QrCode.start({ facingMode: 'user' }, qrConfig, onScanSuccess);
      statusText.value = 'Waiting for scan…';
    } catch (err2) {
      scanState.value = 'error';
      statusText.value = 'Camera not found or blocked. Check permissions.';
      $q.notify({ type: 'negative', message: statusText.value, position: 'top' });
    }
  }
}

async function stopCamera() {
  if (!html5QrCode) return;
  try {
    await html5QrCode.stop();
    await html5QrCode.clear();
  } catch (e) {
  } finally {
    html5QrCode = null;
  }
}

function handleManualInput() {
  const raw = String(manualValue.value || '').trim();
  if (!raw) return;

  scanState.value = 'received';
  lastRaw.value = raw;
  statusText.value = 'Received.';
  emit('scanned', raw);

  manualValue.value = '';
}

watch(
  () => props.modelValue,
  async (isOpen) => {
    if (!isOpen) {
      await stopCamera();
      scanState.value = 'idle';
      return;
    }

    resetStatus();

    if (mode.value === 'camera') {
      await startCamera();
    } else {
      scanState.value = 'waiting';
      statusText.value = 'Waiting for input…';
    }
  }
);

watch(mode, async (newMode) => {
  if (!props.modelValue) return;

  resetStatus();

  if (newMode === 'camera') {
    await stopCamera();
    await startCamera();
  } else {
    await stopCamera();
    scanState.value = 'waiting';
    statusText.value = 'Waiting for input…';
  }
});

onUnmounted(async () => {
  await stopCamera();
});
</script>

<template>
  <div v-if="modelValue" class="qr-dialog-backdrop" @click.stop="close">
    <div class="qr-dialog" @click.stop>
      <div class="qr-dialog-header">
        <div style="display:flex; flex-direction:column; gap:2px;">
          <h4 style="margin:0;">{{ title }}</h4>
          <div v-if="subtitle" class="qr-subtitle">{{ subtitle }}</div>
        </div>

        <div style="display:flex; gap:8px; align-items:center;">
          <div class="qr-modes" v-if="allowManualInput">
            <button class="qr-mode-btn" :class="{ active: mode === 'camera' }" @click="setMode('camera')">Camera</button>
            <button class="qr-mode-btn" :class="{ active: mode === 'manual' }" @click="setMode('manual')">Input</button>
          </div>
          <button class="qr-close" @click="close">✕</button>
        </div>
      </div>

      <div class="qr-dialog-body">
        <div v-if="mode === 'camera'" class="qr-camera">
          <div :id="readerId" class="qr-reader"></div>
        </div>

        <div v-else class="qr-manual">
          <input
            v-model="manualValue"
            class="qr-input"
            type="text"
            placeholder="Waiting for scanner input…"
            @keydown.enter.prevent="handleManualInput"
          />
          <button class="qr-submit" @click="handleManualInput">Accept</button>
        </div>

        <div class="qr-status">
          <div v-if="scanState === 'waiting'" class="qr-wait">{{ statusText || 'Waiting…' }}</div>
          <div v-else-if="scanState === 'received'" class="qr-recv">{{ statusText || 'Received.' }}</div>
          <div v-else-if="scanState === 'error'" class="qr-err">{{ statusText || 'Error' }}</div>

          <div v-if="lastRaw" class="qr-line">
            Last: <strong>{{ lastRaw }}</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.qr-dialog-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.qr-dialog {
  background: white;
  width: min(420px, 95vw);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
  display: flex;
  flex-direction: column;
}

.qr-dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.qr-subtitle {
  font-size: 0.8rem;
  color: #64748b;
}

.qr-modes {
  display: flex;
  gap: 6px;
}

.qr-mode-btn {
  background: #e2e8f0;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 6px 10px;
  cursor: pointer;
  font-weight: 700;
  color: #334155;
  font-size: 12px;
}

.qr-mode-btn.active {
  background: #3b82f6;
  border-color: #2563eb;
  color: white;
}

.qr-close {
  background: transparent;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #64748b;
}

.qr-dialog-body {
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.qr-camera {
  width: 100%;
}

.qr-reader {
  width: 100%;
  aspect-ratio: 1;
  background: #000;
  border-radius: 10px;
  overflow: hidden;
}

.qr-manual {
  display: flex;
  gap: 8px;
}

.qr-input {
  flex: 1;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: 10px 12px;
  font-weight: 700;
}

.qr-submit {
  background: #16a34a;
  color: white;
  border: none;
  border-radius: 10px;
  padding: 10px 12px;
  cursor: pointer;
  font-weight: 800;
}

.qr-status {
  padding: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
}

.qr-wait {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px dashed #93c5fd;
  border-radius: 8px;
  padding: 8px;
  font-weight: 800;
}

.qr-recv {
  background: #dcfce3;
  color: #15803d;
  border: 1px solid #86efac;
  border-radius: 8px;
  padding: 8px;
  font-weight: 800;
}

.qr-err {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 8px;
  font-weight: 800;
}

.qr-line {
  margin-top: 8px;
  font-size: 0.85rem;
  color: #0f172a;
  word-break: break-all;
}
</style>
