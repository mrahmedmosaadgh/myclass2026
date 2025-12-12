<template>
  <div>
    <div class="presentation-header">
      <div class="title-container">
        <LucideIcon name="file-presentation" class="text-indigo-500" size="20" />
        <input
          v-model="presentationTitle"
          class="title-input"
          placeholder="Untitled Presentation"
          @change="handleTitleChange"
        >
      </div>
      <div class="header-controls">
        <Dropdown8 align="right" width="48" :auto-hide="true">
          <template #trigger>
            <button class="btn-primary">
              <LucideIcon v-if="isSaving" name="loader-2" size="18" class="animate-spin" />
              <LucideIcon v-else name="save" size="18" />
              <span>Save</span>
              <LucideIcon name="chevron-down" size="16" class="ml-1 opacity-70" />
            </button>
          </template>

          <template #content>





            <button
         @click="$emit('quickSave')"
              class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 flex items-center gap-2"
            >
              <LucideIcon name="save" size="18" class="text-indigo-600" />
              <span>Quick Save</span>
            </button>
            <button
              @click="$emit('saveAs')"
              class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 flex items-center gap-2"
            >
              <LucideIcon name="file-down" size="18" class="text-indigo-600" />
              <span>Save As...</span>
            </button>



              <button
              @click="$emit('load_all_slides_to_file')"
              class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 flex items-center gap-2"
            >
              <LucideIcon name="file-down" size="18" class="text-indigo-600" />
              <span>load slides ...</span>
            </button>
          </template>
        </Dropdown8>

        <button   @click="$emit('startPresentation')"  class="btn-present">
          <LucideIcon name="play" size="18" />
          <span>Present</span>
        </button>
      </div>
    </div>

    <!-- Controls Panel -->
    <div class="controls-panel" v-if="!readonly">
      <input type="file" id="bgInput" accept="image/*" style="display: none" @change="handleBackgroundImage" ref="bgInput">
      <button @click="$refs.bgInput.click()" class="control-btn">Choose Background</button>
      <button @click="toggleAllElements" class="control-btn">Show/Hide All</button>

      <!-- Element type selector -->
      <div class="element-type-selector">
        <button
          @click="currentElementType = 'text'; addNewElement(20, 20, 'text')"
          :class="{ active: currentElementType === 'text' }"
          class="element-btn"
          title="Add Text Element"
        >
          <span class="icon-text">T</span>
          Text
        </button>
        <button
          @click="currentElementType = 'list'; addNewElement(20, 20, 'list')"
          :class="{ active: currentElementType === 'list' }"
          class="element-btn"
          title="Add List Element"
        >
          <span class="icon-list">≡</span>
          List
        </button>
        <button
          @click="currentElementType = 'video'; addNewElement(20, 20, 'video')"
          :class="{ active: currentElementType === 'video' }"
          class="element-btn"
          title="Add Video Element"
        >
          <span class="icon-video">▶</span>
          Video
        </button>
      </div>

      <Dropdown8 align="right" width="48" :auto-hide="true">
        <template #trigger>
          <button class="control-btn">
            {{ zoomLevel === 'fit' ? 'Fit Width' : `${zoomLevel * 100}%` }}
            <LucideIcon name="chevron-down" size="16" class="ml-1 opacity-70" />
          </button>
        </template>

        <template #content>
          <button
            v-for="zoom in zoomLevels"
            :key="zoom.value"
            @click="handleZoomChange(zoom.value)"
            class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
          >
            {{ zoom.label }}
          </button>
        </template>
      </Dropdown8>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePresentationStore } from '@/Stores/presentationStore';
import LucideIcon from '@/Components/Common/LucideIcon.vue';
import Dropdown8 from './Dropdown8.vue';
import { toast } from 'vue3-toastify';


    const props = defineProps({
  readonly: {
    type: Boolean,
    default: false
  },
  isSaving: {
    type: Boolean,
    default: false
  }

});

const emit = defineEmits(['add-element', 'toggle-elements', 'zoom-change',
 'background-change',
 'quickSave',
 'saveAs',
 'startPresentation',
 'load_all_slides_to_file',

]);

