<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useQrCodec } from '../../composables/useQrCodec.js'

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false
  },
  placeholder: {
    type: String,
    default: 'Type or scan: g1_a'
  }
})

const emit = defineEmits([
  'scanned',
  'select-group',
  'error'
])

const { decodePayload } = useQrCodec()

// ── State ──────────────────────────────────────────────
const inputRef = ref(null)
const buffer = ref('')
const lastResult = ref('')
const lastResultType = ref('idle') // 'success' | 'error' | 'idle'

// ── Computed ───────────────────────────────────────────
const showResult = computed(() => lastResult.value !== '')

const resultClass = computed(() => {
  if (lastResultType.value === 'success') return 'result-success'
  if (lastResultType.value === 'error') return 'result-error'
  return 'result-idle'
})

// ── Methods ────────────────────────────────────────────
function focusInput() {
  if (!props.disabled && inputRef.value) {
    inputRef.value.focus()
  }
}

function clearBuffer() {
  buffer.value = ''
  lastResult.value = ''
  lastResultType.value = 'idle'
  focusInput()
}

function processBuffer() {
  const raw = buffer.value.trim()
  if (!raw) return

  const decoded = decodePayload(raw)

  if (!decoded.ok) {
    // Try group-only selection (e.g. "g1" or "Group A")
    lastResult.value = decoded.error
    lastResultType.value = 'error'
    emit('error', decoded)
    buffer.value = ''
    return
  }

  if (decoded.optionId) {
    // Full payload: group + answer
    lastResult.value = `${decoded.groupId} → ${decoded.optionId}`
    lastResultType.value = 'success'
    emit('scanned', decoded)
  } else {
    // Group-only: select group
    lastResult.value = `Selected: ${decoded.groupId}`
    lastResultType.value = 'success'
    emit('select-group', decoded.groupId)
  }

  buffer.value = ''
}

function handleKeydown(e) {
  if (props.disabled) return

  // Let the input handle its own Enter/Escape when focused
  if (document.activeElement === inputRef.value) return

  // Hardware scanner: accumulate typed characters globally
  if (e.key === 'Enter') {
    processBuffer()
  } else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
    buffer.value += e.key
    // Auto-focus input to show buffer
    focusInput()
  }
}

function handleInputKeydown(e) {
  if (e.key === 'Enter') {
    e.preventDefault()
    processBuffer()
  } else if (e.key === 'Escape') {
    clearBuffer()
  }
}

// ── Lifecycle ────────────────────────────────────────
onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})

defineExpose({
  focusInput,
  clearBuffer,
  buffer
})
</script>

<template>
  <div class="qr-scan-input" :class="{ disabled }">
    <!-- Input Field -->
    <div class="scan-input-wrapper">
      <svg
        class="scan-icon"
        xmlns="http://www.w3.org/2000/svg"
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
      >
        <path d="M3 7V5a2 2 0 0 1 2-2h2" />
        <path d="M17 3h2a2 2 0 0 1 2 2v2" />
        <path d="M21 17v2a2 2 0 0 1-2 2h-2" />
        <path d="M7 21H5a2 2 0 0 1-2-2v-2" />
        <rect x="7" y="7" width="10" height="10" rx="1" />
      </svg>

      <input
        ref="inputRef"
        v-model="buffer"
        type="text"
        class="scan-input"
        :placeholder="placeholder"
        :disabled="disabled"
        @keydown="handleInputKeydown"
        @focus="lastResult = ''"
      />

      <button
        v-if="buffer"
        class="scan-clear-btn"
        @click="clearBuffer"
        title="Clear"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>

      <button
        class="scan-submit-btn"
        :disabled="!buffer || disabled"
        @click="processBuffer"
        title="Submit (Enter)"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9 11 12 14 22 4" />
          <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
        </svg>
      </button>
    </div>

    <!-- Result Feedback -->
    <div
      v-if="showResult"
      class="scan-result"
      :class="resultClass"
    >
      {{ lastResult }}
    </div>

    <!-- Hint -->
    <div v-if="!showResult && !disabled" class="scan-hint">
      Type payload + Enter, or scan with hardware scanner
    </div>
  </div>
</template>

<style scoped>
.qr-scan-input {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.qr-scan-input.disabled {
  opacity: 0.5;
  pointer-events: none;
}

/* Input Wrapper */
.scan-input-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #ffffff;
  border: 1.5px solid #d1d5db;
  border-radius: 8px;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.scan-input-wrapper:focus-within {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.scan-icon {
  flex-shrink: 0;
  color: #9ca3af;
}

.scan-input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-size: 14px;
  font-family: ui-monospace, SFMono-Regular, monospace;
  color: #111827;
  min-width: 0;
}

.scan-input::placeholder {
  color: #9ca3af;
  font-family: ui-sans-serif, system-ui, sans-serif;
}

/* Clear Button */
.scan-clear-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.15s;
}

.scan-clear-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

/* Submit Button */
.scan-submit-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  background: #6366f1;
  color: white;
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.15s;
}

.scan-submit-btn:hover:not(:disabled) {
  background: #4f46e5;
}

.scan-submit-btn:disabled {
  background: #d1d5db;
  cursor: not-allowed;
}

/* Result Feedback */
.scan-result {
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to   { opacity: 1; transform: translateY(0); }
}

.result-success {
  background: #ecfdf5;
  color: #047857;
  border: 1px solid #a7f3d0;
}

.result-error {
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

.result-idle {
  background: #f3f4f6;
  color: #6b7280;
}

/* Hint */
.scan-hint {
  font-size: 12px;
  color: #9ca3af;
  padding-left: 4px;
}
</style>
