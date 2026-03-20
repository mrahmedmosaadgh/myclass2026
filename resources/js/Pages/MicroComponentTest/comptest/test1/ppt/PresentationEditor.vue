<template>
  <div class="bg-grey-2">
    <!-- Modern Header Toolbar -->
    <q-header elevated class="bg-gradient-primary text-white">
      <q-toolbar class="py-2">
        <q-toolbar-title>
          <q-icon name="slideshow" size="md" class="q-mr-sm" />
          <span class="text-h6 text-weight-bold">Presentation Editor</span>
        </q-toolbar-title>

        <q-space />

        <!-- Slide Operations -->
        <q-btn-group flat class="q-mr-md">
          <q-btn
            flat
            color="white"
            icon="add"
            label="Add Slide"
            @click="addNewSlide"
          >
            <q-tooltip>Add new slide</q-tooltip>
          </q-btn>
          
          <q-btn
            flat
            color="white"
            icon="content_copy"
            label="Duplicate"
            @click="duplicateSlide(presentation.currentSlideIndex)"
            :disable="!hasSlides"
          >
            <q-tooltip>Duplicate slide</q-tooltip>
          </q-btn>
          
          <q-btn
            flat
            color="white"
            icon="delete"
            label="Delete"
            @click="deleteSlide(presentation.currentSlideIndex)"
            :disable="presentation.slides.length <= 1"
          >
            <q-tooltip>Delete slide</q-tooltip>
          </q-btn>
        </q-btn-group>

        <q-separator vertical inset class="q-mx-md" />

        <!-- Background Color Picker -->
        <div class="row items-center q-mr-md">
          <q-icon name="palette" size="sm" class="q-mr-xs" />
          <q-color
            v-model="currentBackgroundColor"
            v-model:popup="showColorPicker"
            default-view="spectrum"
            no-header
            no-footer
            flat
            bordered
            class="transparent-bg"
            @update:model-value="updateSlideBackground"
          >
            <template v-slot:default="{ scope }">
              <q-popup-edit v-model="currentBackgroundColor" v-slot="scope"
                ><q-input
                  v-model="scope.value"
                  dense
                  autofocus
                  counter
                  @keyup.enter="scope.set"
              /></q-popup-edit>
            </template>
          </q-color>
        </div>

        <q-separator vertical inset class="q-mx-md" />

        <!-- Export/Import -->
        <q-btn-group flat>
          <q-btn
            flat
            color="white"
            icon="download"
            label="Export"
            @click="exportPresentation"
          >
            <q-tooltip>Export as JSON</q-tooltip>
          </q-btn>
          
          <q-btn
            flat
            color="white"
            icon="upload"
            label="Import"
            @click="triggerImport"
          >
            <q-tooltip>Import from JSON</q-tooltip>
          </q-btn>
          
          <input
            ref="fileInput"
            type="file"
            accept=".json"
            @change="importPresentation"
            style="display: none"
          />
        </q-btn-group>
      </q-toolbar>
    </q-header>

    <div>
      <div class="q-pa-md">
        <div class="row q-col-gutter-md" style="height: calc(100vh - 80px);">
          <!-- Left Sidebar - Slide Thumbnails -->
          <q-drawer
            v-model="showSlidePanel"
            show-if-above
            :width="280"
            :breakpoint="500"
            bordered
            class="bg-white"
          >
            <div class="column full-height">
              <q-card flat class="q-pa-md bg-grey-1">
                <div class="row items-center justify-between">
                  <div class="text-subtitle1 text-weight-bold">
                    <q-icon name="view_carousel" size="sm" class="q-mr-xs" />
                    Slides ({{ presentation.slides.length }})
                  </div>
                  <q-badge color="primary" floating>{{ presentation.currentSlideIndex + 1 }}</q-badge>
                </div>
              </q-card>

              <q-scroll-area class="col" style="flex: 1;">
                <q-list separator>
                  <q-item
                    v-for="(slide, index) in presentation.slides"
                    :key="slide.id"
                    clickable
                    :active="index === presentation.currentSlideIndex"
                    active-class="bg-blue-1"
                    @click="selectSlide(index)"
                    class="slide-thumbnail-item"
                  >
                    <q-item-section avatar>
                      <q-avatar square rounded color="grey-3" class="slide-preview-avatar">
                        <div :style="getSlidePreviewStyle(slide)">
                          <span class="text-caption">{{ getSlideContentPreview(slide) }}</span>
                        </div>
                      </q-avatar>
                    </q-item-section>
                    
                    <q-item-section>
                      <q-item-label class="text-weight-medium">
                        Slide {{ index + 1 }}
                      </q-item-label>
                      <q-item-label caption>
                        {{ slide.elements.length }} element{{ slide.elements.length !== 1 ? 's' : '' }}
                      </q-item-label>
                    </q-item-section>

                    <q-item-section side>
                      <q-btn
                        flat
                        dense
                        round
                        icon="more_vert"
                        @click.stop="openSlideMenu(index)"
                      >
                        <q-menu>
                          <q-list dense>
                            <q-item clickable v-close-popup @click="duplicateSlide(index)">
                              <q-item-section avatar><q-icon name="content_copy" /></q-item-section>
                              <q-item-section>Duplicate</q-item-section>
                            </q-item>
                            <q-item
                              clickable
                              v-close-popup
                              @click="deleteSlide(index)"
                              :disable="presentation.slides.length <= 1"
                            >
                              <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                              <q-item-section class="text-negative">Delete</q-item-section>
                            </q-item>
                          </q-list>
                        </q-menu>
                      </q-btn>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-scroll-area>
            </div>
          </q-drawer>

          <!-- Main Content Area -->
          <div class="col column">
            <!-- Element Toolbar -->
            <q-card flat bordered class="q-mb-md">
              <q-card-section class="q-py-sm">
                <div class="row items-center q-gutter-sm">
                  <span class="text-subtitle2 text-weight-bold q-mr-sm">Add Elements:</span>
                  
                  <q-btn
                    unelevated
                    color="primary"
                    icon="title"
                    label="Text"
                    size="sm"
                    @click="addElement('text')"
                  />
                  
                  <q-btn
                    unelevated
                    color="secondary"
                    icon="image"
                    label="Image"
                    size="sm"
                    @click="addElement('image')"
                  />
                  
                  <q-btn
                    unelevated
                    color="accent"
                    icon="crop_square"
                    label="Rectangle"
                    size="sm"
                    @click="addElement('rectangle')"
                  />
                  
                  <q-btn
                    unelevated
                    color="positive"
                    icon="circle"
                    label="Circle"
                    size="sm"
                    @click="addElement('circle')"
                  />
                  
                  <q-btn
                    unelevated
                    color="info"
                    icon="star"
                    label="Star"
                    size="sm"
                    @click="addElement('star')"
                  />
                  
                  <q-btn
                    unelevated
                    color="warning"
                    icon="link"
                    label="Arrow"
                    size="sm"
                    @click="addElement('arrow')"
                  />
                </div>
              </q-card-section>
            </q-card>

            <!-- Canvas Area -->
            <q-card flat bordered class="col" style="flex: 1; overflow: hidden;">
              <q-card-section class="full-height relative-position bg-grey-3">
                <div v-if="!hasSlides" class="absolute-center text-center text-grey-7">
                  <q-icon name="drafts" size="xl" class="q-mb-md" />
                  <div class="text-h6 q-mb-sm">No slides yet</div>
                  <p>Click "Add Slide" to get started</p>
                  <q-btn
                    unelevated
                    color="primary"
                    icon="add"
                    label="Add Your First Slide"
                    @click="addNewSlide"
                    class="q-mt-md"
                  />
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
                  class="slide-canvas-wrapper"
                />
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import SlideThumbnail from './components/SlideThumbnail.vue';
import SlideCanvas from './components/SlideCanvas.vue';
import ElementToolbar from './components/ElementToolbar.vue';
import SlideToolbar from './components/SlideToolbar.vue';

