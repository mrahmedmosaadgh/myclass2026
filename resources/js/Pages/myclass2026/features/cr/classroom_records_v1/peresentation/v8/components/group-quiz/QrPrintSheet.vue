<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useQrCodec } from '../../composables/useQrCodec.js'

// Shared QR component — same path used by v7
import QrCode from '@/Components/Common/QrCode.vue'

const props = defineProps({
  groups: {
    type: Array,
    required: true
  },
  optionIds: {
    type: Array,
    default: () => ['A', 'B', 'C', 'D']
  },
  title: {
    type: String,
    default: 'Group QR Codes'
  }
})

const emit = defineEmits(['download-pdf', 'print'])

const { encodePayload } = useQrCodec()

const printAreaRef = ref(null)
const a4PreviewScale = ref(1)

// ── Computed ───────────────────────────────────────────
const groupOptionPayloads = computed(() => {
  return props.groups.map(group => ({
    group,
    options: props.optionIds.map(optId => ({
      optionId: optId,
      payload: encodePayload(group.id, optId)
    }))
  }))
})

// ── Methods ────────────────────────────────────────────
function updateA4PreviewScale() {
  if (!printAreaRef.value) return
  const container = printAreaRef.value.parentElement
  if (!container) return
  const containerWidth = container.offsetWidth || 800
  const a4Width = 794 // A4 width in px at 96 DPI
  a4PreviewScale.value = Math.min(containerWidth / a4Width, 1)
}

function triggerPrint() {
  window.print()
  emit('print')
}

async function downloadPdf() {
  // Lazy-load heavy deps only when needed
  const [{ default: html2canvas }, { default: jsPDF }] = await Promise.all([
    import('html2canvas'),
    import('jspdf')
  ])

  if (!printAreaRef.value) return

  const canvas = await html2canvas(printAreaRef.value, {
    scale: 2,
    useCORS: true,
    backgroundColor: '#ffffff',
    logging: false
  })

  const imgData = canvas.toDataURL('image/png')
  const pdf = new jsPDF('p', 'mm', 'a4')
  const pdfWidth = pdf.internal.pageSize.getWidth()
  const pdfHeight = pdf.internal.pageSize.getHeight()

  pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight)
  pdf.save(`group-qr-codes-${Date.now()}.pdf`)

  emit('download-pdf')
}

// ── Lifecycle ────────────────────────────────────────
onMounted(() => {
  nextTick(updateA4PreviewScale)
  window.addEventListener('resize', updateA4PreviewScale)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateA4PreviewScale)
})

defineExpose({
  printAreaRef,
  a4PreviewScale,
  updateA4PreviewScale,
  triggerPrint,
  downloadPdf
})
</script>

<template>
  <div class="qr-print-sheet-wrapper">
    <!-- A4 Printable Area -->
    <div
      ref="printAreaRef"
      class="qr-a4-sheet"
      :style="{ transform: `scale(${a4PreviewScale})`, transformOrigin: 'top left' }"
    >
      <!-- Header -->
      <div class="qr-sheet-header">
        <h1 class="qr-sheet-title">{{ title }}</h1>
        <p class="qr-sheet-subtitle">Scan to submit your answer</p>
      </div>

      <!-- Groups Grid -->
      <div class="qr-groups-grid">
        <div
          v-for="{ group, options } in groupOptionPayloads"
          :key="group.id"
          class="qr-group-block"
        >
          <!-- Group Header -->
          <div
            class="qr-group-header"
            :style="{ backgroundColor: group.color }"
          >
            <span class="qr-group-name">{{ group.name }}</span>
            <span class="qr-group-id">{{ group.id }}</span>
          </div>

          <!-- QR Codes Row -->
          <div class="qr-codes-row">
            <div
              v-for="{ optionId, payload } in options"
              :key="optionId"
              class="qr-code-cell"
            >
              <div class="qr-code-wrapper">
                <QrCode
                  :value="payload"
                  :size="100"
                  level="M"
                />
              </div>
              <div class="qr-option-label">{{ optionId }}</div>
              <div class="qr-payload-text">{{ payload }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="qr-sheet-footer">
        <p>Generated {{ new Date().toLocaleDateString() }}</p>
      </div>
    </div>

    <!-- Action Bar (hidden in print) -->
    <div class="qr-actions no-print">
      <button class="qr-action-btn primary" @click="triggerPrint">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print
      </button>
      <button class="qr-action-btn secondary" @click="downloadPdf">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        PDF
      </button>
    </div>
  </div>
</template>

<style scoped>
.qr-print-sheet-wrapper {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 16px;
}

/* A4 Sheet */
.qr-a4-sheet {
  width: 794px;  /* A4 width at 96 DPI */
  min-height: 1123px; /* A4 height */
  padding: 40px 48px;
  background: #ffffff;
  box-sizing: border-box;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
}

/* Header */
.qr-sheet-header {
  text-align: center;
  margin-bottom: 32px;
  padding-bottom: 16px;
  border-bottom: 2px solid #e5e7eb;
}

.qr-sheet-title {
  margin: 0 0 6px 0;
  font-size: 28px;
  font-weight: 700;
  color: #111827;
}

.qr-sheet-subtitle {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

/* Groups Grid */
.qr-groups-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.qr-group-block {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  background: #f9fafb;
}

.qr-group-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  color: white;
  font-weight: 600;
  font-size: 14px;
}

.qr-group-id {
  opacity: 0.85;
  font-size: 12px;
  font-weight: 500;
}

/* QR Codes Row */
.qr-codes-row {
  display: flex;
  justify-content: space-around;
  padding: 14px;
  gap: 10px;
}

.qr-code-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.qr-code-wrapper {
  padding: 6px;
  background: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
}

.qr-option-label {
  font-size: 16px;
  font-weight: 700;
  color: #374151;
}

.qr-payload-text {
  font-size: 9px;
  color: #9ca3af;
  font-family: ui-monospace, monospace;
  max-width: 80px;
  text-align: center;
  word-break: break-all;
}

/* Footer */
.qr-sheet-footer {
  margin-top: 32px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
  text-align: center;
  font-size: 12px;
  color: #9ca3af;
}

/* Actions */
.qr-actions {
  display: flex;
  gap: 10px;
}

.qr-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s ease;
}

.qr-action-btn.primary {
  background: #6366f1;
  color: white;
  border-color: #6366f1;
}

.qr-action-btn.primary:hover {
  background: #4f46e5;
}

.qr-action-btn.secondary {
  background: white;
  color: #374151;
  border-color: #d1d5db;
}

.qr-action-btn.secondary:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

/* Print Styles */
@media print {
  .no-print {
    display: none !important;
  }

  .qr-a4-sheet {
    transform: none !important;
    border: none !important;
    width: 100% !important;
    min-height: auto !important;
    padding: 20px !important;
  }

  .qr-print-sheet-wrapper {
    align-items: stretch;
  }
}
</style>
