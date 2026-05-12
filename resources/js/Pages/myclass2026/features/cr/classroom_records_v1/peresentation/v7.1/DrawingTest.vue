<script setup>
import { ref, onMounted } from 'vue';
import { useDrawingStore, DRAWING_TOOLS, DEFAULT_DRAWING_PALETTE } from './stores/drawingStore';

// Initialize drawing store
const drawingStore = useDrawingStore();

// Test state
const testResults = ref([]);
const isDrawing = ref(false);

function addLog(message, data = null) {
  const timestamp = new Date().toISOString();
  const logEntry = {
    timestamp,
    message,
    data: data ? JSON.stringify(data, null, 2) : null
  };
  testResults.value.push(logEntry);
  console.log(`[${timestamp}] DRAWING TEST: ${message}`, data || '');
}

function testDrawingStore() {
  addLog('=== DRAWING STORE TEST START ===');
  
  // Test store initialization
  addLog('Drawing store initialized', {
    isDrawingMode: drawingStore.isDrawingMode,
    isToolbarOpen: drawingStore.isToolbarOpen,
    activeTool: drawingStore.activeTool,
    strokeColor: drawingStore.strokeColor,
    brushSize: drawingStore.brushSize
  });
  
  // Test tools array
  addLog('Available tools', DRAWING_TOOLS);
  
  // Test palette
  addLog('Default palette', DEFAULT_DRAWING_PALETTE);
  
  // Test tool switching
  addLog('Testing tool switching...');
  DRAWING_TOOLS.forEach(tool => {
    drawingStore.setTool(tool);
    addLog(`Switched to ${tool}`, { activeTool: drawingStore.activeTool });
  });
  
  // Test drawing mode toggle
  addLog('Testing drawing mode toggle...');
  drawingStore.toggleDrawingMode();
  addLog('Drawing mode ON', { isDrawingMode: drawingStore.isDrawingMode });
  
  drawingStore.toggleDrawingMode();
  addLog('Drawing mode OFF', { isDrawingMode: drawingStore.isDrawingMode });
  
  // Test toolbar toggle
  addLog('Testing toolbar toggle...');
  drawingStore.toggleToolbar();
  addLog('Toolbar OFF', { isToolbarOpen: drawingStore.isToolbarOpen });
  
  drawingStore.toggleToolbar();
  addLog('Toolbar ON', { isToolbarOpen: drawingStore.isToolbarOpen });
  
  addLog('=== DRAWING STORE TEST COMPLETE ===');
}

function testCanvasDrawing() {
  addLog('=== CANVAS DRAWING TEST START ===');
  
  const canvas = document.getElementById('test-canvas');
  if (!canvas) {
    addLog('❌ Canvas not found!');
    return;
  }
  
  addLog('✅ Canvas found', { 
    width: canvas.width, 
    height: canvas.height,
    offsetWidth: canvas.offsetWidth,
    offsetHeight: canvas.offsetHeight
  });
  
  const ctx = canvas.getContext('2d');
  if (!ctx) {
    addLog('❌ Canvas context not available!');
    return;
  }
  
  addLog('✅ Canvas context available');
  
  // Test basic drawing
  ctx.strokeStyle = drawingStore.strokeColor;
  ctx.lineWidth = drawingStore.brushSize;
  ctx.lineCap = 'round';
  
  addLog('Drawing test line...', {
    strokeColor: drawingStore.strokeColor,
    lineWidth: drawingStore.brushSize
  });
  
  ctx.beginPath();
  ctx.moveTo(50, 50);
  ctx.lineTo(200, 50);
  ctx.lineTo(200, 100);
  ctx.stroke();
  
  addLog('✅ Basic drawing test complete');
  addLog('=== CANVAS DRAWING TEST COMPLETE ===');
}

function testMouseEvents() {
  addLog('=== MOUSE EVENTS TEST START ===');
  
  const canvas = document.getElementById('test-canvas');
  if (!canvas) {
    addLog('❌ Canvas not found for mouse test!');
    return;
  }
  
  let isMouseDown = false;
  let lastX = 0;
  let lastY = 0;
  
  function startDrawing(e) {
    isMouseDown = true;
    const rect = canvas.getBoundingClientRect();
    lastX = e.clientX - rect.left;
    lastY = e.clientY - rect.top;
    addLog('🖱️ Mouse down', { x: lastX, y: lastY });
  }
  
  function draw(e) {
    if (!isMouseDown) return;
    
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    const ctx = canvas.getContext('2d');
    ctx.strokeStyle = drawingStore.strokeColor;
    ctx.lineWidth = drawingStore.brushSize;
    ctx.lineCap = 'round';
    
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(x, y);
    ctx.stroke();
    
    lastX = x;
    lastY = y;
  }
  
  function stopDrawing() {
    if (isMouseDown) {
      isMouseDown = false;
      addLog('🖱️ Mouse up - drawing stopped');
    }
  }
  
  canvas.addEventListener('mousedown', startDrawing);
  canvas.addEventListener('mousemove', draw);
  canvas.addEventListener('mouseup', stopDrawing);
  canvas.addEventListener('mouseout', stopDrawing);
  
  addLog('✅ Mouse event listeners attached');
  addLog('=== MOUSE EVENTS TEST COMPLETE ===');
}

