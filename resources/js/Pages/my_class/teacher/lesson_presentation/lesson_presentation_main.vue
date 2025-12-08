<template>
  <div class="presentation-layout">
    <TopBar
      :readonly="isReadonly"
      :isSaving="isSaving"
      @quickSave="quickSave"
      @saveAs="saveAs"
      @startPresentation="startPresentation"
      @load_all_slides_to_file="load_all_slides_to_file"

      @add-element="handleAddElement"
      @toggle-elements="handleToggleElements"
      @zoom-change="handleZoomChange"
      @background-change="handleBackgroundChange"
    />
    <!-- Main Content -->
    <div class="main-content">
      <!-- Slides Panel -->
      <div class="slides-panel">
        <div class="slides-controls">
          <button @click="addNewSlide" class="action-btn add-btn">
            <LucideIcon name="plus" size="18" />
            <span>New Slide</span>
          </button>




        </div>

        <div class="slides-list">
          <div
            v-for="(slide, index) in slides"
            :key="index"
            class="slide-thumbnail"
            :class="{ 'active': index === currentSlideIndex }"
            @click="handleSlideClick(index)"
          >
            <div class="slide-preview">
              <!-- Preview of slide content -->
              <div class="slide-number">{{ index + 1 }}</div>
              <div class="thumbnail-content">
                <!-- <CanvasEditor
                  v-if="slide.content"
                  :modelValue="slide.content"
                  :readonly="true"
                  :thumbnail="true"
                /> -->
                <input type="text" v-model="slide.title"
                class="border-0"
                >
              </div>
            </div>
            <div class="slide-actions">
              <button @click.stop="duplicateSlide(index)" class="duplicate-btn" title="Duplicate slide">
                <LucideIcon name="copy" size="16" />
              </button>
              <button @click.stop="deleteSlide(index)" class="delete-btn" title="Delete slide">
                <LucideIcon name="trash-2" size="16" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Editor Area -->
      <div class="editor-area">
        <div class="canvas-container">
          <CanvasEditor
            v-if="currentSlide"
            :key="currentSlideIndex"
            v-model="currentSlide.content"
            @update:modelValue="updateSlideContent"
            @canvas-click="handleCanvasClick"
          />
        </div>
      </div>
    </div>

    <!-- Presentation Mode -->
    <PresentationMode
      v-if="isPresentationMode"
      :slides="slides"
      :initialIndex="currentSlideIndex"
      @exit="exitPresentation"
    />
  </div>
</template>

<script setup>
import { computed,ref,watch  } from 'vue';
import { usePresentationStore } from '@/Stores/presentationStore';
// import { useSlidesStore } from '@/Stores/slidesStore';
import CanvasEditor from './comp/CanvasEditor.vue';
import PresentationMode from './comp/PresentationMode.vue';
import TopBar from './comp/TopBar.vue';
import { toast } from 'vue3-toastify';
import LucideIcon from '@/Components/Common/LucideIcon.vue';
import Dropdown8 from './comp/Dropdown8.vue';

const store = usePresentationStore();
const isReadonly = ref(false);
const isSaving = ref(false);

// Use computed properties from store
const slides = computed(() => store.slides);
const currentSlideIndex = computed(() => store.currentSlideIndex);
const currentSlide = computed(() => store.currentSlide);

const handleSlideClick = (index) => {
    store.setCurrentSlideIndex(index);
};
const handleAddElement = ({ x, y, type }) => {
  // Check if there are any slides
  if (!slides.value || slides.value.length === 0) {
    // Create a new slide if none exist
    store.addSlide();
    toast.info('Created first slide');
  }

  // Ensure we have a current slide
  if (!currentSlide.value || !currentSlide.value.content) {
    console.warn('No current slide or invalid slide content');
    toast.warning('Please select a slide first');
    return;
  }

  const newElement = {
    id: Date.now(),
    type,
    x: Math.max(0, x - 100),
    y: Math.max(0, y - 50),
    width: 'auto',
    height: 'auto',
    content: type === 'text' ? 'New Text' : '',
    visible: true,
    locked: false,
    style: {
      fontSize: '16px',
      fontFamily: 'Arial',
      color: '#000000',
      backgroundColor: 'white',
      padding: '12px',
      borderRadius: '4px',
      boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
      border: '1px solid #e2e8f0',
      display: type === 'text' ? 'inline-block' : 'block',
      width: type === 'text' ? 'auto' : '320px',
      minWidth: type === 'text' ? '50px' : '320px'
    }
  };

  try {
    store.addElement(newElement);
    console.log('Added new element:', newElement);
  } catch (error) {
    console.error('Failed to add element:', error);
    toast.error('Failed to add element');
  }
};

