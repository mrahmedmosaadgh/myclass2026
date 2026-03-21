<template>
  <div class="visibility-editor">
    <div class="slide-canvas" :style="{ minHeight: slideHeight + 'px' }">
      <!-- Render all elements with dropdown menus -->
      <div
        v-for="element in slide.elements"
        :key="element.id"
        :class="['element-wrapper', { 'selected': selectedElementId === element.id }]"
        :style="getElementWrapperStyle(element)"
        @click="selectElement(element.id)"
      >
        <!-- Element Content -->
        <div class="element-content" :style="getElementOpacity(element)">
          <img 
            v-if="element.type === 'image'" 
            :src="element.src" 
            alt="Element"
            :style="{ width: element.width + 'px', height: element.height + 'px' }"
          >
          <div 
            v-else-if="element.type === 'text'"
            class="text-content"
            :style="{ fontSize: element.fontSize + 'px', color: element.color }"
          >
            {{ element.content }}
          </div>
        </div>

        <!-- Visibility Badge -->
        <div class="visibility-badge" :class="getVisibilityClass(element)">
          <span v-if="element.visibilityOption === 'hidden-clickable'">start HIDDEN (10% opacity)</span>
          <span v-else-if="element.visibilityOption === 'shown-clickable'">start SHOWN (100% opacity)</span>
          <span v-else-if="element.visibilityOption === 'moveable'">MOVEABLE element</span>
          <span v-else>No setting</span>
        </div>

        <!-- Dropdown Button -->
        <button 
          class="dropdown-toggle" 
          @click.stop="toggleDropdown(element.id)"
          :class="{ 'active': openDropdownId === element.id }"
        >
          ⋮
        </button>

        <!-- Dropdown Menu -->
        <div 
          v-if="openDropdownId === element.id" 
          class="dropdown-menu"
          @click.stop
        >
          <div class="dropdown-title">Visibility Settings</div>
          
          <button 
            class="dropdown-option"
            :class="{ 'selected': element.visibilityOption === 'hidden-clickable' }"
            @click="setVisibilityOption(element.id, 'hidden-clickable')"
          >
            <div class="option-content">
              <div class="option-header">
                <span class="option-number">1</span>
                <span class="option-name">Hidden & Clickable</span>
              </div>
              <div class="option-description">
                Start presentation with <strong>0.1 opacity</strong> (hidden), click to show at <strong>1.0 opacity</strong>
              </div>
            </div>
            <span v-if="element.visibilityOption === 'hidden-clickable'" class="checkmark">✓</span>
          </button>

          <button 
            class="dropdown-option"
            :class="{ 'selected': element.visibilityOption === 'shown-clickable' }"
            @click="setVisibilityOption(element.id, 'shown-clickable')"
          >
            <div class="option-content">
              <div class="option-header">
                <span class="option-number">2</span>
                <span class="option-name">Shown & Clickable</span>
              </div>
              <div class="option-description">
                Start presentation with <strong>1.0 opacity</strong> (visible), click to hide at <strong>0.1 opacity</strong>
              </div>
            </div>
            <span v-if="element.visibilityOption === 'shown-clickable'" class="checkmark">✓</span>
          </button>

          <button 
            class="dropdown-option"
            :class="{ 'selected': element.visibilityOption === 'moveable' }"
            @click="setVisibilityOption(element.id, 'moveable')"
          >
            <div class="option-content">
              <div class="option-header">
                <span class="option-number">3</span>
                <span class="option-name">Moveable Element</span>
              </div>
              <div class="option-description">
                Element can be <strong>dragged and moved</strong> during presentation (always visible)
              </div>
            </div>
            <span v-if="element.visibilityOption === 'moveable'" class="checkmark">✓</span>
          </button>
        </div>
      </div>
    </div>
    <!-- Right Panel - Element Settings -->
    <!-- <div class="settings-panel">
      <h3>Element Visibility Settings</h3>                     dddddddddddddddddddddd
      
      
      <div v-if="selectedElement" class="selected-element-info">
        <div class="info-label">SELECTED ELEMENT:</div>
        <div class="info-value">{{ selectedElement.type === 'text' ? selectedElement.content : 'Image' }}</div>
        
        <div class="setting-section">
          <div class="section-title">INITIAL STATE WHEN PRESENTATION STARTS</div>
          
          <div class="option-cards">
            <button 
              class="option-card"
              :class="{ 'active': selectedElement.visibilityOption === 'hidden-clickable' }"
              @click="setVisibilityOption(selectedElement.id, 'hidden-clickable')"
            >
              <div class="card-icon">👻</div>
              <div class="card-title">Start Hidden</div>
              <div class="card-desc">0.1 opacity → Click to show at 1.0</div>
            </button>

            <button 
              class="option-card"
              :class="{ 'active': selectedElement.visibilityOption === 'shown-clickable' }"
              @click="setVisibilityOption(selectedElement.id, 'shown-clickable')"
            >
              <div class="card-icon">👁</div>
              <div class="card-title">Start Visible</div>
              <div class="card-desc">1.0 opacity → Click to hide at 0.1</div>
            </button>

            <button 
              class="option-card"
              :class="{ 'active': selectedElement.visibilityOption === 'moveable' }"
              @click="setVisibilityOption(selectedElement.id, 'moveable')"
            >
              <div class="card-icon">🔄</div>
              <div class="card-title">Moveable</div>
              <div class="card-desc">Can be dragged during presentation</div>
            </button>
          </div>
        </div>

        <div class="info-box">
          <div class="info-box-title">💡 Current Behavior:</div>
          <p v-if="selectedElement.visibilityOption === 'hidden-clickable'">
            This element will start at <strong>10% opacity</strong> (barely visible). 
            Click it during presentation to reveal at <strong>100% opacity</strong>.
            Click again to toggle back to 10%.
          </p>
          <p v-else-if="selectedElement.visibilityOption === 'shown-clickable'">
            This element will start at <strong>100% opacity</strong> (fully visible). 
            Click it during presentation to hide at <strong>10% opacity</strong>.
            Click again to toggle back to 100%.
          </p>
          <p v-else-if="selectedElement.visibilityOption === 'moveable'">
            This element is <strong>moveable</strong>. During presentation, you can 
            click and drag it to reposition anywhere on the slide. It stays at 100% opacity.
          </p>
          <p v-else>
            No visibility setting selected. The element will remain at 100% opacity 
            with no special interaction.
          </p>
        </div>
      </div>

      <div v-else class="no-selection">
        <div class="no-selection-icon">👆</div>
        <div class="no-selection-text">Click an element on the slide to configure its visibility settings</div>
      </div>
    </div> -->
  </div>
