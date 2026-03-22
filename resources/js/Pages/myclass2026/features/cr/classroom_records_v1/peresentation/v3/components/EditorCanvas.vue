<template>
  <div class="flex-1 bg-gray-900 p-6 overflow-auto">
    <!-- Canvas Container -->
    <div class="flex justify-center">
      <div
        ref="canvasRef"
        tabindex="0"
        @paste="handlePaste"
        @dragover.prevent
        @drop="handleDrop"
        @contextmenu.prevent="showContextMenu"
        class="relative bg-white shadow-2xl outline-none"
        :style="{ width: slideWidth + 'px', height: slideHeight + 'px' }"
      >
        <!-- Elements -->
        <ElementNode
          v-for="element in nonDrawingElements"
          :key="element.id"
          :element="element"
          @update="updateElement"
          @delete="deleteElement"
          @duplicate="duplicateElement"
        />

        <!-- Drawing Layer (always visible in edit mode, on top of all elements) -->
        <svg
          class="absolute inset-0 pointer-events-none"
          :style="{ width: slideWidth + 'px', height: slideHeight + 'px', zIndex: 999 }"
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
            <!-- Current drawing path (only when drawing mode is active) -->
            <path
              v-if="isDrawingMode && currentPath"
              :d="currentPath.d"
              :stroke="currentPath.color"
              :stroke-width="currentPath.size"
              fill="none"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </g>
        </svg>

        <!-- Drawing Canvas Overlay (for capturing mouse events) -->
        <div
          v-if="isDrawingMode || isEraserMode"
          class="absolute inset-0"
          :class="isEraserMode ? 'cursor-none' : 'cursor-crosshair'"
          :style="{ width: slideWidth + 'px', height: slideHeight + 'px', zIndex: 1000 }"
          @mousedown="startDrawing"
          @mousemove="handleMouseMove"
          @mouseup="stopDrawing"
          @mouseleave="handleMouseLeave"
        >
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
          <!-- Live drawing preview -->
          <svg
            class="absolute inset-0 pointer-events-none"
            :style="{ width: slideWidth + 'px', height: slideHeight + 'px' }"
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
        </div>

        <!-- Paste Zone Overlay -->
        <div
          v-if="showPasteZone"
          class="absolute inset-0 bg-indigo-50 border-2 border-dashed border-indigo-300 flex items-center justify-center"
        >
          <div class="text-center">
            <svg class="w-12 h-12 text-indigo-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="text-indigo-600 font-medium">Paste or drop content here</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Canvas Context Menu -->
    <div
      v-if="showCanvasMenu"
      ref="contextMenuRef"
      class="fixed bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
      :style="{ left: contextMenuX + 'px', top: contextMenuY + 'px' }"
      @click.stop
    >
      <button
        @click="pasteFromCanvasMenu"
        class="w-full px-4 py-2 text-left text-gray-700 hover:bg-gray-100 flex items-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        Paste from Clipboard
      </button>
      <button
        @click="addDateTimeText"
        class="w-full px-4 py-2 text-left text-gray-700 hover:bg-gray-100 flex items-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Add Date & Time
      </button>
      <div class="border-t border-gray-200 my-1"></div>
      <button
        @click="hideCanvasMenu"
        class="w-full px-4 py-2 text-left text-gray-500 hover:bg-gray-100"
      >
        Cancel
      </button>
    </div>

    <!-- Hidden File Input -->
    <input
      ref="imageInput"
      type="file"
      accept="image/*"
      @change="handleImageUpload"
      class="hidden"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import ElementNode from './ElementNode.vue'

