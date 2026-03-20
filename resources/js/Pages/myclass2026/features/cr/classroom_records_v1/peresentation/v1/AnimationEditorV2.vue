<template>
  <div class="animation-editor-v2">
    <!-- Settings Toggle -->
    <div class="editor-settings-bar">
      <label class="toggle-setting">
        <input type="checkbox" v-model="showPositionDots">
        <span class="toggle-label">📍 Show Position Dots for Hidden Elements</span>
      </label>
    </div>

    <div class="slide-canvas">
      <!-- Visual arrows/indicators for ALL elements -->
      <div 
        v-for="element in slide.elements"
        :key="'indicator-' + element.id"
        class="element-indicator"
        :style="{
          left: (element.x - 30) + 'px',
          top: element.y + 'px'
        }"
        @click.stop="selectElement(element.id)"
      >
        <div class="arrow-indicator">→</div>
      </div>

      <!-- Actual elements with semi-transparent overlay for hidden ones -->
      <div
        v-for="element in slide.elements"
        :key="element.id"
        :class="['element-wrapper', { 'selected': selectedElementId === element.id, 'will-be-hidden': element.startHidden }]"
        :style="{
          left: element.x + 'px',
          top: element.y + 'px',
          width: element.type === 'image' ? element.width + 'px' : 'auto',
          maxWidth: element.type === 'text' ? element.width + 'px' : 'none',
          opacity: getPreviewOpacity(element)
        }"
        @click.stop="selectElement(element.id)"
      >
        <!-- Position Dot Indicator for Hidden Elements -->
        <div 
          v-if="showPositionDots && element.startHidden"
          class="position-dot-indicator"
          :style="{
            left: (element.width / 2) + 'px',
            top: '50%',
            transform: 'translate(-50%, -50%)'
          }"
          @click.stop="selectElement(element.id)"
        >
          <div class="position-dot"></div>
        </div>
        <img 
          v-if="element.type === 'image'" 
          :src="element.src" 
          alt="Element"
          class="element-content"
          :style="{ width: element.width + 'px', height: element.height + 'px' }"
        >
        <div 
          v-else-if="element.type === 'text'"
          class="element-content text-content"
          :style="{ fontSize: element.fontSize + 'px', color: element.color }"
        >
          {{ element.content }}
        </div>

        <!-- Hidden badge -->
        <div v-if="element.startHidden" class="hidden-badge">
          Will start HIDDEN ({{ getHiddenOpacityText(element) }} opacity)
        </div>
        
        <!-- Visible badge -->
        <div v-else class="visible-badge">
          Will start VISIBLE (100%)
        </div>
      </div>
    </div>

    <!-- Control Panel -->
    <div class="control-panel">
      <h3>Element Visibility Settings</h3>
      
      <div v-if="selectedElement" class="element-settings">
        <div class="element-preview">
          <div class="preview-label">Selected Element:</div>
          <div class="preview-name">
            {{ selectedElement.type === 'text' ? truncate(selectedElement.content, 40) : 'Image' }}
          </div>
        </div>

        <div class="setting-group">
          <label>Initial State When Presentation Starts</label>
          <div class="button-group">
            <button 
              @click="setStartVisibility(false)"
              :class="['visibility-btn', 'visible-btn', !selectedElement.startHidden ? 'active' : '']"
            >
              <span class="btn-icon">👁</span>
              <div class="btn-content">
                <div class="btn-title">Start Visible</div>
                <div class="btn-desc">100% opacity when slide loads</div>
              </div>
            </button>
            <button 
              @click="setStartVisibility(true)"
              :class="['visibility-btn', 'hidden-btn', selectedElement.startHidden ? 'active' : '']"
            >
              <span class="btn-icon">👻</span>
              <div class="btn-content">
                <div class="btn-title">Start Hidden</div>
                <div class="btn-desc">Custom opacity when slide loads</div>
              </div>
            </button>
          </div>
        </div>

        <div v-if="selectedElement.startHidden" class="setting-group">
          <label>Hidden Opacity Level</label>
          <div class="opacity-selector">
            <input 
              type="range" 
              min="0" 
              max="50" 
              step="5"
              v-model.number="hiddenOpacityValue"
              @input="updateHiddenOpacity"
              class="opacity-slider"
            >
            <div class="opacity-value-display">
              {{ hiddenOpacityValue }}% opacity
            </div>
            <div class="opacity-preview">
              <span>Preview:</span>
              <div 
                class="opacity-preview-box"
                :style="{ opacity: hiddenOpacityValue / 100 }"
              >
                {{ selectedElement.type === 'text' ? truncate(selectedElement.content, 20) : 'Image' }}
              </div>
            </div>
          </div>
        </div>

        <div class="setting-group">
          <label>Click Behavior During Presentation</label>
          <div class="button-group">
            <button 
              @click="setClickable(true)"
              :class="['click-btn', selectedElement.clickable ? 'active' : '']"
            >
              <span class="btn-icon">👆</span>
              <div class="btn-content">
                <div class="btn-title">Clickable (Toggle)</div>
                <div class="btn-desc">Click to toggle between 10% ↔ 100% opacity</div>
              </div>
            </button>
            <button 
              @click="setClickable(false)"
              :class="['click-btn', !selectedElement.clickable ? 'active' : '']"
            >
              <span class="btn-icon">🔒</span>
              <div class="btn-content">
                <div class="btn-title">Not Clickable</div>
                <div class="btn-desc">Stays at initial state (no interaction)</div>
              </div>
            </button>
          </div>
        </div>

        <div class="info-box">
          <div class="info-title">💡 How This Element Will Behave:</div>
          <div class="info-content">
            <p v-if="selectedElement.startHidden && selectedElement.clickable">
              <strong>Starts at {{ selectedElement.hiddenOpacity || 10 }}% opacity</strong> (barely visible). 
              Click during presentation to <strong>show at 100%</strong>. 
              Click again to <strong>hide at {{ selectedElement.hiddenOpacity || 10 }}%</strong>.
            </p>
            <p v-else-if="!selectedElement.startHidden && selectedElement.clickable">
              <strong>Starts at 100% opacity</strong> (fully visible). 
              Click during presentation to <strong>hide at {{ selectedElement.hiddenOpacity || 10 }}%</strong>. 
              Click again to <strong>show at 100%</strong>.
            </p>
            <p v-else-if="selectedElement.startHidden && !selectedElement.clickable">
              <strong>Starts at {{ selectedElement.hiddenOpacity || 10 }}% opacity</strong> (barely visible) and 
              <strong>stays that way</strong> - no click interaction.
            </p>
            <p v-else>
              <strong>Starts at 100% opacity</strong> (fully visible) and 
              <strong>stays that way</strong> - no click interaction.
            </p>
          </div>
        </div>
      </div>

      <div v-else class="no-selection-message">
        <div class="no-selection-icon">👈</div>
        <div class="no-selection-text">
          <strong>Click an element on the slide</strong>
          <p>or use the arrows (→) to select an element</p>
        </div>
      </div>

      <div class="summary-panel">
        <h4>Slide Summary</h4>
        <div class="summary-stats">
          <div class="stat">
            <span class="stat-label">Total Elements:</span>
            <span class="stat-value">{{ slide.elements.length }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Start Visible:</span>
            <span class="stat-value">{{ visibleCount }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Start Hidden:</span>
            <span class="stat-value">{{ hiddenCount }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Clickable:</span>
            <span class="stat-value">{{ clickableCount }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AnimationEditorV2',
  props: {
    slide: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      selectedElementId: null,
      hiddenOpacityValue: 10, // Default to 10%
      showPositionDots: true // Default to showing position dots
    };
  },
  computed: {
    selectedElement() {
      if (!this.selectedElementId) return null;
      return this.slide.elements.find(el => el.id === this.selectedElementId);
    },
    visibleCount() {
      return this.slide.elements.filter(el => !el.startHidden).length;
    },
    hiddenCount() {
      return this.slide.elements.filter(el => el.startHidden).length;
    },
    clickableCount() {
      return this.slide.elements.filter(el => el.clickable).length;
    }
  },
  methods: {
    selectElement(elementId) {
      this.selectedElementId = elementId;
      // Load the element's current hidden opacity value
      if (this.selectedElement && this.selectedElement.startHidden) {
        this.hiddenOpacityValue = this.selectedElement.hiddenOpacity || 10;
      }
    },
    getPreviewOpacity(element) {
      // For preview in editor, use 0.3 for hidden elements to make them visible enough
      return element.startHidden ? 0.3 : 1;
    },
    getHiddenOpacityText(element) {
      // Display the actual configured opacity value
      return (element.hiddenOpacity || 10) + '%';
    },
    updateHiddenOpacity() {
      if (!this.selectedElement) return;

      const updatedElements = this.slide.elements.map(el => {
        if (el.id === this.selectedElementId) {
          return {
            ...el,
            hiddenOpacity: this.hiddenOpacityValue
          };
        }
        return el;
      });

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });
    },
    setStartVisibility(hidden) {
      if (!this.selectedElement) return;

      const updatedElements = this.slide.elements.map(el => {
        if (el.id === this.selectedElementId) {
          return {
            ...el,
            startHidden: hidden
          };
        }
        return el;
      });

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });
    },
    setClickable(clickable) {
      if (!this.selectedElement) return;

      const updatedElements = this.slide.elements.map(el => {
        if (el.id === this.selectedElementId) {
          return {
            ...el,
            clickable: clickable
          };
        }
        return el;
      });

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });
    },
    truncate(text, length) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    }
  }
};
</script>

