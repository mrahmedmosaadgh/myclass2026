<template>
  <div class="slide-presenter-v2" @click="handleBackgroundClick">
    <!-- Pinned Elements Overlay -->
    <div class="pinned-overlay" v-if="pinnedElements.length > 0">
      <div class="pinned-container">
        <div class="pinned-header">
          <span class="pinned-title">📌 Pinned Elements</span>
          <button @click.stop="unpinAllElements" class="unpin-all-btn" title="Unpin All">
            ❌ Unpin All
          </button>
        </div>
        <div class="pinned-elements-list">
          <div 
            v-for="element in pinnedElements" 
            :key="element.id"
            class="pinned-element-item"
          >
            <div class="pinned-element-content">
              <img v-if="element.type === 'image'" :src="element.src" :alt="'Pinned image'">
              <span v-else>{{ truncate(element.content, 50) }}</span>
            </div>
            <button @click.stop="unpinElement(element.id)" class="unpin-btn" title="Unpin">
              ×
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="present-canvas">
      <SlideElementV2
        v-for="element in currentSlide.elements"
        :key="element.id"
        :element="element"
        :present-state="presentState"
        @toggle="handleElementToggle"
        @pin="handlePinElement"
      />
    </div>

    <!-- Navigation Controls -->
    <div class="present-controls" @click.stop>
      <button 
        @click="previousSlide" 
        :disabled="currentIndex === 0"
        class="nav-btn"
      >
        ◀ Previous
      </button>
      
      <div class="slide-counter">
        {{ currentIndex + 1 }} / {{ slides.length }}
      </div>
      
      <button 
        @click="nextSlide" 
        :disabled="currentIndex === slides.length - 1"
        class="nav-btn"
      >
        Next ▶
      </button>
      
      <button @click="exitPresentation" class="exit-btn">
        Exit
      </button>
    </div>

    <!-- Keyboard Navigation Hint -->
    <div class="keyboard-hint">
      Click elements to toggle visibility (10% ↔ 100%) | Use ← → arrow keys to navigate slides | ESC to exit
    </div>
  </div>
</template>

<script>
import SlideElementV2 from './SlideElementV2.vue';

export default {
  name: 'SlidePresenterV2',
  components: {
    SlideElementV2
  },
  props: {
    slides: {
      type: Array,
      required: true
    },
    initialSlide: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      currentIndex: this.initialSlide,
      presentState: {
        toggledElements: {} // { elementId: boolean (true = shown, false = hidden) }
      },
      pinnedElements: [] // Array of pinned element objects
    };
  },
  computed: {
    currentSlide() {
      return this.slides[this.currentIndex];
    }
  },
  methods: {
    truncate(text, length) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    },
    handlePinElement(element) {
      // Check if already pinned
      const exists = this.pinnedElements.find(el => el.id === element.id);
      if (exists) {
        // Already pinned - do nothing or unpin
        return;
      }
      
      // Add to pinned elements
      this.pinnedElements.push({ ...element });
    },
    unpinElement(elementId) {
      this.pinnedElements = this.pinnedElements.filter(el => el.id !== elementId);
    },
    unpinAllElements() {
      this.pinnedElements = [];
    },
    handleBackgroundClick(event) {
      // Only handle clicks on the background, not on elements or controls
      if (event.target.classList.contains('present-canvas')) {
        // Optional: could advance slide here
      }
    },
    handleElementToggle(elementId) {
      const element = this.currentSlide.elements.find(el => el.id === elementId);
      if (!element || !element.clickable) return;

      // Toggle the element's visibility state
      if (this.presentState.toggledElements[elementId] === undefined) {
        // First click - toggle from initial state
        this.presentState.toggledElements[elementId] = !element.startHidden;
      } else {
        // Subsequent clicks - toggle current state
        this.presentState.toggledElements[elementId] = !this.presentState.toggledElements[elementId];
      }

      // Force reactivity
      this.presentState = { 
        toggledElements: { ...this.presentState.toggledElements } 
      };
    },
    nextSlide() {
      if (this.currentIndex < this.slides.length - 1) {
        this.currentIndex++;
        this.resetToggleState();
      }
    },
    previousSlide() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
        this.resetToggleState();
      }
    },
    resetToggleState() {
      this.presentState = {
        toggledElements: {}
      };
    },
    exitPresentation() {
      this.$emit('exit');
    },
    handleKeydown(event) {
      if (event.key === 'ArrowRight') {
        event.preventDefault();
        this.nextSlide();
      } else if (event.key === 'ArrowLeft') {
        event.preventDefault();
        this.previousSlide();
      } else if (event.key === 'Escape') {
        this.exitPresentation();
      }
    }
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeydown);
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeydown);
  }
};
</script>

