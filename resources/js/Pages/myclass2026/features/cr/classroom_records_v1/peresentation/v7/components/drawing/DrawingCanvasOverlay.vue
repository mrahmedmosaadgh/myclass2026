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

defineExpose({ refreshCanvas });
</script>

<template>
  <div class="drawing-overlay" :class="overlayClasses" @dblclick.stop.prevent="handleDoubleClick" @click.capture="handleOverlayClick">
    <div class="drawing-overlay__grid" v-if="drawingStore.showGrid"></div>
    <canvas ref="canvasRef" class="drawing-overlay__canvas" :style="{ pointerEvents: drawingStore.isDrawingMode ? 'auto' : 'none' }"></canvas>

    <div v-if="!drawingStore.isDrawingMode" class="drawing-overlay__hint">
      <div class="hint-card">
        <p><strong>Drawing mode off.</strong> Open the annotation toolbar or double-click to start sketching.</p>
        <button class="hint-button" @click.stop="drawingStore.toggleDrawingMode(true); drawingStore.toggleToolbar(true)">
          Enter Drawing Mode
        </button>
      </div>
    </div>

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
}

.drawing-overlay.is-active {
  pointer-events: auto;
}

.drawing-overlay__canvas {
  width: 100%;
  height: 100%;
  display: block;
  cursor: crosshair;
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

.drawing-overlay__hint {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
  pointer-events: none;
}

.hint-card {
  pointer-events: auto;
  background: rgba(255, 255, 255, 0.94);
  border-radius: 14px;
  padding: 18px 22px;
  border: 1px solid rgba(148, 163, 184, 0.35);
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.15);
  text-align: center;
  max-width: 360px;
}

.hint-button {
  margin-top: 10px;
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 10px;
  padding: 8px 16px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.hint-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(79, 70, 229, 0.35);
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
