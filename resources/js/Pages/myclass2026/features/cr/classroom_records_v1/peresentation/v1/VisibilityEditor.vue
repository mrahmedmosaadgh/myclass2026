<template>
  <div class="visibility-editor">
    <div class="slide-canvas">
      <!-- Render all elements with visibility controls -->
      <div
        v-for="element in slide.elements"
        :key="element.id"
        :class="['element-wrapper', { 'selected': selectedElementId === element.id }]"
        :style="getElementWrapperStyle(element)"
        @click="selectElement(element.id)"
      >
        <!-- Element Content -->
        <div class="element-content">
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

        <!-- Visibility Indicator Badge -->
        <div class="visibility-badge" :class="getVisibilityMode(element)">
          <span v-if="element.visibilityMode === 'hidden-clickable'">👻 Hidden → Click to Show</span>
          <span v-else-if="element.visibilityMode === 'visible-clickable'">👁 Visible → Click to Hide</span>
          <span v-else>🔒 Always Visible</span>
        </div>

        <!-- Dropdown Menu Button -->
        <button 
          class="menu-button" 
          @click.stop="toggleMenu(element.id)"
          :class="{ 'active': openMenuId === element.id }"
        >
          ⋮
        </button>

        <!-- Dropdown Menu -->
        <div 
          v-if="openMenuId === element.id" 
          class="dropdown-menu"
          @click.stop
        >
          <div class="dropdown-header">Visibility Settings</div>
          
          <button 
            class="dropdown-item"
            :class="{ 'active': element.visibilityMode === 'hidden-clickable' }"
            @click="setVisibilityMode(element.id, 'hidden-clickable')"
          >
            <span class="item-icon">👻</span>
            <div class="item-content">
              <div class="item-title">Hide & Make Clickable</div>
              <div class="item-desc">Starts hidden (10%), click to show (100%)</div>
            </div>
            <span v-if="element.visibilityMode === 'hidden-clickable'" class="check-mark">✓</span>
          </button>

          <button 
            class="dropdown-item"
            :class="{ 'active': element.visibilityMode === 'visible-clickable' }"
            @click="setVisibilityMode(element.id, 'visible-clickable')"
          >
            <span class="item-icon">👁</span>
            <div class="item-content">
              <div class="item-title">Show & Make Clickable</div>
              <div class="item-desc">Starts visible (100%), click to hide (10%)</div>
            </div>
            <span v-if="element.visibilityMode === 'visible-clickable'" class="check-mark">✓</span>
          </button>

          <button 
            class="dropdown-item"
            :class="{ 'active': element.visibilityMode === 'always-visible' || !element.visibilityMode }"
            @click="setVisibilityMode(element.id, 'always-visible')"
          >
            <span class="item-icon">🔒</span>
            <div class="item-content">
              <div class="item-title">Always Visible</div>
              <div class="item-desc">Stays at 100%, no click interaction</div>
            </div>
            <span v-if="element.visibilityMode === 'always-visible' || !element.visibilityMode" class="check-mark">✓</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Instructions -->
    <div class="visibility-instructions">
      <p><strong>Visibility Settings:</strong> Click on an element → Click the ⋮ menu → Choose visibility mode</p>
      <div class="instruction-options">
        <span class="option">👻 Hide & Clickable</span>
        <span class="option">👁 Show & Clickable</span>
        <span class="option">🔒 Always Visible</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VisibilityEditor',
  props: {
    slide: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      selectedElementId: null,
      openMenuId: null
    };
  },
  methods: {
    selectElement(elementId) {
      this.selectedElementId = elementId;
    },
    toggleMenu(elementId) {
      if (this.openMenuId === elementId) {
        this.openMenuId = null;
      } else {
        this.openMenuId = elementId;
      }
    },
    setVisibilityMode(elementId, mode) {
      const updatedElements = this.slide.elements.map(el => {
        if (el.id === elementId) {
          return {
            ...el,
            visibilityMode: mode,
            // Set properties based on mode
            startHidden: mode === 'hidden-clickable',
            clickable: mode !== 'always-visible'
          };
        }
        return el;
      });

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });

      // Close menu after selection
      this.openMenuId = null;
    },
    getVisibilityMode(element) {
      return element.visibilityMode || 'always-visible';
    },
    getElementWrapperStyle(element) {
      const style = {
        position: 'absolute',
        left: element.x + 'px',
        top: element.y + 'px',
        width: element.type === 'image' ? element.width + 'px' : 'auto',
        maxWidth: element.type === 'text' ? element.width + 'px' : 'none'
      };

      // Visual indication of visibility mode
      if (element.visibilityMode === 'hidden-clickable') {
        style.opacity = '0.4';
        style.border = '2px dashed #fb923c';
      } else if (element.visibilityMode === 'visible-clickable') {
        style.opacity = '1';
        style.border = '2px solid #4ade80';
      } else {
        style.opacity = '1';
        style.border = '2px solid transparent';
      }

      return style;
    },
    closeMenuOnClickOutside(event) {
      if (!event.target.closest('.dropdown-menu') && !event.target.closest('.menu-button')) {
        this.openMenuId = null;
      }
    }
  },
  mounted() {
    document.addEventListener('click', this.closeMenuOnClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.closeMenuOnClickOutside);
  }
};
</script>