</template>

<script>
export default {
  name: 'VisibilityEditor',
  props: {
    slide: {
      type: Object,
      required: true
    },
    slideHeight: {
      type: Number,
      default: 1123
    }
  },
  data() {
    return {
      selectedElementId: null,
      openDropdownId: null
    };
  },
  computed: {
    selectedElement() {
      if (!this.selectedElementId) return null;
      return this.slide.elements.find(el => el.id === this.selectedElementId);
    }
  },
  methods: {
    selectElement(elementId) {
      this.selectedElementId = elementId;
    },
    toggleDropdown(elementId) {
      if (this.openDropdownId === elementId) {
        this.openDropdownId = null;
      } else {
        this.openDropdownId = elementId;
        this.selectedElementId = elementId;
      }
    },
    setVisibilityOption(elementId, option) {
      const updatedElements = this.slide.elements.map(el => {
        if (el.id === elementId) {
          return {
            ...el,
            visibilityOption: option,
            startHidden: option === 'hidden-clickable',
            clickable: option === 'hidden-clickable' || option === 'shown-clickable',
            moveable: option === 'moveable'
          };
        }
        return el;
      });

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });

      // Close dropdown after selection
      this.openDropdownId = null;
    },
    getVisibilityClass(element) {
      return element.visibilityOption || 'none';
    },
    getElementOpacity(element) {
      if (element.visibilityOption === 'hidden-clickable') {
        return { opacity: 0.3 };
      }
      return { opacity: 1 };
    },
    getElementWrapperStyle(element) {
      const style = {
        position: 'absolute',
        left: element.x + 'px',
        top: element.y + 'px',
        width: element.type === 'image' ? element.width + 'px' : 'auto',
        maxWidth: element.type === 'text' ? element.width + 'px' : 'none'
      };

      // Add visual indicators
      if (element.visibilityOption === 'hidden-clickable') {
        style.border = '2px dashed #fb923c';
      } else if (element.visibilityOption === 'shown-clickable') {
        style.border = '2px solid #4ade80';
      } else if (element.visibilityOption === 'moveable') {
        style.border = '2px solid #3b82f6';
        style.cursor = 'move';
      } else {
        style.border = '2px solid transparent';
      }

      return style;
    },
    closeDropdownOnClickOutside(event) {
      if (!event.target.closest('.dropdown-menu') && !event.target.closest('.dropdown-toggle')) {
        this.openDropdownId = null;
      }
    }
  },
  mounted() {
    document.addEventListener('click', this.closeDropdownOnClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.closeDropdownOnClickOutside);
  }
};
</script>

<style scoped>
.visibility-editor {
  display: flex;
  gap: 20px;
  width: 100%;
  height: 100%;
}

