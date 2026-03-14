<template>
  <div class="presentation-editor flex h-screen bg-gray-100">
    <!-- Left Sidebar - Slide Thumbnails -->
    <div class="sidebar w-64 bg-white border-r border-gray-200 flex flex-col">
      <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Slides</h2>
        <button 
          @click="addNewSlide"
          class="mt-2 w-full py-2 px-3 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm transition-colors"
        >
          + Add Slide
        </button>
      </div>
      
      <div class="flex-1 overflow-y-auto p-2">
        <SlideThumbnail
          v-for="(slide, index) in presentation.slides"
          :key="slide.id"
          :slide="slide"
          :index="index"
          :is-active="index === presentation.currentSlideIndex"
          @select="selectSlide(index)"
          @duplicate="duplicateSlide(index)"
          @delete="deleteSlide(index)"
        />
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content flex-1 flex flex-col">
      <!-- Top Toolbar -->
      <div class="toolbar bg-white border-b border-gray-200 p-3">
        <SlideToolbar
          :has-slides="hasSlides"
          :current-slide-index="presentation.currentSlideIndex"
          :total-slides="presentation.slides.length"
          @add-slide="addNewSlide"
          @delete-slide="deleteSlide(presentation.currentSlideIndex)"
          @duplicate-slide="duplicateSlide(presentation.currentSlideIndex)"
          @update-background="updateSlideBackground"
        />
      </div>

      <!-- Canvas Area -->
      <div class="flex-1 flex">
        <!-- Element Toolbar -->
        <div class="element-toolbar w-16 bg-white border-r border-gray-200 p-2 flex flex-col items-center space-y-3">
          <ElementToolbar @add-element="addElement" />
        </div>

        <!-- Slide Canvas -->
        <div class="canvas-container flex-1 bg-gray-200 p-8 overflow-auto">
          <div v-if="!hasSlides" class="flex items-center justify-center h-full text-gray-500">
            <div class="text-center">
              <div class="text-2xl mb-2">📋</div>
              <p>No slides yet. Click "Add Slide" to get started.</p>
            </div>
          </div>
          
          <SlideCanvas
            v-else
            :slide="currentSlide"
            :selected-element-id="presentation.selectedElementId"
            @select-element="selectElement"
            @update-element="updateElement"
            @delete-element="deleteElement"
            @move-to-front="moveElementToFront"
            @move-to-back="moveElementToBack"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import SlideThumbnail from './components/SlideThumbnail.vue';
import SlideCanvas from './components/SlideCanvas.vue';
import ElementToolbar from './components/ElementToolbar.vue';
import SlideToolbar from './components/SlideToolbar.vue';

// Presentation state
const presentation = reactive({
  slides: [],
  currentSlideIndex: 0,
  selectedElementId: null
});

// Initialize with one blank slide
onMounted(() => {
  addNewSlide();
});

// Computed properties
const currentSlide = computed(() => {
  return presentation.slides[presentation.currentSlideIndex] || null;
});

const hasSlides = computed(() => presentation.slides.length > 0);

// Methods
const addNewSlide = () => {
  const newSlide = {
    id: Date.now(),
    elements: [],
    backgroundColor: '#ffffff'
  };
  
  presentation.slides.push(newSlide);
  presentation.currentSlideIndex = presentation.slides.length - 1;
  presentation.selectedElementId = null;
};

const deleteSlide = (slideIndex) => {
  if (presentation.slides.length <= 1) return;
  
  presentation.slides.splice(slideIndex, 1);
  
  if (presentation.currentSlideIndex >= presentation.slides.length) {
    presentation.currentSlideIndex = presentation.slides.length - 1;
  }
  
  if (presentation.currentSlideIndex < 0) {
    presentation.currentSlideIndex = 0;
  }
  
  presentation.selectedElementId = null;
};

const selectSlide = (slideIndex) => {
  presentation.currentSlideIndex = slideIndex;
  presentation.selectedElementId = null;
};

const duplicateSlide = (slideIndex) => {
  const originalSlide = presentation.slides[slideIndex];
  const duplicatedSlide = {
    ...originalSlide,
    id: Date.now(),
    elements: originalSlide.elements.map(element => ({
      ...element,
      id: `${Date.now()}_${Math.random()}`
    }))
  };
  
  presentation.slides.splice(slideIndex + 1, 0, duplicatedSlide);
};

const updateSlideBackground = (color) => {
  if (currentSlide.value) {
    currentSlide.value.backgroundColor = color;
  }
};

const addElement = (elementType) => {
  if (!currentSlide.value) return;
  
  const baseElement = {
    id: `${Date.now()}_${Math.random()}`,
    x: 100,
    y: 100,
    width: elementType === 'text' ? 200 : 100,
    height: elementType === 'text' ? 50 : 100,
    rotation: 0
  };
  
  let element;
  
  switch (elementType) {
    case 'text':
      element = {
        ...baseElement,
        type: 'text',
        content: 'Double click to edit text',
        fontSize: 16,
        fontFamily: 'Arial',
        color: '#000000',
        fontWeight: 'normal',
        textAlign: 'left'
      };
      break;
      
    case 'image':
      element = {
        ...baseElement,
        type: 'image',
        src: '',
        alt: 'Image',
        fit: 'contain'
      };
      break;
      
    case 'rectangle':
      element = {
        ...baseElement,
        type: 'shape',
        shapeType: 'rectangle',
        fillColor: '#3498db',
        strokeColor: '#2980b9',
        strokeWidth: 2
      };
      break;
      
    case 'circle':
      element = {
        ...baseElement,
        type: 'shape',
        shapeType: 'circle',
        fillColor: '#e74c3c',
        strokeColor: '#c0392b',
        strokeWidth: 2
      };
      break;
      
    case 'arrow':
      element = {
        ...baseElement,
        type: 'shape',
        shapeType: 'arrow',
        fillColor: '#f39c12',
        strokeColor: '#d35400',
        strokeWidth: 2
      };
      break;
  }
  
  if (element) {
    currentSlide.value.elements.push(element);
    presentation.selectedElementId = element.id;
  }
};

const updateElement = (elementId, updates) => {
  if (!currentSlide.value) return;
  
  const element = currentSlide.value.elements.find(el => el.id === elementId);
  if (element) {
    Object.assign(element, updates);
  }
};

const deleteElement = (elementId) => {
  if (!currentSlide.value) return;
  
  const index = currentSlide.value.elements.findIndex(el => el.id === elementId);
  if (index !== -1) {
    currentSlide.value.elements.splice(index, 1);
    if (presentation.selectedElementId === elementId) {
      presentation.selectedElementId = null;
    }
  }
};

const selectElement = (elementId) => {
  presentation.selectedElementId = elementId;
};

const moveElementToFront = (elementId) => {
  if (!currentSlide.value) return;
  
  const elements = currentSlide.value.elements;
  const index = elements.findIndex(el => el.id === elementId);
  if (index !== -1 && index < elements.length - 1) {
    const element = elements.splice(index, 1)[0];
    elements.push(element);
  }
};

const moveElementToBack = (elementId) => {
  if (!currentSlide.value) return;
  
  const elements = currentSlide.value.elements;
  const index = elements.findIndex(el => el.id === elementId);
  if (index !== -1 && index > 0) {
    const element = elements.splice(index, 1)[0];
    elements.unshift(element);
  }
};
</script>

<style scoped>
.presentation-editor {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.sidebar {
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.toolbar {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>