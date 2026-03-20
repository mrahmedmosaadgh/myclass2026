<template>
  <div class="animation-editor">
    <div class="slide-canvas">
      <!-- Hidden element placeholders (dots) -->
      <div 
        v-for="element in slide.elements.filter(el => el.initialState === 'hidden')"
        :key="'placeholder-' + element.id"
        class="element-placeholder"
        :style="{
          left: element.x + 'px',
          top: element.y + 'px'
        }"
        @click.stop="selectElement(element.id)"
      >
        <div class="placeholder-dot">
          <span class="animation-number" v-if="element.animationOrder">{{ element.animationOrder }}</span>
        </div>
      </div>

      <!-- Actual elements -->
      <SlideElement
        v-for="element in slide.elements"
        :key="element.id"
        :element="element"
        :mode="'animation'"
        @select="selectElement"
      />
    </div>

    <!-- Animation Panel -->
    <div class="animation-panel">
      <h3>Animation Sequence</h3>
      
      <!-- Animation sequence list -->
      <div class="animation-sequence">
        <div 
          v-for="(animElement, index) in animatedElements" 
          :key="animElement.id"
          :class="['sequence-item', selectedElementId === animElement.id ? 'selected' : '']"
          @click="selectElement(animElement.id)"
        >
          <div class="sequence-number">{{ index + 1 }}</div>
          <div class="sequence-info">
            <div class="sequence-title">
              {{ animElement.type === 'text' ? truncate(animElement.content, 30) : 'Image' }}
            </div>
            <div class="sequence-animation">{{ getAnimationLabel(animElement) }}</div>
          </div>
          <div class="sequence-actions">
            <button @click.stop="moveUp(index)" :disabled="index === 0" class="move-btn">↑</button>
            <button @click.stop="moveDown(index)" :disabled="index === animatedElements.length - 1" class="move-btn">↓</button>
            <button @click.stop="removeAnimation(animElement.id)" class="remove-btn">×</button>
          </div>
        </div>

        <div v-if="animatedElements.length === 0" class="empty-sequence">
          <p>No animations yet. Click elements on the slide to add animations.</p>
        </div>
      </div>

      <!-- Selected element settings -->
      <div v-if="selectedElement" class="selected-settings">
        <h4>{{ selectedElement.type === 'text' ? 'Text' : 'Image' }} Animation</h4>
        
        <div class="setting-group">
          <label>Animation Type</label>
          <div class="button-group vertical">
            <button 
              @click="setAnimation('fadeIn')"
              :class="['anim-btn', hasAnimation(selectedElement, 'fadeIn') ? 'active' : '']"
            >
              ✨ Fade In (Show on Click)
            </button>
            <button 
              @click="setAnimation('fadeOut')"
              :class="['anim-btn', hasAnimation(selectedElement, 'fadeOut') ? 'active' : '']"
            >
              💫 Fade Out (Hide on Click)
            </button>
            <button 
              @click="removeAnimation(selectedElement.id)"
              :class="['anim-btn', !selectedElement.animationOrder ? 'active' : '']"
            >
              🚫 No Animation
            </button>
          </div>
        </div>

        <div class="info-box">
          <p><strong>💡 How it works:</strong></p>
          <p v-if="hasAnimation(selectedElement, 'fadeIn')">
            This element will start <strong>hidden</strong> (shown as dot #{{ selectedElement.animationOrder }}). 
            Click the dot during presentation to fade it in.
          </p>
          <p v-else-if="hasAnimation(selectedElement, 'fadeOut')">
            This element will start <strong>visible</strong> (dot #{{ selectedElement.animationOrder }}). 
            Click the dot during presentation to fade it out.
          </p>
          <p v-else>
            This element has no animation and will always be visible.
          </p>
          <p style="margin-top: 12px; color: #4ade80;">
            <strong>✨ Pro Tip:</strong> Use the ↑↓ buttons to reorder when elements appear. Lower numbers appear first!
          </p>
        </div>
      </div>

      <div v-else class="no-selection-hint">
        <p>👆 Click an element on the slide to configure its animation</p>
      </div>
    </div>
  </div>
</template>

<script>
import SlideElement from './SlideElement.vue';

export default {
  name: 'AnimationEditor',
  components: {
    SlideElement
  },
  props: {
    slide: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      selectedElementId: null
    };
  },
  computed: {
    selectedElement() {
      if (!this.selectedElementId) return null;
      return this.slide.elements.find(el => el.id === this.selectedElementId);
    },
    animatedElements() {
      return this.slide.elements
        .filter(el => el.animationOrder)
        .sort((a, b) => a.animationOrder - b.animationOrder);
    }
  },
  methods: {
    selectElement(elementId) {
      this.selectedElementId = elementId;
    },
    setAnimation(animationType) {
      if (!this.selectedElement) return;

      const maxOrder = Math.max(0, ...this.slide.elements.map(el => el.animationOrder || 0));
      const currentHasOrder = this.selectedElement.animationOrder;

      const updatedElements = this.slide.elements.map(el => {
        if (el.id === this.selectedElementId) {
          return {
            ...el,
            animation: animationType,
            initialState: animationType === 'fadeIn' ? 'hidden' : 'visible',
            animationOrder: currentHasOrder || maxOrder + 1
          };
        }
        return el;
      });

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });
    },
    removeAnimation(elementId) {
      const element = this.slide.elements.find(el => el.id === elementId);
      if (!element) return;

      const removedOrder = element.animationOrder;

      const updatedElements = this.slide.elements.map(el => {
        if (el.id === elementId) {
          return {
            ...el,
            animation: null,
            initialState: 'visible',
            animationOrder: null
          };
        }
        // Reorder remaining animations
        if (removedOrder && el.animationOrder && el.animationOrder > removedOrder) {
          return {
            ...el,
            animationOrder: el.animationOrder - 1
          };
        }
        return el;
      });

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });

      if (elementId === this.selectedElementId) {
        this.selectedElementId = null;
      }
    },
    moveUp(index) {
      if (index === 0) return;
      this.swapAnimationOrder(index, index - 1);
    },
    moveDown(index) {
      if (index === this.animatedElements.length - 1) return;
      this.swapAnimationOrder(index, index + 1);
    },
    swapAnimationOrder(index1, index2) {
      const elements = [...this.animatedElements];
      const el1 = elements[index1];
      const el2 = elements[index2];

      const updatedElements = this.slide.elements.map(el => {
        if (el.id === el1.id) {
          return { ...el, animationOrder: index2 + 1 };
        }
        if (el.id === el2.id) {
          return { ...el, animationOrder: index1 + 1 };
        }
        return el;
      });

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });
    },
    hasAnimation(element, type) {
      return element && element.animation === type;
    },
    getAnimationLabel(element) {
      if (element.animation === 'fadeIn') return '✨ Fade In';
      if (element.animation === 'fadeOut') return '💫 Fade Out';
      return 'None';
    },
    truncate(text, length) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    }
  }
};
</script>

