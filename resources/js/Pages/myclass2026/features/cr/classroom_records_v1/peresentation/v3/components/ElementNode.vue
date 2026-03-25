<template>
  <div
    ref="elementRef"
    class="absolute cursor-move touch-manipulation"
    :style="elementStyle"
    @mousedown="startDrag"
    @touchstart="startTouchDrag"
    @touchmove="handleTouchMove"
    @touchend="handleTouchEnd"
    @touchcancel="handleTouchEnd"
  >
    <!-- Element Content -->
    <div
      v-if="element.type === 'text' || element.type === 'heading' || element.type === 'subheading'"
      :contenteditable="isSelected"
      @blur="updateContent"
      @keydown="handleKeydown"
      class="outline-none"
      :style="textStyle"
    >
      {{ element.content }}
    </div>

    <img
      v-else-if="element.type === 'image'"
      :src="element.src"
      class="w-full h-full object-cover"
      draggable="false"
    />

    <div
      v-else-if="element.type === 'rectangle'"
      class="w-full h-full cursor-pointer"
      :style="rectangleStyle"
      @click="handleClick"
    ></div>

    <!-- Selection Border -->
    <div
      v-if="isSelected"
      class="absolute inset-0 border-2 border-indigo-500 pointer-events-none"
    ></div>

    <!-- Action Buttons (shown when selected) -->
    <div
      v-if="isSelected"
      class="absolute -top-8 -right-2 flex gap-1 bg-white rounded-lg shadow-lg border border-gray-200 p-1"
      @click.stop
    >
      <!-- Duplicate Button -->
      <button
        @click="duplicate"
        class="w-6 h-6 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
        title="Duplicate"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
        </svg>
      </button>
      
      <!-- Delete Button -->
      <button
        @click="deleteElement"
        class="w-6 h-6 flex items-center justify-center text-gray-600 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
        title="Delete"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
      </button>
    </div>

    <!-- Resize Handles (Edit Mode Only) -->
    <div v-if="isSelected && (element.type === 'rectangle' || element.type === 'image')" class="resize-controls">
      <!-- Top-Left -->
      <div class="resize-handle resize-nw" @mousedown.stop="startResize($event, 'nw')"></div>
      <!-- Top-Right -->
      <div class="resize-handle resize-ne" @mousedown.stop="startResize($event, 'ne')"></div>
      <!-- Bottom-Left -->
      <div class="resize-handle resize-sw" @mousedown.stop="startResize($event, 'sw')"></div>
      <!-- Bottom-Right -->
      <div class="resize-handle resize-se" @mousedown.stop="startResize($event, 'se')"></div>
      <!-- Top Center -->
      <div class="resize-handle resize-n" @mousedown.stop="startResize($event, 'n')"></div>
      <!-- Right Center -->
      <div class="resize-handle resize-e" @mousedown.stop="startResize($event, 'e')"></div>
      <!-- Bottom Center -->
      <div class="resize-handle resize-s" @mousedown.stop="startResize($event, 's')"></div>
      <!-- Left Center -->
      <div class="resize-handle resize-w" @mousedown.stop="startResize($event, 'w')"></div>
    </div>

    <!-- Context Menu Button -->
    <button
      v-if="isSelected"
      @click.stop="toggleContextMenu"
      class="absolute -top-2 -right-2 w-6 h-6 bg-indigo-500 text-white rounded-full flex items-center justify-center hover:bg-indigo-600 transition-colors"
    >
      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
      </svg>
    </button>

    <!-- Context Menu -->
    <div
      v-if="showContextMenu"
      ref="contextMenuRef"
      class="absolute top-full right-0 mt-1 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
      @click.stop
    >
      <!-- Visibility Section -->
      <div class="p-2 border-b border-gray-200">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Visibility</h4>
        
        <button
          @click="setVisibility('hidden-clickable')"
          :class="[
            'w-full text-left px-3 py-2 rounded text-sm flex items-center space-x-2',
            element.startHidden && element.clickable ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50'
          ]"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
          </svg>
          <span>Start Hidden, Click to Show</span>
        </button>

        <button
          @click="setVisibility('visible-clickable')"
          :class="[
            'w-full text-left px-3 py-2 rounded text-sm flex items-center space-x-2',
            !element.startHidden && element.clickable ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50'
          ]"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
          <span>Start Visible, Click to Hide</span>
        </button>

        <button
          @click="setVisibility('moveable')"
          :class="[
            'w-full text-left px-3 py-2 rounded text-sm flex items-center space-x-2',
            element.moveable ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50'
          ]"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
          </svg>
          <span>Moveable during Presentation</span>
        </button>

        <button
          @click="setVisibility('none')"
          class="w-full text-left px-3 py-2 rounded text-sm flex items-center space-x-2 hover:bg-gray-50"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
          </svg>
          <span>No Interaction</span>
        </button>

        <!-- Hidden Opacity Slider -->
        <div v-if="element.startHidden" class="mt-2 px-3">
          <label class="text-xs text-gray-600">Hidden Opacity: {{ Math.round(element.hiddenOpacity * 100) }}%</label>
          <input
            type="range"
            min="0"
            max="50"
            :value="(element.hiddenOpacity || 0.05) * 100"
            @input="updateHiddenOpacity($event)"
            class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer"
          />
        </div>
      </div>

      <!-- Layers Section -->
      <div class="p-2 border-b border-gray-200">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Layers</h4>
        
        <button
          @click="bringToFront"
          class="w-full text-left px-3 py-2 rounded text-sm hover:bg-gray-50 flex items-center space-x-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"></path>
          </svg>
          <span>Bring to Front</span>
        </button>

        <button
          @click="sendToBack"
          class="w-full text-left px-3 py-2 rounded text-sm hover:bg-gray-50 flex items-center space-x-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7"></path>
          </svg>
          <span>Send to Back</span>
        </button>
      </div>

      <!-- Color Section (for rectangles) -->
      <div v-if="element.type === 'rectangle' || element.type === 'custom-rectangle'" class="p-2 border-b border-gray-200">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Color</h4>
        
        <div class="space-y-2">
          <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-600 w-16">Fill:</label>
            <input
              type="color"
              :value="element.background || '#000000'"
              @input="updateColor('background', $event.target.value)"
              class="w-8 h-8 border border-gray-300 rounded cursor-pointer"
            />
            <input
              type="text"
              :value="element.background || '#000000'"
              @input="updateColor('background', $event.target.value)"
              class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded"
              placeholder="#000000"
            />
          </div>
          
          <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-600 w-16">Border:</label>
            <input
              type="color"
              :value="getBorderColor()"
              @input="updateBorderColor($event.target.value)"
              class="w-8 h-8 border border-gray-300 rounded cursor-pointer"
            />
            <input
              type="text"
              :value="getBorderColor()"
              @input="updateBorderColor($event.target.value)"
              class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded"
              placeholder="#000000"
            />
          </div>
        </div>
      </div>

      <!-- Element Section -->
      <div class="p-2">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Element</h4>
        
        <button
          @click="duplicate"
          class="w-full text-left px-3 py-2 rounded text-sm hover:bg-gray-50 flex items-center space-x-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
          </svg>
          <span>Duplicate</span>
        </button>

        <button
          @click="deleteElement"
          class="w-full text-left px-3 py-2 rounded text-sm hover:bg-red-50 text-red-600 flex items-center space-x-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
          <span>Delete</span>
        </button>
      </div>
    </div>

    <!-- State Indicator Badge -->
    <div
      v-if="element.startHidden || element.clickable || element.moveable"
      class="absolute -top-1 -left-1 w-3 h-3 rounded-full border-2 border-white"
      :class="stateIndicatorClass"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  element: {
    type: Object,
    required: true
  },
  isPresentation: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update', 'delete', 'duplicate', 'click'])

