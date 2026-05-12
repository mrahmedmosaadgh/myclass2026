<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePresentationStore } from '../../../stores/presentationStore';
import EditorCanvas from '../../../components/EditorCanvas.vue';

const presentation = usePresentationStore();
const containerRef = ref(null);
const scale = ref(1);

const updateScale = () => {
  if (!containerRef.value) return;
  const containerWidth = containerRef.value.clientWidth;
  const canvasWidth = 1000; // Hardcoded in EditorCanvas.vue
  scale.value = containerWidth / canvasWidth;
};

onMounted(() => {
  updateScale();
  window.addEventListener('resize', updateScale);
});
</script>

<template>
  <div class="student-slide-view" ref="containerRef">
    <div 
      class="scale-wrapper" 
      :style="{ transform: `scale(${scale})` }"
    >
      <EditorCanvas :read-only="true" />
    </div>
    
    <div v-if="!presentation.currentSlide" class="no-slide">
      <p>No slide to display</p>
    </div>
  </div>
</template>

<style scoped>
.student-slide-view {
  width: 100%;
  height: 100%;
  position: relative;
  overflow: hidden;
  background: #000;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.scale-wrapper {
  transform-origin: top center;
  width: 1000px; /* Match EditorCanvas */
  flex-shrink: 0;
}

.no-slide {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  background: #f8fafc;
}

/* Override EditorCanvas padding/shadow for student view */
:deep(.canvas-wrapper) {
  padding: 0 !important;
  box-shadow: none !important;
  border-radius: 0 !important;
  background: transparent !important;
}

:deep(.canvas) {
  margin: 0 !important;
  border: none !important;
}
</style>
