<template>
  <div class="slide-annotation-overlay">
    <!-- Annotation Canvas -->
    <DrawingCanvas
      ref="canvasRef"
      :is-active="true"
      :drawing-tool="drawingTool"
      :pen-color="penColor"
      :pen-size="penSize"
      :background-color="'transparent'"
      :current-page="1"
      :clear-trigger="clearTrigger"
      :storage-key="storageKey"
      @strokes-updated="handleStrokesUpdated"
    />

    <!-- Compact Toolbar -->
    <div class="annotation-toolbar">
      <q-card flat bordered class="toolbar-card">
        <q-card-section class="q-pa-xs">
          <div class="row items-center q-gutter-xs">
            <!-- Tool Toggle -->
            <q-btn-toggle
              v-model="drawingTool"
              toggle-color="primary"
              flat
              dense
              size="sm"
              :options="[
                {value: 'pen', icon: 'brush'},
                {value: 'eraser', icon: 'cleaning_services'}
              ]"
            />

            <q-separator vertical inset />

            <!-- Color Picker -->
            <q-btn
              flat
              round
              :style="{ backgroundColor: penColor, border: '2px solid #fff' }"
              size="sm"
            >
              <q-popup-proxy>
                <q-color v-model="penColor" no-header no-footer />
              </q-popup-proxy>
            </q-btn>

            <!-- Quick Colors -->
            <q-btn
              v-for="color in quickColors"
              :key="color"
              flat
              round
              :style="{ 
                backgroundColor: color,
                border: penColor === color ? '2px solid #000' : '1px solid #ddd'
              }"
              size="xs"
              @click="penColor = color"
            />

            <q-separator vertical inset />

            <!-- Pen Size -->
            <div style="width: 80px">
              <q-slider
                v-model="penSize"
                :min="1"
                :max="15"
                label
                label-always
                color="primary"
                dense
              >
                <template v-slot:label="{ value }">
                  {{ value }}px
                </template>
              </q-slider>
            </div>

            <q-separator vertical inset />

            <!-- Actions -->
            <q-btn flat dense round icon="undo" @click="undo" size="sm">
              <q-tooltip>Undo</q-tooltip>
            </q-btn>

            <q-btn flat dense round icon="delete_sweep" color="negative" @click="clear" size="sm">
              <q-tooltip>Clear</q-tooltip>
            </q-btn>

            <q-btn flat dense round icon="close" color="negative" @click="$emit('close')" size="sm">
              <q-tooltip>Close</q-tooltip>
            </q-btn>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Info Badge -->
    <div class="annotation-info">
      <q-chip color="primary" text-color="white" dense>
        <q-icon name="edit_note" size="xs" class="q-mr-xs" />
        Annotating Slide
      </q-chip>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import DrawingCanvas from './DrawingComp/DrawingCanvas.vue';

const props = defineProps({
  currentSlideId: {
    type: [String, Number],
    required: true
  },
  currentSection: {
    type: String,
    default: ''
  },
  currentSlideIndex: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['close']);

const canvasRef = ref(null);
const drawingTool = ref('pen');
const penColor = ref('#FF0000');
const penSize = ref(3);
const clearTrigger = ref(0);

const quickColors = ['#FF0000', '#0000FF', '#00FF00', '#FFFF00', '#000000'];

// Generate unique storage key for slide annotations
const storageKey = computed(() => {
  const url = window.location.pathname;
  const match = url.match(/\/lesson-presentation\/student\/(\d+)/);
  const lessonId = match ? match[1] : 'unknown';
  return `slide-annotations-${lessonId}-${props.currentSlideId}`;
});

const undo = () => {
  if (canvasRef.value) {
    canvasRef.value.undo();
  }
};

const clear = () => {
  clearTrigger.value++;
};

const handleStrokesUpdated = (strokes) => {
  // Strokes are automatically saved by DrawingCanvas
};

// Watch for slide changes and reload canvas
watch(() => props.currentSlideId, () => {
  // Canvas will automatically reload from new storage key
  if (canvasRef.value) {
    canvasRef.value.loadFromStorage();
  }
});
</script>

<style scoped lang="scss">
.slide-annotation-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  pointer-events: none;
  z-index: 210;
  
  > * {
    pointer-events: all;
  }
}

.annotation-toolbar {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 220;
  pointer-events: all;
}

.toolbar-card {
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  border-radius: 12px;
  border: 2px solid rgba(0,0,0,0.1);
}

.annotation-info {
  position: fixed;
  top: 70px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 220;
  pointer-events: none;
}
</style>
