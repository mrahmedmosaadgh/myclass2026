<template>
  <div class="drawing-canvas-overlay" :class="{ 'active': isActive }" :style="{ backgroundColor: backgroundColor }">
    <canvas
      ref="canvas"
      @mousedown="startDrawing"
      @mousemove="draw"
      @mouseup="stopDrawing"
      @mouseleave="stopDrawing"
      @touchstart="handleTouchStart"
      @touchmove="handleTouchMove"
      @touchend="stopDrawing"
    ></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';

const props = defineProps({
  isActive: {
    type: Boolean,
    default: false
  },
  drawingTool: {
    type: String,
    default: 'pen'
  },
  penColor: {
    type: String,
    default: '#FF0000'
  },
  penSize: {
    type: Number,
    default: 3
  },
  backgroundColor: {
    type: String,
    default: 'transparent'
  },
  currentPage: {
    type: Number,
    default: 1
  },
  clearTrigger: {
    type: Number,
    default: 0
  },
  storageKey: {
    type: String,
    default: 'drawing-pages'
  }
});

const emit = defineEmits(['strokes-updated', 'pages-updated', 'page-background-changed']);

const canvas = ref(null);
const ctx = ref(null);
const isDrawing = ref(false);
const pages = ref({});
const pageBackgrounds = ref({});
const currentStroke = ref([]);

// Computed
const currentPageStrokes = computed(() => pages.value[props.currentPage] || []);
const currentPageBackground = computed(() => pageBackgrounds.value[props.currentPage] || 'transparent');

onMounted(() => {
  initCanvas();
  loadFromStorage();
  window.addEventListener('resize', resizeCanvas);
});

onUnmounted(() => {
  window.removeEventListener('resize', resizeCanvas);
  saveToStorage();
});

watch(() => props.clearTrigger, () => {
  clearCurrentPage();
});

watch(() => props.isActive, (newVal) => {
  if (newVal) {
    resizeCanvas();
  }
});

watch(() => props.currentPage, () => {
  // Emit background color for current page
  emit('page-background-changed', currentPageBackground.value);
  redrawCanvas();
});

watch(() => props.backgroundColor, (newVal) => {
  // Save background color for current page
  pageBackgrounds.value[props.currentPage] = newVal;
  saveToStorage();
  redrawCanvas();
});

const initCanvas = () => {
  if (!canvas.value) return;
  
  ctx.value = canvas.value.getContext('2d');
  resizeCanvas();
  
  ctx.value.lineCap = 'round';
  ctx.value.lineJoin = 'round';
};

const resizeCanvas = () => {
  if (!canvas.value) return;
  
  const rect = canvas.value.parentElement.getBoundingClientRect();
  canvas.value.width = rect.width;
  canvas.value.height = rect.height;
  
  redrawCanvas();
};

const startDrawing = (e) => {
  if (!props.isActive) return;
  
  isDrawing.value = true;
  const pos = getMousePos(e);
  
  // For eraser, mark stroke as eraser type
  currentStroke.value = [{
    x: pos.x,
    y: pos.y,
    color: props.penColor,
    size: props.penSize,
    tool: props.drawingTool
  }];
};

const draw = (e) => {
  if (!isDrawing.value || !props.isActive) return;
  
  const pos = getMousePos(e);
  currentStroke.value.push({
    x: pos.x,
    y: pos.y,
    color: props.penColor,
    size: props.penSize,
    tool: props.drawingTool
  });
  
  // Redraw entire canvas with current stroke
  redrawCanvas();
  drawStroke(currentStroke.value);
};

const stopDrawing = () => {
  if (!isDrawing.value) return;
  
  isDrawing.value = false;
  if (currentStroke.value.length > 0) {
    // Add stroke to current page
    if (!pages.value[props.currentPage]) {
      pages.value[props.currentPage] = [];
    }
    pages.value[props.currentPage].push([...currentStroke.value]);
    currentStroke.value = [];
    
    saveToStorage();
    emit('strokes-updated', currentPageStrokes.value);
  }
};

const handleTouchStart = (e) => {
  e.preventDefault();
  const touch = e.touches[0];
  const mouseEvent = new MouseEvent('mousedown', {
    clientX: touch.clientX,
    clientY: touch.clientY
  });
  canvas.value.dispatchEvent(mouseEvent);
};

