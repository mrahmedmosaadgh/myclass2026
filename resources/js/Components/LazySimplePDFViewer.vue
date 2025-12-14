<template>
  <div class="lazy-simple-pdf-viewer">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p class="loading-text">Loading Simple PDF Viewer...</p>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">⚠️</div>
      <p class="error-text">Failed to load PDF viewer</p>
      <button @click="retryLoad" class="retry-button">Retry</button>
    </div>
    
    <!-- Lazy Loaded Simple PDF Component -->
    <component 
      v-else-if="SimplePDFComponent" 
      :is="SimplePDFComponent" 
      v-bind="$attrs"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useLazyPDFComponents, pdfComponentImports } from '@/composables/useLazyPDFComponents.js'

// Use the composable
const { isLoading, error, loadPDFComponent } = useLazyPDFComponents()

// State
const SimplePDFComponent = ref(null)

// Lazy load the simple PDF viewer component
const loadSimplePDFViewer = async () => {
  try {
    const component = await loadPDFComponent('SimplePDFViewer', pdfComponentImports.SimplePDFViewer)
    SimplePDFComponent.value = component
  } catch (err) {
    console.error('Failed to load simple PDF viewer:', err)
  }
}

// Retry loading
const retryLoad = () => {
  loadSimplePDFViewer()
}

// Load on mount
onMounted(() => {
  loadSimplePDFViewer()
})
</script>

<style scoped>
.lazy-simple-pdf-viewer {
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
  border-top: 4px solid #10b981;
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
  background-color: #10b981;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.retry-button:hover {
  background-color: #059669;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>