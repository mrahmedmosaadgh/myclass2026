<template>
  <div class="visibility-editor">
    <div class="slide-canvas">
      <!-- Render all elements with visibility controls -->
      <SlideElement
        v-for="element in slide.elements"
        :key="element.id"
        :element="element"
        :mode="'visibility'"
        @update="updateElement"
        @delete="deleteElement"
        @select="selectElement"
      />
    </div>

    <!-- Instructions -->
    <div class="visibility-instructions">
      <p><strong>Visibility Settings:</strong> Click on an element → Click the ⋮ menu → Set visibility options</p>
      <p>✅ Start Hidden, Click to Show | ✅ Start Visible, Click to Hide | ✅ Custom Hidden Opacity</p>
    </div>
  </div>
</template>

<script>
import SlideElement from './SlideElement.vue';

export default {
  name: 'VisibilityEditor',
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
  methods: {
    updateElement(updatedElement) {
      this.$emit('update:slide', {
        ...this.slide,
        elements: this.slide.elements.map(el => 
          el.id === updatedElement.id ? updatedElement : el
        )
      });
    },
    deleteElement(elementId) {
      this.$emit('update:slide', {
        ...this.slide,
        elements: this.slide.elements.filter(el => el.id !== elementId)
      });
    },
    selectElement(elementId) {
      this.selectedElementId = elementId;
    }
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
  overflow: hidden;
}

.visibility-instructions {
  margin-top: 20px;
  padding: 15px;
  background: #f5f5f5;
  border-radius: 8px;
  text-align: center;
}

.visibility-instructions p {
  margin: 8px 0;
  color: #666;
  font-size: 14px;
}

.visibility-instructions strong {
  color: #4a90e2;
}
</style>
