<template>
  <div
    v-if="mode === 'edit' || mode === 'animation' || shouldShowInPresent"
    :class="['slide-element', mode, { 'selected': isSelected }]"
    :style="elementStyle"
    @mousedown="startDrag"
    @click.stop="selectElement"
  >
    <!-- Image Element -->
    <img 
      v-if="element.type === 'image'" 
      :src="element.src" 
      alt="Slide image"
      class="element-image"
      @dragstart.prevent
    >

    <!-- Text Element -->
    <div 
      v-else-if="element.type === 'text'"
      class="element-text"
      :contenteditable="mode === 'edit' && isSelected"
      @blur="updateTextContent"
      @mousedown.stop="startDrag"
      ref="textElement"
    >
      {{ element.content }}
    </div>

    <!-- Controls (Edit Mode Only) -->
    <div v-if="mode === 'edit' && isSelected" class="element-controls">
      <button @click.stop="deleteElement" class="control-btn delete-btn">×</button>
      <div class="resize-handle" @mousedown.stop="startResize"></div>
    </div>

    <!-- Click Action Badge (Animation Mode) -->
    <div v-if="mode === 'animation'" class="action-badge">
      <span v-if="element.clickAction === 'show'">👁 Show</span>
      <span v-else-if="element.clickAction === 'hide'">🔒 Hide</span>
      <span v-else class="no-action">No action</span>
    </div>

    <!-- Initial State Badge (Animation Mode) -->
    <div v-if="mode === 'animation' && element.clickOrder" class="state-badge" :class="element.initialState">
      {{ element.initialState === 'visible' ? 'Starts visible' : 'Starts hidden' }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'SlideElementSimple',
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
      resizeStartHeight: 0
    };
  },
  computed: {
    elementStyle() {
      const style = {
        left: `${this.element.x}px`,
        top: `${this.element.y}px`,
        width: this.element.type === 'image' ? `${this.element.width}px` : 'auto',
        maxWidth: this.element.type === 'text' ? `${this.element.width}px` : 'none'
      };

      if (this.element.type === 'image' && this.element.height) {
        style.height = `${this.element.height}px`;
      }

      if (this.element.type === 'text') {
        style.fontSize = `${this.element.fontSize}px`;
        style.color = this.element.color;
      }

      // Presentation mode visibility - INSTANT show/hide, no fade
      if (this.mode === 'present') {
        if (this.presentMode) {
          const hasBeenClicked = this.presentMode.clickedElements?.includes(this.element.id);
          
          if (this.element.clickAction === 'show') {
            // Starts hidden, shows on click
            if (this.element.initialState === 'hidden' && !hasBeenClicked) {
              style.display = 'none';
            }
          } else if (this.element.clickAction === 'hide') {
            // Starts visible, hides on click
            if (this.element.initialState === 'visible' && hasBeenClicked) {
              style.display = 'none';
            }
          } else {
            // No click action
            if (this.element.initialState === 'hidden') {
              style.display = 'none';
            }
          }
        }
      }

      return style;
    },
    shouldShowInPresent() {
      return true;
    }
  },
  methods: {
    selectElement() {
      if (this.mode === 'animation') {
        this.$emit('select', this.element.id);
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

      const updated = {
        ...this.element,
        width: Math.max(50, this.resizeStartWidth + deltaX)
      };

      if (this.element.type === 'image') {
        updated.height = Math.max(50, this.resizeStartHeight + deltaY);
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
    }
  }
};
</script>

<style scoped>
.slide-element {
  position: absolute;
  cursor: move;
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
  cursor: default;
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

.action-badge {
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

.no-action {
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