<style scoped>
.animation-editor-v2 {
  display: flex;
  gap: 20px;
  width: 100%;
  height: 100%;
  max-width: 100%;
  overflow: auto;
}

.editor-settings-bar {
  background: #2a2a2a;
  padding: 12px 20px;
  border-radius: 8px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.toggle-setting {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}

.toggle-setting input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #3b82f6;
}

.toggle-label {
  color: #fff;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
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

.element-indicator {
  position: absolute;
  pointer-events: all;
  z-index: 100;
  cursor: pointer;
}

.arrow-indicator {
  width: 24px;
  height: 24px;
  background: linear-gradient(135deg, #3b82f6, #60a5fa);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
  transition: all 0.2s;
  animation: pulse-arrow 2s infinite;
}

.arrow-indicator:hover {
  transform: scale(1.2);
  background: linear-gradient(135deg, #60a5fa, #93c5fd);
}

@keyframes pulse-arrow {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
  }
}

.position-dot-indicator {
  position: absolute;
  pointer-events: all;
  z-index: 50;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.position-dot {
  width: 16px;
  height: 16px;
  background: radial-gradient(circle, #3b82f6 0%, #1d4ed8 100%);
  border-radius: 50%;
  opacity: 0.7;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.5), inset 0 1px 3px rgba(255, 255, 255, 0.3);
  animation: pulse-dot 2s infinite;
  transition: all 0.2s ease;
}

.position-dot:hover {
  transform: scale(1.3);
  opacity: 1;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.8), inset 0 1px 3px rgba(255, 255, 255, 0.4);
}

@keyframes pulse-dot {
  0%, 100% {
    transform: scale(1);
    opacity: 0.7;
  }
  50% {
    transform: scale(1.15);
    opacity: 0.9;
  }
}

.element-wrapper {
  position: absolute;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
  border-radius: 4px;
  padding: 4px;
}

.element-wrapper.selected {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.element-wrapper.will-be-hidden {
  background: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 10px,
    rgba(251, 146, 60, 0.1) 10px,
    rgba(251, 146, 60, 0.1) 20px
  );
}

.element-content {
  pointer-events: none;
  user-select: none;
}

.element-content.text-content {
  padding: 8px 12px;
  min-width: 50px;
  min-height: 30px;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.hidden-badge,
.visible-badge {
  position: absolute;
  top: -32px;
  left: 50%;
  transform: translateX(-50%);
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  pointer-events: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.hidden-badge {
  background: #fb923c;
  color: #431407;
}

.visible-badge {
  background: #4ade80;
  color: #064e3b;
}

.control-panel {
  flex: 1;
  min-width: 350px;
  max-width: 450px;
  background: #2a2a2a;
  border-radius: 8px;
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
  height: calc(100% - 60px); /* Account for settings bar */
}

.control-panel h3 {
  margin: 0;
  color: #fff;
  font-size: 20px;
  font-weight: 700;
}

.control-panel h4 {
  margin: 0 0 12px 0;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
}

.element-settings {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.element-preview {
  background: #1a1a1a;
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid #3b82f6;
}

.preview-label {
  color: #888;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}

.preview-name {
  color: #fff;
  font-size: 16px;
  font-weight: 600;
}

.setting-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.setting-group label {
  color: #aaa;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.button-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.opacity-selector {
  background: #1a1a1a;
  padding: 16px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.opacity-slider {
  width: 100%;
  height: 8px;
  border-radius: 4px;
  background: linear-gradient(to right, #3a3a3a 0%, #3b82f6 50%, #ffffff 100%);
  outline: none;
  -webkit-appearance: none;
  appearance: none;
  cursor: pointer;
}

.opacity-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(59, 130, 246, 0.5);
  transition: all 0.2s;
}

.opacity-slider::-webkit-slider-thumb:hover {
  background: #60a5fa;
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.7);
}

.opacity-slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: none;
  box-shadow: 0 2px 6px rgba(59, 130, 246, 0.5);
  transition: all 0.2s;
}

.opacity-slider::-moz-range-thumb:hover {
  background: #60a5fa;
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.7);
}

.opacity-value-display {
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  text-align: center;
  background: #2a2a2a;
  padding: 8px;
  border-radius: 6px;
}

.opacity-preview {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #aaa;
  font-size: 13px;
}

.opacity-preview-box {
  flex: 1;
  padding: 12px;
  background: #3a3a3a;
  border-radius: 6px;
  color: #fff;
  font-size: 14px;
  text-align: center;
  transition: opacity 0.3s ease;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.visibility-btn,
.click-btn {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #1a1a1a;
  border: 2px solid #3a3a3a;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
  color: #fff;
}

.visibility-btn:hover,
.click-btn:hover {
  background: #252525;
  border-color: #4a4a4a;
}

.visibility-btn.active,
.click-btn.active {
  background: #1e3a5f;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-icon {
  font-size: 28px;
  flex-shrink: 0;
  width: 40px;
  text-align: center;
}

.btn-content {
  flex: 1;
}

.btn-title {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 4px;
}

.btn-desc {
  font-size: 12px;
  color: #888;
  line-height: 1.4;
}

.visibility-btn.active .btn-desc,
.click-btn.active .btn-desc {
  color: #93c5fd;
}

.info-box {
  background: #1a1a1a;
  border-left: 4px solid #3b82f6;
  padding: 16px;
  border-radius: 8px;
}

.info-title {
  color: #fff;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 8px;
}

.info-content p {
  margin: 0;
  color: #ccc;
  font-size: 13px;
  line-height: 1.6;
}

.info-content strong {
  color: #fff;
  font-weight: 600;
}

.no-selection-message {
  display: flex;
  align-items: center;
  gap: 20px;
  background: #1a1a1a;
  border: 2px dashed #3a3a3a;
  border-radius: 8px;
  padding: 32px;
  text-align: left;
}

.no-selection-icon {
  font-size: 48px;
  opacity: 0.5;
}

.no-selection-text strong {
  color: #fff;
  font-size: 16px;
  display: block;
  margin-bottom: 8px;
}

.no-selection-text p {
  color: #888;
  font-size: 14px;
  margin: 0;
}

.summary-panel {
  background: #1a1a1a;
  padding: 16px;
  border-radius: 8px;
  margin-top: auto;
}

.summary-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.stat {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #2a2a2a;
  border-radius: 6px;
}

.stat-label {
  color: #888;
  font-size: 12px;
}

.stat-value {
  color: #fff;
  font-size: 16px;
  font-weight: 700;
}
</style>
