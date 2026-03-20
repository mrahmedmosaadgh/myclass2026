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
    </div>

    <!-- Slide Canvas -->
    <div 
      class="slide-editor"
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
  props: {
    slide: {
      type: Object,
      required: true
    }
  },
  methods: {
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
            this.addImageElement(e.target.result);
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
    addImageElement(src) {
      // Validate that slide.elements exists and is an array
      if (!this.slide || !Array.isArray(this.slide.elements)) {
        console.error('Invalid slide structure:', this.slide);
        return;
      }

      const newElement = {
        id: this.generateId(),
        type: 'image',
        src: src,
        x: 100,
        y: 100,
        width: 300,
        height: 200,
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

.toolbar-divider {
  width: 1px;
  height: 24px;
  background: #4a4a4a;
  margin: 0 5px;
}

.slide-editor {
  flex: 1;
  width: 100%;
  min-height: 500px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  outline: none;
  position: relative;
  overflow: auto;
}

.slide-canvas {
  width: 100%;
  height: 100%;
  position: relative;
  overflow: visible;
  border-radius: 8px;
}
</style>
