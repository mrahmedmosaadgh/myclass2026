<template>
  <div class="slide-presenter">
    <div class="present-canvas">
      <SlideElement
        v-for="element in currentSlide.elements"
        :key="element.id"
        :element="element"
        :mode="'present'"
        :present-mode="presentState"
        @animate="handleAnimate"
      />
    </div>

    <!-- Navigation Controls -->
    <div class="present-controls">
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
      Use ← → arrow keys to navigate | Click elements to trigger animations | ESC to exit
    </div>
  </div>
</template>

<script>
import SlideElement from './SlideElement.vue';

export default {
  name: 'SlidePresenter',
  components: {
    SlideElement
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
        animatedElements: []
      }
    };
  },
  computed: {
    currentSlide() {
      return this.slides[this.currentIndex];
    }
  },
  methods: {
    handleAnimate(elementId) {
      const element = this.currentSlide.elements.find(el => el.id === elementId);
      if (!element) return;

      console.log('Element clicked:', elementId, 'Animation:', element.animation, 'Initial state:', element.initialState);

      // Toggle animation state
      if (this.presentState.animatedElements.includes(elementId)) {
        // Remove from animated list (for toggle behavior)
        this.presentState.animatedElements = this.presentState.animatedElements.filter(
          id => id !== elementId
        );
        console.log('Removed from animated:', elementId);
      } else {
        // Add to animated list
        this.presentState.animatedElements.push(elementId);
        console.log('Added to animated:', elementId);
      }

      // Force reactivity
      this.presentState = { ...this.presentState };
      console.log('Updated presentState:', this.presentState);
    },
    nextSlide() {
      if (this.currentIndex < this.slides.length - 1) {
        this.currentIndex++;
        this.resetAnimations();
      }
    },
    previousSlide() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
        this.resetAnimations();
      }
    },
    resetAnimations() {
      this.presentState = {
        animatedElements: []
      };
    },
    exitPresentation() {
      this.$emit('exit');
    },
    handleKeydown(event) {
      if (event.key === 'ArrowRight') {
        this.nextSlide();
      } else if (event.key === 'ArrowLeft') {
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
.slide-presenter {
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

.present-canvas {
  flex: 1;
  width: 100%;
  max-width: 1200px;
  max-height: 675px;
  background: #ffffff;
  position: relative;
  margin: 20px;
  border-radius: 8px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
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
  min-width: 80px;
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
}
</style>
