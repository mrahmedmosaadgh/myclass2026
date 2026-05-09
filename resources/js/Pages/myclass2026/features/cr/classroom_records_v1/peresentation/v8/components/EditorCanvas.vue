<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { usePresentationStore } from '../stores/presentationStore.js'
import { useUIStore } from '../stores/uiStore.js'
import { useClipboardStore } from '../stores/clipboardStore.js'
import { usePaste } from '../composables/usePaste.js'
import ElementNode from './ElementNode.vue'

const presentation = usePresentationStore()
const ui = useUIStore()
const clipboard = useClipboardStore()
const { handlePaste, getPasteElement, hasPasteElement } = usePaste()

const canvasRef = ref(null)

// Computed
const canvasStyle = computed(() => ({
  width: '800px',
  height: '600px',
  transform: `scale(${ui.zoomLevel / 100})`,
  transformOrigin: 'top center'
}))

const currentSlide = computed(() => presentation.currentSlide)

// Methods
function handleCanvasClick(e) {
  if (e.target === canvasRef.value) {
    ui.clearSelection()
  }
}

function handleCanvasContextMenu(e) {
  e.preventDefault()
  ui.showSlideContextMenu(e.clientX, e.clientY)
}

function handleKeyDown(e) {
  // Handle keyboard shortcuts
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return

  // Delete key
  if (e.key === 'Delete' && ui.selectedElementId) {
    presentation.deleteElement(ui.selectedElementId)
    ui.clearSelection()
    return
  }

  // Copy/Cut/Paste
  if ((e.ctrlKey || e.metaKey)) {
    switch (e.key.toLowerCase()) {
      case 'c':
        if (ui.selectedElementId) {
          const element = currentSlide.value.elements.find(el => el.id === ui.selectedElementId)
          if (element) {
            clipboard.copyElement(element, presentation.currentSlide.id)
          }
        }
        e.preventDefault()
        break
        
      case 'x':
        if (ui.selectedElementId) {
          const element = currentSlide.value.elements.find(el => el.id === ui.selectedElementId)
          if (element) {
            const deletedId = clipboard.cutElement(element, presentation.currentSlide.id)
            presentation.deleteElement(deletedId)
            ui.clearSelection()
          }
        }
        e.preventDefault()
        break
        
      case 'v':
        if (clipboard.hasClipboardContent() && !clipboard.isClipboardExpired()) {
          clipboard.pasteElement(presentation.currentSlide.id).then(pastedElement => {
            if (pastedElement) {
              presentation.addElement(pastedElement)
            }
          })
        }
        e.preventDefault()
        break
        
      case 'z':
        // Undo/Redo could be implemented here
        e.preventDefault()
        break
        
      case 'd':
        if (ui.selectedElementId) {
          presentation.duplicateElement(ui.selectedElementId)
        }
        e.preventDefault()
        break
    }
  }

  // Arrow keys for nudging
  if (ui.selectedElementId && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
    e.preventDefault()
    
    const element = currentSlide.value.elements.find(el => el.id === ui.selectedElementId)
    if (!element) return

    const step = e.shiftKey ? 10 : 1
    let changes = {}

    switch (e.key) {
      case 'ArrowUp':
        changes.y = element.y - step
        break
      case 'ArrowDown':
        changes.y = element.y + step
        break
      case 'ArrowLeft':
        changes.x = element.x - step
        break
      case 'ArrowRight':
        changes.x = element.x + step
        break
    }

    presentation.updateElement({
      id: ui.selectedElementId,
      changes
    })
  }
}

async function handlePasteEvent(e) {
  await handlePaste(e)
  
  if (hasPasteElement()) {
    const pasteData = getPasteElement()
    if (pasteData) {
      // Center the pasted element on the canvas
      const canvasRect = canvasRef.value?.getBoundingClientRect()
      if (canvasRect) {
        pasteData.x = Math.max(0, (e.clientX - canvasRect.left - pasteData.width / 2) * 100 / ui.zoomLevel)
        pasteData.y = Math.max(0, (e.clientY - canvasRect.top - pasteData.height / 2) * 100 / ui.zoomLevel)
      }
      
      presentation.addElement(pasteData)
    }
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleKeyDown)
  document.addEventListener('paste', handlePasteEvent)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown)
  document.removeEventListener('paste', handlePasteEvent)
})
</script>

<template>
  <div class="editor-canvas">
    <div 
      ref="canvasRef"
      class="canvas"
      :style="canvasStyle"
      @click="handleCanvasClick"
      @contextmenu="handleCanvasContextMenu"
    >
      <!-- Grid background (visual guide) -->
      <div class="grid-background" />
      
      <!-- Elements -->
      <ElementNode
        v-for="element in currentSlide.elements"
        :key="element.id"
        :element="element"
        :is-present-mode="!ui.isEditMode"
      />
      
      <!-- Empty state -->
      <div v-if="currentSlide.elements.length === 0" class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>Start creating your presentation</h3>
        <p>Add text, images, or shapes to begin</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.editor-canvas {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 20px;
  overflow: auto;
  background: #f3f4f6;
  min-height: 100%;
}

.canvas {
  position: relative;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.grid-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    linear-gradient(to right, #f9fafb 1px, transparent 1px),
    linear-gradient(to bottom, #f9fafb 1px, transparent 1px);
  background-size: 10px 10px;
  pointer-events: none;
  opacity: 0.5;
}

.empty-state {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: #6b7280;
  pointer-events: none;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 600;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

/* Zoom transition */
.canvas {
  transition: transform 0.2s ease;
}
</style>
