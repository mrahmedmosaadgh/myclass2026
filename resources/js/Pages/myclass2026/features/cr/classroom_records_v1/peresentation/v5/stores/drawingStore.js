import { defineStore } from 'pinia';
import { computed, reactive, ref, watch } from 'vue';
import { usePresentationStore } from './presentationStore';

const AUTO_SAVE_DELAY = 700;
const HISTORY_LIMIT = 60;

export const DRAWING_TOOLS = [
  'pen',
  'highlighter',
  'rectangle',
  'circle',
  'line',
  'arrow',
  'text',
  'eraser',
  'laser'
];

export const DEFAULT_DRAWING_PALETTE = [
  '#0f172a',
  '#2563eb',
  '#f87171',
  '#fbbf24',
  '#22c55e',
  '#0ea5e9',
  '#a855f7'
];

export function cloneDrawingsPayload(drawings = []) {
  return Array.isArray(drawings) ? JSON.parse(JSON.stringify(drawings)) : [];
}

export function generateDrawingId(prefix = 'draw') {
  return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

export const useDrawingStore = defineStore('presentation-drawing', () => {
  const presentation = usePresentationStore();

  const isDrawingMode = ref(false);
  const isToolbarOpen = ref(false);
  const activeTool = ref('pen');
  const strokeColor = ref('#0f172a');
  const brushSize = ref(4);
  const strokeOpacity = ref(100);
  const highlighterOpacity = ref(40);
  const showGrid = ref(false);
  const snapToGrid = ref(false);
  const laserPointerActive = ref(false);
  const customPalette = ref([...DEFAULT_DRAWING_PALETTE]);
  const slideBuffers = reactive({});
  const saveTimers = reactive({});

  const currentSlideId = computed(() => presentation.currentSlide?.id || null);
  const slideIds = computed(() => (presentation.slides || []).map(slide => slide.id));

  watch(
    slideIds,
    (ids) => {
      ids.forEach((id) => ensureSlideBuffer(id));
      Object.keys(slideBuffers).forEach((bufferId) => {
        if (!ids.includes(bufferId)) {
          clearAutoSave(bufferId);
          delete slideBuffers[bufferId];
        }
      });
    },
    { immediate: true }
  );

  watch(
    currentSlideId,
    (slideId) => {
      if (slideId) {
        ensureSlideBuffer(slideId, { hydrate: true });
      }
    },
    { immediate: true }
  );

  function getSlideById(slideId) {
    return (presentation.slides || []).find((slide) => slide.id === slideId);
  }

  function ensureSlideBuffer(slideId, { hydrate = false } = {}) {
    if (!slideId) return null;
    const slide = getSlideById(slideId);
    if (!slideBuffers[slideId] || hydrate) {
      slideBuffers[slideId] = {
        drawings: cloneDrawingsPayload(slide?.drawings || []),
        history: [],
        future: [],
        meta: slide?.drawingsMeta ? { ...slide.drawingsMeta } : {},
        isDirty: false
      };
    }
    return slideBuffers[slideId];
  }

  function setTool(tool) {
    if (DRAWING_TOOLS.includes(tool)) {
      activeTool.value = tool;
      if (tool === 'laser') {
        laserPointerActive.value = true;
      } else if (laserPointerActive.value) {
        laserPointerActive.value = false;
      }
    }
  }

  function toggleToolbar(forceValue = null) {
    isToolbarOpen.value = typeof forceValue === 'boolean' ? forceValue : !isToolbarOpen.value;
  }

  function toggleDrawingMode(forceValue = null) {
    const next = typeof forceValue === 'boolean' ? forceValue : !isDrawingMode.value;
    isDrawingMode.value = next;
    if (!next) {
      laserPointerActive.value = false;
    }
  }

  function setStrokeColor(color) {
    strokeColor.value = color;
  }

  function setBrushSize(size) {
    brushSize.value = Math.max(1, Math.min(48, size));
  }

  function setStrokeOpacity(value) {
    strokeOpacity.value = Math.max(5, Math.min(100, value));
  }

  function setHighlighterOpacity(value) {
    highlighterOpacity.value = Math.max(5, Math.min(100, value));
  }

  function toggleGrid(forceValue = null) {
    showGrid.value = typeof forceValue === 'boolean' ? forceValue : !showGrid.value;
  }

  function toggleSnap(forceValue = null) {
    snapToGrid.value = typeof forceValue === 'boolean' ? forceValue : !snapToGrid.value;
  }

  function setCustomPalette(colors) {
    if (Array.isArray(colors) && colors.length) {
      customPalette.value = [...colors];
    }
  }

  function pushHistory(slideId = currentSlideId.value) {
    if (!slideId) return;
    const buffer = ensureSlideBuffer(slideId);
    if (!buffer) return;
    buffer.history.push(cloneDrawingsPayload(buffer.drawings));
    if (buffer.history.length > HISTORY_LIMIT) {
      buffer.history.shift();
    }
    buffer.future = [];
  }

  function canUndo(slideId = currentSlideId.value) {
    const buffer = ensureSlideBuffer(slideId, { hydrate: true });
    return !!(buffer && buffer.history.length);
  }

  function canRedo(slideId = currentSlideId.value) {
    const buffer = ensureSlideBuffer(slideId, { hydrate: true });
    return !!(buffer && buffer.future.length);
  }

  function undo(slideId = currentSlideId.value) {
    const buffer = ensureSlideBuffer(slideId);
    if (!buffer || !buffer.history.length) return;
    const previous = buffer.history.pop();
    buffer.future.push(cloneDrawingsPayload(buffer.drawings));
    buffer.drawings = cloneDrawingsPayload(previous);
    buffer.isDirty = true;
    scheduleAutoSave(slideId);
  }

  function redo(slideId = currentSlideId.value) {
    const buffer = ensureSlideBuffer(slideId);
    if (!buffer || !buffer.future.length) return;
    const next = buffer.future.pop();
    buffer.history.push(cloneDrawingsPayload(buffer.drawings));
    buffer.drawings = cloneDrawingsPayload(next);
    buffer.isDirty = true;
    scheduleAutoSave(slideId);
  }

  function setSlideDrawings(slideId, drawings, { skipHistory = false } = {}) {
    if (!slideId) return;
    const buffer = ensureSlideBuffer(slideId);
    if (!buffer) return;
    if (!skipHistory) {
      pushHistory(slideId);
    }
    buffer.drawings = cloneDrawingsPayload(drawings);
    buffer.isDirty = true;
    scheduleAutoSave(slideId);
  }

  function appendDrawing(slideId, drawing, { skipHistory = false } = {}) {
    if (!slideId || !drawing) return;
    const buffer = ensureSlideBuffer(slideId);
    if (!buffer) return;
    if (!skipHistory) {
      pushHistory(slideId);
    }
    const payload = {
      id: drawing.id || generateDrawingId(),
      ...drawing
    };
    buffer.drawings.push(payload);
    buffer.isDirty = true;
    scheduleAutoSave(slideId);
  }

  function updateDrawing(slideId, drawingId, changes, { skipHistory = false } = {}) {
    const buffer = ensureSlideBuffer(slideId);
    if (!buffer) return;
    const index = buffer.drawings.findIndex((item) => item.id === drawingId);
    if (index === -1) return;
    if (!skipHistory) {
      pushHistory(slideId);
    }
    buffer.drawings[index] = {
      ...buffer.drawings[index],
      ...changes
    };
    buffer.isDirty = true;
    scheduleAutoSave(slideId);
  }

  function deleteDrawing(slideId, drawingId, { skipHistory = false } = {}) {
    const buffer = ensureSlideBuffer(slideId);
    if (!buffer) return;
    const next = buffer.drawings.filter((item) => item.id !== drawingId);
    if (next.length === buffer.drawings.length) return;
    if (!skipHistory) {
      pushHistory(slideId);
    }
    buffer.drawings = next;
    buffer.isDirty = true;
    scheduleAutoSave(slideId);
  }

  function clearSlideDrawings(slideId = currentSlideId.value) {
    const buffer = ensureSlideBuffer(slideId);
    if (!buffer) return;
    if (buffer.drawings.length === 0) return;
    pushHistory(slideId);
    buffer.drawings = [];
    buffer.isDirty = true;
    scheduleAutoSave(slideId);
  }

  function scheduleAutoSave(slideId) {
    if (!slideId) return;
    clearAutoSave(slideId);
    saveTimers[slideId] = setTimeout(() => {
      flushSlideToPresentation(slideId);
    }, AUTO_SAVE_DELAY);
  }

  function clearAutoSave(slideId) {
    if (saveTimers[slideId]) {
      clearTimeout(saveTimers[slideId]);
      delete saveTimers[slideId];
    }
  }

  function flushSlideToPresentation(slideId = currentSlideId.value) {
    if (!slideId) return;
    const buffer = ensureSlideBuffer(slideId);
    if (!buffer) return;
    const now = new Date().toISOString();
    presentation.updateSlideDrawings(slideId, cloneDrawingsPayload(buffer.drawings));
    presentation.updateSlideDrawingsMeta(slideId, {
      lastSavedAt: now,
      lastModified: buffer.drawings?.length ? buffer.drawings[buffer.drawings.length - 1].timestamp || now : now
    });
    buffer.isDirty = false;
    clearAutoSave(slideId);
  }

  function serializeSlide(slideId) {
    const buffer = ensureSlideBuffer(slideId, { hydrate: true });
    if (!buffer) return null;
    return {
      slideId,
      drawings: cloneDrawingsPayload(buffer.drawings),
      meta: { ...buffer.meta }
    };
  }

  function serializeAllSlides() {
    return (presentation.slides || []).map((slide) => serializeSlide(slide.id));
  }

  function hydrateSlideFromSerialized(payload) {
    if (!payload?.slideId) return;
    const buffer = ensureSlideBuffer(payload.slideId, { hydrate: false });
    if (!buffer) return;
    buffer.drawings = cloneDrawingsPayload(payload.drawings || []);
    buffer.meta = payload.meta ? { ...payload.meta } : {};
    buffer.isDirty = true;
    scheduleAutoSave(payload.slideId);
  }

  function hydrateMultipleSlides(payloads = []) {
    if (!Array.isArray(payloads)) return;
    payloads.forEach((payload) => hydrateSlideFromSerialized(payload));
  }

  function isSlideDirty(slideId = currentSlideId.value) {
    const buffer = ensureSlideBuffer(slideId, { hydrate: true });
    return !!(buffer && buffer.isDirty);
  }

  return {
    isDrawingMode,
    isToolbarOpen,
    activeTool,
    strokeColor,
    brushSize,
    strokeOpacity,
    highlighterOpacity,
    showGrid,
    snapToGrid,
    laserPointerActive,
    customPalette,
    currentSlideId,
    slideBuffers,
    setTool,
    setStrokeColor,
    setBrushSize,
    setStrokeOpacity,
    setHighlighterOpacity,
    toggleGrid,
    toggleSnap,
    toggleToolbar,
    toggleDrawingMode,
    setCustomPalette,
    appendDrawing,
    updateDrawing,
    deleteDrawing,
    clearSlideDrawings,
    setSlideDrawings,
    pushHistory,
    undo,
    redo,
    canUndo,
    canRedo,
    scheduleAutoSave,
    flushSlideToPresentation,
    serializeSlide,
    serializeAllSlides,
    hydrateSlideFromSerialized,
    hydrateMultipleSlides,
    isSlideDirty,
    generateDrawingId
  };
});
