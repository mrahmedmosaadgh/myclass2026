<template>
  <div
    v-if="mode === 'edit' || mode === 'visibility' || mode === 'animation' || shouldShowInPresent"
    :class="[
      'slide-element', 
      mode, 
      { 
        'selected': isSelected, 
        'animating': isAnimating,
        'animate__animated animate__bounceIn': isBouncing
      }
    ]"
    :style="elementStyle"
    @mousedown="startDrag"
    @click.stop="selectElement"
  >
    <!-- Animation Indicator Dot (Presentation Mode) - ALWAYS SHOW FOR ANY ANIMATED ELEMENT -->
    <div 
      v-if="mode === 'present' && element.animationOrder"
      :class="['animation-dot', `mode-${dotMode}`, { 'animated': hasBeenAnimated }]"
      :title="getDotTitle()"
      @click.stop="selectElement($event)"
    >
      <span class="dot-icon">
        <span v-if="!hasBeenAnimated" class="order-badge">{{ element.animationOrder }}</span>
        <span v-else>{{ getDotIcon() }}</span>
      </span>
    </div>

    <!-- Image Element -->
    <img 
      v-if="element.type === 'image'" 
      :src="element.src" 
      alt="Slide image"
      class="element-image"
      @dragstart.prevent
      @click.stop="selectElement($event)"
    >

    <!-- Text Element -->
    <div 
      v-else-if="element.type === 'text'"
      class="element-text"
      :contenteditable="mode === 'edit' && isSelected"
      @blur="updateTextContent"
      @mousedown.stop="startDrag"
      @click.stop="selectElement($event)"
      ref="textElement"
    >
      {{ element.content }}
    </div>

    <!-- Controls (Visibility Mode Only) -->
    <div v-if="mode === 'visibility' && isSelected" class="element-controls">
      <!-- Element Actions Dropdown Menu -->
      <q-btn
        dense
        flat
        round
        icon="more_vert"
        class="control-btn actions-menu-btn"
        title="Element actions"
      >
        <q-menu v-model="showActionsMenu" anchor="top end" self="bottom start">
          <q-list style="min-width: 240px">
            <q-item-label header class="text-subtitle2">
              Element Actions
            </q-item-label>
            
            <!-- Visibility Section -->
            <q-item-label header class="text-caption text-grey-7">
              VISIBILITY
            </q-item-label>
            
            <!-- Option 1: Start Hidden, Clickable -->
            <q-item
              clickable
              v-ripple
              :active="element.initialState === 'hidden' && ['fadeIn', 'bounceIn'].includes(element.animation)"
              @click="() => { setStartHiddenClickable(); }"
              v-close-popup
            >
              <q-item-section avatar>
                <q-icon name="visibility_off" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-medium">Start Hidden, Click to Show</q-item-label>
                <q-item-label caption>Element appears on click (Fade/Bounce In)</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon 
                  v-if="element.initialState === 'hidden' && ['fadeIn', 'bounceIn'].includes(element.animation)" 
                  name="check" 
                  color="positive"
                />
              </q-item-section>
            </q-item>

            <!-- Option 2: Start Visible, Clickable -->
            <q-item
              clickable
              v-ripple
              :active="element.initialState === 'visible' && element.animation === 'fadeOut'"
              @click="() => { setStartVisibleClickable(); }"
              v-close-popup
            >
              <q-item-section avatar>
                <q-icon name="visibility" color="accent" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-medium">Start Visible, Click to Hide</q-item-label>
                <q-item-label caption>Element disappears on click (Fade Out)</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon 
                  v-if="element.initialState === 'visible' && element.animation === 'fadeOut'" 
                  name="check" 
                  color="positive"
                />
              </q-item-section>
            </q-item>

            <!-- Option 3: Custom Hidden Opacity -->
            <q-item>
              <q-item-section avatar>
                <q-icon name="opacity" color="warning" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-medium">Custom Hidden Opacity</q-item-label>
                <q-item-label caption>Set transparency when hidden (default: 0.1)</q-item-label>
              </q-item-section>
            </q-item>
            
            <q-item>
              <q-item-section class="q-pa-md">
                <div class="row items-center q-gutter-sm">
                  <q-slider
                    v-model="customOpacityValue"
                    :min="0"
                    :max="1"
                    :step="0.05"
                    label-always
                    color="warning"
                    class="col"
                    @update:model-value="applyCustomOpacity"
                  />
                  <q-input
                    v-model.number="customOpacityValue"
                    type="number"
                    :min="0"
                    :max="1"
                    :step="0.05"
                    dense
                    outlined
                    suffix="%"
                    style="width: 80px"
                    @update:model-value="applyCustomOpacity"
                  >
                    <template v-slot:append>
                      <q-icon name="percent" size="xs" />
                    </template>
                  </q-input>
                </div>
              </q-item-section>
            </q-item>

            <q-separator />
            
            <!-- Delete/Close Section -->
            <q-item-label header class="text-caption text-grey-7">
              ACTIONS
            </q-item-label>
            
            <!-- Delete Element -->
            <q-item 
              clickable 
              v-ripple 
              @click="() => { deleteElement(); }"
              v-close-popup
              class="text-negative"
            >
              <q-item-section avatar>
                <q-icon name="delete" color="negative" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold text-negative">Delete Element</q-item-label>
                <q-item-label caption>Permanently remove this element</q-item-label>
              </q-item-section>
            </q-item>
            
            <!-- Reset Settings -->
            <q-item clickable v-ripple @click="() => { resetVisibility(); }" v-close-popup>
              <q-item-section avatar>
                <q-icon name="refresh" color="grey-7" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Reset Visibility Settings</q-item-label>
                <q-item-label caption>Clear all animation and opacity settings</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-btn>
      
      <!-- Resize Handle -->
      <div class="resize-handle" @mousedown.stop="startResize"></div>
    </div>

    <!-- Animation Badge (Animation Mode) -->
    <div v-if="mode === 'animation'" class="animation-badge">
      <span v-if="element.animation">{{ element.animation }}</span>
      <span v-else class="no-animation">No animation</span>
    </div>

    <!-- Initial State Badge (Animation Mode) -->
    <div v-if="mode === 'animation'" class="state-badge" :class="element.initialState">
      {{ element.initialState === 'visible' ? '👁 Visible' : '🔒 Hidden' }}
    </div>
  </div>
