<template>
  <div class="drawing-canvas-container">
    <div class="toolbar">
      <div class="tool-group">
        <label for="brush-size">Brush Size:</label>
        <input
          id="brush-size"
          type="range"
          min="1"
          max="50"
          v-model="brushSize"
          class="slider"
        />
        <span>{{ brushSize }}px</span>
      </div>
      
      <div class="tool-group">
        <label for="color-picker">Color:</label>
        <input
          id="color-picker"
          type="color"
          v-model="brushColor"
          class="color-input"
        />
      </div>
      
      <div class="tool-group">
        <button @click="clearCanvas" class="clear-btn">Clear Canvas</button>
      </div>
    </div>
    
    <div class="canvas-container">
      <canvas
        ref="canvasRef"
        class="drawing-canvas"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
      ></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue']);

// Reactive variables
const canvasRef = ref(null);
const brushSize = ref(5);
const brushColor = ref('#000000');
const isDrawing = ref(false);
const lastX = ref(0);
const lastY = ref(0);

// Initialize canvas
onMounted(async () => {
  await nextTick();
  initCanvas();
  loadCanvasContent();
});

const initCanvas = () => {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  
  // Set canvas dimensions to match its display size
  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width;
  canvas.height = rect.height;
  
  // Set default styles
  ctx.lineJoin = 'round';
  ctx.lineCap = 'round';
  ctx.lineWidth = brushSize.value;
  ctx.strokeStyle = brushColor.value;
};

const loadCanvasContent = () => {
  if (props.modelValue && props.modelValue.image) {
    const img = new Image();
    img.onload = () => {
      const canvas = canvasRef.value;
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0); 
    };
    img.src = props.modelValue.image;
  }
};

const saveCanvasContent = () => {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const dataUrl = canvas.toDataURL('image/png');
  // Emit update preserving other potential properties
  emit('update:modelValue', { ...props.modelValue, image: dataUrl });
};

// Drawing functions
const startDrawing = (e) => {
  isDrawing.value = true;
  [lastX.value, lastY.value] = [e.offsetX, e.offsetY];
};

const draw = (e) => {
  if (!isDrawing.value) return;
  
  const canvas = canvasRef.value;
  const ctx = canvas.getContext('2d');
  
  ctx.beginPath();
  ctx.moveTo(lastX.value, lastY.value);
  ctx.lineTo(e.offsetX, e.offsetY);
  ctx.stroke();
  
  [lastX.value, lastY.value] = [e.offsetX, e.offsetY];
};

const stopDrawing = () => {
  if (isDrawing.value) {
      isDrawing.value = false;
      saveCanvasContent();
  }
};

const clearCanvas = () => {
  const canvas = canvasRef.value;
  const ctx = canvas.getContext('2d');
  
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  saveCanvasContent();
};

// Watch for changes in brush properties
const updateBrush = () => {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  
  ctx.lineWidth = parseInt(brushSize.value);
  ctx.strokeStyle = brushColor.value;
};

// Watch for property changes and update canvas context accordingly
watch([brushSize, brushColor], updateBrush);
</script>

<style scoped>
.drawing-canvas-container {
  display: flex;
  flex-direction: column;
  width: 100%;
  border: 1px solid #ccc;
  border-radius: 4px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 0.75rem;
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.tool-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.slider {
  width: 100px;
}

.color-input {
  width: 50px;
  height: 30px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  padding: 0;
  cursor: pointer;
}

.clear-btn {
  padding: 0.375rem 0.75rem;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
}

.clear-btn:hover {
  background-color: #c82333;
}

.canvas-container {
  position: relative;
  width: 100%;
  height: 500px;
  background-color: white;
}

.drawing-canvas {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  cursor: crosshair;
  touch-action: none;
}
</style>