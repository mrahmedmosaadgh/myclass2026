<template>
  <div class="slide-editor-container">
    <!-- Element Toolbar -->
    <div class="element-toolbar">
      <button @click="addTextElement('New Text')" class="toolbar-btn">
        <span class="btn-icon">T</span>
        Add Text
      </button>
      <button @click="triggerImageUpload" class="toolbar-btn">
        <span class="btn-icon">🖼</span>
        Add Image
      </button>
      <input 
        ref="imageInput" 
        type="file" 
        accept="image/*" 
        @change="handleImageUpload" 
        style="display: none"
      >
      <div class="toolbar-divider"></div>
      <button @click="addHeading" class="toolbar-btn">
        <span class="btn-icon">H1</span>
        Heading
      </button>
      <button @click="addSubheading" class="toolbar-btn">
        <span class="btn-icon">H2</span>
        Subheading
      </button>
      <div class="toolbar-divider"></div>
      <button @click="addRectangle" class="toolbar-btn">
        <span class="btn-icon">⬜</span>
        Add Rectangle
      </button>
      <div class="toolbar-divider"></div>
      <button @click="pasteFromClipboard" class="toolbar-btn clipboard-btn" title="Paste from clipboard">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="9" y="2" width="6" height="4" rx="1"/>
          <path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2"/>
          <path d="M12 11v6M9 14h6"/>
        </svg>
      </button>
      <div class="toolbar-divider"></div>
      <div class="height-selector">
        <select v-model="selectedHeight" @change="expandSlideHeight" class="height-select">
          <option value="1123">A4 Page (1123px)</option>
          <option value="500">Normal (500px)</option>
          <option value="800">Medium (800px)</option>
          <option value="1200">Large (1200px)</option>
          <option value="custom">Custom...</option>
        </select>
        <input 
          v-if="selectedHeight === 'custom'"
          type="number"
          v-model.number="customHeightValue"
          @change="applyCustomHeight"
          @blur="applyCustomHeight"
          placeholder="Enter px"
          class="custom-height-input"
          min="300"
          max="3000"
          step="50"
        >
      </div>
    </div>

    <!-- Slide Canvas -->
    <div 
      class="slide-editor"
      :class="{ 'expanded': isExpanded }"
      tabindex="0"
      @paste="handlePaste"
      @click="focusSlide"
      ref="slideArea"
    >
      <div class="slide-canvas">
        <SlideElement
          v-for="element in slide.elements"
          :key="element.id"
          :element="element"
          :mode="'edit'"
          @update="updateElement"
          @delete="deleteElement"
          @duplicate="handleDuplicate"
        />
      </div>
    </div>
  </div>
</template>

<script>
import SlideElement from './SlideElement.vue';