const handleToggleElements = () => {
  try {
    store.toggleAllElements();
  } catch (error) {
    console.error('Failed to toggle elements:', error);
    toast.error('Failed to toggle elements');
  }
};

const handleZoomChange = (level) => {
  try {
    store.setZoomLevel(level);
  } catch (error) {
    console.error('Failed to change zoom:', error);
    toast.error('Failed to change zoom level');
  }
};

const handleBackgroundChange = (event) => {
  // Implementation for background change
};

// const quickSave = async () => {
//   isSaving.value = true;
//   try {
//     await store.savePresentation();
//     toast.success('Presentation saved successfully');
//   } catch (error) {
//     toast.error('Failed to save presentation');
//   } finally {
//     isSaving.value = false;
//   }
// };



// Replace local refs with store state
const presentationTitle = computed({
  get: () => store.presentationTitle,
  set: (value) => store.setTitle(value)
});

const addNewSlide = () => {
  store.addSlide(); // Changed from store.addNewSlide() to store.addSlide()
  toast.success('New slide added', {
    position: 'bottom-right',
    autoClose: 2000
  });
};

// Add missing updateSlideContent function
const updateSlideContent = (content) => {
  store.updateSlideContent(content);
};


// Update your save/load methods to use store state
const savePresentation = () => {
  try {
    // Use the preparePresentationData function to get the data
    const presentationData = preparePresentationData();

    // Save locally
    const blob = new Blob([JSON.stringify(presentationData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    const timestamp = new Date().toISOString().slice(0,19).replace(/[:]/g, '-');
    const filename = `${presentationTitle.value}_${timestamp}.json`;

    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    toast.success(`Successfully saved presentation with ${presentationData.slides.length} slides`);
  } catch (error) {
    console.error('Error saving presentation:', error);
    toast.error('Error saving presentation: ' + error.message);
  }
};

// Update your load method
const loadPresentation = async (file) => {
  try {
    const data = JSON.parse(await file.text());
    store.loadPresentation(data);
    toast.success(`Successfully loaded presentation with ${store.totalSlides} slides`);
  } catch (error) {
    console.error('Error loading presentation:', error);
    toast.error('Error loading presentation: ' + error.message);
  }
};

const handleTitleChange = () => {
  // Sanitize the title to be file-system friendly
  presentationTitle.value = presentationTitle.value
    .replace(/[^a-z0-9\s-]/gi, '')
    .trim()
    .replace(/\s+/g, '_');
};

// Toggle save menu
const toggleSaveMenu = () => {
  showSaveMenu.value = !showSaveMenu.value;
  // Close the other menu if it's open
  if (showSaveMenu.value && showSideSaveMenu.value) {
    showSideSaveMenu.value = false;
  }
};

// Close save menu when clicking outside
const closeSaveMenu = () => {
  showSaveMenu.value = false;
};

// Toggle side save menu
const toggleSideSaveMenu = () => {
  showSideSaveMenu.value = !showSideSaveMenu.value;
  // Close the other menu if it's open
  if (showSideSaveMenu.value && showSaveMenu.value) {
    showSaveMenu.value = false;
  }
};

// Close side save menu when clicking outside
const closeSideSaveMenu = () => {
  showSideSaveMenu.value = false;
};

// Function to prepare presentation data before saving
const preparePresentationData = () => {
  // First verify we have all slides
  console.log('Current slides:', slides.value);

  if (!slides.value || !Array.isArray(slides.value)) {
    console.error('Slides is not an array:', slides.value);
    throw new Error('Invalid slides data structure');
  }

  if (slides.value.length === 0) {
    console.warn('No slides to save, creating a default slide');
    // Add a default slide if none exist
    slides.value.push({
      id: Date.now(),
      title: '',
      content: {
        elements: [],
        backgroundImage: ''
      }
    });
  }

  // Map all slides to ensure proper structure
  const preparedSlides = slides.value.map((slide, index) => {
    console.log(`Processing slide ${index}:`, slide);

    if (!slide.content) {
      console.warn(`Slide ${index} missing content, creating default structure`);
      return {
        id: slide.id || Date.now() + index,
        title: slide.title || '',
        content: {
          elements: [],
          backgroundImage: ''
        }
      };
    }

    return {
      id: slide.id || Date.now() + index,
      title: slide.title || '',

      content: {
        elements: Array.isArray(slide.content.elements) ? slide.content.elements : [],
        backgroundImage: slide.content.backgroundImage || ''
      }
    };
  });

  console.log(`Prepared ${preparedSlides.length} slides for saving`);

  return {
    title: presentationTitle.value || 'Untitled Presentation',
    slides: preparedSlides,
    version: "2.0",
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
    slideCount: preparedSlides.length
  };
};



// Save locally (download)
const saveLocally = () => {
  try {
    // Validate slides exist
    if (!slides.value || slides.value.length === 0) {
      toast.error('No slides to save');
      return;
    }

    console.log('Starting save process with', slides.value.length, 'slides');
    const presentationData = preparePresentationData();

    // Verify the prepared data
    if (presentationData.slides.length !== slides.value.length) {
      console.error('Slide count mismatch:', {
        original: slides.value.length,
        prepared: presentationData.slides.length
      });
      throw new Error('Slide count mismatch during save preparation');
    }

    // Create and download the file
    const blob = new Blob([JSON.stringify(presentationData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    const timestamp = new Date().toISOString().slice(0,19).replace(/[:]/g, '-');
    const filename = `${presentationTitle.value}_${timestamp}.json`;

    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showSaveMenu.value = false;

    console.log('Save completed successfully:', {
      slideCount: presentationData.slides.length,
      title: presentationData.title
    });

    toast.success(`Successfully saved presentation with ${presentationData.slides.length} slides`);

  } catch (error) {
    console.error('Error saving presentation:', error);
    toast.error('Error saving presentation: ' + error.message);
  }
};

// Load from file function
const loadFromFile = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.json';

  input.onchange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        const data = JSON.parse(e.target.result);
        console.log('Loading data:', data); // Debug log

        // Handle presentation format
        if (data.slides && Array.isArray(data.slides)) {
          slides.value = data.slides.map(slide => ({
            id: slide.id || Date.now(),
        title: slide.title || '',

            content: {
              elements: slide.content?.elements || [],
              backgroundImage: slide.content?.backgroundImage || ''
            }
          }));

          presentationTitle.value = data.title || 'Untitled Presentation';
          currentSlideIndex.value = 0;

          console.log(`Loaded presentation with ${slides.value.length} slides`);
          alert(`Successfully loaded presentation with ${slides.value.length} slides`);
          return;
        }

        // Handle legacy single slide format
        if (data.elements || (data.content && data.content.elements)) {
          const slideContent = data.elements ?
            { elements: data.elements, backgroundImage: data.backgroundImage || '' } :
            data.content;

          slides.value = [{
            id: Date.now(),
            content: {
              elements: slideContent.elements || [],
              backgroundImage: slideContent.backgroundImage || ''
            }
          }];

          currentSlideIndex.value = 0;
          presentationTitle.value = file.name.replace('.json', '');
          alert('Loaded single slide');
          return;
        }

        throw new Error('Invalid presentation format');
      } catch (error) {
        console.error('Error loading file:', error);
        alert('Error loading file: Invalid format');
      }
    };

    reader.onerror = (error) => {
      console.error('FileReader error:', error);
      alert('Error reading file');
    };

    reader.readAsText(file);
  };

  input.click();
};

// Initialize with one slide
if (slides.value.length === 0) {
  addNewSlide();
}

const isPresentationMode = computed(() => store.isPresentationMode);

const startPresentation = () => {
  try {
    store.setPresentationMode(true);
    console.log('Starting presentation mode');
  } catch (error) {
    console.error('Error starting presentation:', error);
    toast.error('Failed to start presentation');
  }
};

const exitPresentation = () => {
  try {
    store.setPresentationMode(false);
    console.log('Exiting presentation mode');
  } catch (error) {
    console.error('Error exiting presentation:', error);
    toast.error('Failed to exit presentation');
  }
};

// Duplicate slide
const duplicateSlide = (index) => {
  // Create a deep copy of the slide
  const slideToDuplicate = slides.value[index];
  const duplicatedSlide = {
    id: Date.now(), // New unique ID
    title: slides.value[index]?.title,

    content: JSON.parse(JSON.stringify(slideToDuplicate.content))
  };

  // Insert the duplicated slide after the original
  slides.value.splice(index + 1, 0, duplicatedSlide);

  // Select the new slide
  currentSlideIndex.value = index + 1;

  // Show success message
  toast.success('Slide duplicated', {
    position: 'bottom-right',
    autoClose: 2000
  });

  saveSlides();
};

// Add this method to handle slide deletion
const deleteSlide = (index) => {
  if (slides.value.length > 1) {
    slides.value.splice(index, 1);
    if (currentSlideIndex.value >= slides.value.length) {
      currentSlideIndex.value = slides.value.length - 1;
    }
    saveSlides();
  } else {
    toast.error('Cannot delete the only slide', {
      position: 'bottom-right',
      autoClose: 2000
    });
  }
};

// Add the missing saveSlides function
const saveSlides = () => {
  // This function is called when slides are modified
  // We can use it to save to localStorage or trigger other save operations
  console.log('Saving slides, count:', slides.value.length);
  try {
    // Optionally save to localStorage for backup
    // localStorage.setItem('slides_backup', JSON.stringify(slides.value));
  } catch (error) {
    console.error('Error saving slides:', error);
  }
};

// Watch for slides changes
watch(slides, (newSlides) => {
  console.log('Slides changed. New count:', newSlides.length);
  try {
    // localStorage.setItem('slides_backup', JSON.stringify(newSlides));
  } catch (error) {
    console.error('Error saving slides to localStorage:', error);
  }
}, { deep: true });

// Add watch for currentSlideIndex to ensure it's valid
watch(slides, (newSlides) => {
  if (currentSlideIndex.value >= newSlides.length) {
    currentSlideIndex.value = Math.max(0, newSlides.length - 1);
  }
});

const save_all_slides_to_file = () => {
  try {
    // Validate slides exist
    if (!slides.value || slides.value.length === 0) {
      toast.error('No slides to save');
      return;
    }

    const fileName = prompt('Enter file name:', 'presentation_' + new Date().toISOString().slice(0,10));
    if (!fileName) return;

    const presentationData = {
      title: fileName,
      slides: slides.value.map(slide => ({
        id: slide.id,
        title: slide.title || '',

        content: {
          elements: slide.content?.elements || [],
          backgroundImage: slide.content?.backgroundImage || ''
        }
      })),
      version: "2.0",
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
      slideCount: slides.value.length
    };

    const blob = new Blob([JSON.stringify(presentationData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${fileName}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    toast.success(`Successfully saved presentation with ${slides.value.length} slides`);
  } catch (error) {
    console.error('Error saving presentation:', error);
    toast.error('Error saving presentation: ' + error.message);
  }
};

const load_all_slides_to_file = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.json';

  input.onchange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        const data = JSON.parse(e.target.result);
        console.log('Parsed presentation data:', data);

        // Validate the loaded data structure
        if (!data.slides || !Array.isArray(data.slides)) {
          throw new Error('Invalid presentation format');
        }

        // Load the presentation using the store's loadPresentation method
        store.loadPresentation(data);
        toast.success(`Successfully loaded presentation with ${data.slides.length} slides`);
      } catch (error) {
        console.error('Error loading presentation:', error);
        toast.error('Error loading presentation: ' + error.message);
      }
    };

    reader.onerror = () => {
      toast.error('Error reading file');
    };

    reader.readAsText(file);
  };

  input.click();
};

// Initialize missing variables
const showSaveMenu = ref(false);
const showSideSaveMenu = ref(false);
const lastSavedPath = ref('');
const lastSavedTitle = ref('');

const quickSave = async () => {
  try {
    if (isSaving.value) return; // Prevent multiple save operations
    isSaving.value = true;

    if (!lastSavedPath.value) {
      // If never saved before, do Save As instead
      console.log('No previous save path, redirecting to saveAs');
      isSaving.value = false;
      return saveAs();
    }

    console.log('Quick saving to:', lastSavedPath.value);
    const presentationData = preparePresentationData();

    // Save locally
    const blob = new Blob([JSON.stringify(presentationData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);

    // Create and trigger download with a slight delay to ensure browser processes it
    setTimeout(() => {
      const a = document.createElement('a');
      a.href = url;
      a.download = lastSavedPath.value;
      a.style.display = 'none';
      document.body.appendChild(a);

      // Use a try-catch block specifically for the click operation
      try {
        a.click();
        console.log('Download triggered successfully');
      } catch (clickError) {
        console.error('Error triggering download:', clickError);
        toast.error('Failed to trigger download');
        isSaving.value = false;
      }

      // Clean up
      setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        isSaving.value = false;
        // Add success animation to the title
        const titleInput = document.querySelector('.title-input');
        if (titleInput) {
          titleInput.classList.add('save-success');
          setTimeout(() => titleInput.classList.remove('save-success'), 500);
        }

        toast.success('Presentation saved successfully', {
          icon: '💾',
          position: 'bottom-right',
          autoClose: 2000
        });
      }, 100);
    }, 100);

    showSaveMenu.value = false;
    showSideSaveMenu.value = false;
  } catch (error) {
    console.error('Error saving presentation:', error);
    toast.error('Error saving presentation: ' + error.message);
    isSaving.value = false;
  }
};

const saveAs = async () => {
  try {
    if (isSaving.value) return; // Prevent multiple save operations
    isSaving.value = true;

    const fileName = prompt('Enter file name:', presentationTitle.value || 'presentation');
    if (!fileName) {
      console.log('Save As cancelled - no filename provided');
      isSaving.value = false;
      return;
    }

    console.log('Saving as new file:', fileName);
    const presentationData = preparePresentationData();
    const timestamp = new Date().toISOString().slice(0,19).replace(/[:]/g, '-');
    const fullFileName = `${fileName}_${timestamp}.json`;

    // Create blob and URL
    const blob = new Blob([JSON.stringify(presentationData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);

    // Create and trigger download with a slight delay
    setTimeout(() => {
      const a = document.createElement('a');
      a.href = url;
      a.download = fullFileName;
      a.style.display = 'none'; // Hide the element
      document.body.appendChild(a);

      // Use a try-catch block specifically for the click operation
      try {
        a.click();
        console.log('Download triggered successfully for:', fullFileName);
      } catch (clickError) {
        console.error('Error triggering download:', clickError);
        toast.error('Failed to trigger download');
        isSaving.value = false;
      }

      // Clean up with a small delay to ensure browser has time to process
      setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        isSaving.value = false;

        // Update saved information
        lastSavedTitle.value = fileName;
        lastSavedPath.value = fullFileName;
        presentationTitle.value = fileName;

        // Add success animation to the title
        const titleInput = document.querySelector('.title-input');
        if (titleInput) {
          titleInput.classList.add('save-success');
          setTimeout(() => titleInput.classList.remove('save-success'), 500);
        }

        toast.success('Presentation saved as "' + fileName + '"', {
          icon: '📄',
          position: 'bottom-right',
          autoClose: 3000
        });
      }, 100);
    }, 100);

    showSaveMenu.value = false;
    showSideSaveMenu.value = false;
  } catch (error) {
    console.error('Error saving presentation:', error);
    toast.error('Error saving presentation: ' + error.message);
    isSaving.value = false;
  }
};

const handleCanvasClick = ({ x, y,currentElementType }) => {
  if (isReadonly.value) return;

  handleAddElement({
    x,
    y,
    type: currentElementType  || 'text'
    // type: currentElementType.value || 'text'
  });
};

const handleDeleteElement = (elementId) => {
  try {
    store.deleteElement(elementId);
    toast.success('Element deleted successfully');
  } catch (error) {
    console.error('Failed to delete element:', error);
    toast.error('Failed to delete element');
  }
};

const updateElementContent = (elementId, newContent) => {
  console.log('Updating element content:', elementId, newContent);
  store.updateElementInCurrentSlide(elementId, { content: newContent });
};

</script>

<style scoped>
@keyframes save-success {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.save-success {
  animation: save-success 0.5s ease;
}
.presentation-layout {
  display: flex;
  flex-direction: column;
  height: 100vh;
  width: 100%;
  background-color: #f9fafb;
  color: #1f2937;
}

.presentation-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.25rem;
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.title-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.title-input {
  font-size: 1.25rem;
  font-weight: 500;
  border: 1px solid transparent;
  padding: 0.5rem;
  border-radius: 0.375rem;
  background: transparent;
  width: 300px;
  transition: all 0.2s ease;
}

.title-input:hover {
  border-color: #e5e7eb;
}

.title-input:focus {
  border-color: #6366f1;
  outline: none;
  background: white;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

.header-controls {
  display: flex;
  gap: 0.75rem;
}

.main-content {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.slides-panel {
  width: 280px;
  background-color: white;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.slides-controls {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.action-btn {
  width: 100%;
  padding: 0.625rem;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.add-btn {
  background-color: #6366f1;
  color: white;
}

.add-btn:hover {
  background-color: #4f46e5;
}

.save-btn {
  background-color: #3b82f6;
  color: white;
}

.save-btn:hover {
  background-color: #2563eb;
}



.slides-list {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.slide-thumbnail {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  margin-bottom: 0.75rem;
  cursor: pointer;
  position: relative;
  height: 140px;
  transition: all 0.2s ease;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.slide-thumbnail.active {
  border-color: #6366f1;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
}

.slide-thumbnail:hover {
  border-color: #6366f1;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.slide-preview {
  height: 100%;
  padding: 0.5rem;
  position: relative;
}

.slide-number {
  position: absolute;
  top: 5px;
  left: 5px;
  background: rgba(79, 70, 229, 0.9);
  color: white;
  padding: 2px 8px;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.thumbnail-content {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.slide-actions {
  position: absolute;
  top: 5px;
  right: 5px;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.slide-thumbnail:hover .slide-actions {
  opacity: 1;
}

.slide-actions {
  display: flex;
  gap: 6px;
}

.duplicate-btn, .delete-btn {
  color: white;
  border: none;
  border-radius: 9999px;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.duplicate-btn {
  background: rgba(59, 130, 246, 0.9);
}

.duplicate-btn:hover {
  background: rgba(37, 99, 235, 1);
  transform: scale(1.05);
}

.delete-btn {
  background: rgba(239, 68, 68, 0.9);
}

.delete-btn:hover {
  background: rgba(220, 38, 38, 1);
  transform: scale(1.05);
}

.editor-area {
  flex: 1;
  padding: 1rem;
  overflow: hidden;
  background-color: #f9fafb;
}

.canvas-container {
  height: 100%;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.btn-primary {
  background-color: #6366f1;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background-color: #4f46e5;
}

.btn-present {
  background-color: #10b981;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-present:hover {
  background-color: #059669;
}

.save-dropdown {
  position: relative;
}

.save-dropdown-menu {
  position: absolute;
  top: calc(100% + 0.25rem);
  left: 0;
  min-width: 200px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  z-index: 1000;
  overflow: hidden;
}

.dropdown-item {
  width: 100%;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  background: none;
  cursor: pointer;
  color: #374151;
  transition: all 0.15s ease;
  text-align: left;
}

.dropdown-item:hover {
  background-color: #f3f4f6;
}

.dropdown-item:not(:last-child) {
  border-bottom: 1px solid #f3f4f6;
}
</style>