</template>

<script>
import 'animate.css';

export default {
  name: 'SlideElement',
  props: {
    element: {
      type: Object,
      required: true
    },
    mode: {
      type: String,
      required: true
    },
    presentMode: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      isSelected: false,
      isDragging: false,
      isResizing: false,
      dragStartX: 0,
      dragStartY: 0,
      elementStartX: 0,
      elementStartY: 0,
      resizeStartWidth: 0,
      resizeStartHeight: 0,
      isAnimating: false,
      isBouncing: false,
      showActionsMenu: false,
      customOpacityValue: this.element.hiddenOpacity || 0.1
    };
  },
  computed: {
    hasBeenAnimated() {
      if (this.mode !== 'present' || !this.presentMode) return false;
      return this.presentMode.animatedElements?.includes(this.element.id);
    },
    dotMode() {
      // Determine the animation mode based on initial state and animation type
      if (!this.element.animationOrder || !this.element.animation) return null;
      
      // Mode 1: Start Hidden, Click to Show (Fade In or Bounce In)
      if (this.element.initialState === 'hidden' && ['fadeIn', 'bounceIn'].includes(this.element.animation)) {
        return 'show';
      }
      
      // Mode 2: Start Visible, Click to Hide (Fade Out)
      if (this.element.initialState === 'visible' && this.element.animation === 'fadeOut') {
        return 'hide';
      }
      
      return null;
    },
    elementStyle() {
      const style = {
        left: `${this.element.x}px`,
        top: `${this.element.y}px`,
      };

      // Handle width - support pixels, percentages, and auto
      if (this.element.type === 'image') {
        if (typeof this.element.width === 'string') {
          if (this.element.width.includes('%')) {
            style.width = this.element.width; // Use percentage directly
          } else if (this.element.width === 'auto') {
            style.width = 'auto'; // Use natural image width
          } else {
            style.width = `${this.element.width}px`;
          }
        } else {
          style.width = `${this.element.width}px`;
        }
        
        // Handle height - support pixels, percentages, and auto
        if (this.element.height) {
          if (typeof this.element.height === 'string') {
            if (this.element.height.includes('%')) {
              style.height = this.element.height; // Use percentage directly
            } else if (this.element.height === 'auto') {
              style.height = 'auto'; // Use natural image height
            } else {
              style.height = `${this.element.height}px`;
            }
          } else {
            style.height = `${this.element.height}px`;
          }
        }
      } else if (this.element.type === 'text') {
        style.width = 'auto';
        style.maxWidth = `${this.element.width}px`;
      }

      if (this.element.type === 'text') {
        style.fontSize = `${this.element.fontSize}px`;
        style.color = this.element.color;
      }

      // Presentation mode visibility
      if (this.mode === 'present') {
        if (this.presentMode) {
          // Check if element has been clicked/animated
          const hasBeenAnimated = this.presentMode.animatedElements?.includes(this.element.id);
          
          if (this.element.animation === 'fadeIn') {
            if (this.element.initialState === 'hidden' && !hasBeenAnimated) {
              style.opacity = this.element.hiddenOpacity || '0.1';
              style.pointerEvents = this.element.hiddenOpacity > 0 ? 'auto' : 'none';
            } else {
              style.opacity = '1';
              style.pointerEvents = 'auto';
            }
          } else if (this.element.animation === 'fadeOut') {
            if (this.element.initialState === 'visible' && !hasBeenAnimated) {
              style.opacity = '1';
              style.pointerEvents = 'auto';
            } else if (this.element.initialState === 'visible' && hasBeenAnimated) {
              style.opacity = this.element.hiddenOpacity || '0.1';
              style.pointerEvents = this.element.hiddenOpacity > 0 ? 'auto' : 'none';
            } else if (this.element.initialState === 'hidden') {
              // Hidden initially with fadeOut animation - show after click
              if (hasBeenAnimated) {
                style.opacity = '1';
                style.pointerEvents = 'auto';
              } else {
                style.opacity = this.element.hiddenOpacity || '0.1';
                style.pointerEvents = this.element.hiddenOpacity > 0 ? 'auto' : 'none';
              }
            }
          } else {
            // No animation, respect initial state
            if (this.element.initialState === 'hidden') {
              style.opacity = this.element.hiddenOpacity || '0.1';
              style.pointerEvents = this.element.hiddenOpacity > 0 ? 'auto' : 'none';
            } else {
              style.opacity = '1';
              style.pointerEvents = 'auto';
            }
          }
        } else {
          // No presentMode object, use default visibility
          if (this.element.initialState === 'hidden') {
            style.opacity = this.element.hiddenOpacity || '0.1';
            style.pointerEvents = this.element.hiddenOpacity > 0 ? 'auto' : 'none';
          } else {
            style.opacity = '1';
            style.pointerEvents = 'auto';
          }
        }
      }

      return style;
    },
    shouldShowInPresent() {
      if (this.mode !== 'present') return true;
      
      // Always render, but use CSS to control visibility
      return true;
    }
  },
  methods: {
    getDotIcon() {
      if (!this.dotMode) return '';
      
      // Mode 1: Show mode (start hidden, click to show)
      if (this.dotMode === 'show') {
        return this.hasBeenAnimated ? '✅' : '👁';  // Eye when hidden, check when shown
      }
      
      // Mode 2: Hide mode (start visible, click to hide)
      if (this.dotMode === 'hide') {
        return this.hasBeenAnimated ? '❌' : '✨';  // Sparkle when visible, X when hidden
      }
      
      return '';
    },
    getDotTitle() {
      if (!this.dotMode) return '';
      
      if (this.dotMode === 'show') {
        return this.hasBeenAnimated ? 'Click to hide' : 'Click to show';
      }
      
      if (this.dotMode === 'hide') {
        return this.hasBeenAnimated ? 'Click to show' : 'Click to hide';
      }
      
      return '';
    },
    selectElement(event) {
      if (this.mode === 'animation') {
        this.$emit('select', this.element.id);
      }
      if (this.mode === 'present') {
        // Trigger bounce animation on click
        if (this.element.animation) {
          this.isBouncing = true;
          // Remove the animation class after it completes
          setTimeout(() => {
            this.isBouncing = false;
          }, 1000); // Animate.css animations are typically 1s
        }
        // Always emit animate event in present mode when clicked
        this.$emit('animate', this.element.id);
      }
      this.isSelected = true;
    },
    startDrag(event) {
      if (this.mode !== 'edit') return;
      
      this.isDragging = true;
      this.isSelected = true;
      this.dragStartX = event.clientX;
      this.dragStartY = event.clientY;
      this.elementStartX = this.element.x;
      this.elementStartY = this.element.y;

      document.addEventListener('mousemove', this.drag);
      document.addEventListener('mouseup', this.stopDrag);
    },
    drag(event) {
      if (!this.isDragging) return;

      const deltaX = event.clientX - this.dragStartX;
      const deltaY = event.clientY - this.dragStartY;

      const updated = {
        ...this.element,
        x: this.elementStartX + deltaX,
        y: this.elementStartY + deltaY
      };

      this.$emit('update', updated);
    },
    stopDrag() {
      this.isDragging = false;
      document.removeEventListener('mousemove', this.drag);
      document.removeEventListener('mouseup', this.stopDrag);
    },
    startResize(event) {
      if (this.mode !== 'edit') return;

      this.isResizing = true;
      this.dragStartX = event.clientX;
      this.dragStartY = event.clientY;
      this.resizeStartWidth = this.element.width;
      this.resizeStartHeight = this.element.height || 0;

      document.addEventListener('mousemove', this.resize);
      document.addEventListener('mouseup', this.stopResize);
    },
    resize(event) {
      if (!this.isResizing) return;

      const deltaX = event.clientX - this.dragStartX;
      const deltaY = event.clientY - this.dragStartY;

      // Only allow pixel-based resizing in edit mode, not percentages
      const currentWidth = typeof this.resizeStartWidth === 'string' ? 300 : this.resizeStartWidth;
      const currentHeight = typeof this.resizeStartHeight === 'string' ? 200 : this.resizeStartHeight;

      const updated = {
        ...this.element,
        width: Math.max(50, currentWidth + deltaX)
      };

      if (this.element.type === 'image') {
        updated.height = Math.max(50, currentHeight + deltaY);
      }

      this.$emit('update', updated);
    },
    stopResize() {
      this.isResizing = false;
      document.removeEventListener('mousemove', this.resize);
      document.removeEventListener('mouseup', this.stopResize);
    },
    updateTextContent(event) {
      const newContent = event.target.innerText;
      if (newContent !== this.element.content) {
        this.$emit('update', {
          ...this.element,
          content: newContent
        });
      }
    },
    deleteElement() {
      this.$emit('delete', this.element.id);
    },
    
    // Visibility Control Methods
    setStartHiddenClickable() {
      const updated = {
        ...this.element,
        initialState: 'hidden',
        animation: 'fadeIn', // Default to fadeIn
        hiddenOpacity: this.customOpacityValue || 0.1
      };
      this.$emit('update', updated);
      this.showActionsMenu = false;
    },
    
    setStartVisibleClickable() {
      const updated = {
        ...this.element,
        initialState: 'visible',
        animation: 'fadeOut',
        hiddenOpacity: this.customOpacityValue || 0.1
      };
      this.$emit('update', updated);
      this.showActionsMenu = false;
    },
    
    applyCustomOpacity(value) {
      const opacity = Math.max(0, Math.min(1, value));
      const updated = {
        ...this.element,
        hiddenOpacity: opacity
      };
      this.$emit('update', updated);
      this.customOpacityValue = opacity;
    },
    
    resetVisibility() {
      const updated = {
        ...this.element,
        initialState: undefined,
        animation: undefined,
        animationOrder: undefined,
        hiddenOpacity: undefined
      };
      this.$emit('update', updated);
      this.customOpacityValue = 0.1;
      this.showActionsMenu = false;
    }
  }
};
</script>

