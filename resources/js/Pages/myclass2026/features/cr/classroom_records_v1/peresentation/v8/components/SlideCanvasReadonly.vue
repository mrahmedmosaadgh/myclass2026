<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import ElementNode from './ElementNode.vue'

const props = defineProps({
  slide: Object,
  isPresentMode: {
    type: Boolean,
    default: false
  },
  zoomLevel: {
    type: Number,
    default: 100
  }
})

const canvasWrapper = ref(null)
const scale = ref(1)

const CANVAS_WIDTH = 800
const CANVAS_HEIGHT = 600

const canvasStyle = computed(() => ({
  width: `${CANVAS_WIDTH}px`,
  height: `${CANVAS_HEIGHT}px`,
  position: 'relative',
  background: 'white',
  border: '1px solid #e5e7eb',
  borderRadius: '8px',
  overflow: 'hidden',
  transform: `scale(${scale.value * (props.zoomLevel / 100)})`,
  transformOrigin: 'top center'
}))

function updateScale() {
  if (!canvasWrapper.value) return

  const containerWidth = canvasWrapper.value.clientWidth
  const containerHeight = canvasWrapper.value.clientHeight

  if (containerWidth === 0 || containerHeight === 0) return

  const scaleX = containerWidth / CANVAS_WIDTH
  const scaleY = containerHeight / CANVAS_HEIGHT

  scale.value = Math.min(scaleX, scaleY, 1.2) // Cap at 1.2x to avoid over-scaling
}

let resizeObserver = null

onMounted(() => {
  if (canvasWrapper.value) {
    resizeObserver = new ResizeObserver(() => {
      updateScale()
    })
    resizeObserver.observe(canvasWrapper.value)
    // Initial scale calculation
    setTimeout(updateScale, 0)
  }
})

onUnmounted(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})
</script>

<template>
  <div ref="canvasWrapper" class="canvas-wrapper">
    <div class="slide-canvas-readonly" :style="canvasStyle">
      <!-- Grid background -->
      <div class="grid-background" />

      <!-- Elements -->
      <ElementNode
        v-for="element in slide.elements"
        :key="element.id"
        :element="element"
        :is-present-mode="isPresentMode"
      />

      <!-- Empty state -->
      <div v-if="slide.elements.length === 0" class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>Empty Slide</h3>
        <p>No elements on this slide</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.canvas-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.slide-canvas-readonly {
  margin: 0 auto;
  transition: transform 0.2s ease;
}

.grid-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image:
    linear-gradient(to right, #f9fafb 1px, transparent 1px),
    linear-gradient(to bottom, #f9fafb 1px, transparent 1px);
  background-size: 10px 10px;
  pointer-events: none;
  opacity: 0.3;
}

.empty-state {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: #d1d5db;
  pointer-events: none;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 600;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}
</style>
