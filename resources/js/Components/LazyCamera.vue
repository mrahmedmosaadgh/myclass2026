<template>
  <div class="lazy-camera-wrapper">
    <!-- Show loading state while camera component loads -->
    <div v-if="!cameraLoaded && showCamera" class="camera-loading">
      <q-spinner-dots size="3em" color="primary" />
      <div class="loading-text">Loading camera...</div>
    </div>
    
    <!-- Lazy load camera component only when needed -->
    <Suspense v-if="showCamera">
      <template #default>
        <component 
          :is="CameraComponent" 
          v-bind="$attrs"
          @captured="handleCaptured"
          @mounted="cameraLoaded = true"
        />
      </template>
      <template #fallback>
        <div class="camera-loading">
          <q-spinner-dots size="3em" color="primary" />
          <div class="loading-text">Loading camera...</div>
        </div>
      </template>
    </Suspense>
    
    <!-- Button to trigger camera loading -->
    <div v-if="!showCamera" class="camera-trigger">
      <q-btn
        color="primary"
        icon="camera_alt"
        label="Open Camera"
        @click="loadCamera"
        :loading="loadingCamera"
        size="lg"
        class="full-width"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, defineAsyncComponent, onUnmounted } from 'vue'

const props = defineProps({
  autoLoad: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['captured', 'camera-loaded', 'camera-error'])

// Reactive state
const showCamera = ref(props.autoLoad)
const cameraLoaded = ref(false)
const loadingCamera = ref(false)

// Lazy load camera component with error handling
const CameraComponent = defineAsyncComponent({
  loader: () => import('@/Pages/my_table_mnger/reward_sys/reward_sys_comp/CameraCapture.vue'),
  loadingComponent: {
    template: `
      <div class="camera-loading">
        <q-spinner-dots size="3em" color="primary" />
        <div class="loading-text">Loading camera component...</div>
      </div>
    `
  },
  errorComponent: {
    template: `
      <div class="camera-error">
        <q-icon name="error_outline" size="3em" color="negative" />
        <div class="error-text">Failed to load camera</div>
        <q-btn 
          flat 
          color="primary" 
          label="Retry" 
          @click="$emit('retry')"
          class="q-mt-md"
        />
      </div>
    `,
    emits: ['retry']
  },
  delay: 200,
  timeout: 10000,
  suspensible: true,
  onError: (error, retry, fail, attempts) => {
    console.error('Camera component loading error:', error)
    emit('camera-error', error)
    
    if (attempts <= 2) {
      // Retry up to 2 times
      setTimeout(retry, 1000)
    } else {
      fail()
    }
  }
})

/**
 * Load camera component on demand
 */
async function loadCamera() {
  if (showCamera.value) return
  
  loadingCamera.value = true
  
  try {
    // Small delay to show loading state
    await new Promise(resolve => setTimeout(resolve, 100))
    
    showCamera.value = true
    emit('camera-loaded')
  } catch (error) {
    console.error('Failed to load camera:', error)
    emit('camera-error', error)
  } finally {
    loadingCamera.value = false
  }
}

/**
 * Handle camera capture event
 */
function handleCaptured(data) {
  emit('captured', data)
}

/**
 * Cleanup when component is unmounted
 */
onUnmounted(() => {
  // Component cleanup is handled by the CameraCapture component itself
  cameraLoaded.value = false
  showCamera.value = false
})

// Expose methods for parent components
defineExpose({
  loadCamera,
  isLoaded: () => cameraLoaded.value,
  isVisible: () => showCamera.value
})
</script>

<style scoped>
.lazy-camera-wrapper {
  width: 100%;
  min-height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.camera-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  min-height: 200px;
}

.loading-text {
  margin-top: 1rem;
  color: #666;
  font-size: 0.9rem;
  text-align: center;
}

.camera-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  min-height: 200px;
}

.error-text {
  margin-top: 1rem;
  color: #c10015;
  font-size: 0.9rem;
  text-align: center;
}

.camera-trigger {
  width: 100%;
  max-width: 300px;
  padding: 1rem;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .lazy-camera-wrapper {
    min-height: 150px;
  }
  
  .camera-loading,
  .camera-error {
    padding: 1rem;
    min-height: 150px;
  }
}
</style>