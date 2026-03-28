import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUIStore = defineStore('ui', () => {
  const selectedElementId = ref(null);
  const isEditMode = ref(true);
  const isSectionManagerOpen = ref(false);
  const isAIPasteDialogOpen = ref(false);
  const isGroupQuizGeneratorOpen = ref(false);
  const lastPointer = ref({ x: 150, y: 150 });
  const pasteOffset = ref(0);
  const zoomLevel = ref(100); // Zoom percentage (50-200)

  function selectElement(id) {
    if (!isEditMode.value) return;
    selectedElementId.value = id;
  }

  function clearSelection() {
    selectedElementId.value = null;
  }

  function toggleMode() {
    isEditMode.value = !isEditMode.value;
    if (!isEditMode.value) clearSelection();
  }

  function updateLastPointer(x, y) {
    lastPointer.value = { x, y };
    pasteOffset.value = 0; // reset cascading offset when user clicks
  }

  function incrementPasteOffset() {
    pasteOffset.value += 20;
    if (pasteOffset.value > 150) pasteOffset.value = 0;
  }

  function zoomIn() {
    if (zoomLevel.value < 200) {
      zoomLevel.value = Math.min(200, zoomLevel.value + 10);
    }
  }

  function zoomOut() {
    if (zoomLevel.value > 50) {
      zoomLevel.value = Math.max(50, zoomLevel.value - 10);
    }
  }

  function resetZoom() {
    zoomLevel.value = 100;
  }

  function setZoom(level) {
    zoomLevel.value = Math.max(50, Math.min(200, level));
  }

  return {
    selectedElementId,
    isEditMode,
    isSectionManagerOpen,
    isAIPasteDialogOpen,
    isGroupQuizGeneratorOpen,
    lastPointer,
    pasteOffset,
    zoomLevel,
    selectElement,
    clearSelection,
    toggleMode,
    updateLastPointer,
    incrementPasteOffset,
    zoomIn,
    zoomOut,
    resetZoom,
    setZoom
  };
});