const elementRef = ref(null)
const contextMenuRef = ref(null)
const isSelected = ref(false)
const showContextMenu = ref(false)
const isDragging = ref(false)
const isResizing = ref(false)
const dragStart = ref({ x: 0, y: 0 })
const elementStart = ref({ x: 0, y: 0, width: 0, height: 0 })
const resizeHandle = ref('')

const elementStyle = computed(() => ({
  left: `${props.element.x}px`,
  top: `${props.element.y}px`,
  width: `${props.element.width}px`,
  height: `${props.element.height}px`,
  zIndex: props.element.zIndex || 1
}))

const textStyle = computed(() => ({
  fontSize: `${props.element.fontSize}px`,
  color: props.element.color,
  fontFamily: 'system-ui, -apple-system, sans-serif',
  lineHeight: '1.2'
}))

const rectangleStyle = computed(() => ({
  backgroundColor: props.element.background || 'red',
  borderColor: getBorderColor(),
  borderWidth: '2px',
  borderStyle: 'solid',
  borderRadius: '0px'
}))

const stateIndicatorClass = computed(() => {
  if (props.element.moveable) return 'bg-blue-500'
  if (props.element.clickable && props.element.startHidden) return 'bg-amber-500'
  if (props.element.clickable) return 'bg-emerald-500'
  return 'bg-gray-500'
})

