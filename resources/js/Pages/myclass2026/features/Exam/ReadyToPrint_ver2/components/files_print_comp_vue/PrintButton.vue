<template>
  <!-- PrintButton.vue -->
  <!-- Drop this anywhere. Pass a target ref, a URL, or leave blank for full page print. -->
  <button
    class="print-btn"
    :class="[variant, size, { loading: busy }]"
    :disabled="busy"
    @click="handlePrint"
  >
    <svg v-if="!busy" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="6 9 6 2 18 2 18 9" />
      <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
      <rect x="6" y="14" width="12" height="8" />
    </svg>
    <span v-if="busy" class="spinner" />
    <span v-if="label">{{ label }}</span>
  </button>
</template>

<script setup>
import { ref } from 'vue'
import { usePrint } from '@/composables/usePrint'

const props = defineProps({
  // What to print. Priority: url > target > whole page
  url:         { type: String,  default: null },   // Laravel route e.g. /invoices/5/print
  target:      { type: Object,  default: null },   // Vue ref to a DOM element

  // Print options
  title:       { type: String,  default: 'Print' },
  pageSize:    { type: String,  default: 'A4' },
  orientation: { type: String,  default: 'portrait' },   // 'portrait' | 'landscape'
  extraStyles: { type: String,  default: '' },

  // Appearance
  label:       { type: String,  default: 'Print' },
  variant:     { type: String,  default: 'default' }, // 'default' | 'ghost' | 'danger'
  size:        { type: String,  default: 'md' },       // 'sm' | 'md' | 'lg'
})

const emit = defineEmits(['before-print', 'after-print', 'error'])

const busy = ref(false)
const { printElement, printFromUrl, printPage } = usePrint()

const printOptions = () => ({
  title:       props.title,
  pageSize:    props.pageSize,
  orientation: props.orientation,
  extraStyles: props.extraStyles,
})

const handlePrint = async () => {
  emit('before-print')
  busy.value = true
  try {
    if (props.url) {
      await printFromUrl(props.url, printOptions())
    } else if (props.target) {
      printElement(props.target, printOptions())
    } else {
      printPage()
    }
    emit('after-print')
  } catch (err) {
    emit('error', err)
  } finally {
    busy.value = false
  }
}
</script>

<style scoped>
.print-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  border-radius: 6px;
  font-weight: 500;
  transition: background 0.15s, opacity 0.15s;
  white-space: nowrap;
  border: 1px solid transparent;
}
.print-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

/* Sizes */
.print-btn.sm { padding: 5px 10px; font-size: 12px; }
.print-btn.md { padding: 8px 14px; font-size: 14px; }
.print-btn.lg { padding: 11px 20px; font-size: 16px; }

/* Variants */
.print-btn.default { background: #1a1a1a; color: #fff; border-color: #1a1a1a; }
.print-btn.default:hover { background: #333; }
.print-btn.ghost   { background: transparent; color: #1a1a1a; border-color: #ccc; }
.print-btn.ghost:hover { background: #f5f5f5; }
.print-btn.danger  { background: #dc2626; color: #fff; border-color: #dc2626; }
.print-btn.danger:hover { background: #b91c1c; }

/* Disabled/loading */
.print-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Spinner */
.spinner {
  display: inline-block;
  width: 14px; height: 14px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Hide button in actual print */
@media print { .print-btn { display: none !important; } }
</style>
