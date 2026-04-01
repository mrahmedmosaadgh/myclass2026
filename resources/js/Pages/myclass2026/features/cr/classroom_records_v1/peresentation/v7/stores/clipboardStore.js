import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useClipboardStore = defineStore('clipboard', () => {
  const copiedElement = ref(null);
  const isCut = ref(false);
  const sourceSlideId = ref(null);
  const sourcePresentationId = ref(null);

  function copyElement(element, slideId = null, presentationId = null) {
    copiedElement.value = JSON.parse(JSON.stringify(element));
    isCut.value = false;
    sourceSlideId.value = slideId;
    sourcePresentationId.value = presentationId;
  }

  function cutElement(element, slideId = null, presentationId = null) {
    copiedElement.value = JSON.parse(JSON.stringify(element));
    isCut.value = true;
    sourceSlideId.value = slideId;
    sourcePresentationId.value = presentationId;
  }

  function pasteElement(x = null, y = null) {
    if (!copiedElement.value) return null;
    
    const pastedElement = JSON.parse(JSON.stringify(copiedElement.value));
    
    // Generate new ID to avoid conflicts
    pastedElement.id = 'el-' + Date.now() + Math.floor(Math.random() * 1000);
    
    // Update position if provided
    if (x !== null) pastedElement.x = x;
    if (y !== null) pastedElement.y = y;
    
    // If this was a cut operation, clear the clipboard after pasting
    if (isCut.value) {
      clearClipboard();
    }
    
    return pastedElement;
  }

  function clearClipboard() {
    copiedElement.value = null;
    isCut.value = false;
    sourceSlideId.value = null;
    sourcePresentationId.value = null;
  }

  function hasClipboardContent() {
    return copiedElement.value !== null;
  }

  return {
    copiedElement,
    isCut,
    sourceSlideId,
    sourcePresentationId,
    copyElement,
    cutElement,
    pasteElement,
    clearClipboard,
    hasClipboardContent
  };
});
