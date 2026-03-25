<template>
  <Head title="Presentation Builder V3 - MyClass2026" />
  <div class="min-h-screen bg-gray-900">

    
    <!-- Top Bar -->
    <TopBar
      :mode="mode"
      :selected-size="selectedSize"
      :custom-width="customWidth"
      :custom-height="customHeight"
      :has-prev-slide="hasPrevSlide"
      :has-next-slide="hasNextSlide"
      @mode-change="mode = $event"
      @slide-size-change="handleSlideSizeChange"
      @add-slide="addSlide"
      @add-text="addElement('text')"
      @add-rectangle="addElement('rectangle')"
      @add-image="triggerImageUpload"
      @prev-slide="prevSlide"
      @next-slide="nextSlide"
      @paste="triggerPaste"
    />

    <!-- Main Content -->
    <div class="flex h-screen">
      <!-- Main Content Area -->
      <div class="flex-1 bg-gray-900 overflow-auto">
        <!-- Slide Panel -->
        <SlidePanel
          ref="slidePanelRef"
          :slides="slides"
          :current-slide-index="currentSlideIndex"
          @slide-select="currentSlideIndex = $event"
          @slide-delete="deleteSlide"
        />

        <!-- Canvas Area - Full Width -->
        <div class="w-full flex justify-center items-center p-4">
          <DrawingCanvas
            v-if="mode === 'edit'"
            :slide-width="computedSlideWidth"
            :slide-height="computedSlideHeight"
            :current-slide="currentSlide"
            :is-presentation="false"
            @slide-update="updateSlide"
            @element-update="updateElement"
            @element-delete="deleteElement"
            @element-duplicate="duplicateElement"
          />

          <PresenterV3
            v-if="mode === 'present'"
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import TopBar from './components/TopBar.vue'
import SlidePanel from './components/SlidePanel.vue'
import DrawingCanvas from './components/DrawingCanvas.vue'
import PresenterV3 from './components/PresenterV3.vue'

// Define props to prevent Vue warnings
const props = defineProps({
  errors: {
    type: Object,
    default: () => ({})
  },
  jetstream: {
    type: Object,
    default: () => ({})
  },
  auth: {
    type: Object,
    default: () => ({})
  },
  errorBags: {
    type: Object,
    default: () => ({})
  },
  csrf_token: {
    type: String,
    default: ''
  },
  user_context: {
    type: Object,
    default: () => ({})
  },
  context_meta: {
    type: Object,
    default: () => ({})
  },
  title: {
    type: String,
    default: ''
  }
})

// Refs
const slidePanelRef = ref(null)

// State
const mode = ref('edit') // 'edit' | 'present'
const currentSlideIndex = ref(0)
const slideHeight = ref(1123) // A4 default
const slideWidth = ref(794) // Default width

// Slide size settings
const selectedSize = ref('widescreen')
const customWidth = ref(1920)
const customHeight = ref(1080)

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
      return 1123
  }
})

// Slide navigation computed properties
const hasPrevSlide = computed(() => currentSlideIndex.value > 0)
const hasNextSlide = computed(() => currentSlideIndex.value < slides.value.length - 1)

// Methods
const addSlide = () => {
  const newSlide = {
    id: Date.now(),
    elements: []
  }
  slides.value.push(newSlide)
  currentSlideIndex.value = slides.value.length - 1
}

const deleteSlide = (slideId) => {
  if (slides.value.length <= 1) return // Don't delete last slide
  
  const slideIndex = slides.value.findIndex(slide => slide.id === slideId)
  if (slideIndex !== -1) {
    slides.value.splice(slideIndex, 1)
    
    // Adjust current slide index if needed
    if (currentSlideIndex.value >= slides.value.length) {
      currentSlideIndex.value = slides.value.length - 1
    }
  }
}

const prevSlide = () => {
  if (hasPrevSlide.value) {
    currentSlideIndex.value--
  }
}