const handleTouchMove = (e) => {
  e.preventDefault();
  const touch = e.touches[0];
  const mouseEvent = new MouseEvent('mousemove', {
    clientX: touch.clientX,
    clientY: touch.clientY
  });
  canvas.value.dispatchEvent(mouseEvent);
};

const getMousePos = (e) => {
  const rect = canvas.value.getBoundingClientRect();
  return {
    x: e.clientX - rect.left,
    y: e.clientY - rect.top
  };
};

const drawStroke = (stroke) => {
  if (!ctx.value || stroke.length < 2) return;
  
  // Set composite mode based on tool
  if (stroke[0].tool === 'eraser') {
    ctx.value.globalCompositeOperation = 'destination-out';
    ctx.value.strokeStyle = 'rgba(0,0,0,1)'; // Use opaque black for erasing
    ctx.value.lineWidth = stroke[0].size * 3; // Make eraser wider
  } else {
    ctx.value.globalCompositeOperation = 'source-over';
    ctx.value.strokeStyle = stroke[0].color;
    ctx.value.lineWidth = stroke[0].size;
  }
  
  ctx.value.beginPath();
  ctx.value.moveTo(stroke[0].x, stroke[0].y);
  
  for (let i = 1; i < stroke.length; i++) {
    ctx.value.lineTo(stroke[i].x, stroke[i].y);
  }
  
  ctx.value.stroke();
  
  // Reset composite mode
  ctx.value.globalCompositeOperation = 'source-over';
};

const redrawCanvas = () => {
  if (!ctx.value) return;
  
  ctx.value.clearRect(0, 0, canvas.value.width, canvas.value.height);
  
  const strokes = currentPageStrokes.value;
  strokes.forEach(stroke => {
    drawStroke(stroke);
  });
};

const clearCurrentPage = () => {
  pages.value[props.currentPage] = [];
  currentStroke.value = [];
  if (ctx.value) {
    ctx.value.clearRect(0, 0, canvas.value.width, canvas.value.height);
  }
  saveToStorage();
  emit('strokes-updated', []);
};

const undo = () => {
  if (!pages.value[props.currentPage] || pages.value[props.currentPage].length === 0) return;
  
  pages.value[props.currentPage].pop();
  redrawCanvas();
  saveToStorage();
  emit('strokes-updated', currentPageStrokes.value);
};

const saveToStorage = () => {
  try {
    const data = {
      pages: pages.value,
      backgrounds: pageBackgrounds.value
    };
    localStorage.setItem(props.storageKey, JSON.stringify(data));
    emit('pages-updated', Object.keys(pages.value).length);
  } catch (error) {
    console.error('Failed to save to localStorage:', error);
  }
};

const loadFromStorage = () => {
  try {
    const saved = localStorage.getItem(props.storageKey);
    if (saved) {
      const data = JSON.parse(saved);
      
      // Support old format (just pages) and new format (pages + backgrounds)
      if (data.pages) {
        pages.value = data.pages;
        pageBackgrounds.value = data.backgrounds || {};
      } else {
        // Old format - just pages
        pages.value = data;
        pageBackgrounds.value = {};
      }
      
      // Emit background for current page
      emit('page-background-changed', currentPageBackground.value);
      redrawCanvas();
      emit('pages-updated', Object.keys(pages.value).length);
    }
  } catch (error) {
    console.error('Failed to load from localStorage:', error);
  }
};

const addNewPage = (pageNumber) => {
  if (!pages.value[pageNumber]) {
    pages.value[pageNumber] = [];
    saveToStorage();
    emit('pages-updated', Object.keys(pages.value).length);
  }
};

defineExpose({
  clearCurrentPage,
  undo,
  addNewPage,
  saveToStorage,
  loadFromStorage
});
</script>

<style scoped lang="scss">
.drawing-canvas-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  pointer-events: none;
  z-index: 200;
  transition: background-color 0.3s ease;
  
  &.active {
    pointer-events: all;
    border: 3px dashed rgba(255, 87, 34, 0.6);
    
    canvas {
      cursor: crosshair;
    }
  }
  
  canvas {
    width: 100%;
    height: 100%;
    cursor: default;
  }
}
</style>