export default {
  name: 'SlideEditor',
  components: {
    SlideElement
  },
  data() {
    return {
      isExpanded: false,
      selectedHeight: '1123',
      customHeightValue: 1200
    };
  },
  props: {
    slide: {
      type: Object,
      required: true
    }
  },
  methods: {
    async pasteFromClipboard() {
      try {
        const clipboardItems = await navigator.clipboard.read();
        for (const item of clipboardItems) {
          for (const type of item.types) {
            if (type.startsWith('image/')) {
              const blob = await item.getType(type);
              const reader = new FileReader();
              reader.onload = (e) => this.addImageElement(e.target.result, true);
              reader.readAsDataURL(blob);
              return;
            }
          }
        }
        // Fallback to text
        const text = await navigator.clipboard.readText();
        if (text) this.addTextElement(text);
      } catch {
        // Fallback: focus slide and let user Ctrl+V manually
        this.$refs.slideArea.focus();
      }
    },
    focusSlide() {
      this.$refs.slideArea.focus();
    },
    async handlePaste(event) {
      event.preventDefault();
      const items = event.clipboardData.items;

      for (let item of items) {
        if (item.type.indexOf('image') !== -1) {
          const blob = item.getAsFile();
          const reader = new FileReader();
          
          reader.onload = (e) => {
            this.addImageElement(e.target.result, true); // true = from clipboard
          };
          reader.readAsDataURL(blob);
        } else if (item.type === 'text/plain') {
          item.getAsString((text) => {
            this.addTextElement(text);
          });
        }
      }
    },
    triggerImageUpload() {
      this.$refs.imageInput.click();
    },
    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.addImageElement(e.target.result);
        };
        reader.readAsDataURL(file);
      }
      event.target.value = '';
    },
    addHeading() {
      this.addTextElement('Heading', 48, '#000000');
    },
    addSubheading() {
      this.addTextElement('Subheading', 32, '#333333');
    },
    addRectangle() {
      // Validate that slide.elements exists and is an array
      if (!this.slide || !Array.isArray(this.slide.elements)) {
        console.error('Invalid slide structure:', this.slide);
        return;
      }

      const newElement = {
        id: this.generateId(),
        type: 'rectangle',
        x: 150,
        y: 150,
        width: 200,
        height: 150,
        color: '#4a90e2',
        borderColor: '#2c5aa0',
        borderWidth: 2,
        borderRadius: 0,
        opacity: 1,
        initialState: 'visible', // 'visible' or 'hidden'
        animation: null // 'fadeIn', 'fadeOut', or null
      };
      
      // Ensure non-zero dimensions
      if (!newElement.width || newElement.width <= 0) {
        newElement.width = 200;
      }
      if (!newElement.height || newElement.height <= 0) {
        newElement.height = 150;
      }
      
      const updatedSlide = {
        ...this.slide,
        elements: [...this.slide.elements, newElement]
      };
      
      console.log('Adding rectangle element:', newElement);
      console.log('Updated slide:', updatedSlide);
      this.$emit('update:slide', updatedSlide);
    },
    addImageElement(src, isFromClipboard = false) {
      // Validate that slide.elements exists and is an array
      if (!this.slide || !Array.isArray(this.slide.elements)) {
        console.error('Invalid slide structure:', this.slide);
        return;
      }

      const newElement = {
        id: this.generateId(),
        type: 'image',
        src: src,
        x: isFromClipboard ? 0 : 100,
        y: isFromClipboard ? 0 : 100,
        width: isFromClipboard ? 'auto' : 300,
        height: isFromClipboard ? 'auto' : 200,
        initialState: 'visible', // 'visible' or 'hidden'
        animation: null // 'fadeIn', 'fadeOut', or null
      };
      
      const updatedSlide = {
        ...this.slide,
        elements: [...this.slide.elements, newElement]
      };
      
      console.log('Adding image element:', newElement);
      console.log('Updated slide:', updatedSlide);
      this.$emit('update:slide', updatedSlide);
    },
    addTextElement(text, fontSize = 24, color = '#000000') {
      // Validate that slide.elements exists and is an array
      if (!this.slide || !Array.isArray(this.slide.elements)) {
        console.error('Invalid slide structure:', this.slide);
        return;
      }

      const newElement = {
        id: this.generateId(),
        type: 'text',
        content: text,
        x: 150,
        y: 150,
        width: 400,
        height: 'auto',
        fontSize: fontSize,
        color: color,
        initialState: 'visible',
        animation: null
      };
      
      const updatedSlide = {
        ...this.slide,
        elements: [...this.slide.elements, newElement]
      };
      
      console.log('Adding text element:', newElement);
      console.log('Updated slide:', updatedSlide);
      this.$emit('update:slide', updatedSlide);
    },
    updateElement(updatedElement) {
      const updatedSlide = {
        ...this.slide,
        elements: this.slide.elements.map(el => 
          el.id === updatedElement.id ? updatedElement : el
        )
      };
      this.$emit('update:slide', updatedSlide);
    },
    deleteElement(elementId) {
      const updatedSlide = {
        ...this.slide,
        elements: this.slide.elements.filter(el => el.id !== elementId)
      };
      this.$emit('update:slide', updatedSlide);
    },
    handleDuplicate(originalElement) {
      // Validate that slide.elements exists and is an array
      if (!this.slide || !Array.isArray(this.slide.elements)) {
        console.error('Invalid slide structure:', this.slide);
        return;
      }

      // Create a deep copy of the element with a new ID
      const duplicateElement = JSON.parse(JSON.stringify(originalElement));
      
      // Generate new unique ID
      duplicateElement.id = this.generateId();
      
      // Offset position slightly so it's visible as a separate element
      duplicateElement.x = (originalElement.x || 0) + 20;
      duplicateElement.y = (originalElement.y || 0) + 20;
      
      // Remove any zIndex to avoid stacking issues
      delete duplicateElement.zIndex;
      
      const updatedSlide = {
        ...this.slide,
        elements: [...this.slide.elements, duplicateElement]
      };
      
      console.log('Duplicating element:', originalElement);
      console.log('Created duplicate:', duplicateElement);
      console.log('Updated slide:', updatedSlide);
      this.$emit('update:slide', updatedSlide);
    },
    expandSlideHeight() {
      // Handle height selection from dropdown
      let height = this.selectedHeight;
      
      if (height === 'custom') {
        // Use custom value if selected
        height = this.customHeightValue;
      } else {
        // Convert string to number for preset values
        height = parseInt(height, 10);
      }
      
      // Update expanded state based on height
      this.isExpanded = height > 1123;
      
      // Emit event with specific height value
      this.$emit('expand-height', { isExpanded: this.isExpanded, height: height });
    },
    applyCustomHeight() {
      // Validate custom height input
      if (this.customHeightValue < 300) {
        this.customHeightValue = 300;
      } else if (this.customHeightValue > 3000) {
        this.customHeightValue = 3000;
      }
      
      // Apply the custom height
      this.expandSlideHeight();
    },
    generateId() {
      return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }
  },
  mounted() {
    this.focusSlide();
  }
};
</script>

