<template>
  <div class="presentation-builder-v2">
    <Head title="Presentation Builder V2 - Classroom Records" />
    <!-- Toolbar -->
    <div class="toolbar">
      <div class="toolbar-section">
        <button @click="addSlide" class="btn btn-primary">
          <span class="icon">+</span> Add Slide
        </button>
        <button @click="deleteCurrentSlide" class="btn btn-danger" :disabled="slides.length <= 1">
          <span class="icon">🗑</span> Delete Slide
        </button>
      </div>

      <div class="toolbar-section">
        <button 
          @click="mode = 'edit'" 
          :class="['btn', mode === 'edit' ? 'btn-active' : '']"
        >
          <span class="icon">✏️</span> Edit Mode
        </button>
        <button 
          @click="mode = 'visibility'" 
          :class="['btn', mode === 'visibility' ? 'btn-active' : '']"
        >
          <span class="icon">👁</span> Visibility Settings
        </button>
        <button 
          @click="mode = 'present'" 
          :class="['btn', mode === 'present' ? 'btn-active' : '']"
        >
          <span class="icon">▶️</span> Present
        </button>
      </div>

      <div class="toolbar-section">
        <button @click="exportJSON" class="btn">
          <span class="icon">💾</span> Export JSON
        </button>
        <button @click="triggerImport" class="btn">
          <span class="icon">📁</span> Import JSON
        </button>
        <input 
          ref="fileInput" 
          type="file" 
          accept=".json" 
          @change="importJSON" 
          style="display: none"
        >
      </div>
    </div>

    <!-- Slide Navigation -->
    <div class="slide-nav" v-if="mode !== 'present'">
      <button 
        v-for="(slide, index) in slides" 
        :key="slide.id"
        @click="currentSlideIndex = index"
        :class="['slide-thumb', currentSlideIndex === index ? 'active' : '']"
      >
        {{ index + 1 }}
      </button>
    </div>

    <!-- Main Slide Area -->
    <div class="slide-container" :class="{ 'expanded': isSlideExpanded }" :style="{ minHeight: slideHeight + 'px' }" v-if="currentSlide">
      <SlideEditor
        v-if="mode === 'edit'"
        :slide="currentSlide"
        @update:slide="updateSlide"
        @expand-height="handleExpandHeight"
        :style="{ minHeight: slideHeight + 'px' }"
      />
      
      <VisibilityEditor
        v-else-if="mode === 'visibility'"
        :slide="currentSlide"
        @update:slide="updateSlide"
      />
      
      <SlidePresenterV2
        v-else-if="mode === 'present'"
        :slides="slides"
        :initial-slide="currentSlideIndex"
        @exit="mode = 'edit'"
      />
    </div>

    <!-- Instructions -->
    <div class="instructions" v-if="mode === 'edit'">
      <p><strong>Tip:</strong> Click on the slide to paste images/text from clipboard (Ctrl+V)</p>
    </div>
    <div class="instructions" v-if="mode === 'visibility'">
      <p><strong>V2 Mode:</strong> Click element → Click ⋮ menu → Set visibility options</p>
      <p>✅ Start Hidden, Clickable | ✅ Start Visible, Clickable | ✅ Custom Opacity Slider</p>
    </div>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import SlideEditor from './SlideEditor.vue';
import VisibilityEditor from './VisibilityEditor.vue';
import SlidePresenterV2 from './SlidePresenterV2.vue';
import { soundManager } from '@/Services/SoundManager';

