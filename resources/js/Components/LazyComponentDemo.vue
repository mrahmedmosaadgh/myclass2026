<template>
  <div class="lazy-component-demo">
    <q-card class="demo-card">
      <q-card-section>
        <div class="text-h6">Lazy Component Loading Demo</div>
        <div class="text-subtitle2">Code Splitting Implementation</div>
      </q-card-section>
      
      <q-card-section>
        <div class="demo-section">
          <h6>Camera Component (Lazy Loaded)</h6>
          <p>This camera component is only loaded when needed, reducing initial bundle size.</p>
          
          <LazyCamera
            ref="lazyCameraRef"
            @captured="handleCameraCapture"
            @camera-loaded="handleCameraLoaded"
            @camera-error="handleCameraError"
          />
        </div>
        
        <q-separator class="q-my-md" />
        
        <div class="demo-section">
          <h6>PDF Viewer (Lazy Loaded)</h6>
          <p>PDF viewer components are split into separate chunks.</p>
          
          <q-btn
            color="secondary"
            icon="picture_as_pdf"
            label="Load PDF Viewer"
            @click="loadPDFViewer"
            :loading="loadingPDF"
            class="q-mb-md"
          />
          
          <div v-if="showPDFViewer" class="pdf-container">
            <Suspense>
              <template #default>
                <LazyPDFViewer :src="pdfSrc" />
              </template>
              <template #fallback>
                <div class="loading-container">
                  <q-spinner-dots size="2em" color="primary" />
                  <div>Loading PDF viewer...</div>
                </div>
              </template>
            </Suspense>
          </div>
        </div>
        
        <q-separator class="q-my-md" />
        
        <div class="demo-section">
          <h6>Component Loading Stats</h6>
          <div class="stats-grid">
            <div class="stat-item">
              <div class="stat-label">Cached Components</div>
              <div class="stat-value">{{ componentStats.cached }}</div>
            </div>
            <div class="stat-item">
              <div class="stat-label">Loading Components</div>
              <div class="stat-value">{{ componentStats.loading }}</div>
            </div>
            <div class="stat-item">
              <div class="stat-label">Preloading Components</div>
              <div class="stat-value">{{ componentStats.preloading }}</div>
            </div>
          </div>
        </div>
      </q-card-section>
      
      <q-card-actions align="right">
        <q-btn flat color="primary" @click="refreshStats">Refresh Stats</q-btn>
        <q-btn flat color="negative" @click="clearCache">Clear Cache</q-btn>
      </q-card-actions>
    </q-card>
  </div>
</template>

<script setup>
import { ref, onMounted, defineAsyncComponent } from 'vue'
import { Notify } from 'quasar'
import LazyCamera from './LazyCamera.vue'
import lazyLoader, { LazyPDFViewer } from '../utils/LazyComponentLoader.js'

// Reactive state
const lazyCameraRef = ref(null)
const showPDFViewer = ref(false)
const loadingPDF = ref(false)
const componentStats = ref({ cached: 0, loading: 0, preloading: 0 })
const pdfSrc = ref('/path/to/sample.pdf') // Replace with actual PDF path

/**
 * Handle camera capture
 */
function handleCameraCapture(data) {
  Notify.create({
    type: 'positive',
    message: 'Image captured successfully!',
    position: 'top'
  })
  console.log('Camera captured:', data)
}

/**
 * Handle camera loaded
 */
function handleCameraLoaded() {
  Notify.create({
    type: 'info',
    message: 'Camera component loaded',
    position: 'top'
  })
  refreshStats()
}

/**
 * Handle camera error
 */
function handleCameraError(error) {
  Notify.create({
    type: 'negative',
    message: 'Failed to load camera component',
    position: 'top'
  })
  console.error('Camera error:', error)
}

/**
 * Load PDF viewer component
 */
async function loadPDFViewer() {
  loadingPDF.value = true
  
  try {
    // Small delay to show loading state
    await new Promise(resolve => setTimeout(resolve, 200))
    showPDFViewer.value = true
    
    Notify.create({
      type: 'positive',
      message: 'PDF viewer loaded',
      position: 'top'
    })
  } catch (error) {
    Notify.create({
      type: 'negative',
      message: 'Failed to load PDF viewer',
      position: 'top'
    })
    console.error('PDF viewer error:', error)
  } finally {
    loadingPDF.value = false
    refreshStats()
  }
}

/**
 * Refresh component statistics
 */
function refreshStats() {
  componentStats.value = lazyLoader.getCacheStats()
}

/**
 * Clear component cache
 */
function clearCache() {
  lazyLoader.clearCache()
  showPDFViewer.value = false
  refreshStats()
  
  Notify.create({
    type: 'info',
    message: 'Component cache cleared',
    position: 'top'
  })
}

// Initialize stats on mount
onMounted(() => {
  refreshStats()
  
  // Refresh stats periodically
  setInterval(refreshStats, 5000)
})
</script>

<style scoped>
.lazy-component-demo {
  max-width: 800px;
  margin: 0 auto;
  padding: 1rem;
}

.demo-card {
  margin-bottom: 1rem;
}

.demo-section {
  margin-bottom: 1.5rem;
}

.demo-section h6 {
  margin: 0 0 0.5rem 0;
  color: #1976d2;
}

.demo-section p {
  margin: 0 0 1rem 0;
  color: #666;
  font-size: 0.9rem;
}

.pdf-container {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 1rem;
  min-height: 200px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: #666;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.stat-item {
  text-align: center;
  padding: 1rem;
  background: #f5f5f5;
  border-radius: 8px;
}

.stat-label {
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1976d2;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .lazy-component-demo {
    padding: 0.5rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>