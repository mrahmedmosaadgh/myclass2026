<template>
  <div class="slide-canvas-container flex justify-center">
    <div 
      class="slide-canvas relative shadow-lg"
      :style="{
        width: '960px',
        height: '540px',
        backgroundColor: slide.backgroundColor,
        transform: `scale(${zoomLevel})`,
        transformOrigin: 'top center'
      }"
      @click="handleCanvasClick"
    >
      <!-- Elements -->
      <DraggableElement
        v-for="element in slide.elements"
        :key="element.id"
        :element="element"
        :is-selected="selectedElementId === element.id"
        @update="updates => $emit('update-element', element.id, updates)"
        @select="$emit('select-element', element.id)"
        @delete="$emit('delete-element', element.id)"
        @move-to-front="$emit('move-to-front', element.id)"
        @move-to-back="$emit('move-to-back', element.id)"
      />
      
      <!-- Selection overlay -->
      <div 
        v-if="selectionBox.isActive"
        class="absolute border-2 border-blue-500 bg-blue-100 bg-opacity-20"
        :style="{
          left: selectionBox.startX + 'px',
          top: selectionBox.startY + 'px',
          width: (selectionBox.endX - selectionBox.startX) + 'px',
          height: (selectionBox.endY - selectionBox.startY) + 'px'
        }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import DraggableElement from './DraggableElement.vue';

const props = defineProps({
  slide: {
    type: Object,
    required: true
  },
  selectedElementId: {
    type: [String, null],
    default: null
  }
});

defineEmits(['select-element', 'update-element', 'delete-element', 'move-to-front', 'move-to-back']);

const zoomLevel = ref(0.8);

// Selection box state
const selectionBox = reactive({
  isActive: false,
  startX: 0,
  startY: 0,
  endX: 0,
  endY: 0
});

const handleCanvasClick = (event) => {
  // Deselect when clicking on empty canvas area
  if (event.target === event.currentTarget) {
    emit('select-element', null);
  }
};
</script>

<style scoped>
.slide-canvas-container {
  min-height: 100%;
  display: flex;
  align-items: flex-start;
  padding: 20px 0;
}

.slide-canvas {
  position: relative;
  border: 1px solid #ddd;
  background-size: 20px 20px;
  background-image: 
    linear-gradient(to right, #f0f0f0 1px, transparent 1px),
    linear-gradient(to bottom, #f0f0f0 1px, transparent 1px);
}
</style>