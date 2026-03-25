<template>
  <div
    ref="canvasRef"
    class="relative bg-white shadow-2xl outline-none"
    :style="{ width: slideWidth + 'px', height: slideHeight + 'px' }"
    tabindex="0"
    @mousedown="startDrawing"
    @mousemove="handleMouseMove"
    @mouseup="stopDrawing"
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

    <!-- Drawing Layer -->
    <svg
      class="absolute inset-0"
      :class="{ 'pointer-events-none': !isDrawingMode && !isEraserMode }"
      :style="{ width: slideWidth + 'px', height: slideHeight + 'px', zIndex: 40 }"
    >
      <defs>
        <rect
          id="drawing-clip"
          :width="slideWidth"
          :height="slideHeight"
        />
      </defs>
      <g clip-path="url(#drawing-clip)">
        <!-- Render saved drawings from slide elements -->
        <path
          v-for="element in drawingElements"
          :key="element.id"
          :d="element.path"
          :stroke="element.color"
          :stroke-width="element.size"
          fill="none"
          stroke-linecap="round"
          stroke-linejoin="round"
        />

        <!-- Current drawing path -->
        <path
          v-if="currentPath"
          :d="currentPath.d"
          :stroke="currentPath.color"
          :stroke-width="currentPath.size"
          fill="none"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </g>
    </svg>

    <!-- Live Drawing Preview -->
    <svg
      class="absolute inset-0"
      :class="{ 'pointer-events-none': !isDrawingMode && !isEraserMode }"
      :style="{ width: slideWidth + 'px', height: slideHeight + 'px', zIndex: 40 }"
    >
      <defs>
        <rect
          id="drawing-clip"
          :width="slideWidth"
          :height="slideHeight"
        />
      </defs>
      <g clip-path="url(#drawing-clip)">
        <path
          v-if="currentPath"
          :d="currentPath.d"
          :stroke="currentPath.color"
          :stroke-width="currentPath.size"
          fill="none"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </g>
    </svg>

    <!-- Visual Eraser Cursor -->
    <div
      v-if="isEraserMode && eraserPosition"
      class="fixed pointer-events-none border-2 border-red-500 rounded-full bg-red-500/20"
      :style="{
        left: (eraserPosition.x - 10) + 'px',
        top: (eraserPosition.y - 10) + 'px',
        width: '20px',
        height: '20px',
        zIndex: 9999
      }"
    ></div>

    <!-- Context Menu -->
    <div
      v-if="isContextMenuVisible"
      class="context-menu fixed bg-white rounded-lg shadow-lg border border-gray-200 p-2 z-50"
      :style="{
        left: contextMenuPosition.x + 'px',
        top: contextMenuPosition.y + 'px'
      }"
      @click.self="isContextMenuVisible = false"
    >
      <button
        @click="duplicateElement(contextMenuElement)"
        class="w-full px-4 py-2 text-left hover:bg-gray-100 transition-colors"
      >
        Duplicate
      </button>
      <button
        @click="deleteElement(contextMenuElement?.id)"
        class="w-full px-4 py-2 text-left hover:bg-gray-100 transition-colors text-red-600"
      >
        Delete
      </button>
    </div>
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

// Drawing methods
const startDrawing = (event) => {
  if (!props.isDrawingMode && !props.isEraserMode) return
  
  isDrawingLocal.value = true
  const rect = event.currentTarget.getBoundingClientRect()
  const x = event.clientX - rect.left
  const y = event.clientY - rect.top
  
  if (props.isEraserMode) {
    // In eraser mode, check if we're clicking on a drawing element
    const clickedDrawing = findDrawingAtPosition(x, y)
    if (clickedDrawing) {
      deleteElement(clickedDrawing.id)
    }
  } else {
    // Normal drawing mode
    currentPath.value = {
      d: `M${x},${y}`,
      color: props.penColor,
      size: props.penSize,
      points: [{x, y}]
    }
  }
}

const handleMouseMove = (event) => {
  // Track eraser position for visual cursor
  if (props.isEraserMode) {
    eraserPosition.value = { x: event.clientX, y: event.clientY }
  }
  
  // Handle drawing if in drawing mode
  if (isDrawingLocal.value && currentPath.value && !props.isEraserMode) {
    draw(event)
  }
}

const handleMouseLeave = () => {
  // Clear eraser position when mouse leaves canvas
  eraserPosition.value = null
  stopDrawing()
}