const props = defineProps({
  currentSlide: {
    type: Object,
    required: true
  },
  slideHeight: {
    type: Number,
    required: true
  },
  slideWidth: {
    type: Number,
    required: true
  },
  selectedSize: {
    type: String,
    required: true
  },
  customWidth: {
    type: Number,
    required: true
  },
  customHeight: {
    type: Number,
    required: true
  },
  isEraserMode: {
    type: Boolean,
    required: true
  },
  isDrawingMode: {
    type: Boolean,
    required: true
  },
  penSize: {
    type: Number,
    required: true
  },
  penColor: {
    type: String,
    required: true
  },
  drawingPaths: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['slide-update', 'slide-size-change', 'drawing-start', 'drawing-move', 'drawing-end', 'drawing-clear', 'drawing-undo'])

const imageInput = ref(null)
const canvasRef = ref(null)
const contextMenuRef = ref(null)
const showPasteZone = ref(false)
const showCanvasMenu = ref(false)
const contextMenuX = ref(0)
const contextMenuY = ref(0)

// Settings visibility
const showSettings = ref(false)

// Drawing state
const isDrawingLocal = ref(false)
const currentPath = ref(null)
const eraserPosition = ref(null)

// Computed properties
const nonDrawingElements = computed(() => {
  return props.currentSlide.elements.filter(el => el.type !== 'drawing')
})

const drawingElements = computed(() => {
  return props.currentSlide.elements.filter(el => el.type === 'drawing')
})

// Drawing methods
const startDrawing = (event) => {
  if (!props.isDrawingMode && !props.isEraserMode) return
  
  console.log('🎨 Starting drawing/erasing at:', { 
    clientX: event.clientX, 
    clientY: event.clientY, 
    mode: props.isDrawingMode, 
    eraser: props.isEraserMode 
  })
  
  isDrawingLocal.value = true
  const rect = event.currentTarget.getBoundingClientRect()
  const x = event.clientX - rect.left
  const y = event.clientY - rect.top
  
  console.log('📐 Calculated coordinates:', { 
    rectLeft: rect.left, 
    rectTop: rect.top, 
    canvasX: x, 
    canvasY: y,
    canvasWidth: rect.width,
    canvasHeight: rect.height
  })
  
  if (props.isEraserMode) {
    // In eraser mode, check if we're clicking on a drawing element
    const clickedDrawing = findDrawingAtPosition(x, y)
    if (clickedDrawing) {
      // Remove the clicked drawing
      console.log('🗑️ Erasing drawing:', clickedDrawing.id)
      deleteElement(clickedDrawing.id)
    } else {
      console.log('❌ No drawing found to erase at this position')
    }
  } else {
    // Normal drawing mode
    currentPath.value = {
      d: `M${x},${y}`,
      color: props.penColor,
      size: props.penSize,
      points: [{x, y}]
    }
    
    emit('drawing-start', { x, y, color: props.penColor, size: props.penSize })
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
  // Track eraser position for visual cursor
  if (props.isEraserMode) {
    eraserPosition.value = { x: event.clientX, y: event.clientY }
  }
  
  if (!isDrawingLocal.value || !currentPath.value || props.isEraserMode) return
  
  const rect = event.currentTarget.getBoundingClientRect()
  const x = event.clientX - rect.left
  const y = event.clientY - rect.top
  
  // Add smooth curves using quadratic curves
  const points = currentPath.value.points
  if (points.length >= 2) {
    const lastPoint = points[points.length - 1]
    const controlPoint = {
      x: (lastPoint.x + x) / 2,
      y: (lastPoint.y + y) / 2
    }
    currentPath.value.d += ` Q${controlPoint.x},${controlPoint.y} ${x},${y}`
  } else {
    currentPath.value.d += ` L${x},${y}`
  }
  
  currentPath.value.points.push({x, y})
  emit('drawing-move', { x, y })
}

const stopDrawing = () => {
  if (!isDrawingLocal.value) return
  
  // Clear eraser position when not drawing
  eraserPosition.value = null
  
  //console.log('🎨 Stopping drawing, path length:', currentPath.value?.points?.length)
  
  isDrawingLocal.value = false
  
  if (currentPath.value && currentPath.value.points.length > 1 && !props.isEraserMode) {
    emit('drawing-end', currentPath.value)
    currentPath.value = null
  }
}

// Helper function to find drawing elements at a specific position
const findDrawingAtPosition = (x, y) => {
  const drawingElements = props.currentSlide.elements.filter(el => el.type === 'drawing')
  const eraserSize = 20 // Eraser detection radius in pixels
  
  console.log('🔍 Checking eraser at position:', { x, y, drawingCount: drawingElements.length })
  
  for (const drawing of drawingElements) {
    // Check if click is within drawing bounds first (quick check)
    if (x >= drawing.x && x <= drawing.x + drawing.width &&
        y >= drawing.y && y <= drawing.y + drawing.height) {
      
      console.log('🎯 Within drawing bounds, checking path proximity...')
      
      // More precise check: see if click is near the actual path
      if (isPointNearPath(x, y, drawing.path, drawing.size, eraserSize)) {
        console.log('✅ Found drawing to erase:', drawing.id)
        return drawing
      }
    }
  }
  
  console.log('❌ No drawing found at position')
  return null
}

// Helper function to check if a point is near an SVG path
const isPointNearPath = (px, py, pathData, strokeWidth, tolerance = 10) => {
  try {
    // Create a temporary SVG path element to measure distance
    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path')
    path.setAttribute('d', pathData)
    
    // Get the path length and check points along the path
    const pathLength = path.getTotalLength()
    const samplePoints = Math.min(100, Math.floor(pathLength / 5)) // Sample every 5 pixels or max 100 points
    
    console.log('📏 Analyzing path:', { pathLength, samplePoints, strokeWidth, tolerance })
    
    for (let i = 0; i <= samplePoints; i++) {
      const distance = i / samplePoints
      const point = path.getPointAtLength(pathLength * distance)
      
      // Calculate distance from click to path point
      const dx = px - point.x
      const dy = py - point.y
      const distanceToPath = Math.sqrt(dx * dx + dy * dy)
      
      // If point is within tolerance (considering stroke width), it's a hit
      if (distanceToPath <= (strokeWidth / 2) + tolerance) {
        console.log('🎯 Hit detected at distance:', distanceToPath)
        return true
      }
    }
    
    return false
  } catch (error) {
    // If path parsing fails, fall back to bounding box check
    console.warn('Path analysis failed, using bounding box:', error)
    return true
  }
}

let pasteTimeout = null

const addElement = (type) => {
  const dimensions = {
    text: { width: 200, height: 30, content: 'Text', fontSize: 24 },
    heading: { width: 300, height: 60, content: 'Heading', fontSize: 48 },
    subheading: { width: 250, height: 40, content: 'Subheading', fontSize: 32 },
    image: { width: 200, height: 150, content: '', fontSize: 16 },
    rectangle: { width: 150, height: 100, content: '', fontSize: 16 }
  }

  const dim = dimensions[type] || dimensions.text

  const newElement = {
    id: Date.now(),
    type,
    x: 100,
    y: 100,
    width: dim.width,
    height: dim.height,
    content: dim.content,
    fontSize: dim.fontSize,
    color: '#000000',
    background: '#3B82F6',
    border: '2px solid #1E40AF',
    opacity: 1,
    startHidden: false,
    clickable: type === 'rectangle' ? true : false,
    moveable: type === 'rectangle' ? false : true,
    zIndex: 1
  }

  const updatedSlide = {
    ...props.currentSlide,
    elements: [...props.currentSlide.elements, newElement]
  }
  
  emit('slide-update', updatedSlide)
}

const updateElement = (updatedElement) => {
  const updatedSlide = {
    ...props.currentSlide,
    elements: props.currentSlide.elements.map(el => 
      el.id === updatedElement.id ? updatedElement : el
    )
  }
  
  emit('slide-update', updatedSlide)
}

const deleteElement = (elementId) => {
  const updatedSlide = {
    ...props.currentSlide,
    elements: props.currentSlide.elements.filter(el => el.id !== elementId)
  }
  
  emit('slide-update', updatedSlide)
}

const duplicateElement = (duplicatedElement) => {
  const updatedSlide = {
    ...props.currentSlide,
    elements: [...props.currentSlide.elements, duplicatedElement]
  }
  
  emit('slide-update', updatedSlide)
}

const triggerImageUpload = () => {
  imageInput.value?.click()
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    const reader = new FileReader()
    reader.onload = (e) => {
      // Create an image to get original dimensions
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
}

const toggleSettings = () => {
  showSettings.value = !showSettings.value
}

const handleSizeChange = (size) => {
  emit('slide-size-change', { selectedSize: size })
}

const handleCustomSizeChange = (dimension, value) => {
  const numValue = parseInt(value) || 0
  if (dimension === 'width') {
    emit('slide-size-change', { customWidth: numValue })
  } else if (dimension === 'height') {
    emit('slide-size-change', { customHeight: numValue })
  }
}

const triggerPaste = async () => {
  // console.log('🔥 Trigger paste button clicked')
  canvasRef.value?.focus()
  // console.log('🔥 Canvas focused, attempting to paste from clipboard')
  await pasteFromClipboard()
}

const pasteFromClipboard = async () => {
  // // console.log('🔥 pasteFromClipboard called')
  try {
    // First try the modern clipboard API for images and rich content
    if (navigator.clipboard && navigator.clipboard.read) {
      // console.log('🔥 Using modern clipboard API')
      const clipboardItems = await navigator.clipboard.read()
      // console.log('🔥 Clipboard items found:', clipboardItems.length)
      for (const clipboardItem of clipboardItems) {
        // console.log('🔥 Clipboard item types:', clipboardItem.types)
        for (const type of clipboardItem.types) {
          if (type.startsWith('image/')) {
            // console.log('🔥 Found image type:', type)
            const blob = await clipboardItem.getType(type)
            const reader = new FileReader()
            reader.onload = (e) => {
              // Create an image to get original dimensions
              const img = new Image()
              img.onload = () => {
                // console.log('🔥 Image loaded, dimensions:', img.width, 'x', img.height)
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

                const updatedSlide = {
                  ...props.currentSlide,
                  elements: [...props.currentSlide.elements, imageElement]
                }
                
                // console.log('🔥 Emitting slide update with image element')
                emit('slide-update', updatedSlide)
              }
              img.src = e.target.result
            }
            reader.readAsDataURL(blob)
            return
          } else if (type === 'text/plain') {
            // console.log('🔥 Found text type:', type)
            const text = await clipboardItem.getType(type)
            const reader = new FileReader()
            reader.onload = (e) => {
              // console.log('🔥 Text content loaded:', e.target.result)
              const textElement = {
                id: Date.now(),
                type: 'text',
                x: 100,
                y: 100,
                width: 200,
                height: 30,
                content: e.target.result,
                fontSize: 24,
                color: '#000000',
                opacity: 1,
                startHidden: false,
                clickable: false,
                moveable: false,
                zIndex: 1
              }

              const updatedSlide = {
                ...props.currentSlide,
                elements: [...props.currentSlide.elements, textElement]
              }
              
              // console.log('🔥 Emitting slide update with text element')
              emit('slide-update', updatedSlide)
            }
            reader.readAsText(text)
            return
          }
        }
      }
    }

    // Fallback: Try to read text clipboard (more reliable for text)
    // console.log('🔥 Trying fallback text clipboard')
    if (navigator.clipboard && navigator.clipboard.readText) {
      const text = await navigator.clipboard.readText()
      // console.log('🔥 Fallback text:', text)
      if (text && text.trim()) {
        const textElement = {
          id: Date.now(),
          type: 'text',
          x: 100,
          y: 100,
          width: 200,
          height: 30,
          content: text,
          fontSize: 24,
          color: '#000000',
          opacity: 1,
          startHidden: false,
          clickable: false,
          moveable: false,
          zIndex: 1
        }

        const updatedSlide = {
          ...props.currentSlide,
          elements: [...props.currentSlide.elements, textElement]
        }
        
        // console.log('🔥 Emitting slide update with fallback text element')
        emit('slide-update', updatedSlide)
        return
      }
    }

    // If nothing was found, show a message
    // console.log('🔥 No content found in clipboard')
    showPasteMessage('No content found in clipboard')
    
  } catch (error) {
    console.error('🔥 Failed to read clipboard:', error)
    
    // Try simple text fallback
    try {
      // console.log('🔥 Trying simple text fallback')
      const text = await navigator.clipboard.readText()
      // console.log('🔥 Simple fallback text:', text)
      if (text && text.trim()) {
        const textElement = {
          id: Date.now(),
          type: 'text',
          x: 100,
          y: 100,
          width: 200,
          height: 30,
          content: text,
          fontSize: 24,
          color: '#000000',
          opacity: 1,
          startHidden: false,
          clickable: false,
          moveable: false,
          zIndex: 1
        }

        const updatedSlide = {
          ...props.currentSlide,
          elements: [...props.currentSlide.elements, textElement]
        }
        
        // console.log('🔥 Emitting slide update with simple fallback text')
        emit('slide-update', updatedSlide)
      } else {
        // console.log('🔥 No text content in simple fallback')
        showPasteMessage('No text content found in clipboard')
      }
    } catch (textError) {
      console.error('🔥 Failed to read text clipboard:', textError)
      showPasteMessage('Clipboard access denied or no content available')
    }
  }
}

const showPasteMessage = (message) => {
  // Show a temporary message
  const messageEl = document.createElement('div')
  messageEl.textContent = message
  messageEl.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-orange-500 text-white px-4 py-2 rounded-lg shadow-lg z-50'
  document.body.appendChild(messageEl)
  
  setTimeout(() => {
    if (document.body.contains(messageEl)) {
      document.body.removeChild(messageEl)
    }
  }, 3000)
}

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
              width: img.width,
              height: img.height,
              src: e.target.result,
              opacity: 1,
              startHidden: false,
              clickable: false,
              moveable: false,
              zIndex: 1
            }
            emit('slide-update', { ...props.currentSlide, elements: [...props.currentSlide.elements, imageElement] })
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
            opacity: 1,
            startHidden: false,
            clickable: false,
            moveable: false,
            zIndex: 1
          }
          emit('slide-update', { ...props.currentSlide, elements: [...props.currentSlide.elements, textElement] })
        })
        break
      }
    }
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  showPasteZone.value = false

  const files = event.dataTransfer.files
  for (const file of files) {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        // Create an image to get original dimensions
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
  }
}

