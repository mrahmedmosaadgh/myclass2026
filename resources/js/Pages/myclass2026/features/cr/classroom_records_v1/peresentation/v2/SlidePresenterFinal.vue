<template>
  <div class="slide-presenter-final">
    <div class="present-canvas" :style="{ height: slideHeight + 'px' }">
      <div
        v-for="element in currentSlide.elements"
        :key="element.id"
        :class="['present-element', { 
          'clickable': isClickable(element), 
          'moveable': isMoveable(element),
          'dragging': draggingId === element.id
        }]"
        :style="getElementStyle(element)"
        @click="handleElementClick(element)"
        @mousedown="handleMouseDown(element, $event)"
      >
        <!-- Image -->
        <img 
          v-if="element.type === 'image'" 
          :src="element.src" 
          alt="Element"
          class="element-image"
          :style="{ width: element.width + 'px', height: element.height + 'px' }"
        >
        
        <!-- Text -->
        <div 
          v-else-if="element.type === 'text'"
          class="element-text"
          :style="{ fontSize: element.fontSize + 'px', color: element.color }"
        >
          {{ element.content }}
        </div>

        <!-- Moveable indicator -->
        <div v-if="isMoveable(element)" class="move-indicator">
          🔄 Drag to move
        </div>
      </div>
    </div>

    <!-- Navigation Controls -->
    <div class="present-controls" @click.stop>
      <button 
        @click="previousSlide" 
        :disabled="currentIndex === 0"
        class="nav-btn prev-btn"
        title="Previous slide"
      >
        &#8592;
      </button>
      
      <div class="slide-counter">
        {{ currentIndex + 1 }} / {{ slides.length }}
      </div>
      
      <button 
        @click="nextSlide" 
        :disabled="currentIndex === slides.length - 1"
        class="nav-btn next-btn"
        title="Next slide"
      >
        &#8594;
      </button>
      
      <button @click="exitPresentation" class="exit-btn" title="Exit presentation">
        &#10005;
      </button>
    </div>
  </div>
</template>

<script>
import { soundManager } from '@/Services/SoundManager';