<style scoped>
.slide-element {
  position: absolute;
  cursor: move;
  transition: opacity 0.5s ease, filter 0.3s ease, transform 0.3s ease;
}

.slide-element.edit {
  outline: 2px solid transparent;
}

.slide-element.edit.selected {
  outline: 2px solid #4a90e2;
  outline-offset: 2px;
}

.slide-element.animation {
  cursor: pointer;
  outline: 2px dashed #6a4ae2;
  outline-offset: 4px;
}

.slide-element.present {
  cursor: pointer;
  transition: opacity 0.5s ease, filter 0.3s ease, transform 0.3s ease;
  /* Clear outline for visibility in presentation mode */
  outline: 2px solid rgba(106, 74, 226, 0.4);  /* More visible */
  outline-offset: 2px;
  position: relative; /* Needed for dot positioning */
}

.slide-element.present:hover {
  filter: brightness(1.15);  /* Brighter on hover */
  transform: scale(1.03);  /* Slightly larger */
  outline-color: rgba(106, 74, 226, 0.7);  /* Brighter on hover */
  box-shadow: 0 4px 12px rgba(106, 74, 226, 0.3);
}

/* Element Text - Make obviously clickable in present mode */
.element-text {
  padding: 8px 12px;
  min-width: 50px;
  min-height: 30px;
  word-wrap: break-word;
  white-space: pre-wrap;
  outline: none;
  background: transparent;
  transition: all 0.3s ease;
}

