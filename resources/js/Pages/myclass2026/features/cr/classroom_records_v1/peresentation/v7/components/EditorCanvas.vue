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

// Rectangle drawing state
const isDrawingRect = ref(false);
const drawStartPos = ref({ x: 0, y: 0 });
const drawCurrentPos = ref({ x: 0, y: 0 });
const tempRect = ref(null);

// Computed properties for question view mode
const groupMcqElements = computed(() => {
  return presentation.currentSlide?.elements?.filter(el => el.type === 'group-mcq') || [];
});

const visibleElements = computed(() => {
  if (ui.isEditMode) {
    // In edit mode, show all elements
    return presentation.currentSlide?.elements || [];
  }
  
  if (ui.questionViewMode === 'list') {
    // In list view, show all elements
    return presentation.currentSlide?.elements || [];
  }
  
  // In single question view, only show the current question
  const mcqElements = groupMcqElements.value;
  if (mcqElements.length === 0) {
    return presentation.currentSlide?.elements || [];
  }
  
  const currentIndex = ui.currentQuestionIndex;
  const targetElement = mcqElements[currentIndex] || mcqElements[0];
  
  // Return only non-group-mcq elements plus the current group-mcq element
  return presentation.currentSlide?.elements?.filter(el => 
    el.type !== 'group-mcq' || el.id === targetElement.id
  ) || [];
});

const fitScale = computed(() => {
  return 1;
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
  return { x, y };
}

function handleCanvasMousedown(e) {
  const pos = capturePointer(e);

  // If in rectangle drawing mode, start drawing
  if (ui.isDrawRectangleMode && ui.isEditMode) {
    isDrawingRect.value = true;
    drawStartPos.value = { ...pos };
    drawCurrentPos.value = { ...pos };
    return;
  }

  ui.clearSelection();
}

function handleCanvasMousemove(e) {
  if (!isDrawingRect.value) return;

  const rect = e.currentTarget.getBoundingClientRect();
  const scale = canvasScale.value || 1;
  const x = (e.clientX - rect.left) / scale;
  const y = (e.clientY - rect.top) / scale;
  drawCurrentPos.value = { x, y };
}

function handleCanvasMouseup(e) {
  if (!isDrawingRect.value) return;

  isDrawingRect.value = false;

  // Calculate rectangle dimensions
  const x = Math.min(drawStartPos.value.x, drawCurrentPos.value.x);
  const y = Math.min(drawStartPos.value.y, drawCurrentPos.value.y);
  const width = Math.abs(drawCurrentPos.value.x - drawStartPos.value.x);
  const height = Math.abs(drawCurrentPos.value.y - drawStartPos.value.y);

  // Only create if rectangle has meaningful size (min 20x20)
  if (width >= 20 && height >= 20) {
    // Create rectangle element with toggle visibility (shown-clickable with 0.05 hidden opacity)
    const element = {
      id: 'el-' + Date.now() + Math.floor(Math.random() * 1000),
      type: 'rectangle',
      x: x,
      y: y,
      width: width,
      height: height,
      zIndex: presentation.currentSlide?.elements?.length + 1 || 1,
      visibilityOption: 'shown-clickable',
      isVisible: true,
      hiddenOpacity: 0.05,
      bgColor: '#93c5fd'
    };

    presentation.addElement(element);
    ui.selectElement(element.id);
  }

  // Exit drawing mode after creating rectangle
  ui.setDrawRectangleMode(false);
}

// Computed style for the temporary drawing rectangle
const tempRectStyle = computed(() => {
  if (!isDrawingRect.value) return null;

  const x = Math.min(drawStartPos.value.x, drawCurrentPos.value.x);
  const y = Math.min(drawStartPos.value.y, drawCurrentPos.value.y);
  const width = Math.abs(drawCurrentPos.value.x - drawStartPos.value.x);
  const height = Math.abs(drawCurrentPos.value.y - drawStartPos.value.y);

  return {
    position: 'absolute',
    left: x + 'px',
    top: y + 'px',
    width: width + 'px',
    height: height + 'px',
    border: '2px dashed #10b981',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    pointerEvents: 'none',
    zIndex: 10000
  };
});

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
  <div
    ref="wrapperRef"
    class="canvas-wrapper"
    :class="{ 'present-mode': !ui.isEditMode, 'draw-rect-mode': ui.isDrawRectangleMode }"
    :style="{ height: calculateWrapperHeight() + 'px', '--canvas-scale': canvasScale }"
  >
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
      @mousemove.self="handleCanvasMousemove"
      @mouseup.self="handleCanvasMouseup"
      @mouseleave.self="handleCanvasMouseup"
    >
      <ElementNode
        v-for="el in visibleElements"
        :key="el.id"
        :element="el"
      />

      <!-- Question Navigation for Single Question View -->
      <div v-if="!ui.isEditMode && ui.questionViewMode === 'single' && groupMcqElements.length > 1" class="question-nav-overlay">
        <button 
          class="q-nav-btn q-nav-prev" 
          @click="ui.previousQuestion()"
          :disabled="ui.currentQuestionIndex === 0"
          title="Previous Question"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <div class="q-nav-counter">
          {{ ui.currentQuestionIndex + 1 }} / {{ groupMcqElements.length }}
        </div>
        <button 
          class="q-nav-btn q-nav-next" 
          @click="ui.nextQuestion()"
          :disabled="ui.currentQuestionIndex >= groupMcqElements.length - 1"
          title="Next Question"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
      </div>

      <!-- Temporary drawing rectangle -->
      <div v-if="isDrawingRect && tempRectStyle" :style="tempRectStyle"></div>

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

/* Drawing mode cursor indicator */
.draw-rect-mode .canvas {
  cursor: crosshair;
}

/* Question Navigation Overlay */
.question-nav-overlay {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 10px 16px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(229, 231, 235, 0.8);
  z-index: 1000;
}

.q-nav-btn {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #374151;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.q-nav-btn:hover:not(:disabled) {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: scale(1.05);
}

.q-nav-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.q-nav-counter {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1f2937;
  min-width: 60px;
  text-align: center;
}
</style>
