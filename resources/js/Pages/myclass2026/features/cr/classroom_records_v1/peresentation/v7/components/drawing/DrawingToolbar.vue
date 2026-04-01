<script setup>
import { computed, ref, onMounted } from 'vue';
import { useDrawingStore, DRAWING_TOOLS, DEFAULT_DRAWING_PALETTE } from '../../stores/drawingStore';
import { useDrawingHistory } from '../../composables/drawing/useDrawingHistory';
import ToolButton from './ToolButton.vue';

const drawingStore = useDrawingStore();
const { undo, redo, canUndo, canRedo, clear } = useDrawingHistory();

onMounted(() => {
  const timestamp = new Date().toISOString();
  console.log(`[${timestamp}] DrawingToolbar component mounted`);
  console.log(`[${timestamp}] DrawingToolbar - isToolbarOpen:`, drawingStore.isToolbarOpen);
  console.log(`[${timestamp}] DrawingToolbar - activeTool:`, drawingStore.activeTool);
  console.log(`[${timestamp}] Build version: 2026-04-01-21-22-fixed`);
});

const palette = computed(() => drawingStore.customPalette?.length ? drawingStore.customPalette : DEFAULT_DRAWING_PALETTE);
const brushSize = ref(drawingStore.brushSize);
const opacity = ref(drawingStore.strokeOpacity);
const highlighterOpacity = ref(drawingStore.highlighterOpacity);

function setTool(tool) {
  drawingStore.setTool(tool);
}

function toggleToolbar() {
  drawingStore.toggleToolbar();
}

function toggleDrawingMode() {
  drawingStore.toggleDrawingMode();
}

function handleBrushSizeInput(event) {
  const value = Number(event.target.value);
  brushSize.value = value;
  drawingStore.setBrushSize(value);
}

function handleOpacityInput(event) {
  const value = Number(event.target.value);
  opacity.value = value;
  drawingStore.setStrokeOpacity(value);
}

function handleHighlighterOpacity(event) {
  const value = Number(event.target.value);
  highlighterOpacity.value = value;
  drawingStore.setHighlighterOpacity(value);
}

function handleColorPick(color) {
  drawingStore.setStrokeColor(color);
}

function handleClear() {
  clear();
}

const groupedTools = computed(() => ([
  {
    title: 'Freehand',
    tools: ['pen', 'highlighter']
  },
  {
    title: 'Shapes',
    tools: ['rectangle', 'circle', 'line', 'arrow']
  },
  {
    title: 'Special',
    tools: ['text', 'eraser', 'laser']
  }
]));
</script>

<template>
  <Teleport to="body">
    <div class="drawing-toolbar-wrapper">
      <div class="drawing-toolbar" :class="{ open: drawingStore.isToolbarOpen }">
        <div class="toolbar-header">
          <div>
            <h3>Annotation Tools</h3>
            <p>Overlay notes stay per slide and auto-save offline.</p>
          </div>
          <button class="close-btn" @click="toggleToolbar">⨯</button>
        </div>

        <div class="toolbar-section">
          <div class="mode-toggle">
            <span>Drawing Mode</span>
            <label class="switch">
              <input type="checkbox" :checked="drawingStore.isDrawingMode" @change="toggleDrawingMode" />
              <span class="slider"></span>
            </label>
          </div>
        </div>

        <div class="toolbar-section tools">
          <template v-for="group in groupedTools" :key="group.title">
            <p class="section-label">{{ group.title }}</p>
            <div class="tool-grid">
              <button
                v-for="tool in group.tools"
                :key="tool"
                class="tool-button"
                :class="{ active: drawingStore.activeTool === tool }"
                :title="tool"
                @click="setTool(tool)"
              >
                <!-- Pen Icon -->
                <svg v-if="tool === 'pen'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                  <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                  <path d="M2 2l7.586 7.586"></path>
                  <circle cx="11" cy="11" r="2"></circle>
                </svg>
                
                <!-- Highlighter Icon -->
                <svg v-else-if="tool === 'highlighter'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="m9 11-6 6v3h9l3-3"></path>
                  <path d="m22 12-4.6 4.6a2 2 0 0 1-2.8 0l-5.2-5.2a2 2 0 0 1 0-2.8L14 4"></path>
                </svg>
                
                <!-- Rectangle Icon -->
                <svg v-else-if="tool === 'rectangle'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                </svg>
                
                <!-- Circle Icon -->
                <svg v-else-if="tool === 'circle'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                </svg>
                
                <!-- Line Icon -->
                <svg v-else-if="tool === 'line'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="5" y1="19" x2="19" y2="5"></line>
                </svg>
                
                <!-- Arrow Icon -->
                <svg v-else-if="tool === 'arrow'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                  <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
                
                <!-- Text Icon -->
                <svg v-else-if="tool === 'text'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="4 7 4 4 20 4 20 7"></polyline>
                  <line x1="9" y1="20" x2="15" y2="20"></line>
                  <line x1="12" y1="4" x2="12" y2="20"></line>
                </svg>
                
                <!-- Eraser Icon -->
                <svg v-else-if="tool === 'eraser'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="m7 21-4.3-4.3c-1-1-1-2.5 0-3.4l9.6-9.6c1-1 2.5-1 3.4 0l5.6 5.6c1 1 1 2.5 0 3.4L13 21"></path>
                  <path d="M22 21H7"></path>
                  <path d="m5 11 9 9"></path>
                </svg>
                
                <!-- Laser Icon -->
                <svg v-else-if="tool === 'laser'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 2v6"></path>
                  <path d="M15 2v6"></path>
                  <path d="M12 17v5"></path>
                  <path d="M5 8v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"></path>
                  <circle cx="12" cy="12" r="2"></circle>
                </svg>
              </button>
            </div>
          </template>
        </div>

        <div class="toolbar-section">
          <p class="section-label">Colors</p>
          <div class="color-palette">
            <button
              v-for="color in palette"
              :key="color"
              class="color-dot"
              :style="{ backgroundColor: color, borderColor: drawingStore.strokeColor === color ? '#0f172a' : 'transparent' }"
              @click="handleColorPick(color)"
            ></button>
            <input type="color" class="color-picker" :value="drawingStore.strokeColor" @input="(event) => handleColorPick(event.target.value)" />
          </div>
        </div>

        <div class="toolbar-section sliders">
          <label>
            Brush Size
            <input type="range" min="1" max="48" v-model="brushSize" @input="handleBrushSizeInput" />
            <span>{{ brushSize }} px</span>
          </label>
          <label v-if="drawingStore.activeTool !== 'highlighter'">
            Opacity
            <input type="range" min="5" max="100" step="5" v-model="opacity" @input="handleOpacityInput" />
            <span>{{ opacity }}%</span>
          </label>
          <label v-else>
            Highlighter Opacity
            <input type="range" min="5" max="100" step="5" v-model="highlighterOpacity" @input="handleHighlighterOpacity" />
            <span>{{ highlighterOpacity }}%</span>
          </label>
        </div>

        <div class="toolbar-section actions">
          <ToolButton label="Undo" :disabled="!canUndo" @click="undo" />
          <ToolButton label="Redo" :disabled="!canRedo" @click="redo" />
          <ToolButton label="Clear Slide" @click="handleClear" />
        </div>
      </div>

      <button
        v-if="!drawingStore.isToolbarOpen"
        class="drawing-toolbar-handle"
        @click="toggleToolbar(true)"
        title="Show Annotation Tools"
      >
        ✏️ Annotations
      </button>
    </div>
  </Teleport>
