<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useDrawingStore } from '../../stores/drawingStore';
import { useDrawingEngine } from '../../composables/drawing/useDrawingEngine';
import { setupCanvas } from '../../composables/drawing/canvasRenderer';

const props = defineProps({
  scale: {
    type: Number,
    default: 1
  }
});

const emit = defineEmits(['draw', 'strokeEnd']);

const drawingStore = useDrawingStore();
const canvasRef = ref(null);
let renderer;

const {
  strokes,
  currentStroke,
  startStroke,
  addPoint,
  endStroke,
  undo,
  redo,
  clear
} = useDrawingEngine();

const overlayClasses = computed(() => ({
  'is-active': drawingStore.isDrawingMode,
  'has-grid': drawingStore.showGrid
}));

const laserPosition = ref(null);

const laserStyle = computed(() => {
  if (!laserPosition.value) return {};
  return {
    transform: `translate(${laserPosition.value.x}px, ${laserPosition.value.y}px)`
  };
});

function clearLaser() {
  laserPosition.value = null;
}

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

// =========================
// POINTER EVENTS
// =========================
function getPoint(e) {
  const rect = canvasRef.value.getBoundingClientRect();
  const scale = props.scale || 1;
  return {
    x: (e.clientX - rect.left) / scale,
    y: (e.clientY - rect.top) / scale
  };
}

function onPointerDown(e) {
  if (!drawingStore.isDrawingMode) return;

  if (drawingStore.activeTool === 'laser') {
    laserPosition.value = getPoint(e);
    return;
  }

  canvasRef.value.setPointerCapture(e.pointerId);

  const style = {
    type: drawingStore.activeTool,
    color: drawingStore.strokeColor,
    width: drawingStore.brushSize,
    opacity: drawingStore.activeTool === 'highlighter' ? drawingStore.highlighterOpacity / 100 : drawingStore.strokeOpacity / 100
  };

  startStroke(getPoint(e), style);
}

function onPointerMove(e) {
  if (!currentStroke.value) return;
  addPoint(getPoint(e));
  draw();
}

function onPointerUp() {
  if (!currentStroke.value) return;
  endStroke();
  draw();
  emit('strokeEnd', strokes.value);
  emit('draw', { type: 'stroke', strokes: strokes.value });
}

function onPointerCancel() {
  if (!currentStroke.value) return;
  endStroke();
  draw();
}

function onPointerMoveLaser(e) {
  if (drawingStore.activeTool === 'laser' && drawingStore.isDrawingMode) {
    laserPosition.value = getPoint(e);
  }
}

// =========================
// RENDER LOOP
// =========================
function draw() {
  if (!renderer) return;
  
  renderer.render(strokes.value, currentStroke.value);
  
  if (drawingStore.showGrid) {
    renderer.drawGrid(20, '#e5e7eb', 0.5);
  }
}

function refreshCanvas() {
  draw();
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

// =========================
// LIFECYCLE
// =========================
const onWindowResize = () => {
  if (!renderer) return;
  renderer.resize(props.scale);
  draw();
};

onMounted(() => {
  renderer = setupCanvas(canvasRef.value);
  renderer.resize(props.scale);

  draw();

  window.addEventListener('resize', onWindowResize);

  canvasRef.value.addEventListener('pointerdown', onPointerDown);
  canvasRef.value.addEventListener('pointermove', onPointerMove);
  canvasRef.value.addEventListener('pointerup', onPointerUp);
  canvasRef.value.addEventListener('pointercancel', onPointerCancel);
  canvasRef.value.addEventListener('lostpointercapture', onPointerCancel);
  canvasRef.value.addEventListener('pointermove', onPointerMoveLaser);
});

onUnmounted(() => {
  window.removeEventListener('resize', onWindowResize);

  if (!canvasRef.value) return;
  canvasRef.value.removeEventListener('pointerdown', onPointerDown);
  canvasRef.value.removeEventListener('pointermove', onPointerMove);
  canvasRef.value.removeEventListener('pointerup', onPointerUp);
  canvasRef.value.removeEventListener('pointercancel', onPointerCancel);
  canvasRef.value.removeEventListener('lostpointercapture', onPointerCancel);
  canvasRef.value.removeEventListener('pointermove', onPointerMoveLaser);
});

watch(strokes, draw, { deep: true });

watch(
  () => [drawingStore.showGrid, drawingStore.isDrawingMode],
  () => {
    if (!drawingStore.isDrawingMode) {
      clearLaser();
    }
    draw();
  }
);

watch(
  () => drawingStore.activeTool,
  (tool) => {
    if (tool !== 'laser') {
      clearLaser();
    }
  }
);

watch(
  () => props.scale,
  () => {
    if (!renderer) return;
    renderer.resize(props.scale);
    draw();
  }
);

// Expose controls for external use
defineExpose({ undo, redo, clear, refreshCanvas });

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
