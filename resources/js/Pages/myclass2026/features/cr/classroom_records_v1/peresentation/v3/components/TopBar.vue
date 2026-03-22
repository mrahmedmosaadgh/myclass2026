<template>
  <div class="fixed top-0 left-0 right-0 h-16 bg-gray-800 border-b border-gray-700 z-50 flex items-center justify-between px-6">
    <!-- Left: Logo/Title and Tools -->
    <div class="flex items-center space-x-6">
      <!-- Logo/Title -->
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
          </svg>
        </div>
        <span class="text-white font-semibold">Presentation Builder V3</span>
      </div>

      <!-- Editing Tools (only show in edit mode) -->
      <div v-if="mode === 'edit'" class="flex items-center space-x-2">
        <!-- Add Slide -->
        <div class="relative">
          <button
            @click="$emit('add-slide')"
            class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
          </button>
          <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 opacity-0 hover:opacity-100 transition-opacity duration-200 pointer-events-none">
            <div class="bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap">
              Add Slide
            </div>
          </div>
        </div>

        <!-- Elements Dropdown -->
        <div class="relative" ref="elementsDropdown">
          <div class="relative">
            <button
              @click="toggleElementsDropdown"
              class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
              </svg>
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 opacity-0 hover:opacity-100 transition-opacity duration-200 pointer-events-none">
              <div class="bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap">
                Add Elements
              </div>
            </div>
          </div>
          
          <!-- Dropdown Menu -->
          <div
            v-if="showElementsDropdown"
            class="absolute top-full left-0 mt-2 w-48 bg-gray-700 rounded-lg shadow-lg border border-gray-600 z-50"
          >
            <button
              @click="$emit('add-element', 'text'); showElementsDropdown = false"
              class="w-full px-4 py-3 text-left text-white hover:bg-gray-600 transition-colors duration-200 flex items-center space-x-3"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span>Add Text</span>
            </button>
            <button
              @click="$emit('add-element', 'heading'); showElementsDropdown = false"
              class="w-full px-4 py-3 text-left text-white hover:bg-gray-600 transition-colors duration-200 flex items-center space-x-3"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <span>Add Heading</span>
            </button>
            <button
              @click="$emit('add-element', 'subheading'); showElementsDropdown = false"
              class="w-full px-4 py-3 text-left text-white hover:bg-gray-600 transition-colors duration-200 flex items-center space-x-3"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-7 1h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v9a2 2 0 002 2z"></path>
              </svg>
              <span>Add Subheading</span>
            </button>
            <button
              @click="$emit('add-element', 'rectangle'); showElementsDropdown = false"
              class="w-full px-4 py-3 text-left text-white hover:bg-gray-600 transition-colors duration-200 flex items-center space-x-3"
            >
              <svg class="w-5 h-5" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="4" y="6" width="16" height="12" fill="currentColor" stroke="white" stroke-width="2"></rect>
              </svg>
              <span>Add Rectangle</span>
            </button>
            <button
              @click="$emit('add-image'); showElementsDropdown = false"
              class="w-full px-4 py-3 text-left text-white hover:bg-gray-600 transition-colors duration-200 flex items-center space-x-3"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <span>Add Image</span>
            </button>
          </div>
        </div>

        <!-- Divider -->
        <div class="w-px h-6 bg-gray-600"></div>

        <!-- Drawing Tools -->
        <div class="flex items-center space-x-2">
          <button
            @click="$emit('toggle-drawing')"
            :class="[
              'p-2 transition-colors rounded flex items-center justify-center',
              isDrawingMode 
                ? 'bg-blue-600 text-white' 
                : 'text-gray-300 hover:text-white hover:bg-gray-700'
            ]"
            title="Pen Tool"
          >
            <!-- Pen Icon with Color Indicator -->
            <div class="relative">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 0L21 12l-4.586 4.586a2.5 2.5 0 11-3.536 0L6.5 6.5a2.5 2.5 0 00-3.536 0L3 12l4.586 4.586a2.5 2.5 0 003.536 0z"></path>
              </svg>
              <!-- Color Dot Indicator -->
              <div 
                v-if="isDrawingMode"
                class="absolute -bottom-1 -right-1 w-3 h-3 rounded-full border-2 border-white"
                :style="{ backgroundColor: penColor }"
              ></div>
            </div>
          </button>

          <!-- Eraser Tool -->
          <button
            @click="$emit('toggle-eraser')"
            :class="[
              'p-2 transition-colors rounded flex items-center justify-center',
              isEraserMode 
                ? 'bg-red-600 text-white' 
                : 'text-gray-300 hover:text-white hover:bg-gray-700'
            ]"
            title="Eraser Tool"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>

          <!-- Clear All Drawings -->
          <button
            @click="$emit('clear-drawing')"
            class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
            title="Clear All Drawings"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </button>
        </div>

        <!-- Divider -->
        <div class="w-px h-6 bg-gray-600"></div>

        <!-- Color Bar (visible when drawing mode is ON) -->
        <div
          v-if="isDrawingMode"
          class="flex items-center space-x-2 px-2 py-1 bg-white rounded-lg border border-gray-300"
        >
          <!-- Color Picker -->
          <input
            type="color"
            :value="penColor"
            @input="$emit('pen-color-change', $event.target.value)"
            class="w-8 h-8 border-2 border-gray-300 rounded cursor-pointer"
            title="Custom Color"
          />
          
          <!-- Quick Colors -->
          <button
            v-for="color in quickColors"
            :key="color"
            @click="$emit('pen-color-change', color)"
            :class="[
              'w-8 h-8 rounded border-2 transition-all',
              penColor === color ? 'border-gray-800 scale-110' : 'border-gray-300 hover:border-gray-500'
            ]"
            :style="{ backgroundColor: color }"
            :title="color"
          ></button>
          
          <!-- Divider -->
          <div class="w-px h-6 bg-gray-300 mx-2"></div>
          
          <!-- Pen Size -->
          <div class="flex items-center space-x-2">
            <label class="text-xs text-gray-600">Size</label>
            <input
              type="range"
              min="1"
              max="20"
              :value="penSize"
              @input="$emit('pen-size-change', parseInt($event.target.value))"
              class="w-20 h-6"
            />
            <span class="text-xs text-gray-700 w-8 text-center">{{ penSize }}px</span>
          </div>
        </div>

        <!-- Paste -->
        <button
          @click="$emit('paste')"
          class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
          title="Paste from Clipboard"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Right: Actions -->
    <div class="flex items-center space-x-3">
      <!-- Present Button (only show in edit mode) -->
      <div v-if="mode === 'edit'" class="relative group">
        <button
          @click="$emit('mode-change', 'present')"
          class="w-12 h-12 rounded-full bg-blue-600 hover:bg-blue-700 flex items-center justify-center transition-all duration-200 transform hover:scale-105"
        >
          <svg class="w-6 h-6 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z"/>
          </svg>
        </button>
        <!-- Tooltip -->
        <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
          <div class="bg-black text-white text-xs rounded py-1 px-2 whitespace-nowrap">
            Present
          </div>
        </div>
      </div>

      <!-- Slide Size Settings (only show in edit mode) -->
      <div v-if="mode === 'edit'" class="relative">
        <button
          @click="toggleSettings"
          class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
          title="Slide Settings"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </button>
        
        <!-- Settings Dropdown -->
        <div
          v-if="showSettings"
          class="absolute top-full mt-2 right-0 bg-white rounded-lg shadow-lg p-4 w-64 z-50"
        >
          <h3 class="text-sm font-semibold text-gray-900 mb-3">Slide Size</h3>
          
          <div class="space-y-2">
            <label class="flex items-center p-2 rounded hover:bg-gray-100 cursor-pointer">
              <input
                type="radio"
                :model-value="selectedSize"
                value="widescreen"
                class="mr-3"
                @change="$emit('slide-size-change', { selectedSize: 'widescreen' })"
              />
              <div>
                <div class="font-medium text-sm">Widescreen 16:9</div>
                <div class="text-xs text-gray-500">1920 × 1080 px (10 × 5.625 in)</div>
              </div>
            </label>
            
            <label class="flex items-center p-2 rounded hover:bg-gray-100 cursor-pointer">
              <input
                type="radio"
                :model-value="selectedSize"
                value="standard"
                class="mr-3"
                @change="$emit('slide-size-change', { selectedSize: 'standard' })"
              />
              <div>
                <div class="font-medium text-sm">Standard 4:3</div>
                <div class="text-xs text-gray-500">1024 × 768 px (10 × 7.5 in)</div>
              </div>
            </label>
            
            <label class="flex items-center p-2 rounded hover:bg-gray-100 cursor-pointer">
              <input
                type="radio"
                :model-value="selectedSize"
                value="custom"
                class="mr-3"
                @change="$emit('slide-size-change', { selectedSize: 'custom' })"
              />
              <div>
                <div class="font-medium text-sm">Custom</div>
                <div class="text-xs text-gray-500">Set custom dimensions</div>
              </div>
            </label>
          </div>
          
          <!-- Custom Dimensions -->
          <div v-if="selectedSize === 'custom'" class="mt-3 pt-3 border-t border-gray-200">
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="text-xs text-gray-600">Width (px)</label>
                <input
                  type="color"
                  :value="penColor"
                  @input="$emit('pen-color-change', $event.target.value)"
                  class="w-12 h-8 border border-gray-300 rounded cursor-pointer"
                />
                <input
                  :value="customWidth"
                  type="number"
                  class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                  placeholder="1920"
                  @input="$emit('slide-size-change', { customWidth: parseInt($event.target.value) || 0 })"
                />
              </div>
              <div>
                <label class="text-xs text-gray-600">Height (px)</label>
                <input
                  :value="customHeight"
                  type="number"
                  class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                  placeholder="1080"
                  @input="$emit('slide-size-change', { customHeight: parseInt($event.target.value) || 0 })"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Export -->
      <button
        @click="$emit('export')"
        class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded-md transition-colors"
        title="Export"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
      </button>

      <!-- Import -->
      <label class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded-md transition-colors cursor-pointer" title="Import">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
        </svg>
        <input
          type="file"
          accept=".json"
          @change="handleImport"
          class="hidden"
        />
      </label>

      <!-- Delete Slide -->
      <button
        @click="$emit('delete-slide', currentSlideIndex)"
        :disabled="totalSlides <= 1"
        :class="[
          'p-2 rounded-md transition-colors',
          totalSlides <= 1
            ? 'text-gray-600 cursor-not-allowed'
            : 'text-gray-300 hover:text-red-400 hover:bg-gray-700'
        ]"
        title="Delete Slide"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  mode: {
    type: String,
    required: true
  },
  currentSlideIndex: {
    type: Number,
    required: true
  },
  totalSlides: {
    type: Number,
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
  isDrawingMode: {
    type: Boolean,
    required: true
  },
  isEraserMode: {
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
  }
})

const emit = defineEmits([
  'mode-change',
  'export',
  'import',
  'delete-slide',
  'height-change',
  'slide-size-change',
  'add-slide',
  'add-element',
  'add-image',
  'paste',
  'toggle-drawing',
  'toggle-eraser',
  'pen-size-change',
  'pen-color-change',
  'clear-drawing',
  'undo-drawing'
])

const showSettings = ref(false)

// Dropdown state
const showElementsDropdown = ref(false)
const elementsDropdown = ref(null)

// Quick color palette for drawing (5 main colors)
const quickColors = [
  '#000000', // Black
  '#FF0000', // Red  
  '#0000FF', // Blue
  '#00FF00', // Green
  '#FFFF00'  // Yellow
]

const toggleSettings = () => {
  showSettings.value = !showSettings.value
}

// Dropdown methods
const toggleElementsDropdown = () => {
  showElementsDropdown.value = !showElementsDropdown.value
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (elementsDropdown.value && !elementsDropdown.value.contains(event.target)) {
    showElementsDropdown.value = false
  }
}

const handleImport = (event) => {
  const file = event.target.files[0]
  if (file) {
    emit('import', file)
  }
  // Reset input
  event.target.value = ''
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