export default {
  name: 'SlidePresenterFinal',
  props: {
    slides: {
      type: Array,
      required: true
    },
    initialSlide: {
      type: Number,
      default: 0
    },
    slideHeight: {
      type: Number,
      default: 1123
    }
  },
  data() {
    return {
      currentIndex: this.initialSlide,
      elementStates: {}, // { elementId: { visible: boolean, x: number, y: number } }
      draggingId: null,
      dragStartX: 0,
      dragStartY: 0,
      elementStartX: 0,
      elementStartY: 0
    };
  },
  computed: {
    currentSlide() {
      return this.slides[this.currentIndex];
    }
  },
  methods: {
    isClickable(element) {
      return element.visibilityOption === 'hidden-clickable' || 
             element.visibilityOption === 'shown-clickable';
    },
    isMoveable(element) {
      return element.visibilityOption === 'moveable';
    },
    getElementState(elementId) {
      return this.elementStates[elementId] || {};
    },
    getElementStyle(element) {
      const state = this.getElementState(element.id);
      const x = state.x !== undefined ? state.x : element.x;
      const y = state.y !== undefined ? state.y : element.y;

      const style = {
        position: 'absolute',
        left: x + 'px',
        top: y + 'px',
        width: element.type === 'image' ? element.width + 'px' : 'auto',
        maxWidth: element.type === 'text' ? element.width + 'px' : 'none',
        transition: this.draggingId === element.id ? 'none' : 'opacity 0.4s ease, left 0.3s ease, top 0.3s ease'
      };

      // Handle visibility based on option
      if (element.visibilityOption === 'hidden-clickable') {
        // Starts at 0.05, click to show at 1.0
        const isVisible = state.visible !== undefined ? state.visible : false;
        style.opacity = isVisible ? 1 : 0.05;
      } else if (element.visibilityOption === 'shown-clickable') {
        // Starts at 1.0, click to hide at 0.1
        const isVisible = state.visible !== undefined ? state.visible : true;
        style.opacity = isVisible ? 1 : 0.1;
      } else {
        // Always visible
        style.opacity = 1;
      }

      // Cursor for moveable elements
      if (this.isMoveable(element)) {
        style.cursor = 'move';
      } else if (this.isClickable(element)) {
        style.cursor = 'pointer';
      }

      return style;
    },
    handleElementClick(element) {
      if (this.isClickable(element)) {
        if (soundManager.isReady()) {
          soundManager.playClick(0.5);
        }
        const currentState = this.getElementState(element.id);
        
        if (element.visibilityOption === 'hidden-clickable') {
          // Toggle: 0.1 ↔ 1.0 (starts at 0.1)
          const isVisible = currentState.visible !== undefined ? currentState.visible : false;
          this.elementStates = {
            ...this.elementStates,
            [element.id]: {
            ...currentState,
            visible: !isVisible
            }
          };
        } else if (element.visibilityOption === 'shown-clickable') {
          // Toggle: 1.0 ↔ 0.1 (starts at 1.0)
          const isVisible = currentState.visible !== undefined ? currentState.visible : true;
          this.elementStates = {
            ...this.elementStates,
            [element.id]: {
            ...currentState,
            visible: !isVisible
            }
          };
        }
      }
    },
    handleMouseDown(element, event) {
      if (!this.isMoveable(element)) return;

      event.preventDefault();
      this.draggingId = element.id;
      this.dragStartX = event.clientX;
      this.dragStartY = event.clientY;

      const state = this.getElementState(element.id);
      this.elementStartX = state.x !== undefined ? state.x : element.x;
      this.elementStartY = state.y !== undefined ? state.y : element.y;

      document.addEventListener('mousemove', this.handleMouseMove);
      document.addEventListener('mouseup', this.handleMouseUp);
    },
    handleMouseMove(event) {
      if (!this.draggingId) return;

      const deltaX = event.clientX - this.dragStartX;
      const deltaY = event.clientY - this.dragStartY;

      const currentState = this.getElementState(this.draggingId);
      this.elementStates = {
        ...this.elementStates,
        [this.draggingId]: {
          ...currentState,
          x: this.elementStartX + deltaX,
          y: this.elementStartY + deltaY
        }
      };
    },
    handleMouseUp() {
      this.draggingId = null;
      document.removeEventListener('mousemove', this.handleMouseMove);
      document.removeEventListener('mouseup', this.handleMouseUp);
    },
    nextSlide() {
      if (this.currentIndex < this.slides.length - 1) {
        this.currentIndex++;
        this.resetSlideState();
      }
    },
    previousSlide() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
        this.resetSlideState();
      }
    },
    resetSlideState() {
      this.elementStates = {};
      this.draggingId = null;
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
    if (!soundManager.isReady()) {
      soundManager.initialize();
    }
    window.addEventListener('keydown', this.handleKeydown);
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeydown);
    document.removeEventListener('mousemove', this.handleMouseMove);
    document.removeEventListener('mouseup', this.handleMouseUp);
  }
};
</script>

<style scoped>
.slide-presenter-final {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #000;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  z-index: 1000;
  overflow: auto;
}

.present-canvas {
  flex: 0 0 auto;
  width: 100%;
  max-width: 1200px;
  background: #ffffff;
  position: relative;
  margin: 20px;
  border-radius: 8px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}

.present-element {
  user-select: none;
}

.present-element.clickable:hover {
  filter: brightness(1.05);
}

.present-element.moveable {
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
  border-radius: 4px;
}

.present-element.dragging {
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.5);
  z-index: 1000;
}

.element-image,
.element-text {
  pointer-events: none;
}

.element-text {
  padding: 8px 12px;
  min-width: 50px;
  min-height: 30px;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.move-indicator {
  position: absolute;
  top: -28px;
  left: 50%;
  transform: translateX(-50%);
  background: #3b82f6;
  color: white;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.2s;
}

.present-element.moveable:hover .move-indicator {
  opacity: 1;
}

.present-controls {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 10px 16px;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  border-radius: 40px;
  z-index: 2000;
  border: 1px solid rgba(255,255,255,0.1);
}

.nav-btn {
  width: 40px;
  height: 40px;
  background: #4a90e2;
  border: none;
  color: white;
  border-radius: 50%;
  cursor: pointer;
  font-size: 18px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  line-height: 1;
}

.nav-btn:hover:not(:disabled) {
  background: #5a9ff2;
  transform: scale(1.1);
}

.nav-btn:disabled {
  background: #3a3a3a;
  cursor: not-allowed;
  opacity: 0.4;
}

.slide-counter {
  color: white;
  font-size: 14px;
  font-weight: 600;
  min-width: 50px;
  text-align: center;
}

.exit-btn {
  width: 40px;
  height: 40px;
  background: #e24a4a;
  border: none;
  color: white;
  border-radius: 50%;
  cursor: pointer;
  font-size: 16px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  margin-left: 8px;
}

.exit-btn:hover {
  background: #f25a5a;
  transform: scale(1.1);
}
</style>
