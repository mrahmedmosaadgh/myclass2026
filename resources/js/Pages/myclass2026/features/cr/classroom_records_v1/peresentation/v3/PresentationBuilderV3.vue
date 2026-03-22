<template>
  <Head title="Presentation Builder V3 - MyClass2026" />
  <div class="min-h-screen bg-gray-900">

    
    <!-- Top Bar -->
    <TopBar
      :mode="mode"
      :current-slide-index="currentSlideIndex"
      :total-slides="slides.length"
      :slide-height="computedSlideHeight"
      :slide-width="computedSlideWidth"
      :selected-size="selectedSize"
      :custom-width="customWidth"
      :custom-height="customHeight"
      :is-drawing-mode="isDrawingMode"
      :is-eraser-mode="isEraserMode"
      :pen-size="penSize"
      :pen-color="penColor"
      @mode-change="mode = $event"
      @export="exportJSON"
      @import="importJSON"
      @delete-slide="deleteSlide"
      @height-change="slideHeight = $event"
      @slide-size-change="handleSlideSizeChange"
      @add-element="addElement"
      @add-image="triggerImageUpload"
      @add-slide="addSlide"
      @paste="triggerPaste"
      @toggle-drawing="toggleDrawingMode"
      @toggle-eraser="toggleEraserMode"
      @pen-size-change="penSize = $event"
      @pen-color-change="(color) => { //console.log('🎨 Pen color changed to:', color);
        penColor = color }"
      @clear-drawing="clearDrawing"
      @undo-drawing="undoDrawing"
    />

    <!-- Main Content -->
    <div class="flex h-screen pt-4">
      <!-- Edit Mode -->
      <template v-if="mode === 'edit'">
        <!-- Left Slide Panel -->
        <SlidePanel
          :slides="slides"
          :current-slide-index="currentSlideIndex"
          @slide-select="currentSlideIndex = $event"
          @slide-delete="deleteSlide"
        />

        <!-- Editor Canvas -->
        <EditorCanvas
          :current-slide="currentSlide"
          :slide-height="computedSlideHeight"
          :slide-width="computedSlideWidth"
          :selected-size="selectedSize"
          :custom-width="customWidth"
          :custom-height="customHeight"
          :is-eraser-mode="isEraserMode"
          :is-drawing-mode="isDrawingMode"
          :pen-size="penSize"
          :pen-color="penColor"
          :drawing-paths="drawingPaths"
          @slide-update="updateSlide"
          @slide-size-change="handleSlideSizeChange"
          @drawing-start="handleDrawingStart"
          @drawing-move="handleDrawingMove"
          @drawing-end="handleDrawingEnd"
          @drawing-clear="clearDrawing"
          @drawing-undo="undoDrawing"
        />
      </template>

      <!-- Present Mode -->
      <PresenterV3
        v-else
        :slides="slides"
        :current-slide-index="currentSlideIndex"
        :slide-width="computedSlideWidth"
        :slide-height="computedSlideHeight"
        :selected-size="selectedSize"
        :custom-width="customWidth"
        :custom-height="customHeight"
        @slide-change="currentSlideIndex = $event"
        @exit="mode = 'edit'"
        @slide-size-change="handleSlideSizeChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import TopBar from './components/TopBar.vue'
import SlidePanel from './components/SlidePanel.vue'
import EditorCanvas from './components/EditorCanvas.vue'
import PresenterV3 from './components/PresenterV3.vue'

// State
const mode = ref('edit') // 'edit' | 'present'
const currentSlideIndex = ref(0)
const slideHeight = ref(1123) // A4 default
const slideWidth = ref(794) // Default width

// Slide size settings
const selectedSize = ref('widescreen')
const customWidth = ref(1920)
const customHeight = ref(1080)

// Drawing settings
const isDrawingMode = ref(false)
const isEraserMode = ref(false)
const penSize = ref(2)
const penColor = ref('#000000')
const drawingPaths = ref([])
const currentPath = ref(null)

const slides = ref([
  {
    id: 1,
    elements: []
  }
])

// Computed
const currentSlide = computed(() => slides.value[currentSlideIndex.value] || { id: 1, elements: [] })

// Computed slide dimensions based on selected size
const computedSlideWidth = computed(() => {
  switch (selectedSize.value) {
    case 'widescreen':
      return 1920
    case 'standard':
      return 1024
    case 'custom':
      return customWidth.value || 1920
    default:
      return 794
  }
})

const computedSlideHeight = computed(() => {
  switch (selectedSize.value) {
    case 'widescreen':
      return 1080
    case 'standard':
      return 768
    case 'custom':
      return customHeight.value || 1080
    default:
      return slideHeight.value
  }
})

// Methods
const addSlide = () => {
  const newSlide = {
    id: Date.now(),
    elements: []
  }
  slides.value.push(newSlide)
  currentSlideIndex.value = slides.value.length - 1
}

const deleteSlide = (index) => {
  if (slides.value.length > 1) {
    slides.value.splice(index, 1)
    if (currentSlideIndex.value >= slides.value.length) {
      currentSlideIndex.value = slides.value.length - 1
    }
  }
}

