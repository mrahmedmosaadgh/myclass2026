<template>
  <div class="presentation-mode-container">
    <!-- Presentation Controls -->
    <div
      class="presentation-controls"
      :class="{ 'hidden': controlsHidden && controlsLocked }"
      @mouseenter="showControls"
      @mouseleave="autoHideControls"
    >
      <div class="slide-navigation">
        <button @click="previousSlide" :disabled="currentIndex === 0" class="nav-btn">
          <LucideIcon name="chevron-left" size="20" />
        </button>
        <span class="slide-counter">{{ currentIndex + 1 }} / {{ slides.length }}</span>
        <button @click="nextSlide" :disabled="currentIndex === slides.length - 1" class="nav-btn">
          <LucideIcon name="chevron-right" size="20" />
        </button>
      </div>

      <div class="control-buttons">
        <button @click="toggleControlsLock" class="control-btn" :class="{ 'active': controlsLocked }" title="Lock controls visibility">
          <LucideIcon :name="controlsLocked ? 'lock' : 'unlock'" size="18" />
        </button>
        <button @click="toggleFullscreen" class="control-btn" title="Toggle fullscreen">
          <LucideIcon :name="isFullscreen ? 'minimize' : 'maximize'" size="18" />
        </button>
        <button @click="exitPresentation" class="exit-btn">
          <LucideIcon name="x" size="18" class="mr-1" />
          <span>Exit</span>
        </button>
      </div>
    </div>

    <!-- Show Controls Button (appears when controls are hidden and locked) -->
    <button
      v-if="controlsHidden && controlsLocked"
      @click="showControlsTemporarily"
      class="show-controls-btn"
      title="Show controls"
    >
      <LucideIcon name="menu" size="20" />
    </button>

    <!-- Slide Display -->
    <div class="presentation-view">
      <div class="slide-container">
        <CanvasEditor
          v-if="currentSlide"
          :key="currentIndex"
          v-model="currentSlide.content"
          :presentationMode="true"
          :readonly="true"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import CanvasEditor from './CanvasEditor.vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';

const props = defineProps({
  slides: {
    type: Array,
    required: true
  },
  initialIndex: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['exit']);

const currentIndex = ref(props.initialIndex);
const controlsHidden = ref(false);
const controlsLocked = ref(false);
const isFullscreen = ref(false);
const hideControlsTimeout = ref(null);

const currentSlide = computed(() => props.slides[currentIndex.value]);

const nextSlide = () => {
  if (currentIndex.value < props.slides.length - 1) {
    currentIndex.value++;
  }
};

const previousSlide = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--;
  }
};

const showControls = () => {
  controlsHidden.value = false;
  if (hideControlsTimeout.value) {
    clearTimeout(hideControlsTimeout.value);
    hideControlsTimeout.value = null;
  }
};

const autoHideControls = () => {
  if (!controlsLocked.value) {
    hideControlsTimeout.value = setTimeout(() => {
      controlsHidden.value = true;
    }, 3000);
  }
};

const showControlsTemporarily = () => {
  controlsHidden.value = false;
  hideControlsTimeout.value = setTimeout(() => {
    if (controlsLocked.value) {
      controlsHidden.value = true;
    }
  }, 5000);
};

const toggleControlsLock = () => {
  controlsLocked.value = !controlsLocked.value;
  if (controlsLocked.value) {
    // If we're locking the controls, make sure they're visible for a moment
    controlsHidden.value = false;
    // Then hide them after a delay if that's the desired state
    hideControlsTimeout.value = setTimeout(() => {
      controlsHidden.value = true;
    }, 3000);
  } else {
    // If we're unlocking, make sure they stay visible
    controlsHidden.value = false;
  }
};

const toggleFullscreen = async () => {
  try {
    if (!document.fullscreenElement) {
      await document.documentElement.requestFullscreen();
      isFullscreen.value = true;
    } else {
      await document.exitFullscreen();
      isFullscreen.value = false;
    }
  } catch (err) {
    console.error('Error toggling fullscreen:', err);
  }
};

const exitPresentation = async () => {
  if (document.fullscreenElement) {
    await document.exitFullscreen();
  }
  emit('exit');
};

const handleKeyPress = (event) => {
  switch (event.key) {
    case 'ArrowRight':
    case 'Space':
      nextSlide();
      break;
    case 'ArrowLeft':
      previousSlide();
      break;
    case 'Escape':
      exitPresentation();
      break;
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeyPress);
  document.documentElement.requestFullscreen()
    .then(() => {
      isFullscreen.value = true;
    })
    .catch((e) => console.log('Fullscreen error:', e));

  // Auto-hide controls after a delay
  hideControlsTimeout.value = setTimeout(() => {
    controlsHidden.value = true;
  }, 3000);

  // Listen for fullscreen changes
  document.addEventListener('fullscreenchange', () => {
    isFullscreen.value = !!document.fullscreenElement;
  });
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyPress);
  document.removeEventListener('fullscreenchange', () => {});
  if (hideControlsTimeout.value) {
    clearTimeout(hideControlsTimeout.value);
  }
});
</script>

<style scoped>
.presentation-mode-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: #000;
  z-index: 1000;
}

.presentation-controls {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  display: flex;
  justify-content: space-between;
  gap: 20px;
  align-items: center;
  background: rgba(15, 23, 42, 0.8);
  padding: 12px 20px;
  border-radius: 12px;
  backdrop-filter: blur(8px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease, opacity 0.5s ease;
  opacity: 0.95;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.presentation-controls.hidden {
  opacity: 0;
  transform: translate(-50%, 100px);
  pointer-events: none;
}

.presentation-controls:hover {
  opacity: 1;
  transform: translateX(-50%) scale(1.02);
}

.control-buttons {
  display: flex;
  align-items: center;
  gap: 10px;
}

.slide-navigation {
  display: flex;
  align-items: center;
  gap: 15px;
  background: rgba(0, 0, 0, 0.2);
  padding: 6px 12px;
  border-radius: 8px;
}

.nav-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  cursor: pointer;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.nav-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.05);
}

.nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.slide-counter {
  color: white;
  font-size: 0.9em;
  min-width: 70px;
  text-align: center;
  font-weight: 500;
  background: rgba(0, 0, 0, 0.3);
  padding: 4px 10px;
  border-radius: 6px;
}

.control-btn {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.control-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.05);
}

.control-btn.active {
  background: rgba(99, 102, 241, 0.6);
  color: white;
}

.exit-btn {
  background: rgba(239, 68, 68, 0.8);
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.exit-btn:hover {
  background: rgba(220, 38, 38, 0.9);
  transform: scale(1.05);
}

.show-controls-btn {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  background: rgba(15, 23, 42, 0.6);
  color: white;
  border: none;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  opacity: 0.5;
}

.show-controls-btn:hover {
  opacity: 1;
  transform: translateX(-50%) scale(1.1);
  background: rgba(15, 23, 42, 0.8);
}

.presentation-view {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: white;
}

.slide-container {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>

