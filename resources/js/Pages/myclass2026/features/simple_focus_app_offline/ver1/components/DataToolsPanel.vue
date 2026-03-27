<script setup>
import { ref } from 'vue';

const props = defineProps({
  canInstall: {
    type: Boolean,
    default: false,
  },
  isInstalled: {
    type: Boolean,
    default: false,
  },
  isImporting: {
    type: Boolean,
    default: false,
  },
  serviceWorkerStatus: {
    type: String,
    default: 'idle',
  },
});

const emit = defineEmits(['export', 'clear', 'install', 'import-file']);

const fileInput = ref(null);

function openPicker() {
  fileInput.value?.click();
}

function handleFileChange(event) {
  const [file] = event.target.files || [];
  if (file) {
    emit('import-file', file);
  }

  event.target.value = '';
}
</script>

<template>
  <section class="tools-panel">
    <div class="tools-head">
      <span class="tools-tag">TOOLS</span>
      <span class="tools-status">SW: {{ serviceWorkerStatus.toUpperCase() }}</span>
    </div>

    <div class="tools-actions">
      <button class="tool-button" @click="emit('export')">EXPORT JSON</button>
      <button class="tool-button" :disabled="isImporting" @click="openPicker">
        {{ isImporting ? 'IMPORTING...' : 'IMPORT JSON' }}
      </button>
      <button class="tool-button danger" @click="emit('clear')">CLEAR DATA</button>
      <button class="tool-button critical" @click="emit('reset')">RESET EVERYTHING</button>
      <button
        v-if="canInstall && !isInstalled"
        class="tool-button success"
        @click="emit('install')"
      >
        INSTALL APP
      </button>
      <span v-else class="tool-chip">
        {{ isInstalled ? 'INSTALLED' : 'READY' }}
      </span>
    </div>

    <input
      ref="fileInput"
      type="file"
      accept="application/json"
      class="hidden-input"
      @change="handleFileChange"
    >
  </section>
</template>

<style scoped>
.tools-panel {
  border: 1px solid rgba(74, 222, 128, 0.5);
  background: rgba(10, 10, 10, 0.85);
  padding: 1rem;
  font-family: 'Courier New', Courier, monospace;
  color: #f0fdf4;
  box-shadow: inset 0 0 0 1px rgba(74, 222, 128, 0.1), 0 0 20px rgba(0, 0, 0, 0.3);
  border-radius: 2px;
}

.tools-head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 0.8rem;
}

.tools-tag,
.tools-status {
  color: #4ade80;
  letter-spacing: 0.18em;
  font-size: 0.8rem;
  font-weight: 500;
}

.tools-actions {
  display: flex;
  gap: 0.7rem;
  flex-wrap: wrap;
  align-items: center;
}

.tool-button,
.tool-chip {
  border: 1px solid #22c55e;
  background: transparent;
  color: #d1fae5;
  padding: 0.72rem 0.9rem;
  font-family: inherit;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.tool-button {
  cursor: pointer;
}

.tool-button:hover {
  background: rgba(34, 197, 94, 0.1);
}

.tool-button.danger {
  border-color: #f87171;
  color: #fecaca;
}

.tool-button.critical {
  border-color: #dc2626;
  color: #ef4444;
  font-weight: 600;
  background: rgba(220, 38, 38, 0.05);
  animation: pulse-red 2s infinite;
}

.tool-button.critical:hover {
  background: rgba(220, 38, 38, 0.15);
  border-color: #b91c1c;
  box-shadow: 0 0 12px rgba(220, 38, 38, 0.4);
}

.tool-button.success {
  border-color: #86efac;
  color: #86efac;
}

@keyframes pulse-red {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
  }
}

.tool-chip {
  color: #86efac;
}

.tool-button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.hidden-input {
  display: none;
}
</style>
