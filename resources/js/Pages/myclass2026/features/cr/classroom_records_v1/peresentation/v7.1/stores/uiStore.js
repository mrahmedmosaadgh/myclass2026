import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUIStore = defineStore('ui', () => {
  const selectedElementId = ref(null);
  const isEditMode = ref(true);
  const isSectionManagerOpen = ref(false);
  const isAIPasteDialogOpen = ref(false);
  const isGroupQuizGeneratorOpen = ref(false);
  const showDistributionModal = ref(false);
  const isSlideNavVisible = ref(true);
  const areEditToolsVisible = ref(true);
  const isFocusMode = ref(false);
  const lastPointer = ref({ x: 150, y: 150 });
  const pasteOffset = ref(0);
  const zoomLevel = ref(100); // Zoom percentage (50-200)
  const presentModeLayout = ref('single'); // 'single' | 'continuous'
  const isPagesView = ref(false); // Edit-mode continuous pages view (PDF-like)
  const isDrawRectangleMode = ref(false); // Click-and-drag rectangle drawing mode
  const useGroupQuizV2 = ref(true); // Toggle Group Quiz V2 modern UI

  const focusPrevState = ref({
    isSlideNavVisible: true,
    areEditToolsVisible: true
  });

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

  function togglePresentModeLayout() {
    presentModeLayout.value = presentModeLayout.value === 'continuous' ? 'single' : 'continuous';
  }

  function togglePagesView() {
    isPagesView.value = !isPagesView.value;
    if (isPagesView.value) {
      selectedElementId.value = null;
    }
  }

  function toggleSlideNav() {
    isSlideNavVisible.value = !isSlideNavVisible.value;
  }

  function toggleEditTools() {
    areEditToolsVisible.value = !areEditToolsVisible.value;
  }

  function toggleFocusMode() {
    isFocusMode.value = !isFocusMode.value;
    if (isFocusMode.value) {
      focusPrevState.value = {
        isSlideNavVisible: isSlideNavVisible.value,
        areEditToolsVisible: areEditToolsVisible.value
      };
      isSlideNavVisible.value = false;
      areEditToolsVisible.value = false;
    } else {
      isSlideNavVisible.value = !!focusPrevState.value.isSlideNavVisible;
      areEditToolsVisible.value = !!focusPrevState.value.areEditToolsVisible;
    }
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

  function toggleDrawRectangleMode() {
    isDrawRectangleMode.value = !isDrawRectangleMode.value;
  }

  function setDrawRectangleMode(value) {
    isDrawRectangleMode.value = value;
  }

  function toggleGroupQuizV2() {
    useGroupQuizV2.value = !useGroupQuizV2.value;
  }

  return {
    selectedElementId,
    isEditMode,
    isSectionManagerOpen,
    isAIPasteDialogOpen,
    isGroupQuizGeneratorOpen,
    showDistributionModal,
    isSlideNavVisible,
    areEditToolsVisible,
    isFocusMode,
    lastPointer,
    pasteOffset,
    zoomLevel,
    presentModeLayout,
    isPagesView,
    isDrawRectangleMode,
    useGroupQuizV2,
    selectElement,
    clearSelection,
    toggleMode,
    togglePresentModeLayout,
    togglePagesView,
    toggleSlideNav,
    toggleEditTools,
    toggleFocusMode,
    updateLastPointer,
    incrementPasteOffset,
    zoomIn,
    zoomOut,
    resetZoom,
    setZoom,
    toggleDrawRectangleMode,
    setDrawRectangleMode,
    toggleGroupQuizV2
  };
});