const store = usePresentationStore();
// const isSaving = ref(false);
const currentElementType = ref('text');
const zoomLevel = ref(1);
const bgInput = ref(null);

const zoomLevels = [
  { value: 'fit', label: 'Fit Width' },
  { value: 0.5, label: '50%' },
  { value: 1, label: '100%' },
  { value: 1.2, label: '120%' },
  { value: 1.5, label: '150%' },
  { value: 2, label: '200%' },
  { value: 2.5, label: '250%' }
];

const presentationTitle = computed({
  get: () => store.presentationTitle || 'Untitled Presentation',
  set: (value) => store.setTitle(value)
});

const handleTitleChange = () => {
  try {
    presentationTitle.value = presentationTitle.value
      .replace(/[^a-z0-9\s-]/gi, '')
      .trim()
      .replace(/\s+/g, '_');
  } catch (error) {
    console.error('Error updating title:', error);
    toast.error('Failed to update title');
  }
};

const quickSave = async () => {
  if (isSaving.value) return;

  isSaving.value = true;
  try {
    await store.savePresentation();
    toast.success('Presentation saved successfully');
  } catch (error) {
    console.error('Save error:', error);
    toast.error('Failed to save presentation');
  } finally {
    isSaving.value = false;
  }
};

const saveAs = () => {
  try {
    store.saveAsPresentation();
  } catch (error) {
    console.error('Save As error:', error);
    toast.error('Failed to save presentation');
  }
};

const startPresentation = () => {
  try {
    emit('startPresentation');
  } catch (error) {
    console.error('Error in TopBar startPresentation:', error);
    toast.error('Failed to start presentation');
  }
};

const addNewElement = (x, y, type) => {
  try {
    // Check if store.currentSlide exists and has the expected structure
    if (!store.currentSlide || !store.currentSlide.content) {
      console.log('Debug - Current slide state:', {
        currentSlideIndex: store.currentSlideIndex,
        totalSlides: store.slides.length,
        currentSlide: store.currentSlide
      });

      // Emit the event anyway - the parent component will handle creating a new slide if needed
      emit('add-element', { x, y, type });
    } else {
      emit('add-element', { x, y, type });
    }
  } catch (error) {
    console.error('Add element error:', error);
    toast.error('Failed to add element');
  }
};

const toggleAllElements = () => {
  try {
    if (!store.currentSlide) {
      toast.warning('No slide selected');
      return;
    }
    emit('toggle-elements');
  } catch (error) {
    console.error('Toggle elements error:', error);
    toast.error('Failed to toggle elements');
  }
};

const handleZoomChange = (value) => {
  try {
    zoomLevel.value = value;
    emit('zoom-change', value);
  } catch (error) {
    console.error('Zoom change error:', error);
    toast.error('Failed to change zoom level');
  }
};

const handleBackgroundImage = (event) => {
  try {
    if (!event.target.files || !event.target.files[0]) {
      return;
    }
    emit('background-change', event);
  } catch (error) {
    console.error('Background image error:', error);
    toast.error('Failed to change background');
  }
};
</script>

<style scoped>
.presentation-header {
  @apply flex justify-between items-center p-4 border-b border-gray-200;
}

.title-container {
  @apply flex items-center gap-2;
}

.title-input {
  @apply border-0 text-xl font-semibold focus:ring-0 focus:outline-none;
}

.header-controls {
  @apply flex items-center gap-2;
}

.controls-panel {
  @apply flex items-center gap-2 p-4 border-b border-gray-200 bg-white;
}

.control-btn {
  @apply px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors flex items-center gap-1;
}

.element-type-selector {
  @apply flex items-center gap-2;
}

.element-btn {
  @apply px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors flex items-center gap-1;
}

.element-btn.active {
  @apply bg-indigo-600 text-white;
}

.btn-primary {
  @apply flex items-center gap-1 px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors;
}

.btn-present {
  @apply flex items-center gap-1 px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors;
}

.icon-text, .icon-list, .icon-video {
  @apply font-bold text-lg;
}
</style>