<style scoped>
.animation-editor {
  display: flex;
  gap: 20px;
  width: 100%;
  max-width: 1400px;
}

.slide-canvas {
  flex: 0 0 900px;
  height: 506px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  position: relative;
  overflow: hidden;
}

.element-placeholder {
  position: absolute;
  pointer-events: all;
  z-index: 1000;
  cursor: pointer;
}

.placeholder-dot {
  width: 24px;
  height: 24px;
  background: linear-gradient(135deg, #6a4ae2, #8b5cf6);
  border: 3px solid #ffffff;
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(106, 74, 226, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse 2s infinite;
  position: relative;
}

.placeholder-dot::before {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 2px solid #6a4ae2;
  animation: ripple 2s infinite;
}

.animation-number {
  color: white;
  font-size: 11px;
  font-weight: 700;
  z-index: 1;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

@keyframes ripple {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animation-panel {
  flex: 1;
  background: #2a2a2a;
  border-radius: 8px;
  padding: 20px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.animation-panel h3 {
  margin: 0;
  color: #fff;
  font-size: 20px;
  font-weight: 700;
}

.animation-panel h4 {
  margin: 0 0 16px 0;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
}

.animation-sequence {
  background: #1a1a1a;
  border-radius: 8px;
  padding: 12px;
  min-height: 200px;
  max-height: 300px;
  overflow-y: auto;
}

.sequence-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #2a2a2a;
  border: 2px solid #3a3a3a;
  border-radius: 6px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.sequence-item:hover {
  border-color: #6a4ae2;
  background: #323232;
}

.sequence-item.selected {
  border-color: #6a4ae2;
  background: #3a3a4a;
}

.sequence-number {
  width: 28px;
  height: 28px;
  background: linear-gradient(135deg, #6a4ae2, #8b5cf6);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  flex-shrink: 0;
}

.sequence-info {
  flex: 1;
  min-width: 0;
}

.sequence-title {
  color: #fff;
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 4px;
}

.sequence-animation {
  color: #aaa;
  font-size: 12px;
}

.sequence-actions {
  display: flex;
  gap: 4px;
  flex-shrink: 0;
}

.move-btn,
.remove-btn {
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.move-btn {
  background: #3a3a3a;
  color: #fff;
}

.move-btn:hover:not(:disabled) {
  background: #4a4a4a;
}

.move-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.remove-btn {
  background: #e24a4a;
  color: white;
}

.remove-btn:hover {
  background: #f25a5a;
}

.empty-sequence {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 150px;
  color: #666;
  font-size: 14px;
  text-align: center;
  padding: 20px;
}

.selected-settings {
  background: #1a1a1a;
  border-radius: 8px;
  padding: 16px;
}

.setting-group {
  margin-bottom: 20px;
}

.setting-group label {
  display: block;
  margin-bottom: 8px;
  color: #aaa;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.button-group {
  display: flex;
  gap: 8px;
}

.button-group.vertical {
  flex-direction: column;
}

.state-btn,
.anim-btn {
  flex: 1;
  padding: 12px 16px;
  background: #2a2a2a;
  border: 2px solid #3a3a3a;
  color: #fff;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
  text-align: left;
}

.state-btn:hover,
.anim-btn:hover {
  background: #3a3a3a;
  border-color: #4a4a4a;
}

.state-btn.active {
  background: #4ade80;
  border-color: #4ade80;
  color: #064e3b;
}

.anim-btn.active {
  background: #6a4ae2;
  border-color: #7a5af2;
  color: #fff;
}

.info-box {
  background: #2a2a2a;
  border-left: 4px solid #6a4ae2;
  padding: 16px;
  border-radius: 6px;
  margin-top: 16px;
}

.info-box p {
  margin: 0 0 8px 0;
  color: #ccc;
  font-size: 13px;
  line-height: 1.6;
}

.info-box p:last-child {
  margin-bottom: 0;
}

.info-box strong {
  color: #fff;
}

.no-selection {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #2a2a2a;
  border-radius: 8px;
  border: 2px dashed #4a4a4a;
}

.no-selection p {
  color: #aaa;
  font-size: 16px;
  text-align: center;
  padding: 40px;
}

.no-selection-hint {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #1a1a1a;
  border-radius: 8px;
  border: 2px dashed #3a3a3a;
  min-height: 200px;
}

.no-selection-hint p {
  color: #666;
  font-size: 14px;
  text-align: center;
  padding: 40px;
}
</style>
