import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useClipboardStore = defineStore('clipboard-v8', () => {
  // State
  const clipboard = ref({
    element: null,
    sourceSlideId: null,
    timestamp: null
  })

  // Actions
  function copyElement(element, slideId) {
    clipboard.value = {
      element: { ...element },
      sourceSlideId: slideId,
      timestamp: Date.now()
    }
  }

  function cutElement(element, slideId) {
    copyElement(element, slideId)
    // Return the element ID for deletion
    return element.id
  }

  async function pasteElement(targetSlideId, offsetX = 20, offsetY = 20) {
    if (!clipboard.value.element) return null

    const pastedElement = {
      ...clipboard.value.element,
      id: `el-${Date.now()}`,
      x: clipboard.value.element.x + offsetX,
      y: clipboard.value.element.y + offsetY,
      zIndex: clipboard.value.element.zIndex + 1
    }

    return pastedElement
  }

  function hasClipboardContent() {
    return !!clipboard.value.element
  }

  function clearClipboard() {
    clipboard.value = {
      element: null,
      sourceSlideId: null,
      timestamp: null
    }
  }

  function isClipboardExpired(maxAge = 300000) { // 5 minutes
    if (!clipboard.value.timestamp) return true
    return Date.now() - clipboard.value.timestamp > maxAge
  }

  return {
    // State
    clipboard,
    
    // Actions
    copyElement,
    cutElement,
    pasteElement,
    hasClipboardContent,
    clearClipboard,
    isClipboardExpired
  }
})
