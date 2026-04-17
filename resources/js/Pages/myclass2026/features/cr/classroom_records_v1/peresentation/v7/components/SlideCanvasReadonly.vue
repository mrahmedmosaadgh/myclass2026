<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useUIStore } from '../stores/uiStore';
import EditableText from './EditableText.vue';
import EditableMath from './EditableMath.vue';

const props = defineProps({
  slide: {
    type: Object,
    required: true
  },
  baseWidth: {
    type: Number,
    default: 1000
  }
});

const ui = useUIStore();

const wrapperRef = ref(null);
const wrapperWidth = ref(0);
let resizeObserver;

const fitScale = computed(() => {
  const w = wrapperWidth.value || window.innerWidth;
  return w > 0 ? w / props.baseWidth : 1;
});

const canvasScale = computed(() => {
  return fitScale.value * (ui.zoomLevel / 100);
});

const canvasStyle = computed(() => ({
  width: props.baseWidth + 'px',
  height: (props.slide?.height || 600) + 'px',
  transform: `scale(${canvasScale.value})`,
  transformOrigin: 'top left',
  backgroundColor: 'white'
}));

function normalizeContent(val) {
  return typeof val === 'string' ? val : String(val || '');
}

onMounted(() => {
  if (!wrapperRef.value) return;
  wrapperWidth.value = wrapperRef.value.clientWidth || window.innerWidth;
  resizeObserver = new ResizeObserver((entries) => {
    const entry = entries[0];
    if (entry?.contentRect?.width) {
      wrapperWidth.value = entry.contentRect.width;
    }
  });
  resizeObserver.observe(wrapperRef.value);
});

onUnmounted(() => {
  if (resizeObserver && wrapperRef.value) {
    resizeObserver.unobserve(wrapperRef.value);
  }
});
</script>

<template>
  <div ref="wrapperRef" class="slide-readonly-wrapper">
    <div class="slide-readonly-canvas" :style="canvasStyle">
      <div
        v-for="el in (props.slide?.elements || [])"
        :key="el.id"
        class="slide-element"
        :style="{
          transform: `translate(${el.x || 0}px, ${el.y || 0}px) scale(${(el.zoom || 100) / 100})`,
          transformOrigin: 'top left',
          width: (el.width || 100) + 'px',
          height: (el.height || 40) + 'px',
          zIndex: el.zIndex || 1,
          opacity: 1,
          pointerEvents: 'none'
        }"
      >
        <div v-if="el.type === 'text'" :style="{ color: el.color || '#000' }">
          <EditableText
            :content="normalizeContent(el.content)"
            :isEditMode="false"
          />
        </div>

        <div v-else-if="el.type === 'math'" :style="{ color: el.color || '#000' }">
          <EditableMath
            :content="normalizeContent(el.content)"
            :is-edit-mode="false"
          />
        </div>

        <img
          v-else-if="el.type === 'image'"
          :src="el.src"
          style="width: 100%; height: 100%; object-fit: cover;"
        />

        <div
          v-else-if="el.type === 'rectangle'"
          style="width: 100%; height: 100%; border: 1px solid #3b82f6; border-radius: 4px;"
          :style="{ backgroundColor: el.bgColor || '#93c5fd' }"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.slide-readonly-wrapper {
  width: 100%;
  overflow: hidden;
}

.slide-readonly-canvas {
  position: relative;
  margin: 0 auto;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.08), 0 2px 4px -2px rgb(0 0 0 / 0.08);
}

.slide-element {
  position: absolute;
  top: 0;
  left: 0;
}
</style>
