<template>
  <div class="fixed inset-0 bg-black z-50 overflow-hidden">
    <!-- White Canvas Container -->
    <div class="h-full flex items-center justify-center p-8">
      <div
        class="bg-white shadow-2xl relative"
        :style="{ width: '794px', maxHeight: '80vh', overflowY: 'auto' }"
      >
        <!-- Current Slide -->
        <div
          class="relative"
          :style="{ height: slideHeight + 'px' }"
        >
          <!-- Elements with Presentation States -->
          <div
            v-for="element in currentSlide.elements"
            :key="element.id"
            class="absolute"
            :style="getElementStyle(element)"
            @click="handleElementClick(element)"
            @mousedown="startDrag(element, $event)"
          >
            <!-- Text Elements -->
            <div
              v-if="element.type === 'text' || element.type === 'heading' || element.type === 'subheading'"
              :style="getTextStyle(element)"
            >
              {{ element.content }}
            </div>

            <!-- Image Elements -->
            <img
              v-else-if="element.type === 'image'"
              :src="element.src"
              class="w-full h-full object-cover"
              draggable="false"
            />

            <!-- Rectangle Elements -->
            <div
              v-else-if="element.type === 'rectangle'"
              class="w-full h-full"
              :style="getRectangleStyle(element)"
            ></div>

            <!-- Custom Rectangle Elements -->
            <div
              v-else-if="element.type === 'custom-rectangle'"
              class="w-full h-full cursor-pointer"
              :style="getCustomRectangleStyle(element)"
              @click="handleCustomRectangleClick(element)"
            ></div>

            <!-- Rounded Rectangle Elements -->
            <div
              v-else-if="element.type === 'rounded-rectangle'"
              class="w-full h-full cursor-pointer"
              :style="getRoundedRectangleStyle(element)"
              @click="handleCustomRectangleClick(element)"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Navigation Pill -->
    <div class="fixed bottom-8 left-1/2 transform -translate-x-1/2">
      <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-6 py-3 flex items-center space-x-4">
        <!-- Previous Button -->
        <button
          @click="previousSlide"
          :disabled="currentSlideIndex === 0"
          class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transition-colors"
        >
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>

        <!-- Slide Counter -->
        <div class="text-white font-medium">
          {{ currentSlideIndex + 1 }} / {{ slides.length }}
        </div>

        <!-- Next Button -->
        <button
          @click="nextSlide"
          :disabled="currentSlideIndex === slides.length - 1"
          class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transition-colors"
        >
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>

        <!-- Exit Button -->
        <button
          @click="$emit('exit')"
          class="w-10 h-10 rounded-full bg-red-500/80 hover:bg-red-600 flex items-center justify-center transition-colors"
        >
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  slides: {
    type: Array,
    required: true
  },
  currentSlideIndex: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['slide-change', 'exit'])

// Track element states during presentation
const elementStates = ref(new Map())

const currentSlide = computed(() => props.slides[props.currentSlideIndex] || { elements: [] })

const slideHeight = computed(() => {
  // Default to A4 height if not specified
  return 1123
})

const getElementStyle = (element) => {
  const baseStyle = {
    position: 'absolute',
    left: `${element.x}px`,
    top: `${element.y}px`,
    width: `${element.width}px`,
    height: `${element.height}px`,
    zIndex: element.zIndex || 1,
    cursor: getElementCursor(element)
  }

  // Apply visibility states
  if (element.clickable) {
    const stateKey = `${props.currentSlideIndex}-${element.id}`
    const isVisible = elementStates.value.get(stateKey) !== false
    
    if (element.startHidden) {
      baseStyle.opacity = isVisible ? 1 : (element.hiddenOpacity || 0.05)
    } else {
      baseStyle.opacity = isVisible ? 1 : 0.1
    }
  } else {
    baseStyle.opacity = element.opacity || 1
  }

  return baseStyle
}

const getTextStyle = (element) => ({
  fontSize: `${element.fontSize}px`,
  color: element.color,
  fontFamily: 'system-ui, -apple-system, sans-serif',
  lineHeight: '1.2',
  userSelect: 'none'
})

const getRectangleStyle = (element) => ({
  backgroundColor: element.background || '#3B82F6',
  borderColor: getBorderColor(element),
  borderWidth: '2px',
  borderStyle: 'solid',
  borderRadius: '0px'
})

