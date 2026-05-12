import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useUIStore = defineStore('ui-v8', () => {
  // State
  const selectedElementId = ref(null)
  const isEditMode = ref(true)
  const isSlideNavVisible = ref(true)
  const areEditToolsVisible = ref(true)
  const isFocusMode = ref(false)
  const isPagesView = ref(false)
  const zoomLevel = ref(100)
  const presentModeLayout = ref('continuous') // 'continuous' | 'single'
  const showAnnotations = ref(true)
  
  // Context menu state
  const contextMenu = ref({
    show: false,
    x: 0,
    y: 0,
    elementId: null
  })

  // Slide context menu state
  const slideContextMenu = ref({
    show: false,
    x: 0,
    y: 0
  })

  // Getters
  const hasSelectedElement = computed(() => !!selectedElementId.value)

  // Actions
  function selectElement(id) {
    selectedElementId.value = id
  }

  function clearSelection() {
    selectedElementId.value = null
  }

  function toggleEditMode() {
    isEditMode.value = !isEditMode.value
    if (!isEditMode.value) {
      clearSelection()
    }
  }

  function toggleSlideNav() {
    isSlideNavVisible.value = !isSlideNavVisible.value
  }

  function toggleEditTools() {
    areEditToolsVisible.value = !areEditToolsVisible.value
  }

  function toggleFocusMode() {
    isFocusMode.value = !isFocusMode.value
    if (isFocusMode.value) {
      // Enter focus mode - hide distractions
      areEditToolsVisible.value = false
    }
  }

  function togglePagesView() {
    isPagesView.value = !isPagesView.value
  }

  function setZoom(level) {
    zoomLevel.value = Math.max(25, Math.min(200, level))
  }

  function zoomIn() {
    setZoom(zoomLevel.value + 10)
  }

  function zoomOut() {
    setZoom(zoomLevel.value - 10)
  }

  function resetZoom() {
    setZoom(100)
  }

  function togglePresentModeLayout() {
    presentModeLayout.value = presentModeLayout.value === 'continuous' ? 'single' : 'continuous'
  }

  function toggleAnnotations() {
    showAnnotations.value = !showAnnotations.value
  }

  function showContextMenu(x, y, elementId) {
    contextMenu.value = {
      show: true,
      x,
      y,
      elementId
    }
  }

  function hideContextMenu() {
    contextMenu.value.show = false
  }

  function showSlideContextMenu(x, y) {
    slideContextMenu.value = {
      show: true,
      x,
      y
    }
  }

  function hideSlideContextMenu() {
    slideContextMenu.value.show = false
  }

  // Keyboard shortcuts
  const shortcuts = {
    // Element shortcuts
    delete: () => {
      if (selectedElementId.value) {
        // This will be handled by the component that uses the store
        return 'delete-element'
      }
    },
    
    // Zoom shortcuts
    zoomIn: () => zoomIn(),
    zoomOut: () => zoomOut(),
    resetZoom: () => resetZoom(),
    
    // Mode shortcuts
    toggleEdit: () => toggleEditMode(),
    toggleFocus: () => toggleFocusMode(),
    toggleSlideNav: () => toggleSlideNav(),
    togglePagesView: () => togglePagesView(),
    
    // Layout shortcuts
    togglePresentModeLayout: () => togglePresentModeLayout()
  }

  return {
    // State
    selectedElementId,
    isEditMode,
    isSlideNavVisible,
    areEditToolsVisible,
    isFocusMode,
    isPagesView,
    zoomLevel,
    presentModeLayout,
    showAnnotations,
    contextMenu,
    slideContextMenu,
    
    // Getters
    hasSelectedElement,
    
    // Actions
    selectElement,
    clearSelection,
    toggleEditMode,
    toggleSlideNav,
    toggleEditTools,
    toggleFocusMode,
    togglePagesView,
    setZoom,
    zoomIn,
    zoomOut,
    resetZoom,
    togglePresentModeLayout,
    toggleAnnotations,
    showContextMenu,
    hideContextMenu,
    showSlideContextMenu,
    hideSlideContextMenu,
    shortcuts
  }
})
