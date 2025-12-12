<script setup lang="ts">
import { ref, watch } from 'vue'
import PdfEmbed from 'vue-pdf-embed'

const props = defineProps<{
  pdfUrl: string // URL or base64 data URL of the PDF
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'loaded'): void
  (e: 'error', error: Error): void
}>()

const pdfRef = ref<any>(null)
const isLoading = ref(true)

// Optional: expose methods if parent needs control
defineExpose({
  print: () => pdfRef.value?.print(),
  download: () => pdfRef.value?.download(),
})
</script>

<template>
  <div class="pdf-viewer-container" style="width: 100%; height: 100vh; position: relative;">
    <!-- Loading state -->
    <div v-if="props.loading ?? isLoading" class="loading-overlay">
      <div class="spinner"></div>
      <p>Loading PDF...</p>
    </div>

    <!-- Error state (optional) -->
    <div v-if="!pdfUrl" class="error-message">
      <p>No PDF URL provided</p>
    </div>

    <!-- PDF Viewer -->
    <PdfEmbed
      v-else
      ref="pdfRef"
      :source="pdfUrl"
      @loaded="() => { isLoading = false; emit('loaded') }"
      @load-failed="(err: Error) => { isLoading = false; emit('error', err) }"
      style="width: 100%; height: 100%;"
    />
  </div>
</template>

<style scoped>
.pdf-viewer-container {
  background: #525659;
  overflow: hidden;
}

.loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  z-index: 10;
  font-size: 1.2rem;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #333;
  border-top: 5px solid #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #ccc;
  font-size: 1.5rem;
}
</style>