const startDrag = (event) => {
  if (event.target.closest('button, input')) return
  
  isDragging.value = true
  isSelected.value = true
  
  dragStart.value = {
    x: event.clientX,
    y: event.clientY
  }
  
  elementStart.value = {
    x: props.element.x,
    y: props.element.y
  }
  
  document.addEventListener('mousemove', handleDrag)
  document.addEventListener('mouseup', stopDrag)
  event.preventDefault()
}

const handleDrag = (event) => {
  if (!isDragging.value) return
  
  const deltaX = event.clientX - dragStart.value.x
  const deltaY = event.clientY - dragStart.value.y
  
  const updatedElement = {
    ...props.element,
    x: elementStart.value.x + deltaX,
    y: elementStart.value.y + deltaY
  }
  
  emit('update', updatedElement)
}

const stopDrag = () => {
  isDragging.value = false
  document.removeEventListener('mousemove', handleDrag)
  document.removeEventListener('mouseup', stopDrag)
}

// Touch event handlers for mobile
const startTouchDrag = (event) => {
  if (event.target.closest('button, input')) return
  
  const touch = event.touches[0]
  if (!touch) return
  
  isDragging.value = true
  isSelected.value = true
  
  dragStart.value = {
    x: touch.clientX,
    y: touch.clientY
  }
  
  elementStart.value = {
    x: props.element.x,
    y: props.element.y
  }
  
  document.addEventListener('touchmove', handleTouchDrag, { passive: false })
  document.addEventListener('touchend', stopTouchDrag)
  event.preventDefault()
}

const handleTouchDrag = (event) => {
  if (!isDragging.value) return
  
  const touch = event.touches[0]
  if (!touch) return
  
  const deltaX = touch.clientX - dragStart.value.x
  const deltaY = touch.clientY - dragStart.value.y
  
  const updatedElement = {
    ...props.element,
    x: elementStart.value.x + deltaX,
    y: elementStart.value.y + deltaY
  }
  
  emit('update', updatedElement)
  event.preventDefault()
}

const stopTouchDrag = () => {
  isDragging.value = false
  document.removeEventListener('touchmove', handleTouchDrag)
  document.removeEventListener('touchend', stopTouchDrag)
}

const startResize = (event, handle) => {
  isResizing.value = true
  resizeHandle.value = handle
  
  dragStart.value = {
    x: event.clientX,
    y: event.clientY
  }
  
  elementStart.value = {
    x: props.element.x,
    y: props.element.y,
    width: props.element.width,
    height: props.element.height
  }
  
  document.addEventListener('mousemove', handleResize)
  document.addEventListener('mouseup', stopResize)
  event.preventDefault()
}