.slide-element.present .element-text:hover {
  background: rgba(106, 74, 226, 0.08);  /* Light purple tint on hover */
  border-radius: 4px;
}

.slide-element.animating {
  transition: opacity 0.5s ease;
}

/* Fade In Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Fade Out Animation */
@keyframes fadeOut {
  from {
    opacity: 1;
    transform: scale(1);
  }
  to {
    opacity: 0;
    transform: scale(0.95);
  }
}

.slide-element.fade-in {
  animation: fadeIn 0.5s ease forwards;
}

.slide-element.fade-out {
  animation: fadeOut 0.5s ease forwards;
}

/* Animation Indicator Dot */
.animation-dot {
  position: absolute;
  top: -35px;
  left: 50%;
  transform: translateX(-50%);
  width: 40px;  /* Even bigger for easy clicking */
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  cursor: pointer;
  pointer-events: auto;
  transition: all 0.3s ease;
  z-index: 10;
  /* HIGH VISIBILITY - Easy to see and click */
  opacity: 0.8;  /* Much more visible - 80% opacity! */
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

/* Hidden initial state - starts invisible, click to show */
.animation-dot.mode-show {
  background: #6a4ae2;  /* Purple when waiting to show */
  box-shadow: 0 3px 12px rgba(106, 74, 226, 0.6);
}

.animation-dot.mode-show.animated {
  background: #4ade80;  /* Green after shown */
  box-shadow: 0 3px 12px rgba(74, 222, 128, 0.7);
  opacity: 1;  /* Fully visible after clicked */
}

/* Visible initial state - starts visible, click to hide */
.animation-dot.mode-hide {
  background: #fb923c;  /* Orange when waiting to hide */
  box-shadow: 0 3px 12px rgba(251, 146, 60, 0.6);
}

.animation-dot.mode-hide.animated {
  background: #ef4444;  /* Red after hidden */
  box-shadow: 0 3px 12px rgba(239, 68, 68, 0.7);
  opacity: 1;  /* Fully visible after clicked */
}

.animation-dot:hover {
  transform: translateX(-50%) scale(1.25);  /* Even bigger on hover */
  opacity: 1 !important;  /* Full opacity on hover */
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.5);
}

