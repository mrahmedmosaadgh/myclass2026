<template>
  <div class="flex-1 bg-gray-900 p-6 overflow-auto">
    <!-- Element Toolbar -->
    <div class="mb-4 flex justify-center">
      <div class="bg-gray-800 rounded-lg p-2 flex items-center space-x-2">
        <!-- Text -->
        <button
          @click="addElement('text')"
          class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
          title="Add Text"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </button>

        <!-- Heading -->
        <button
          @click="addElement('heading')"
          class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
          title="Add Heading"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </button>

        <!-- Subheading -->
        <button
          @click="addElement('subheading')"
          class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
          title="Add Subheading"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-7 1h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v9a2 2 0 002 2z"></path>
          </svg>
        </button>

        <!-- Image -->
        <button
          @click="triggerImageUpload"
          class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
          title="Add Image"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </button>

        <!-- Rectangle -->
        <button
          @click="addElement('rectangle')"
          class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
          title="Add Rectangle"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="4" y="6" width="16" height="12" stroke="currentColor" stroke-width="2"></rect>
          </svg>
        </button>

        <!-- Paste -->
        <button
          @click="triggerPaste"
          class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
          title="Paste from Clipboard"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Canvas Container -->
    <div class="flex justify-center">
      <div
        @paste="handlePaste"
        @dragover.prevent
        @drop="handleDrop"
        class="relative bg-white shadow-2xl"
        :style="{ width: '794px', height: slideHeight + 'px' }"
      >
        <!-- Elements -->
        <ElementNode
          v-for="element in currentSlide.elements"
          :key="element.id"
          :element="element"
          @update="updateElement"
          @delete="deleteElement"
        />

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
import { ref, onMounted, onUnmounted } from 'vue'
import ElementNode from './ElementNode.vue'

const props = defineProps({
  currentSlide: {
    type: Object,
    required: true
  },
  slideHeight: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['slide-update'])

const imageInput = ref(null)
const showPasteZone = ref(false)

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
    backgroundColor: type === 'rectangle' ? '#3B82F6' : 'transparent',
    borderColor: type === 'rectangle' ? '#1E40AF' : 'transparent',
    borderWidth: type === 'rectangle' ? 2 : 0,
    borderRadius: type === 'rectangle' ? 8 : 0,
    opacity: 1,
    startHidden: false,
    clickable: false,
    moveable: false,
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

const triggerPaste = () => {
  // Focus the canvas and show a hint to use Ctrl+V
  const canvas = document.querySelector('.bg-white.shadow-2xl') || document.body
  canvas.focus()
  
  // Show a temporary tooltip or message
  const message = document.createElement('div')
  message.textContent = 'Press Ctrl+V to paste'
  message.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-indigo-500 text-white px-4 py-2 rounded-lg shadow-lg z-50'
  document.body.appendChild(message)
  
  setTimeout(() => {
    document.body.removeChild(message)
  }, 2000)
  
  // Also try the clipboard API as fallback
  pasteFromClipboard()
}

const pasteFromClipboard = async () => {
  try {
    // Request clipboard permission if needed
    if (navigator.permissions && navigator.permissions.query) {
      const permission = await navigator.permissions.query({ name: 'clipboard-read' })
      if (permission.state === 'denied') {
        console.warn('Clipboard access denied')
        return
      }
    }

    const clipboardItems = await navigator.clipboard.read()
    for (const clipboardItem of clipboardItems) {
      for (const type of clipboardItem.types) {
        if (type.startsWith('image/')) {
          const blob = await clipboardItem.getType(type)
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
          reader.readAsDataURL(blob)
          return
        } else if (type === 'text/plain') {
          const text = await clipboardItem.getType(type)
          const reader = new FileReader()
          reader.onload = (e) => {
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
            
            emit('slide-update', updatedSlide)
          }
          reader.readAsText(text)
          return
        }
      }
    }
  } catch (error) {
    console.error('Failed to read clipboard:', error)
    // Fallback: Try to read text clipboard
    try {
      const text = await navigator.clipboard.readText()
      if (text) {
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
        
        emit('slide-update', updatedSlide)
      }
    } catch (textError) {
      console.error('Failed to read text clipboard:', textError)
    }
  }
}

const handlePaste = (event) => {
  event.preventDefault()
  pasteFromClipboard()
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

onMounted(() => {
  document.addEventListener('dragover', handleDragOver)
})

onUnmounted(() => {
  document.removeEventListener('dragover', handleDragOver)
  if (pasteTimeout) clearTimeout(pasteTimeout)
})
</script>