</template>

<style scoped>
.drawing-toolbar {
  position: fixed;
  top: 80px;
  right: 24px;
  width: 320px;
  max-width: calc(100% - 48px);
  background: rgba(255, 255, 255, 0.98);
  border-radius: 24px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 35px 70px rgba(15, 23, 42, 0.18);
  z-index: 4000;
  transform: translateX(110%);
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 22px 22px 24px;
}

.drawing-toolbar.open {
  transform: translateX(0);
}

.toolbar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.toolbar-header h3 {
  font-size: 16px;
  margin: 0;
  color: #0f172a;
}

.toolbar-header p {
  margin: 4px 0 0;
  font-size: 13px;
  color: #475569;
}

.close-btn {
  border: none;
  background: transparent;
  font-size: 18px;
  cursor: pointer;
  color: #94a3b8;
}

.toolbar-section {
  margin-bottom: 18px;
}

.mode-toggle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  color: #0f172a;
}

.tools .tool-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.tool-button {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
  border-radius: 12px;
  border: 2px solid rgba(148, 163, 184, 0.3);
  background: rgba(255, 255, 255, 0.95);
  color: #475569;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.tool-button::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
  opacity: 0;
  transition: opacity 0.2s ease;
}

.tool-button:hover:not(.active) {
  border-color: #6366f1;
  color: #4f46e5;
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(99, 102, 241, 0.15);
}

.tool-button:hover::before {
  opacity: 1;
}

.tool-button.active {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  border-color: transparent;
  box-shadow: 0 12px 24px rgba(99, 102, 241, 0.4);
  transform: scale(1.05);
}

.tool-button svg {
  position: relative;
  z-index: 1;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

.section-label {
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.8px;
  color: #94a3b8;
  margin: 12px 0 8px;
}

.color-palette {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.color-dot {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer;
  transition: transform 0.15s ease;
}

.color-dot:hover {
  transform: translateY(-2px);
}

.color-picker {
  flex-grow: 1;
  height: 32px;
  border-radius: 8px;
  border: 1px solid #cbd5f5;
}

.sliders label {
  display: flex;
  flex-direction: column;
  font-size: 13px;
  color: #0f172a;
  font-weight: 600;
}

.sliders input[type='range'] {
  margin: 6px 0;
}

.sliders span {
  font-size: 12px;
  color: #475569;
}

.actions {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.switch {
  position: relative;
  width: 44px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #cbd5f5;
  transition: 0.2s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: '';
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.2s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #4f46e5;
}

input:checked + .slider:before {
  transform: translateX(20px);
}

.drawing-toolbar-handle {
  position: fixed;
  top: 120px;
  right: 12px;
  background: #0f172a;
  color: white;
  border: none;
  border-radius: 999px 0 0 999px;
  padding: 10px 14px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.35);
  z-index: 4001;
  cursor: pointer;
  font-weight: 600;
  letter-spacing: 0.5px;
}

@media (max-width: 768px) {
  .drawing-toolbar {
    width: calc(100% - 32px);
    right: 16px;
  }
  .drawing-toolbar-handle {
    top: auto;
    bottom: 140px;
    right: 8px;
  }
}
</style>