<style scoped>
.slide-editor-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
  width: 100%;
  max-width: 1200px;
  height: 100%;
}

.element-toolbar {
  display: flex;
  gap: 10px;
  padding: 12px;
  background: #2a2a2a;
  border-radius: 8px;
  align-items: center;
  flex-wrap: wrap;
  flex-shrink: 0;
}

.toolbar-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: #3a3a3a;
  border: 1px solid #4a4a4a;
  color: #fff;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  white-space: nowrap;
}

.toolbar-btn:hover {
  background: #4a4a4a;
  border-color: #5a5a5a;
  transform: translateY(-1px);
}

.btn-icon {
  font-size: 16px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
}

.clipboard-btn {
  padding: 0;
  width: 42px;
  height: 42px;
  justify-content: center;
  background: #5b21b6;
  border-color: #7c3aed;
  flex-shrink: 0;
}

.clipboard-btn:hover {
  background: #6d28d9;
  border-color: #a78bfa;
  transform: translateY(-1px);
}

.toolbar-divider {
  width: 1px;
  height: 24px;
  background: #4a4a4a;
  margin: 0 5px;
}

.height-selector {
  display: flex;
  align-items: center;
  gap: 8px;
}

.height-select {
  padding: 8px 12px;
  background: #3a3a3a;
  border: 1px solid #4a4a4a;
  color: #fff;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  outline: none;
}

.height-select:hover {
  border-color: #5a5a5a;
  background: #4a4a4a;
}

.height-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.custom-height-input {
  width: 90px;
  padding: 8px 10px;
  background: #3a3a3a;
  border: 1px solid #4a4a4a;
  color: #fff;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.2s;
  outline: none;
}

.custom-height-input:hover {
  border-color: #5a5a5a;
}

.custom-height-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.slide-editor {
  flex: 1;
  width: 100%;
  min-height: 1123px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  outline: none;
  position: relative;
  overflow: auto;
  transition: min-height 0.3s ease;
}

.slide-editor.expanded {
  /* Height will be set dynamically by parent component */
}

.slide-canvas {
  width: 100%;
  height: 100%;
  position: relative;
  overflow: visible;
  border-radius: 8px;
}
</style>
