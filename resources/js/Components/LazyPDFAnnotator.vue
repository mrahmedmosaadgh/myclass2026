<template>
  <div class="lazy-pdf-annotator">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p class="loading-text">Loading PDF Annotator...</p>
      <p class="loading-subtext">This may take a moment...</p>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">⚠️</div>
      <p class="error-text">Failed to load PDF annotator</p>
      <p class="error-details">{{ error.message || 'Unknown error occurred' }}</p>
      <button @click="retryLoad" class="retry-button">Retry</button>
    </div>
    
    <!-- Lazy Loaded PDF Annotator Component -->
    <component 
      v-else-if="PDFAnnotatorComponent" 
      :is="PDFAnnotatorComponent" 
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
const PDFAnnotatorComponent = ref(null)

// Lazy load the PDF annotator component
const loadPDFAnnotator = async () => {
  try {
    const component = await loadPDFComponent('PDFAnnotator', pdfComponentImports.PDFAnnotator)
    PDFAnnotatorComponent.value = component
  } catch (err) {
    console.error('Failed to load PDF annotator:', err)
  }
}

// Retry loading
const retryLoad = () => {
  loadPDFAnnotator()
}

// Load on mount
onMounted(() => {
  loadPDFAnnotator()
})
</script>

<style scoped>
.lazy-pdf-annotator {
  width: 100%;
  height: 100%;
  min-height: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #6b7280;
  text-align: center;
}

.loading-spinner {
  width: 64px;
  height: 64px;
  border: 5px solid #e5e7eb;
  border-top: 5px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1.5rem;
}

.loading-text {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
}

.loading-subtext {
  font-size: 0.9rem;
  color: #9ca3af;
  margin: 0;
}

.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #dc2626;
  text-align: center;
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 1.5rem;
}

.error-text {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
}

.error-details {
  font-size: 0.9rem;
  color: #ef4444;
  margin: 0 0 1.5rem 0;
  max-width: 400px;
}

.retry-button {
  padding: 0.75rem 1.5rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.retry-button:hover {
  background-color: #2563eb;
  transform: translateY(-1px);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>