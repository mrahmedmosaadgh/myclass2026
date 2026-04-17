import { ref } from 'vue';

export function useDrawingEngine() {
  const strokes = ref([]);
  const undone = ref([]);
  const currentStroke = ref(null);

  function startStroke(point, style) {
    currentStroke.value = {
      points: [point],
      style,
      id: `stroke-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
      timestamp: Date.now()
    };
  }

  function addPoint(point) {
    if (!currentStroke.value) return;
    currentStroke.value.points.push(point);
  }

  function endStroke() {
    if (!currentStroke.value) return;
    strokes.value.push(currentStroke.value);
    currentStroke.value = null;
    undone.value = [];
  }

  function undo() {
    if (!strokes.value.length) return;
    undone.value.push(strokes.value.pop());
  }

  function redo() {
    if (!undone.value.length) return;
    strokes.value.push(undone.value.pop());
  }

  function clear() {
    strokes.value = [];
    undone.value = [];
  }

  function addStroke(stroke) {
    strokes.value.push(stroke);
  }

  function removeStroke(strokeId) {
    const index = strokes.value.findIndex(s => s.id === strokeId);
    if (index !== -1) {
      undone.value.push(strokes.value.splice(index, 1)[0]);
    }
  }

  function getStrokes() {
    return [...strokes.value];
  }

  function getCurrentStroke() {
    return currentStroke.value;
  }

  function isDrawing() {
    return currentStroke.value !== null;
  }

  return {
    strokes,
    undone,
    currentStroke,
    startStroke,
    addPoint,
    endStroke,
    undo,
    redo,
    clear,
    addStroke,
    removeStroke,
    getStrokes,
    getCurrentStroke,
    isDrawing
  };
}