.slide-canvas {
  flex: 1;
  position: relative;
  background: #ffffff;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  overflow: visible;
}

.element-wrapper {
  cursor: pointer;
  transition: all 0.2s;
  padding: 8px;
  border-radius: 6px;
  position: relative;
}

.element-wrapper:hover {
  background: rgba(59, 130, 246, 0.05);
}

.element-wrapper.selected {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.4);
}

.element-content {
  pointer-events: none;
  user-select: none;
  transition: opacity 0.3s;
}

.text-content {
  padding: 8px 12px;
  min-width: 50px;
  min-height: 30px;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.visibility-badge {
  position: absolute;
  top: -28px;
  left: 0;
  padding: 4px 12px;
  border-radius: 14px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  pointer-events: none;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.visibility-badge.hidden-clickable {
  background: #fb923c;
  color: #7c2d12;
}

.visibility-badge.shown-clickable {
  background: #4ade80;
  color: #14532d;
}

.visibility-badge.moveable {
  background: #3b82f6;
  color: #1e3a8a;
}

.dropdown-toggle {
  position: absolute;
  top: -10px;
  right: -10px;
  width: 32px;
  height: 32px;
  background: #3b82f6;
  color: white;
  border: 3px solid white;
  border-radius: 50%;
  cursor: pointer;
  font-size: 20px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.5);
  transition: all 0.2s;
  z-index: 100;
}

.dropdown-toggle:hover {
  background: #2563eb;
  transform: scale(1.1);
}

.dropdown-toggle.active {
  background: #1d4ed8;
  transform: scale(1.1) rotate(90deg);
}

.dropdown-menu {
  position: absolute;
  top: 35px;
  right: -10px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  min-width: 380px;
  z-index: 1000;
  overflow: hidden;
  animation: dropdownOpen 0.2s ease;
}

@keyframes dropdownOpen {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-title {
  padding: 14px 18px;
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
  font-weight: 700;
  font-size: 13px;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.dropdown-option {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  background: white;
  border: none;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
  transition: all 0.15s;
  text-align: left;
}

.dropdown-option:last-child {
  border-bottom: none;
}

.dropdown-option:hover {
  background: #f9fafb;
}

.dropdown-option.selected {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
}

.option-content {
  flex: 1;
}

.option-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 6px;
}

.option-number {
  width: 24px;
  height: 24px;
  background: #3b82f6;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.option-name {
  font-weight: 600;
  font-size: 15px;
  color: #1f2937;
}

.option-description {
  font-size: 12px;
  color: #6b7280;
  line-height: 1.5;
  padding-left: 34px;
}

.option-description strong {
  color: #374151;
  font-weight: 600;
}

.checkmark {
  font-size: 20px;
  color: #3b82f6;
  font-weight: bold;
  flex-shrink: 0;
}

.settings-panel {
  width: 400px;
  background: #1f2937;
  border-radius: 8px;
  padding: 24px;
  color: white;
  overflow-y: auto;
}

.settings-panel h3 {
  margin: 0 0 20px 0;
  font-size: 18px;
  font-weight: 700;
  color: white;
}

.selected-element-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-label {
  font-size: 11px;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}

.info-value {
  font-size: 20px;
  font-weight: 700;
  color: white;
  padding: 12px;
  background: #374151;
  border-radius: 6px;
  border-left: 4px solid #3b82f6;
}

.setting-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.section-title {
  font-size: 12px;
  color: #d1d5db;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.option-cards {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.option-card {
  padding: 14px;
  background: #374151;
  border: 2px solid #4b5563;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}

.option-card:hover {
  background: #4b5563;
  border-color: #6b7280;
}

.option-card.active {
  background: #1e40af;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.card-icon {
  font-size: 28px;
  margin-bottom: 8px;
}

.card-title {
  font-size: 15px;
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
}

.card-desc {
  font-size: 12px;
  color: #d1d5db;
  line-height: 1.4;
}

.option-card.active .card-desc {
  color: #bfdbfe;
}

.info-box {
  background: #374151;
  border-left: 4px solid #3b82f6;
  padding: 16px;
  border-radius: 8px;
}

.info-box-title {
  font-size: 14px;
  font-weight: 600;
  color: white;
  margin-bottom: 10px;
}

.info-box p {
  margin: 0;
  font-size: 13px;
  color: #d1d5db;
  line-height: 1.6;
}

.info-box strong {
  color: white;
  font-weight: 600;
}

.no-selection {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 60px 20px;
  text-align: center;
}

.no-selection-icon {
  font-size: 64px;
  opacity: 0.5;
}

.no-selection-text {
  font-size: 14px;
  color: #9ca3af;
  line-height: 1.6;
}
</style>