export default {
  name: 'PresentationBuilderV2',
  components: {
    Head,
    SlideEditor,
    // AnimationEditorV2,
    SlidePresenterV2
  },
  data() {
    return {
      slides: [
        {
          id: this.generateId(),
          elements: []
        }
      ],
      currentSlideIndex: 0,
      mode: 'edit', // 'edit', 'visibility', 'present'
      isSlideExpanded: false,
      slideHeight: 500,
      isOnline: navigator.onLine
    };
  },
  computed: {
    currentSlide() {
      return this.slides[this.currentSlideIndex];
    }
  },
  methods: {
    generateId() {
      return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },
    addSlide() {
      this.slides.push({
        id: this.generateId(),
        elements: []
      });
      this.currentSlideIndex = this.slides.length - 1;
    },
    deleteCurrentSlide() {
      if (this.slides.length > 1) {
        this.slides.splice(this.currentSlideIndex, 1);
        if (this.currentSlideIndex >= this.slides.length) {
          this.currentSlideIndex = this.slides.length - 1;
        }
      }
    },
    updateSlide(updatedSlide) {
      this.slides[this.currentSlideIndex] = updatedSlide;
    },
    handleExpandHeight({ isExpanded, height }) {
      this.isSlideExpanded = isExpanded;
      this.slideHeight = height;
    },
    exportJSON() {
      const data = JSON.stringify(this.slides, null, 2);
      const blob = new Blob([data], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `presentation-v2-${Date.now()}.json`;
      a.click();
      URL.revokeObjectURL(url);
    },
    triggerImport() {
      this.$refs.fileInput.click();
    },
    importJSON(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          try {
            // Parse the JSON
            const imported = JSON.parse(e.target.result);
            
            // Validate structure
            if (!Array.isArray(imported)) {
              throw new Error('JSON must be an array of slides');
            }
            
            if (imported.length === 0) {
              throw new Error('JSON array is empty');
            }
            
            // Validate each slide has required properties
            for (let i = 0; i < imported.length; i++) {
              const slide = imported[i];
              if (!slide.id) {
                throw new Error(`Slide ${i + 1} is missing 'id' property`);
              }
              if (!Array.isArray(slide.elements)) {
                throw new Error(`Slide ${i + 1} is missing 'elements' array`);
              }
            }
            
            // All validations passed - import the slides
            this.slides = imported;
            this.currentSlideIndex = 0;
            
            // Show success message
            alert(`Successfully imported ${imported.length} slide(s)!`);
            
          } catch (error) {
            console.error('Import error:', error);
            alert('Error parsing JSON: ' + error.message);
          }
        };
        reader.onerror = () => {
          alert('Error reading file');
        };
        reader.readAsText(file);
      }
      event.target.value = '';
    },
    handleOnline() {
      this.isOnline = true;
      console.log('Application is back online!');
    },
    handleOffline() {
      this.isOnline = false;
      console.warn('Application went offline');
    },
    registerServiceWorker() {
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
          .then(registration => {
            console.log('Service Worker registered successfully:', registration.scope);
            
            // Check for updates periodically
            setInterval(() => {
              registration.update();
            }, 60000); // Check every minute
          })
          .catch(error => {
            console.error('Service Worker registration failed:', error);
          });
      }
    },
  },
  mounted() {
    // Initialize sound manager once when component loads
    soundManager.initialize();
    
    // Register service worker for offline support
    this.registerServiceWorker();
    
    // Listen for online/offline events
    window.addEventListener('online', this.handleOnline);
    window.addEventListener('offline', this.handleOffline);
  },
  beforeUnmount() {
    window.removeEventListener('online', this.handleOnline);
    window.removeEventListener('offline', this.handleOffline);
  },
};
</script>

<style scoped>
.presentation-builder-v2 {
  width: 100%;
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #1a1a1a;
  color: #fff;
  font-family: system-ui, -apple-system, sans-serif;
}

.toolbar {
  display: flex;
  gap: 20px;
  padding: 15px 20px;
  background: #2a2a2a;
  border-bottom: 2px solid #3a3a3a;
  flex-wrap: wrap;
}

.toolbar-section {
  display: flex;
  gap: 10px;
  align-items: center;
}

.btn {
  padding: 8px 16px;
  background: #3a3a3a;
  border: 1px solid #4a4a4a;
  color: #fff;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  transition: all 0.2s;
}

.btn:hover:not(:disabled) {
  background: #4a4a4a;
  border-color: #5a5a5a;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: #4a90e2;
  border-color: #5a9ff2;
}

.btn-primary:hover {
  background: #5a9ff2;
}

.btn-danger {
  background: #e24a4a;
  border-color: #f25a5a;
}

.btn-danger:hover:not(:disabled) {
  background: #f25a5a;
}

.btn-active {
  background: #3b82f6;
  border-color: #60a5fa;
}

.icon {
  font-size: 16px;
}

.slide-nav {
  display: flex;
  gap: 8px;
  padding: 15px 20px;
  background: #252525;
  overflow-x: auto;
}

.slide-thumb {
  min-width: 60px;
  height: 45px;
  background: #3a3a3a;
  border: 2px solid #4a4a4a;
  color: #fff;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.slide-thumb:hover {
  border-color: #3b82f6;
}

.slide-thumb.active {
  background: #3b82f6;
  border-color: #60a5fa;
}

.slide-container {
  flex: 1;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 20px;
  overflow: auto;
  min-height: 500px; /* Default height */
  transition: min-height 0.3s ease;
}

.slide-container.expanded {
  /* Height controlled by inline style */
}

.instructions {
  padding: 10px 20px;
  background: #2a2a2a;
  border-top: 1px solid #3a3a3a;
  font-size: 13px;
  color: #aaa;
}

.instructions strong {
  color: #fff;
}
</style>
