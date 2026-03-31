<script setup>
import { computed, ref } from 'vue';
import { useDrawingStore, DRAWING_TOOLS, DEFAULT_DRAWING_PALETTE } from '../../stores/drawingStore';
import { useDrawingHistory } from '../../composables/drawing/useDrawingHistory';
import ToolButton from './ToolButton.vue';

const drawingStore = useDrawingStore();
const { undo, redo, canUndo, canRedo, clear } = useDrawingHistory();

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
              <ToolButton
                v-for="tool in group.tools"
                :key="tool"
                :label="tool"
                :active="drawingStore.activeTool === tool"
                @click="setTool(tool)"
              />
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