.dot-icon {
  user-select: none;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.order-badge {
  background: rgba(255, 255, 255, 1);  /* Solid white background */
  color: #6a4ae2;
  font-weight: 800;  /* Bolder number */
  font-size: 16px;  /* Larger number */
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}

.element-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
  pointer-events: none;
}

.element-text {
  padding: 8px 12px;
  min-width: 50px;
  min-height: 30px;
  word-wrap: break-word;
  white-space: pre-wrap;
  outline: none;
  background: transparent;
}

.element-text[contenteditable="true"] {
  background: rgba(74, 144, 226, 0.1);
  border-radius: 4px;
}

.element-controls {
  position: absolute;
  top: -12px;
  right: -12px;
  display: flex;
  gap: 4px;
}

.control-btn {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: none;
  background: #e24a4a;
  color: white;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  padding: 0;
}

.control-btn:hover {
  background: #f25a5a;
}

.control-btn.actions-menu-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid #c9e4ca;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #5a7d5a;
  transition: all 0.2s ease;
  outline: none;
  padding: 0;
}

.control-btn.actions-menu-btn:hover {
  background: #e8f5e9;
  border-color: #4caf50;
  color: #2e7d32;
  transform: scale(1.1);
}

.resize-handle {
  position: absolute;
  bottom: -12px;
  right: -12px;
  width: 16px;
  height: 16px;
  background: #4a90e2;
  border: 2px solid white;
  border-radius: 50%;
  cursor: nwse-resize;
}

.resize-handle:hover {
  background: #5a9ff2;
}

.animation-badge {
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  background: #6a4ae2;
  color: white;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  pointer-events: none;
}

.no-animation {
  opacity: 0.6;
}

.state-badge {
  position: absolute;
  bottom: -30px;
  left: 50%;
  transform: translateX(-50%);
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  pointer-events: none;
}

.state-badge.visible {
  background: #4ade80;
  color: #064e3b;
}

.state-badge.hidden {
  background: #fb923c;
  color: #431407;
}
</style>