const updateSlide = (updatedSlide) => {
  const index = slides.value.findIndex(s => s.id === updatedSlide.id)
  if (index !== -1) {
    slides.value[index] = { ...updatedSlide }
  }
}

const handleSlideSizeChange = (sizeData) => {
  if (sizeData.selectedSize) {
    selectedSize.value = sizeData.selectedSize
  }
  if (sizeData.customWidth !== undefined) {
    customWidth.value = sizeData.customWidth
  }
  if (sizeData.customHeight !== undefined) {
    customHeight.value = sizeData.customHeight
  }
}

// Methods for handling toolbar actions
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
    ...currentSlide.value,
    elements: [...currentSlide.value.elements, newElement]
  }
  
  updateSlide(updatedSlide)
}

const triggerImageUpload = () => {
  // Create a temporary file input and trigger it
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/*'
  input.onchange = (event) => {
    const file = event.target.files[0]
    if (file && file.type.startsWith('image/')) {
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

          const updatedSlide = {
            ...currentSlide.value,
            elements: [...currentSlide.value.elements, imageElement]
          }
          
          updateSlide(updatedSlide)
        }
        img.src = e.target.result
      }
      reader.readAsDataURL(file)
    }
  }
  input.click()
}

const triggerPaste = async () => {
  // Focus on the canvas and trigger paste
  const canvas = document.querySelector('[tabindex="0"]')
  if (canvas) {
    canvas.focus()
    // Create a synthetic paste event
    const pasteEvent = new Event('paste', { bubbles: true })
    canvas.dispatchEvent(pasteEvent)
  }
}

// Drawing methods
const toggleDrawingMode = () => {
  isDrawingMode.value = !isDrawingMode.value
  ///console.log('🎨 Drawing mode toggled to:', isDrawingMode.value)
}

const toggleEraserMode = () => {
  isEraserMode.value = !isEraserMode.value
  // Turn off drawing mode when eraser is activated
  if (isEraserMode.value) {
    isDrawingMode.value = false
  }
}

const clearDrawing = () => {
  // Remove all drawing elements from current slide
  const updatedSlide = {
    ...currentSlide.value,
    elements: currentSlide.value.elements.filter(el => el.type !== 'drawing')
  }
  updateSlide(updatedSlide)
  
  drawingPaths.value = []
  currentPath.value = null
}

const undoDrawing = () => {
  // Remove last drawing from current slide
  const drawingElements = currentSlide.value.elements.filter(el => el.type === 'drawing')
  if (drawingElements.length > 0) {
    const updatedSlide = {
      ...currentSlide.value,
      elements: currentSlide.value.elements.filter(el => el.id !== drawingElements[drawingElements.length - 1].id)
    }
    updateSlide(updatedSlide)
    
    if (drawingPaths.value.length > 0) {
      drawingPaths.value.pop()
    }
  }
}

// Drawing event handlers
const handleDrawingStart = (data) => {
  //console.log('🎨 Drawing start received:', data)
  currentPath.value = {
    d: `M${data.x},${data.y}`,
    color: data.color,
    size: data.size,
    points: [{x: data.x, y: data.y}]
  }
}

const handleDrawingMove = (data) => {
  //console.log('🎨 Drawing move received:', data)
  if (currentPath.value) {
    // Add smooth curves using quadratic curves
    const points = currentPath.value.points
    if (points.length >= 2) {
      const lastPoint = points[points.length - 1]
      const controlPoint = {
        x: (lastPoint.x + data.x) / 2,
        y: (lastPoint.y + data.y) / 2
      }
      currentPath.value.d += ` Q${controlPoint.x},${controlPoint.y} ${data.x},${data.y}`
    } else {
      currentPath.value.d += ` L${data.x},${data.y}`
    }
    currentPath.value.points.push({x: data.x, y: data.y})
  }
}

const handleDrawingEnd = (path) => {
  //console.log('🎨 Drawing end received:', path)
  if (path && path.points.length > 1) {
    // Save drawing as a new element in the current slide
    const drawingElement = {
      id: Date.now(),
      type: 'drawing',
      path: path.d,
      color: path.color,
      size: path.size,
      x: 0,
      y: 0,
      width: slideWidth.value,
      height: slideHeight.value,
      opacity: 1,
      zIndex: 1000 // Drawings should be on top
    }
    
    const updatedSlide = {
      ...currentSlide.value,
      elements: [...currentSlide.value.elements, drawingElement]
    }
    
    //console.log('🎨 Adding drawing element to slide:', drawingElement)
    updateSlide(updatedSlide)
    drawingPaths.value.push(path)
    currentPath.value = null
  }
}

const exportJSON = () => {
  const data = {
    version: 'v3',
    timestamp: new Date().toISOString(),
    slides: slides.value,
    slideHeight: slideHeight.value
  }
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `presentation-v3-${Date.now()}.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

const importJSON = (file) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    try {
      const data = JSON.parse(e.target.result)
      if (data.slides && Array.isArray(data.slides)) {
        slides.value = data.slides
        if (data.slideHeight) {
          slideHeight.value = data.slideHeight
        }
        currentSlideIndex.value = 0
      }
    } catch (error) {
      console.error('Invalid JSON file:', error)
    }
  }
  reader.readAsText(file)
}
</script>