<style scoped>
.slide-presenter-v2 {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #000;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

/* Pinned Elements Overlay */
.pinned-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.7) 100%);
  padding: 15px 30px;
  z-index: 1100;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
  border-bottom: 2px solid #f59e0b;
}

.pinned-container {
  max-width: 1200px;
  margin: 0 auto;
}

.pinned-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.pinned-title {
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.unpin-all-btn {
  padding: 6px 14px;
  background: #ef4444;
  border: none;
  color: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s ease;
}

.unpin-all-btn:hover {
  background: #dc2626;
  transform: translateY(-1px);
}

.pinned-elements-list {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.pinned-element-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 8px 12px;
  transition: all 0.2s ease;
}

.pinned-element-item:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
}

.pinned-element-content {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #fff;
  font-size: 14px;
  max-width: 300px;
  overflow: hidden;
}

.pinned-element-content img {
  max-height: 40px;
  max-width: 60px;
  object-fit: cover;
  border-radius: 4px;
}

.unpin-btn {
  width: 24px;
  height: 24px;
  background: #ef4444;
  border: none;
  color: white;
  border-radius: 50%;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.unpin-btn:hover {
  background: #dc2626;
  transform: scale(1.1);
}

.present-canvas {
  flex: 1;
  width: 100%;
  max-width: 1200px;
  max-height: calc(100vh - 180px); /* Adjust based on controls height */
  background: #ffffff;
  position: relative;
  margin: 20px;
  border-radius: 8px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  overflow-y: auto; /* Enable vertical scrolling */
  overflow-x: hidden; /* Hide horizontal overflow */
}

/* Custom scrollbar for presentation mode */
.present-canvas::-webkit-scrollbar {
  width: 12px;
}

.present-canvas::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 6px;
}

.present-canvas::-webkit-scrollbar-thumb {
  background: rgba(74, 144, 226, 0.6);
  border-radius: 6px;
}

.present-canvas::-webkit-scrollbar-thumb:hover {
  background: rgba(74, 144, 226, 0.8);
}

.present-canvas::-webkit-scrollbar-thumb:active {
  background: rgba(74, 144, 226, 1);
}

.present-controls {
  display: flex;
  gap: 20px;
  align-items: center;
  padding: 20px;
  background: rgba(0, 0, 0, 0.8);
  border-radius: 12px;
  margin-bottom: 20px;
}

.nav-btn {
  padding: 12px 24px;
  background: #4a90e2;
  border: none;
  color: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.2s;
}

.nav-btn:hover:not(:disabled) {
  background: #5a9ff2;
  transform: translateY(-2px);
}

.nav-btn:disabled {
  background: #3a3a3a;
  cursor: not-allowed;
  opacity: 0.5;
}

.slide-counter {
  color: white;
  font-size: 18px;
  font-weight: 600;
  min-width: 120px;
  text-align: center;
}

.exit-btn {
  padding: 12px 24px;
  background: #e24a4a;
  border: none;
  color: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.2s;
}

.exit-btn:hover {
  background: #f25a5a;
}

.keyboard-hint {
  position: absolute;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  color: rgba(255, 255, 255, 0.5);
  font-size: 12px;
  text-align: center;
  max-width: 80%;
}
</style>