function clearCanvas() {
  const canvas = document.getElementById('test-canvas');
  if (canvas) {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    addLog('🧹 Canvas cleared');
  }
}

function clearLogs() {
  testResults.value = [];
  addLog('📋 Logs cleared - fresh start');
}

onMounted(() => {
  addLog('🚀 Drawing Test Page mounted');
  
  // Wait a moment for store to initialize
  setTimeout(() => {
    testDrawingStore();
    testCanvasDrawing();
    testMouseEvents();
  }, 100);
});
</script>

<template>
  <div class="drawing-test-page">
    <div class="test-header">
      <h1>🎨 Drawing Tools Test Page</h1>
      <p>Full diagnostic testing for drawing functionality</p>
    </div>
    
    <div class="test-controls">
      <button @click="testDrawingStore" class="test-btn">🔧 Test Store</button>
      <button @click="testCanvasDrawing" class="test-btn">🖼️ Test Canvas</button>
      <button @click="testMouseEvents" class="test-btn">🖱️ Test Mouse</button>
      <button @click="clearCanvas" class="test-btn">🧹 Clear Canvas</button>
      <button @click="clearLogs" class="test-btn">📋 Clear Logs</button>
    </div>
    
    <div class="test-content">
      <div class="canvas-section">
        <h2>Test Canvas</h2>
        <canvas 
          id="test-canvas" 
          width="400" 
          height="300"
          class="test-canvas"
          style="border: 2px solid #374151; background: white;"
        ></canvas>
        <p class="canvas-info">Try drawing on this canvas with your mouse</p>
      </div>
      
      <div class="logs-section">
        <h2>Test Logs ({{ testResults.length }})</h2>
        <div class="logs-container">
          <div 
            v-for="(log, index) in testResults" 
            :key="index"
            class="log-entry"
          >
            <div class="log-timestamp">{{ log.timestamp }}</div>
            <div class="log-message">{{ log.message }}</div>
            <div v-if="log.data" class="log-data">
              <pre>{{ log.data }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="test-footer">
      <p>URL: <code>/builder-v7-test-drawing</code></p>
      <p>Build: {{ new Date().toISOString() }}</p>
    </div>
  </div>
</template>

<style scoped>
.drawing-test-page {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: monospace;
  background: #1f2937;
  color: #f3f4f6;
  min-height: 100vh;
}

.test-header {
  text-align: center;
  margin-bottom: 30px;
}

.test-header h1 {
  font-size: 2.5rem;
  margin-bottom: 10px;
}

.test-controls {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.test-btn {
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

.test-btn:hover {
  background: #2563eb;
}

.test-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 30px;
}

.canvas-section {
  background: #374151;
  padding: 20px;
  border-radius: 8px;
}

.canvas-section h2 {
  margin-bottom: 15px;
  color: #60a5fa;
}

.test-canvas {
  display: block;
  margin: 0 auto 15px;
  cursor: crosshair;
}

.canvas-info {
  text-align: center;
  font-size: 0.9rem;
  color: #9ca3af;
}

.logs-section {
  background: #374151;
  padding: 20px;
  border-radius: 8px;
}

.logs-section h2 {
  margin-bottom: 15px;
  color: #60a5fa;
}

.logs-container {
  max-height: 400px;
  overflow-y: auto;
  background: #1f2937;
  padding: 15px;
  border-radius: 6px;
}

.log-entry {
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid #374151;
}

.log-entry:last-child {
  border-bottom: none;
}

.log-timestamp {
  font-size: 0.8rem;
  color: #9ca3af;
  margin-bottom: 5px;
}

.log-message {
  font-weight: bold;
  margin-bottom: 5px;
  color: #fbbf24;
}

.log-data {
  background: #111827;
  padding: 10px;
  border-radius: 4px;
  font-size: 0.8rem;
  overflow-x: auto;
}

.log-data pre {
  margin: 0;
  color: #10b981;
}

.test-footer {
  text-align: center;
  padding: 20px;
  background: #374151;
  border-radius: 8px;
}

.test-footer code {
  background: #1f2937;
  padding: 5px 10px;
  border-radius: 4px;
  color: #60a5fa;
}

@media (max-width: 768px) {
  .test-content {
    grid-template-columns: 1fr;
  }
  
  .test-controls {
    justify-content: stretch;
  }
  
  .test-btn {
    flex: 1;
  }
}
</style>
