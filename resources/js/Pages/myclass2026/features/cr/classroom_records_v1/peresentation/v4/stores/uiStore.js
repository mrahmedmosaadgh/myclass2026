import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUIStore = defineStore('ui', () => {
  const selectedElementId = ref(null);
  const isEditMode = ref(true);
  const isSectionManagerOpen = ref(false);
  const lastPointer = ref({ x: 150, y: 150 });
  const pasteOffset = ref(0);

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

  return {
    selectedElementId,
    isEditMode,
    isSectionManagerOpen,
    lastPointer,
    pasteOffset,
    selectElement,
    clearSelection,
    toggleMode,
    updateLastPointer,
    incrementPasteOffset
  };
});
