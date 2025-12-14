<template>
  <div class="lazy-pdf-viewer">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p class="loading-text">Loading PDF Viewer...</p>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">⚠️</div>
      <p class="error-text">Failed to load PDF viewer</p>
      <button @click="retryLoad" class="retry-button">Retry</button>
    </div>
    
    <!-- Lazy Loaded PDF Component -->
    <component 
      v-else-if="PDFComponent" 
      :is="PDFComponent" 
      v-bind="$attrs"
      @loaded="$emit('loaded')"
      @error="$emit('error', $event)"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import bundleAnalyzer from '@/utils/BundleAnalyzer.js'

// Props
defineProps({
  pdfUrl: {
    type: String,
    default: ''
  }
})

// Emits
defineEmits(['loaded', 'error'])

// State
const PDFComponent = ref(null)
const isLoading = ref(true)
const error = ref(null)

// Lazy load the PDF viewer component
const loadPDFViewer = async () => {
  try {
    isLoading.value = true
    error.value = null
    
    // Use the ReusableHtmlViewer as PDF viewer
    const module = await import('@/Pages/print_html/components/ReusableHtmlViewer.vue')
    PDFComponent.value = module.default
    
    isLoading.value = false
  } catch (err) {
    console.error('Failed to load PDF viewer:', err)
    error.value = err
    isLoading.value = false
  }
}

// Retry loading
const retryLoad = () => {
  loadPDFViewer()
}

// Load on mount
onMounted(() => {
  loadPDFViewer()
})
</script>

<style scoped>
.lazy-pdf-viewer {
  width: 100%;
  height: 100%;
  min-height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: #6b7280;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.loading-text {
  font-size: 1.1rem;
  font-weight: 500;
  margin: 0;
}

.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: #dc2626;
  text-align: center;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.error-text {
  font-size: 1.1rem;
  font-weight: 500;
  margin: 0 0 1rem 0;
}

.retry-button {
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.retry-button:hover {
  background-color: #2563eb;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>