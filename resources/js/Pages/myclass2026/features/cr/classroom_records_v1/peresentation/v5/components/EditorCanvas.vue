<script setup>
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import ElementNode from './ElementNode.vue';

const presentation = usePresentationStore();
const ui = useUIStore();

function capturePointer(e) {
  const rect = e.currentTarget.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  ui.updateLastPointer(x, y);
}

function handleCanvasMousedown(e) {
  capturePointer(e);
  ui.clearSelection();
}
</script>

<template>
  <div class="canvas-wrapper">
    <div
      class="canvas"
      :style="{ 
        height: (presentation.currentSlide?.height || 600) + 'px',
        transform: `scale(${ui.zoomLevel / 100})`,
        transformOrigin: 'top center'
      }"
      @mousedown.self="handleCanvasMousedown"
    >
      <ElementNode
        v-for="el in presentation.currentSlide.elements"
        :key="el.id"
        :element="el"
      />
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
  overflow: auto;
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
  transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
