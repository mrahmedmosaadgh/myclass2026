<script setup>
import { computed } from 'vue';
import { useDrawingStore } from '../../stores/drawingStore';
import { useDrawingCanvas } from '../../composables/drawing/useDrawingCanvas';

const emit = defineEmits(['draw']);

const drawingStore = useDrawingStore();

const { canvasRef, laserPosition, scheduleDraw } = useDrawingCanvas({
  emitDrawEvent: (payload) => emit('draw', payload)
});

const overlayClasses = computed(() => ({
  'is-active': drawingStore.isDrawingMode,
  'has-grid': drawingStore.showGrid
}));

const laserStyle = computed(() => {
  if (!laserPosition.value) return {};
  return {
    transform: `translate(${laserPosition.value.x}px, ${laserPosition.value.y}px)`
  };
});

function handleDoubleClick() {
  if (!drawingStore.isDrawingMode) {
    drawingStore.toggleDrawingMode(true);
    drawingStore.toggleToolbar(true);
  }
}

function handleOverlayClick(event) {
  if (!drawingStore.isDrawingMode) {
    event.stopPropagation();
  }
}

function refreshCanvas() {
  scheduleDraw();
}

function handleTouchStart(event) {
  if (drawingStore.isDrawingMode) {
    // Prevent default behavior when in drawing mode
    event.preventDefault();
    event.stopPropagation();
  }
}

function handleTouchMove(event) {
  if (drawingStore.isDrawingMode) {
    // Prevent default behavior when in drawing mode
    event.preventDefault();
    event.stopPropagation();
  }
}

function handleTouchEnd(event) {
  if (drawingStore.isDrawingMode) {
    // Prevent default behavior when in drawing mode
    event.preventDefault();
    event.stopPropagation();
  }
}

defineExpose({ refreshCanvas });
</script>

<template>
  <div class="drawing-overlay" :class="overlayClasses" @dblclick.stop.prevent="handleDoubleClick" @click.capture="handleOverlayClick"
       @touchstart.prevent="handleTouchStart" @touchmove.prevent="handleTouchMove" @touchend.prevent="handleTouchEnd">
    <div class="drawing-overlay__grid" v-if="drawingStore.showGrid"></div>
    <canvas ref="canvasRef" class="drawing-overlay__canvas" :style="{ pointerEvents: drawingStore.isDrawingMode ? 'auto' : 'none' }"></canvas>

    <div v-if="laserPosition" class="laser-pointer" :style="laserStyle"></div>
  </div>
</template>

<style scoped>
.drawing-overlay {
  position: absolute;
  inset: 0;
  z-index: 3000;
  pointer-events: none;
  user-select: none;
  overflow: hidden;
  touch-action: none; /* Prevent touch actions like pan and zoom */
}

.drawing-overlay.is-active {
  pointer-events: auto;
  touch-action: none; /* Ensure touch actions are blocked when drawing */
}

.drawing-overlay__canvas {
  width: 100%;
  height: 100%;
  display: block;
  cursor: crosshair;
  touch-action: none; /* Prevent touch scrolling/panning on canvas */
}

.drawing-overlay__grid {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(rgba(99, 102, 241, 0.08) 1px, transparent 1px),
    linear-gradient(90deg, rgba(99, 102, 241, 0.08) 1px, transparent 1px);
  background-size: 20px 20px;
  pointer-events: none;
  z-index: 1;
}

.laser-pointer {
  position: absolute;
  width: 16px;
  height: 16px;
  background: radial-gradient(circle, rgba(248, 113, 113, 1), rgba(239, 68, 68, 0.2));
  border-radius: 50%;
  transform: translate(-50%, -50%);
  pointer-events: none;
  box-shadow: 0 0 12px rgba(248, 113, 113, 0.8);
  z-index: 3;
}
</style>