const handleDragOver = () => {
  showPasteZone.value = true
  if (pasteTimeout) clearTimeout(pasteTimeout)
  pasteTimeout = setTimeout(() => {
    showPasteZone.value = false
  }, 3000)
}

// Context Menu Functions
const showContextMenu = (event) => {
  contextMenuX.value = event.clientX
  contextMenuY.value = event.clientY
  showCanvasMenu.value = true
  
  // Hide menu when clicking outside
  nextTick(() => {
    document.addEventListener('click', hideCanvasMenu)
  })
}

const hideCanvasMenu = () => {
  showCanvasMenu.value = false
  document.removeEventListener('click', hideCanvasMenu)
}

const pasteFromCanvasMenu = async () => {
  // console.log('🔥 Context menu paste clicked')
  hideCanvasMenu()
  await pasteFromClipboard()
}

const addDateTimeText = () => {
  hideCanvasMenu()
  
  const now = new Date()
  const dateTimeString = now.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
  
  const textElement = {
    id: Date.now(),
    type: 'text',
    x: 100,
    y: 100,
    width: 400,
    height: 30,
    content: dateTimeString,
    fontSize: 18,
    color: '#000000',
    opacity: 1,
    startHidden: false,
    clickable: false,
    moveable: false,
    zIndex: 1
  }
  
  const updatedSlide = {
    ...props.currentSlide,
    elements: [...props.currentSlide.elements, textElement]
  }
  
  emit('slide-update', updatedSlide)
}

onMounted(() => {
  document.addEventListener('dragover', handleDragOver)
})

onUnmounted(() => {
  document.removeEventListener('dragover', handleDragOver)
  if (pasteTimeout) clearTimeout(pasteTimeout)
})
</script>
