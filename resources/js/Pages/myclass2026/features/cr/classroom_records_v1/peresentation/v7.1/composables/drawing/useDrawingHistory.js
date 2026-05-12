import { computed } from 'vue';
import { useDrawingStore } from '../../stores/drawingStore';

export function useDrawingHistory(slideIdRef = null) {
  const drawingStore = useDrawingStore();

  const activeSlideId = computed(() => {
    if (slideIdRef && typeof slideIdRef.value !== 'undefined') {
      return slideIdRef.value;
    }
    return drawingStore.currentSlideId;
  });

  function withSlideId(cb) {
    return (...args) => {
      const slideId = activeSlideId.value;
      if (!slideId) return;
      return cb(slideId, ...args);
    };
  }

  const undo = withSlideId((slideId) => drawingStore.undo(slideId));
  const redo = withSlideId((slideId) => drawingStore.redo(slideId));
  const clear = withSlideId((slideId) => drawingStore.clearSlideDrawings(slideId));

  const canUndo = computed(() => drawingStore.canUndo(activeSlideId.value));
  const canRedo = computed(() => drawingStore.canRedo(activeSlideId.value));
  const isDirty = computed(() => drawingStore.isSlideDirty(activeSlideId.value));

  return {
    undo,
    redo,
    clear,
    canUndo,
    canRedo,
    isDirty
  };
}
