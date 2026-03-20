<template>
  <div
    :class="['slide-element-v2', { 'clickable': element.clickable, 'pinned': isPinned }]"
    :style="elementStyle"
    @click="handleClick"
  >
    <!-- Pin Button (Presentation Mode) -->
    <button 
      v-if="element.clickable"
      @click.stop="handlePinClick"
      class="pin-button"
      :title="isPinned ? 'Unpin' : 'Pin to top'"
    >
      {{ isPinned ? '📌 Unpin' : '📍 Pin' }}
    </button>

    <!-- Position Dot Indicator for Hidden Elements -->
    <div 
      v-if="showPositionDot"
      class="position-dot-presenter"
      :style="{
        left: '50%',
        top: '50%',
        transform: 'translate(-50%, -50%)'
      }"
      @click.stop="handleClick"
    >
      <div class="dot"></div>
    </div>

    <!-- Image Element -->
    <img 
      v-if="element.type === 'image'" 
      :src="element.src" 
      alt="Slide element"
      class="element-content"
    >

    <!-- Text Element -->
    <div 
      v-else-if="element.type === 'text'"
      class="element-content text-content"
    >
      {{ element.content }}
    </div>
  </div>
</template>

<script>
import { soundManager } from '@/Services/SoundManager';

export default {
  name: 'SlideElementV2',
  props: {
    element: {
      type: Object,
      required: true
    },
    presentState: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      showPositionDot: false, // Will be calculated in mounted
      isPinned: false // Track if this element is pinned
    };
  },
  computed: {
    elementStyle() {
      const style = {
        position: 'absolute',
        left: `${this.element.x}px`,
        top: `${this.element.y}px`,
        width: this.element.type === 'image' ? `${this.element.width}px` : 'auto',
        maxWidth: this.element.type === 'text' ? `${this.element.width}px` : 'none',
        transition: 'opacity 0.4s ease'
      };

      if (this.element.type === 'image' && this.element.height) {
        style.height = `${this.element.height}px`;
      }

      if (this.element.type === 'text') {
        style.fontSize = `${this.element.fontSize}px`;
        style.color = this.element.color;
      }

      // Determine current opacity
      const isToggled = this.presentState.toggledElements[this.element.id];
      
      // Determine current opacity based on custom hiddenOpacity value
      const hiddenOpacity = (this.element.hiddenOpacity || 10) / 100;
      
      if (isToggled === undefined) {
        // No toggle yet - use initial state
        style.opacity = this.element.startHidden ? hiddenOpacity : 1;
      } else {
        // Has been toggled - use toggled state
        style.opacity = isToggled ? 1 : hiddenOpacity;
      }

      // Add cursor style for clickable elements
      if (this.element.clickable) {
        style.cursor = 'pointer';
      }

      return style;
    }
  },
  methods: {
    handlePinClick() {
      // Toggle pin state
      this.isPinned = !this.isPinned;
      
      // Emit pin event to parent with element data
      this.$emit('pin', this.element);
    },
    handleClick(event) {
      event.stopPropagation();
      if (this.element.clickable) {
        // Play click sound
        if (soundManager.isReady()) {
          soundManager.playClick(0.5); // 50% volume
        }
        this.$emit('toggle', this.element.id);
      }
    },
    updatePositionDotVisibility() {
      // Show dot if element is hidden (either initially or toggled)
      const isToggled = this.presentState.toggledElements[this.element.id];
      
      if (isToggled === undefined) {
        // No toggle yet - use initial state
        this.showPositionDot = this.element.startHidden;
      } else {
        // Has been toggled - show dot if currently hidden
        this.showPositionDot = !isToggled;
      }
    }
  },
  mounted() {
    // Ensure sound manager is initialized
    if (!soundManager.isReady()) {
      soundManager.initialize();
    }
    // Calculate initial dot visibility
    this.updatePositionDotVisibility();
  },
  updated() {
    // Update dot visibility when state changes
    this.updatePositionDotVisibility();
  }
};
</script>

<style scoped>
.slide-element-v2 {
  user-select: none;
  position: relative;
}

.slide-element-v2.clickable:hover {
  filter: brightness(1.05);
}

.slide-element-v2.pinned {
  outline: 2px solid #f59e0b;
  outline-offset: 2px;
}

/* Pin Button */
.pin-button {
  position: absolute;
  top: -10px;
  right: -10px;
  z-index: 100;
  padding: 6px 10px;
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  border: 2px solid #fff;
  border-radius: 20px;
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  transition: all 0.2s ease;
  white-space: nowrap;
}

.pin-button:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 16px rgba(251, 191, 36, 0.5);
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.pin-button:active {
  transform: scale(0.95);
}

.position-dot-presenter {
  position: absolute;
  pointer-events: all;
  z-index: 10;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dot {
  width: 14px;
  height: 14px;
  background: radial-gradient(circle, #3b82f6 0%, #1d4ed8 100%);
  border-radius: 50%;
  opacity: 0.7;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.5), inset 0 1px 3px rgba(255, 255, 255, 0.3);
  animation: pulse-dot-presenter 2s infinite;
  transition: all 0.2s ease;
}

.dot:hover {
  transform: scale(1.3);
  opacity: 1;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.8), inset 0 1px 3px rgba(255, 255, 255, 0.4);
}

@keyframes pulse-dot-presenter {
  0%, 100% {
    transform: scale(1);
    opacity: 0.7;
  }
  50% {
    transform: scale(1.15);
    opacity: 0.9;
  }
}

.element-content {
  pointer-events: none;
  display: block;
}

.element-content.text-content {
  padding: 8px 12px;
  min-width: 50px;
  min-height: 30px;
  word-wrap: break-word;
  white-space: pre-wrap;
  pointer-events: none;
}
</style>