const $q = useQuasar();

// UI State
const showSlidePanel = ref(true);
const showColorPicker = ref(false);
const currentBackgroundColor = ref('#ffffff');
const fileInput = ref(null);

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
  
  $q.notify({
    type: 'positive',
    message: 'Slide added successfully',
    icon: 'check_circle',
    position: 'top'
  });
};

const deleteSlide = (slideIndex) => {
  if (presentation.slides.length <= 1) {
    $q.notify({
      type: 'warning',
      message: 'Cannot delete the last slide',
      icon: 'warning',
      position: 'top'
    });
    return;
  }
  
  $q.dialog({
    title: 'Delete Slide',
    message: 'Are you sure you want to delete this slide?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    presentation.slides.splice(slideIndex, 1);
    
    if (presentation.currentSlideIndex >= presentation.slides.length) {
      presentation.currentSlideIndex = presentation.slides.length - 1;
    }
    
    if (presentation.currentSlideIndex < 0) {
      presentation.currentSlideIndex = 0;
    }
    
    presentation.selectedElementId = null;
    
    $q.notify({
      type: 'info',
      message: 'Slide deleted',
      icon: 'delete',
      position: 'top'
    });
  });
};

const selectSlide = (slideIndex) => {
  presentation.currentSlideIndex = slideIndex;
  presentation.selectedElementId = null;
  
  // Update background color picker
  if (currentSlide.value) {
    currentBackgroundColor.value = currentSlide.value.backgroundColor || '#ffffff';
  }
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
  
  $q.notify({
    type: 'info',
    message: 'Slide duplicated',
    icon: 'content_copy',
    position: 'top'
  });
};