<style scoped>
.visibility-editor {
  position: relative;
  width: 100%;
  height: 100%;
}

.slide-canvas {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 500px;
  background: #ffffff;
  border: 2px dashed #e0e0e0;
  border-radius: 8px;
  overflow: visible;
}

.element-wrapper {
  cursor: pointer;
  transition: all 0.2s;
  padding: 8px;
  border-radius: 6px;
}

.element-wrapper:hover {
  background: rgba(59, 130, 246, 0.05);
}

.element-wrapper.selected {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

.element-content {
  pointer-events: none;
  user-select: none;
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
  top: -32px;
  left: 50%;
  transform: translateX(-50%);
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  pointer-events: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.visibility-badge.hidden-clickable {
  background: #fb923c;
  color: #431407;
}

.visibility-badge.visible-clickable {
  background: #4ade80;
  color: #064e3b;
}

.visibility-badge.always-visible {
  background: #94a3b8;
  color: #1e293b;
}

.menu-button {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 28px;
  height: 28px;
  background: #3b82f6;
  color: white;
  border: 2px solid white;
  border-radius: 50%;
  cursor: pointer;
  font-size: 18px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
  transition: all 0.2s;
  z-index: 10;
}

.menu-button:hover {
  background: #2563eb;
  transform: scale(1.1);
}

.menu-button.active {
  background: #1d4ed8;
  transform: scale(1.1) rotate(90deg);
}

.dropdown-menu {
  position: absolute;
  top: 30px;
  right: -8px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  min-width: 320px;
  z-index: 1000;
  overflow: hidden;
  animation: dropdownSlide 0.2s ease;
}

@keyframes dropdownSlide {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-header {
  padding: 12px 16px;
  background: #f3f4f6;
  border-bottom: 2px solid #e5e7eb;
  font-weight: 700;
  font-size: 13px;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: white;
  border: none;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
  transition: all 0.15s;
  text-align: left;
}

.dropdown-item:last-child {
  border-bottom: none;
}

.dropdown-item:hover {
  background: #f9fafb;
}

.dropdown-item.active {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
}

.item-icon {
  font-size: 24px;
  flex-shrink: 0;
  width: 32px;
  text-align: center;
}

.item-content {
  flex: 1;
  min-width: 0;
}

.item-title {
  font-weight: 600;
  font-size: 14px;
  color: #1f2937;
  margin-bottom: 2px;
}

.item-desc {
  font-size: 12px;
  color: #6b7280;
  line-height: 1.4;
}

.check-mark {
  font-size: 18px;
  color: #3b82f6;
  font-weight: bold;
  flex-shrink: 0;
}

.visibility-instructions {
  margin-top: 20px;
  padding: 15px 20px;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border: 2px solid #bae6fd;
  border-radius: 12px;
  text-align: center;
}

.visibility-instructions p {
  margin: 0 0 12px 0;
  color: #0c4a6e;
  font-size: 14px;
}

.visibility-instructions strong {
  color: #0369a1;
  font-weight: 700;
}

.instruction-options {
  display: flex;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
}

.option {
  padding: 6px 14px;
  background: white;
  border: 2px solid #3b82f6;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  color: #1e40af;
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
}
</style>
