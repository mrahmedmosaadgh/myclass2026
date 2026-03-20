<template>
  <div class="presentation-builder">
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
          @click="mode = 'clicks'" 
          :class="['btn', mode === 'clicks' ? 'btn-active' : '']"
        >
          <span class="icon">👆</span> Click Actions
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
    <div class="slide-container" v-if="currentSlide">
      <SlideEditor
        v-if="mode === 'edit'"
        :slide="currentSlide"
        @update:slide="updateSlide"
      />
      
      <AnimationEditorSimple
        v-else-if="mode === 'clicks'"
        :slide="currentSlide"
        @update:slide="updateSlide"
      />
      
      <SlidePresenterSimple
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
    <div class="instructions" v-if="mode === 'clicks'">
      <p><strong>Mode:</strong> Instant show/hide on click - no fade animations</p>
    </div>
  </div>
</template>

<script>
import SlideEditor from './SlideEditor.vue';
import AnimationEditorSimple from './AnimationEditorSimple.vue';
import SlidePresenterSimple from './SlidePresenterSimple.vue';

export default {
  name: 'PresentationBuilderSimple',
  components: {
    SlideEditor,
    AnimationEditorSimple,
    SlidePresenterSimple
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
      mode: 'edit' // 'edit', 'clicks', 'present'
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
    exportJSON() {
      const data = JSON.stringify(this.slides, null, 2);
      const blob = new Blob([data], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `presentation-simple-${Date.now()}.json`;
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
            const imported = JSON.parse(e.target.result);
            if (Array.isArray(imported) && imported.length > 0) {
              this.slides = imported;
              this.currentSlideIndex = 0;
            } else {
              alert('Invalid JSON format');
            }
          } catch (error) {
            alert('Error parsing JSON: ' + error.message);
          }
        };
        reader.readAsText(file);
      }
      event.target.value = '';
    }
  }
};
</script>

<style scoped>
.presentation-builder {
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
  background: #6a4ae2;
  border-color: #7a5af2;
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
  border-color: #6a4ae2;
}

.slide-thumb.active {
  background: #6a4ae2;
  border-color: #7a5af2;
}

.slide-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  overflow: hidden;
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