const getCustomRectangleStyle = (element) => ({
  backgroundColor: element.background || '#3B82F6',
  borderColor: getBorderColor(element),
  borderWidth: '2px',
  borderStyle: 'solid',
  borderRadius: '0px'
})

const getRoundedRectangleStyle = (element) => ({
  backgroundColor: element.background || '#3B82F6',
  borderColor: getBorderColor(element),
  borderWidth: '2px',
  borderStyle: 'solid',
  borderRadius: '12px'
})

const getBorderColor = (element) => {
  if (element.border) {
    // Extract color from border string like "2px solid #000000"
    const match = element.border.match(/#\w+/)
    return match ? match[0] : '#000000'
  }
  return element.color || '#000000'
}

const getElementCursor = (element) => {
  if (element.moveable) return 'move'
  if (element.clickable) return 'pointer'
  return 'default'
}

const handleElementClick = (element) => {
  if (element.clickable) {
    // Toggle visibility state
    const stateKey = `${props.currentSlideIndex}-${element.id}`
    const currentState = elementStates.value.get(stateKey) !== false
    elementStates.value.set(stateKey, !currentState)
    
    // Play click sound if available
    if (window.SoundManager) {
      SoundManager.playClick(0.5)
    }
  }
}

const handleCustomRectangleClick = (element) => {
  if (element.clickable) {
    // Toggle visibility state
    const stateKey = `${props.currentSlideIndex}-${element.id}`
    const currentState = elementStates.value.get(stateKey) !== false
    elementStates.value.set(stateKey, !currentState)
    
    // Visual feedback
    const target = event.target
    target.style.transform = 'scale(0.95)'
    setTimeout(() => {
      target.style.transform = 'scale(1)'
    }, 100)
    
    // Play click sound if available
    if (window.SoundManager) {
      SoundManager.playClick(0.5)
    }
    
    console.log('Rectangle toggled visibility:', !currentState ? 'hidden' : 'visible')
  }
}

// Drag functionality for moveable elements
const draggedElement = ref(null)
const dragStart = ref({ x: 0, y: 0 })
const elementStart = ref({ x: 0, y: 0 })

const startDrag = (element, event) => {
  if (!element.moveable) return
  
  draggedElement.value = element
  dragStart.value = { x: event.clientX, y: event.clientY }
  elementStart.value = { x: element.x, y: element.y }
  
  document.addEventListener('mousemove', handleDrag)
  document.addEventListener('mouseup', stopDrag)
  event.preventDefault()
}

const handleDrag = (event) => {
  if (!draggedElement.value) return
  
  const deltaX = event.clientX - dragStart.value.x
  const deltaY = event.clientY - dragStart.value.y
  
  // Update element position temporarily (won't persist to original data)
  draggedElement.value.x = elementStart.value.x + deltaX
  draggedElement.value.y = elementStart.value.y + deltaY
}

const stopDrag = () => {
  draggedElement.value = null
  document.removeEventListener('mousemove', handleDrag)
  document.removeEventListener('mouseup', stopDrag)
}

const previousSlide = () => {
  if (props.currentSlideIndex > 0) {
    emit('slide-change', props.currentSlideIndex - 1)
  }
}

const nextSlide = () => {
  if (props.currentSlideIndex < props.slides.length - 1) {
    emit('slide-change', props.currentSlideIndex + 1)
  }
}

const handleKeydown = (event) => {
  switch (event.key) {
    case 'ArrowLeft':
      previousSlide()
      break
    case 'ArrowRight':
      nextSlide()
      break
    case 'Escape':
      emit('exit')
      break
  }
}

// Reset element states when slide changes
const resetElementStates = () => {
  // Clear states for the current slide only
  const keysToDelete = []
  for (const key of elementStates.value.keys()) {
    if (key.startsWith(`${props.currentSlideIndex}-`)) {
      keysToDelete.push(key)
    }
  }
  keysToDelete.forEach(key => elementStates.value.delete(key))
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  resetElementStates()
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('mousemove', handleDrag)
  document.removeEventListener('mouseup', stopDrag)
})

// Watch for slide changes
watch(() => props.currentSlideIndex, () => {
  resetElementStates()
})
</script>
