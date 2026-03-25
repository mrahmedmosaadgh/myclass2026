<script setup>
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import ElementNode from './ElementNode.vue';

const presentation = usePresentationStore();
const ui = useUIStore();

function capturePointer(e) {
  const rect = e.currentTarget.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  ui.updateLastPointer(x, y);
}

function handleCanvasMousedown(e) {
  capturePointer(e);
  ui.clearSelection();
}
</script>

<template>
  <div
    class="canvas"
    @mousedown.self="handleCanvasMousedown"
  >
    <ElementNode
      v-for="el in presentation.currentSlide.elements"
      :key="el.id"
      :element="el"
    />
  </div>
</template>

<style scoped>
.canvas {
  position: relative;
  width: 1000px;
  height: 600px;
  background: white;
  margin: auto;
  border: 1px solid #ccc;
  background-image: linear-gradient(to right, #eee 1px, transparent 1px),
                    linear-gradient(to bottom, #eee 1px, transparent 1px);
  background-size: 10px 10px;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  overflow: hidden;
}
</style>