const handleResize = (event) => {
  if (!isResizing.value) return
  
  const deltaX = event.clientX - dragStart.value.x
  const deltaY = event.clientY - dragStart.value.y
  
  let updatedElement = { ...props.element }
  
  // Maintain aspect ratio for images if needed (optional enhancement)
  const maintainAspectRatio = props.element.type === 'image'
  const originalRatio = elementStart.value.width / elementStart.value.height
  
  switch (resizeHandle.value) {
    case 'se':
      updatedElement.width = Math.max(50, elementStart.value.width + deltaX)
      updatedElement.height = Math.max(30, elementStart.value.height + deltaY)
      if (maintainAspectRatio) {
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
          updatedElement.height = updatedElement.width / originalRatio
        } else {
          updatedElement.width = updatedElement.height * originalRatio
        }
      }
      break
    case 'e':
      updatedElement.width = Math.max(50, elementStart.value.width + deltaX)
      if (maintainAspectRatio) updatedElement.height = updatedElement.width / originalRatio
      break
    case 's':
      updatedElement.height = Math.max(30, elementStart.value.height + deltaY)
      if (maintainAspectRatio) updatedElement.width = updatedElement.height * originalRatio
      break
    case 'nw':
      const nwWidth = Math.max(50, elementStart.value.width - deltaX)
      const nwHeight = Math.max(30, elementStart.value.height - deltaY)
      if (maintainAspectRatio) {
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
          updatedElement.width = nwWidth
          updatedElement.height = nwWidth / originalRatio
        } else {
          updatedElement.height = nwHeight
          updatedElement.width = nwHeight * originalRatio
        }
        updatedElement.x = elementStart.value.x + (elementStart.value.width - updatedElement.width)
        updatedElement.y = elementStart.value.y + (elementStart.value.height - updatedElement.height)
      } else {
        updatedElement.x = elementStart.value.x + deltaX
        updatedElement.y = elementStart.value.y + deltaY
        updatedElement.width = nwWidth
        updatedElement.height = nwHeight
      }
      break
    case 'n':
      updatedElement.height = Math.max(30, elementStart.value.height - deltaY)
      if (maintainAspectRatio) {
        updatedElement.width = updatedElement.height * originalRatio
        updatedElement.x = elementStart.value.x + (elementStart.value.width - updatedElement.width) / 2
      }
      updatedElement.y = elementStart.value.y + (elementStart.value.height - updatedElement.height)
      break
    case 'ne':
      const neWidth = Math.max(50, elementStart.value.width + deltaX)
      const neHeight = Math.max(30, elementStart.value.height - deltaY)
      if (maintainAspectRatio) {
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
          updatedElement.width = neWidth
          updatedElement.height = neWidth / originalRatio
        } else {
          updatedElement.height = neHeight
          updatedElement.width = neHeight * originalRatio
        }
        updatedElement.y = elementStart.value.y + (elementStart.value.height - updatedElement.height)
      } else {
        updatedElement.y = elementStart.value.y + deltaY
        updatedElement.width = neWidth
        updatedElement.height = neHeight
      }
      break
    case 'sw':
      const swWidth = Math.max(50, elementStart.value.width - deltaX)
      const swHeight = Math.max(30, elementStart.value.height + deltaY)
      if (maintainAspectRatio) {
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
          updatedElement.width = swWidth
          updatedElement.height = swWidth / originalRatio
        } else {
          updatedElement.height = swHeight
          updatedElement.width = swHeight * originalRatio
        }
        updatedElement.x = elementStart.value.x + (elementStart.value.width - updatedElement.width)
      } else {
        updatedElement.x = elementStart.value.x + deltaX
        updatedElement.width = swWidth
        updatedElement.height = swHeight
      }
      break
    case 'w':
      updatedElement.width = Math.max(50, elementStart.value.width - deltaX)
      if (maintainAspectRatio) {
        updatedElement.height = updatedElement.width / originalRatio
        updatedElement.y = elementStart.value.y + (elementStart.value.height - updatedElement.height) / 2
      }
      updatedElement.x = elementStart.value.x + (elementStart.value.width - updatedElement.width)
      break
  }
  
  emit('update', updatedElement)
}

const stopResize = () => {
  isResizing.value = false
  document.removeEventListener('mousemove', handleResize)
  document.removeEventListener('mouseup', stopResize)
}

const updateContent = (event) => {
  emit('update', {
    ...props.element,
    content: event.target.textContent
  })
}

const handleKeydown = (event) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    event.target.blur()
  }
}

const toggleContextMenu = () => {
  showContextMenu.value = !showContextMenu.value
}

