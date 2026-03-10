<template>
  <div class="drawing-overlay-wrapper">
    <!-- Drawing Canvas Overlay -->
    <DrawingCanvas
      ref="canvasRef"
      :is-active="isDrawingActive"
      :drawing-tool="drawingTool"
      :pen-color="penColor"
      :pen-size="penSize"
      :background-color="backgroundColor"
      :current-page="currentPage"
      :clear-trigger="clearTrigger"
      :storage-key="storageKey"
      @strokes-updated="handleStrokesUpdated"
      @pages-updated="handlePagesUpdated"
      @page-background-changed="handlePageBackgroundChanged"
    />

    <!-- Floating Toolbar -->
    <DrawingToolbar
      :is-active="isDrawingActive"
      v-model:drawing-tool="drawingTool"
      v-model:pen-color="penColor"
      v-model:pen-size="penSize"
      v-model:background-color="backgroundColor"
      :current-page="currentPage"
      :total-pages="totalPages"
      @toggle="toggleDrawing"
      @undo="undo"
      @clear="clear"
      @prev-page="prevPage"
      @next-page="nextPage"
      @add-page="addPage"
    />

    <!-- Drawing Mode Indicator -->
    <transition name="fade">
      <div v-if="isDrawingActive" class="drawing-mode-indicator">
        <q-icon :name="drawingTool === 'pen' ? 'brush' : 'cleaning_services'" size="sm" class="q-mr-xs" />
        {{ drawingTool === 'pen' ? 'Drawing Mode' : 'Eraser Mode' }} - Page {{ currentPage }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import DrawingCanvas from './DrawingComp/DrawingCanvas.vue';
import DrawingToolbar from './DrawingComp/DrawingToolbar.vue';

const emit = defineEmits(['drawing-state-changed']);

const canvasRef = ref(null);
const isDrawingActive = ref(false);
const drawingTool = ref('pen');
const penColor = ref('#FF0000');
const penSize = ref(3);
const backgroundColor = ref('transparent');
const currentPage = ref(1);
const totalPages = ref(1);
const clearTrigger = ref(0);

// Generate unique storage key based on lesson/presentation
const storageKey = computed(() => {
  const url = window.location.pathname;
  const match = url.match(/\/lesson-presentation\/student\/(\d+)/);
  return match ? `drawing-lesson-${match[1]}` : 'drawing-pages';
});

const toggleDrawing = () => {
  isDrawingActive.value = !isDrawingActive.value;
  
  // Reset background to transparent when exiting
  if (!isDrawingActive.value) {
    backgroundColor.value = 'transparent';
  }
  
  emit('drawing-state-changed', isDrawingActive.value);
};

const handlePageBackgroundChanged = (bgColor) => {
  // Update background color when switching pages
  backgroundColor.value = bgColor;
};

const undo = () => {
  if (canvasRef.value) {
    canvasRef.value.undo();
  }
};

const clear = () => {
  clearTrigger.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  currentPage.value++;
  // Ensure page exists
  if (canvasRef.value) {
    canvasRef.value.addNewPage(currentPage.value);
  }
  if (currentPage.value > totalPages.value) {
    totalPages.value = currentPage.value;
  }
};

const addPage = () => {
  totalPages.value++;
  currentPage.value = totalPages.value;
  if (canvasRef.value) {
    canvasRef.value.addNewPage(currentPage.value);
  }
};

const handleStrokesUpdated = (strokes) => {
  // Can emit or save strokes if needed
};

const handlePagesUpdated = (pageCount) => {
  if (pageCount > totalPages.value) {
    totalPages.value = pageCount;
  }
};

defineExpose({
  toggleDrawing,
  isActive: isDrawingActive
});
</script>

<style scoped lang="scss">
.drawing-overlay-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  pointer-events: none;
  z-index: 200;
}

.drawing-mode-indicator {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(255, 87, 34, 0.95);
  color: white;
  padding: 10px 24px;
  border-radius: 24px;
  font-weight: 600;
  font-size: 14px;
  z-index: 250;
  display: flex;
  align-items: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  pointer-events: none;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