const updateSlideBackground = (color) => {
  if (currentSlide.value) {
    currentSlide.value.backgroundColor = color;
    $q.notify({
      type: 'info',
      message: 'Background updated',
      icon: 'palette',
      position: 'top',
      timeout: 1000
    });
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

const getSlidePreviewStyle = (slide) => {
  return {
    width: '100%',
    height: '100%',
    backgroundColor: slide.backgroundColor || '#ffffff',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    padding: '4px'
  };
};

const getSlideContentPreview = (slide) => {
  if (slide.elements.length === 0) return 'Blank';
  
  const textElement = slide.elements.find(el => el.type === 'text');
  if (textElement && textElement.content) {
    return textElement.content.substring(0, 20) + (textElement.content.length > 20 ? '...' : '');
  }
  
  return `${slide.elements.length} element${slide.elements.length !== 1 ? 's' : ''}`;
};

const openSlideMenu = (index) => {
  // Menu handled by Quasar
};

const exportPresentation = () => {
  const data = JSON.stringify(presentation.slides, null, 2);
  const blob = new Blob([data], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `presentation-${Date.now()}.json`;
  a.click();
  URL.revokeObjectURL(url);
  
  $q.notify({
    type: 'positive',
    message: 'Presentation exported successfully',
    icon: 'download',
    position: 'top'
  });
};

const triggerImport = () => {
  fileInput.value?.click();
};

const importPresentation = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const reader = new FileReader();
  reader.onload = (e) => {
    try {
      const imported = JSON.parse(e.target.result);
      
      if (!Array.isArray(imported)) {
        throw new Error('Invalid format: must be an array of slides');
      }
      
      presentation.slides = imported;
      presentation.currentSlideIndex = 0;
      presentation.selectedElementId = null;
      
      $q.notify({
        type: 'positive',
        message: `Imported ${imported.length} slide(s)`,
        icon: 'upload',
        position: 'top'
      });
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: `Import failed: ${error.message}`,
        icon: 'error',
        position: 'top',
        timeout: 5000
      });
    }
  };
  
  reader.readAsText(file);
  event.target.value = '';
};
</script>

<style scoped lang="scss">
.presentation-editor {
  font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.slide-thumbnail-item {
  transition: all 0.3s ease;
  
  &:hover {
    background-color: rgba(59, 130, 246, 0.08);
  }
}

.slide-preview-avatar {
  min-width: 60px;
  max-width: 60px;
  min-height: 45px;
  max-height: 45px;
  
  & > div {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    
    span {
      font-size: 8px;
      text-align: center;
      line-height: 1.2;
    }
  }
}

.transparent-bg {
  background: transparent !important;
  box-shadow: none !important;
}

.slide-canvas-wrapper {
  width: 100%;
  height: 100%;
  overflow: auto;
}

// Scrollbar styling
.q-scroll-area::-webkit-scrollbar {
  width: 8px;
}

.q-scroll-area::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.q-scroll-area::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.q-scroll-area::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>