const nextSlide = () => {
  if (hasNextSlide.value) {
    currentSlideIndex.value++
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
  try {
    // Request clipboard read permission
    const clipboardItems = await navigator.clipboard.read()
    
    // Focus on the canvas first
    const canvas = document.querySelector('[tabindex="0"]')
    if (canvas) {
      canvas.focus()
      
      let pastedSuccessfully = false
      // Manually add clipboard data to event
      let imageHandled = false
      
      for (const item of clipboardItems) {
        for (const type of item.types) {
          if (type.startsWith('image/') && !imageHandled) {
            const blob = await item.getType(type)
            
            // Create image element directly
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
                  ...currentSlide.value,
                  elements: [...currentSlide.value.elements, imageElement]
                }
                updateSlide(updatedSlide)
                showSuccessToast('Image pasted successfully!')
              }
              img.src = e.target.result
            }
            reader.readAsDataURL(blob)
            imageHandled = true
            pastedSuccessfully = true
            break
          } else if (type === 'text/plain') {
            const text = await item.getType(type)
            const textContent = await text.text()
            
            if (textContent?.trim()) {
              const textElement = {
                id: Date.now(),
                type: 'text',
                x: 100,
                y: 100,
                width: 400,
                height: 30,
                content: textContent.trim(),
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
                ...currentSlide.value,
                elements: [...currentSlide.value.elements, textElement]
              }
              updateSlide(updatedSlide)
              showSuccessToast('Text pasted successfully!')
              pastedSuccessfully = true
            }
          }
        }
        if (imageHandled) break
      }
      
      if (!pastedSuccessfully) {
        showInfoToast('No supported content found in clipboard')
      }
    }
  } catch (error) {
    console.error('Failed to read clipboard:', error)
    
    // Fallback: Focus canvas and show instructions
    const canvas = document.querySelector('[tabindex="0"]')
    if (canvas) {
      canvas.focus()
      showInfoToast('Press Ctrl+V to paste')
    }
  }
}

// Toast notification functions
const showSuccessToast = (message) => {
  const toast = document.createElement('div')
  toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300'
  toast.textContent = message
  document.body.appendChild(toast)
  
  // Animate in
  setTimeout(() => {
    toast.classList.add('opacity-100')
  }, 10)
  
  // Remove after delay
  setTimeout(() => {
    toast.classList.add('opacity-0')
    setTimeout(() => {
      if (document.body.contains(toast)) {
        document.body.removeChild(toast)
      }
    }, 300)
  }, 2000)
}

const showInfoToast = (message) => {
  const toast = document.createElement('div')
  toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300'
  toast.textContent = message
  document.body.appendChild(toast)
  
  // Animate in
  setTimeout(() => {
    toast.classList.add('opacity-100')
  }, 10)
  
  // Remove after delay
  setTimeout(() => {
    toast.classList.add('opacity-0')
    setTimeout(() => {
      if (document.body.contains(toast)) {
        document.body.removeChild(toast)
      }
    }, 300)
  }, 2000)
}

// Element management functions - moved to component level for accessibility
const updateElement = (element) => {
  if (!element) return // Guard clause for null/undefined
  
  const updatedSlide = {
    ...currentSlide.value,
    elements: currentSlide.value.elements.map(el => el.id === element.id ? element : el)
  }
  updateSlide(updatedSlide)
}

const deleteElement = (elementId) => {
  if (!elementId) return // Guard clause for null/undefined
  
  const updatedSlide = {
    ...currentSlide.value,
    elements: currentSlide.value.elements.filter(el => el.id !== elementId)
  }
  updateSlide(updatedSlide)
}

const duplicateElement = (element) => {
  if (!element) return // Guard clause for null/undefined
  
  const newElement = {
    ...element,
    id: Date.now(),
    x: (element.x || 0) + 20,
    y: (element.y || 0) + 20
  }
  const updatedSlide = {
    ...currentSlide.value,
    elements: [...currentSlide.value.elements, newElement]
  }
  updateSlide(updatedSlide)
}

// Drawing methods
const toggleDrawingMode = () => {
  isDrawingMode.value = !isDrawingMode.value
}

const toggleEraserMode = () => {
  isEraserMode.value = !isEraserMode.value
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

// Mobile slide panel toggle
const toggleMobileSlidePanel = () => {
  if (slidePanelRef.value) {
    slidePanelRef.value.openMobilePanel()
  }
}
</script>