const setVisibility = (type) => {
  let updatedElement = { ...props.element }
  
  switch (type) {
    case 'hidden-clickable':
      updatedElement.startHidden = true
      updatedElement.clickable = true
      updatedElement.moveable = false
      updatedElement.hiddenOpacity = 0.05
      break
    case 'visible-clickable':
      updatedElement.startHidden = false
      updatedElement.clickable = true
      updatedElement.moveable = false
      break
    case 'moveable':
      updatedElement.startHidden = false
      updatedElement.clickable = false
      updatedElement.moveable = true
      break
    case 'none':
      updatedElement.startHidden = false
      updatedElement.clickable = false
      updatedElement.moveable = false
      break
  }
  
  emit('update', updatedElement)
  showContextMenu.value = false
}

const updateHiddenOpacity = (event) => {
  emit('update', {
    ...props.element,
    hiddenOpacity: parseInt(event.target.value) / 100
  })
}

const bringToFront = () => {
  emit('update', {
    ...props.element,
    zIndex: 9999
  })
  showContextMenu.value = false
}

const sendToBack = () => {
  emit('update', {
    ...props.element,
    zIndex: 1
  })
  showContextMenu.value = false
}

const duplicate = () => {
  const duplicated = {
    ...props.element,
    id: Date.now(),
    x: props.element.x + 20,
    y: props.element.y + 20
  }
  emit('duplicate', duplicated)
  showContextMenu.value = false
}

const deleteElement = () => {
  emit('delete', props.element.id)
  showContextMenu.value = false
}

const handleClick = (event) => {
  if (props.element.clickable) {
    event.stopPropagation()
    
    if (props.isPresentation) {
      // In presentation mode, emit click event to parent
      emit('click', props.element)
    } else {
      // In edit mode, you can add custom click behavior here
    }
  }
}

const updateColor = (property, color) => {
  emit('update', {
    ...props.element,
    [property]: color
  })
}

const getBorderColor = () => {
  if (props.element.border) {
    // Extract color from border string like "2px solid #000000"
    const match = props.element.border.match(/#\w+/)
    return match ? match[0] : '#000000'
  }
  return props.element.color || '#000000'
}

const updateBorderColor = (color) => {
  emit('update', {
    ...props.element,
    border: `2px solid ${color}`
  })
}

const handleClickOutside = (event) => {
  if (elementRef.value && !elementRef.value.contains(event.target)) {
    isSelected.value = false
    showContextMenu.value = false
  } else if (elementRef.value && elementRef.value.contains(event.target)) {
    isSelected.value = true
  }
}

onMounted(() => {
  if (elementRef.value) {
    document.addEventListener('click', handleClickOutside)
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('mousemove', handleDrag)
  document.removeEventListener('mouseup', stopDrag)
  document.removeEventListener('mousemove', handleResize)
  document.removeEventListener('mouseup', stopResize)
  document.removeEventListener('touchmove', handleTouchDrag)
  document.removeEventListener('touchend', stopTouchDrag)
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

/* Resize Controls - All 8 handles (from v2) */
.resize-controls {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none; /* Allow clicks to pass through */
}

.resize-handle {
  position: absolute;
  width: 12px;
  height: 12px;
  background: white;
  border: 2px solid #4a90e2;
  border-radius: 50%;
  pointer-events: auto; /* Enable clicking on handles */
  z-index: 10;
  transition: all 0.2s ease;
}

.resize-handle:hover {
  background: #4a90e2;
  transform: scale(1.3);
  box-shadow: 0 2px 8px rgba(74, 144, 226, 0.5);
}

/* Corner Handles */
.resize-nw {
  top: -6px;
  left: -6px;
  cursor: nwse-resize;
}

.resize-ne {
  top: -6px;
  right: -6px;
  cursor: nesw-resize;
}

.resize-sw {
  bottom: -6px;
  left: -6px;
  cursor: nesw-resize;
}

.resize-se {
  bottom: -6px;
  right: -6px;
  cursor: nwse-resize;
}

/* Edge Center Handles */
.resize-n {
  top: -6px;
  left: 50%;
  transform: translateX(-50%);
  cursor: ns-resize;
}

.resize-e {
  right: -6px;
  top: 50%;
  transform: translateY(-50%);
  cursor: ew-resize;
}

.resize-s {
  bottom: -6px;
  left: 50%;
  transform: translateX(-50%);
  cursor: ns-resize;
}

.resize-w {
  left: -6px;
  top: 50%;
  transform: translateY(-50%);
  cursor: ew-resize;
}
</style>
