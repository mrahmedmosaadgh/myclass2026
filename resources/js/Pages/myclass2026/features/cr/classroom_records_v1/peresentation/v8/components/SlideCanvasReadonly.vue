<script setup>
import { computed } from 'vue'
import ElementNode from './ElementNode.vue'

const props = defineProps({
  slide: Object,
  isPresentMode: {
    type: Boolean,
    default: false
  }
})

const canvasStyle = computed(() => ({
  width: '800px',
  height: '600px',
  position: 'relative',
  background: 'white',
  border: '1px solid #e5e7eb',
  borderRadius: '8px',
  overflow: 'hidden'
}))
</script>

<template>
  <div class="slide-canvas-readonly" :style="canvasStyle">
    <!-- Grid background -->
    <div class="grid-background" />
    
    <!-- Elements -->
    <ElementNode
      v-for="element in slide.elements"
      :key="element.id"
      :element="element"
      :is-present-mode="isPresentMode"
    />
    
    <!-- Empty state -->
    <div v-if="slide.elements.length === 0" class="empty-state">
      <div class="empty-icon">📝</div>
      <h3>Empty Slide</h3>
      <p>No elements on this slide</p>
    </div>
  </div>
</template>

<style scoped>
.slide-canvas-readonly {
  margin: 0 auto;
}

.grid-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    linear-gradient(to right, #f9fafb 1px, transparent 1px),
    linear-gradient(to bottom, #f9fafb 1px, transparent 1px);
  background-size: 10px 10px;
  pointer-events: none;
  opacity: 0.3;
}

.empty-state {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: #d1d5db;
  pointer-events: none;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 600;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}
</style>
