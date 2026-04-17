<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import ElementNode from './ElementNode.vue';
import DrawingCanvasOverlay from './drawing/DrawingCanvasOverlay.vue';

const presentation = usePresentationStore();
const ui = useUIStore();

const SLIDE_BASE_WIDTH = 1000;
const wrapperRef = ref(null);
const wrapperWidth = ref(0);
let resizeObserver;

const fitScale = computed(() => {
  const w = wrapperWidth.value || window.innerWidth;
  return w > 0 ? w / SLIDE_BASE_WIDTH : 1;
});

const canvasScale = computed(() => {
  return fitScale.value * (ui.zoomLevel / 100);
});

function capturePointer(e) {
  const rect = e.currentTarget.getBoundingClientRect();
  const scale = canvasScale.value || 1;
  const x = (e.clientX - rect.left) / scale;
  const y = (e.clientY - rect.top) / scale;
  ui.updateLastPointer(x, y);
}

function handleCanvasMousedown(e) {
  capturePointer(e);
  ui.clearSelection();
}

function calculateWrapperHeight() {
  const baseHeight = presentation.currentSlide?.height || 600;
  const scaledHeight = baseHeight * (canvasScale.value || 1);
  return Math.max(scaledHeight, window.innerHeight * 0.8);
}

onMounted(() => {
  if (!wrapperRef.value) return;
  wrapperWidth.value = wrapperRef.value.clientWidth || window.innerWidth;
  resizeObserver = new ResizeObserver((entries) => {
    const entry = entries[0];
    if (entry?.contentRect?.width) {
      wrapperWidth.value = entry.contentRect.width;
    }
  });
  resizeObserver.observe(wrapperRef.value);
});

onUnmounted(() => {
  if (resizeObserver && wrapperRef.value) {
    resizeObserver.unobserve(wrapperRef.value);
  }
});
</script>

<template>
  <div ref="wrapperRef" class="canvas-wrapper" :class="{ 'present-mode': !ui.isEditMode }" :style="{ height: calculateWrapperHeight() + 'px' }">
    <div
      class="canvas"
      :class="{ 'present-mode-canvas': !ui.isEditMode }"
      :style="{ 
        width: SLIDE_BASE_WIDTH + 'px',
        height: (presentation.currentSlide?.height || 600) + 'px',
        transform: `scale(${canvasScale})`,
        transformOrigin: 'top left',
        backgroundColor: 'white' // White background for slide
      }"
      @mousedown.self="handleCanvasMousedown"
    >
      <ElementNode
        v-for="el in presentation.currentSlide.elements"
        :key="el.id"
        :element="el"
      />

      <DrawingCanvasOverlay :scale="canvasScale" />
    </div>

    <!-- Dynamic Height Extension UI -->
    <div v-if="ui.isEditMode" style="text-align: center; margin-top: 15px; margin-bottom: 20px;">
      <button 
        class="extend-height-btn" 
        @click="presentation.currentSlide.height = (presentation.currentSlide.height || 600) + 200"
        title="Increase Slide Height"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 13 12 18 17 13"></polyline><line x1="12" y1="18" x2="12" y2="6"></line></svg>
        Extend Slide Height
      </button>
    </div>
  </div>
</template>

<style scoped>
.canvas-wrapper {
  width: 100%;
  max-width: 100vw;
  min-width: 100vw;
  overflow-x: auto;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  background: #e5e7eb;
}

.canvas {
  position: relative;
  width: 1000px;
  background: white;
  flex-shrink: 0;
  margin: auto;
  border: 1px solid #ccc;
  background-image: linear-gradient(to right, #eee 1px, transparent 1px),
                    linear-gradient(to bottom, #eee 1px, transparent 1px);
  background-size: 10px 10px;
  border-radius: 8px;
  overflow: hidden;
  transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Hide grid in present mode for cleaner white background */
.present-mode-canvas {
  background-image: none !important;
  border: none !important;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
}

.extend-height-btn {
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 20px;
  padding: 6px 14px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #4b5563;
  cursor: pointer;
  box-shadow: 0 2px 4px rgb(0 0 0 / 0.1);
  transition: all 0.2s;
}
.extend-height-btn:hover {
  background: #f3f4f6;
  color: #111827;
  border-color: #9ca3af;
  transform: translateY(1px);
}
</style>