const draw = (event) => {
  if (!isDrawingLocal.value || !currentPath.value || props.isEraserMode) return
  
  const rect = event.currentTarget.getBoundingClientRect()
  const x = event.clientX - rect.left
  const y = event.clientY - rect.top
  
  // Enhanced smooth curves using cubic Bezier curves
  const points = currentPath.value.points
  if (points.length >= 2) {
    const lastPoint = points[points.length - 1]
    
    // Only add point if moved enough distance (reduces point density)
    const distance = Math.sqrt(Math.pow(x - lastPoint.x, 2) + Math.pow(y - lastPoint.y, 2))
    if (distance > 2) { // Minimum distance threshold
      if (points.length >= 3) {
        // Use cubic Bezier for smoother curves
        const secondLastPoint = points[points.length - 2]
        const controlPoint1 = {
          x: lastPoint.x + (secondLastPoint.x - lastPoint.x) * 0.2,
          y: lastPoint.y + (secondLastPoint.y - lastPoint.y) * 0.2
        }
        const controlPoint2 = {
          x: lastPoint.x + (x - lastPoint.x) * 0.2,
          y: lastPoint.y + (y - lastPoint.y) * 0.2
        }
        currentPath.value.d += ` C${controlPoint1.x},${controlPoint1.y} ${controlPoint2.x},${controlPoint2.y} ${x},${y}`
      } else {
        // Use quadratic for initial segments
        const controlPoint = {
          x: lastPoint.x + (x - lastPoint.x) * 0.3,
          y: lastPoint.y + (y - lastPoint.y) * 0.3
        }
        currentPath.value.d += ` Q${controlPoint.x},${controlPoint.y} ${x},${y}`
      }
    }
  }
  
  currentPath.value.points.push({x, y})
}

const stopDrawing = () => {
  if (!isDrawingLocal.value) return
  
  isDrawingLocal.value = false
  
  if (currentPath.value && currentPath.value.points.length > 1 && !props.isEraserMode) {
    // Create drawing element
    const drawingElement = {
      id: Date.now(),
      type: 'drawing',
      x: 0,
      y: 0,
      width: props.slideWidth,
      height: props.slideHeight,
      path: currentPath.value.d,
      color: currentPath.value.color,
      size: currentPath.value.size,
      opacity: 1,
      startHidden: false,
      clickable: false,
      moveable: false,
      zIndex: 1
    }
    
    // Add to current slide
    const updatedSlide = {
      ...props.currentSlide,
      elements: [...props.currentSlide.elements, drawingElement]
    }
    
    emit('slide-update', updatedSlide)
    currentPath.value = null
  }
}

// Touch event handlers for mobile
const handleTouchStart = (event) => {
  event.preventDefault()
  if (!props.isDrawingMode && !props.isEraserMode) return
  
  const touch = event.touches[0]
  if (!touch) return
  
  isDrawingLocal.value = true
  const rect = event.currentTarget.getBoundingClientRect()
  const x = touch.clientX - rect.left
  const y = touch.clientY - rect.top
  
  if (props.isEraserMode) {
    const clickedDrawing = findDrawingAtPosition(x, y)
    if (clickedDrawing) {
      deleteElement(clickedDrawing.id)
    }
  } else {
    currentPath.value = {
      d: `M${x},${y}`,
      color: props.penColor,
      size: props.penSize,
      points: [{x, y}]
    }
  }
}

const handleTouchMove = (event) => {
  event.preventDefault()
  if (!isDrawingLocal.value) return
  
  const touch = event.touches[0]
  if (!touch) return
  
  // Update eraser position for touch
  if (props.isEraserMode) {
    eraserPosition.value = { x: touch.clientX, y: touch.clientY }
  }
  
  // Use enhanced drawing algorithm
  if (isDrawingLocal.value && currentPath.value && !props.isEraserMode) {
    draw({ currentTarget: event.currentTarget, clientX: touch.clientX, clientY: touch.clientY })
  }
}

const handleTouchEnd = (event) => {
  event.preventDefault()
  stopDrawing()
}

// Helper function to find drawing elements at a specific position
const findDrawingAtPosition = (x, y) => {
  const drawingElements = props.currentSlide.elements.filter(el => el.type === 'drawing')
  const eraserSize = 20 // Eraser detection radius in pixels
  
  for (const drawing of drawingElements) {
    // Check if click is within drawing bounds first (quick check)
    if (x >= drawing.x && x <= drawing.x + drawing.width &&
        y >= drawing.y && y <= drawing.y + drawing.height) {
      return drawing
    }
  }
  return null
}

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
