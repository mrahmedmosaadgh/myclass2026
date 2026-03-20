<template>
  <div class="slide-presenter" @click="handleSlideClick">
    <div class="present-canvas">
      <SlideElementSimple
        v-for="element in currentSlide.elements"
        :key="element.id"
        :element="element"
        :mode="'present'"
        :present-mode="presentState"
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
        <span v-if="totalClicks > 0" class="click-counter">
          (Click {{ currentClickStep }} / {{ totalClicks }})
        </span>
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
      Click or press SPACE to show/hide elements | Use ← → arrow keys to navigate slides | ESC to exit
    </div>
  </div>
</template>

<script>
import SlideElementSimple from './SlideElementSimple.vue';

export default {
  name: 'SlidePresenterSimple',
  components: {
    SlideElementSimple
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
        clickedElements: [],
        currentClickIndex: 0
      }
    };
  },
  computed: {
    currentSlide() {
      return this.slides[this.currentIndex];
    },
    sortedClicks() {
      return this.currentSlide.elements
        .filter(el => el.clickOrder)
        .sort((a, b) => a.clickOrder - b.clickOrder);
    },
    totalClicks() {
      return this.sortedClicks.length;
    },
    currentClickStep() {
      return this.presentState.currentClickIndex;
    }
  },
  methods: {
    handleSlideClick(event) {
      if (event.target.closest('.present-controls')) return;
      this.advanceClick();
    },
    advanceClick() {
      if (this.presentState.currentClickIndex < this.totalClicks) {
        const nextClick = this.sortedClicks[this.presentState.currentClickIndex];
        
        if (nextClick) {
          this.presentState.clickedElements.push(nextClick.id);
          this.presentState.currentClickIndex++;
          this.presentState = { ...this.presentState };
        }
      } else {
        // All clicks complete, go to next slide
        this.nextSlide();
      }
    },
    nextSlide() {
      if (this.currentIndex < this.slides.length - 1) {
        this.currentIndex++;
        this.resetClicks();
      }
    },
    previousSlide() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
        this.resetClicks();
      }
    },
    resetClicks() {
      this.presentState = {
        clickedElements: [],
        currentClickIndex: 0
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
      } else if (event.key === ' ' || event.key === 'Spacebar') {
        event.preventDefault();
        this.advanceClick();
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
  min-width: 120px;
  text-align: center;
}

.click-counter {
  display: block;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  margin-top: 4px;
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
