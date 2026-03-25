<template>
  <div
    ref="canvasRef"
    class="relative bg-white shadow-2xl outline-none"
    :style="{ width: slideWidth + 'px', height: slideHeight + 'px' }"
    tabindex="0"
 
    @mousemove="handleMouseMove"
   
    @mouseleave="handleMouseLeave"
    @touchstart="handleTouchStart"
    @touchmove="handleTouchMove"
    @touchend="handleTouchEnd"
    @touchcancel="handleTouchEnd"
    @paste="handlePaste"
    @drop="handleDrop"
    @contextmenu.prevent="showContextMenu"
  >
    <!-- Elements Layer -->
    <ElementNode
      v-for="element in nonDrawingElements"
      :key="element.id"
      :element="element"
      :is-presentation="isPresentation"
      @update="updateElement"
      @delete="deleteElement"
      @duplicate="duplicateElement"
      @click="handleElementClick"
    />

   

 
 
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import ElementNode from './ElementNode.vue'

// Props
const props = defineProps({
  slideWidth: { type: Number, required: true },
  slideHeight: { type: Number, required: true },
  currentSlide: { type: Object, required: true },
  isDrawingMode: { type: Boolean, default: false },
  isEraserMode: { type: Boolean, default: false },
  penColor: { type: String, default: '#000000' },
  penSize: { type: Number, default: 2 },
  isPresentation: { type: Boolean, default: false }
})

// Emits
const emit = defineEmits(['slide-update', 'element-update', 'element-delete', 'element-duplicate'])

// Refs
const canvasRef = ref(null)
const isDrawingLocal = ref(false)
const currentPath = ref(null)
const eraserPosition = ref(null)
const contextMenuPosition = ref({ x: 0, y: 0 })
const contextMenuElement = ref(null)
const isContextMenuVisible = ref(false)

// Click outside handler to close context menu
const closeContextMenu = (event) => {
  if (!event.target.closest('.context-menu')) {
    isContextMenuVisible.value = false
  }
}

// Computed
const nonDrawingElements = computed(() => {
  return props.currentSlide.elements.filter(el => el.type !== 'drawing')
})

const drawingElements = computed(() => {
  return props.currentSlide.elements.filter(el => el.type === 'drawing')
})

 

 

// Element management
const updateElement = (element) => {
  emit('element-update', element)
}

const deleteElement = (elementId) => {
  emit('element-delete', elementId)
}

const duplicateElement = (element) => {
  if (!element) return // Guard clause for null/undefined
  emit('element-duplicate', element)
}

const handleElementClick = (element) => {
  if (props.isPresentation) {
    emit('element-click', element)
  }
}

const showContextMenu = (event) => {
  event.preventDefault()
  const rect = canvasRef.value.getBoundingClientRect()
  contextMenuPosition.value = {
    x: event.clientX - rect.left,
    y: event.clientY - rect.top
  }
  contextMenuElement.value = props.currentSlide.elements.find(el => el.id === event.target.closest('[data-element-id]')?.dataset.elementId)
  isContextMenuVisible.value = true
}

// Paste handling
const handlePaste = (event) => {
  event.preventDefault()
  event.stopPropagation()

  const items = event.clipboardData?.items
  if (!items) return

  let imageHandled = false
  for (const item of Array.from(items)) {
    if (item.type.startsWith('image/')) {
      const blob = item.getAsFile()
      if (blob) {
        const reader = new FileReader()
        reader.onload = (e) => {
          const img = new Image()
          img.onload = () => {
            const imageElement = {
              id: Date.now(),
              type: 'image',
              x: 100,
              y: 100,
              width: Math.min(img.width, 400), // Limit width
              height: Math.min(img.height, 300), // Limit height
              src: e.target.result,
              opacity: 1,
              startHidden: false,
              clickable: false,
              moveable: false,
              zIndex: 1
            }
            
            // Add to current slide
            const updatedSlide = {
              ...props.currentSlide,
              elements: [...props.currentSlide.elements, imageElement]
            }
            
            emit('slide-update', updatedSlide)
          }
          img.src = e.target.result
        }
        reader.readAsDataURL(blob)
        imageHandled = true
        break
      }
    }
  }

  if (!imageHandled) {
    for (const item of Array.from(items)) {
      if (item.type === 'text/plain') {
        item.getAsString((text) => {
          if (!text?.trim()) return
          
          const textElement = {
            id: Date.now(),
            type: 'text',
            x: 100,
            y: 100,
            width: 400,
            height: 30,
            content: text.trim(),
            fontSize: 24,
            color: '#000000',
            background: '#3B82F6',
            border: '2px solid #1E40AF',
            opacity: 1,
            startHidden: false,
            clickable: false,
            moveable: true,
            zIndex: 1
          }
          
          // Add to current slide
          const updatedSlide = {
            ...props.currentSlide,
            elements: [...props.currentSlide.elements, textElement]
          }
          
          emit('slide-update', updatedSlide)
        })
        break
      }
    }
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  
  const files = event.dataTransfer?.files
  if (files && files.length > 0) {
    handleImageUpload(files[0])
  }
}

const handleImageUpload = (file) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    const img = new Image()
    img.onload = () => {
      const imageElement = {
        id: Date.now(),
        type: 'image',
        x: 100,
        y: 100,
        width: img.width,
        height: img.height,
        src: e.target.result,
        opacity: 1,
        startHidden: false,
        clickable: false,
        moveable: false,
        zIndex: 1
      }
      
      // Add to current slide
      const updatedSlide = {
        ...props.currentSlide,
        elements: [...props.currentSlide.elements, imageElement]
      }
      
      emit('slide-update', updatedSlide)
    }
    img.src = e.target.result
  }
  reader.readAsDataURL(file)
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', closeContextMenu)
})

onUnmounted(() => {
  document.removeEventListener('click', closeContextMenu)
})
</script>

<style scoped>
.touch-manipulation {
  touch-action: none;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

.touch-manipulation * {
  pointer-events: none;
}

/* Ensure elements are properly sized for touch */
.touch-manipulation {
  min-width: 44px;
  min-height: 44px;
}

/* Make text elements editable on touch */
.touch-manipulation div[contenteditable="true"] {
  pointer-events: auto;
}
</style>
