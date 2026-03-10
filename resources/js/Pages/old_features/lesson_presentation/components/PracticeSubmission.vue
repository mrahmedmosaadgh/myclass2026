<template>
  <q-card style="min-width: 700px; max-width: 90vw;">
    <q-card-section>
      <div class="text-h6">Submit Practice Solution</div>
      <div class="text-caption text-grey-7">Upload an image or draw your solution</div>
    </q-card-section>

    <q-separator />

    <q-card-section>
      <!-- Tabs for Upload vs Drawing -->
      <q-tabs
        v-model="activeTab"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="justify"
      >
        <q-tab name="upload" label="Upload Image" icon="upload_file" />
        <q-tab name="draw" label="Draw Solution" icon="draw" />
      </q-tabs>

      <q-separator class="q-my-md" />

      <q-tab-panels v-model="activeTab" animated>
        <!-- Upload Tab -->
        <q-tab-panel name="upload">
          <div class="column q-gutter-md">
            <q-file
              v-model="uploadedFile"
              label="Choose an image"
              accept="image/*"
              max-file-size="5242880"
              @update:model-value="onFileSelected"
              outlined
              clearable
            >
              <template v-slot:prepend>
                <q-icon name="attach_file" />
              </template>
              <template v-slot:hint>
                Max file size: 5MB. Supported formats: JPG, PNG, GIF
              </template>
            </q-file>

            <!-- Image Preview -->
            <div v-if="imagePreview" class="preview-container">
              <div class="text-subtitle2 q-mb-sm">Preview:</div>
              <img :src="imagePreview" alt="Preview" class="preview-image" />
            </div>
          </div>
        </q-tab-panel>

        <!-- Drawing Tab -->
        <q-tab-panel name="draw">
          <div class="column q-gutter-md">
            <!-- Drawing Tools -->
            <div class="row q-gutter-sm items-center">
              <q-btn-group outline>
                <q-btn
                  :outline="drawingTool !== 'pen'"
                  :unelevated="drawingTool === 'pen'"
                  color="primary"
                  icon="edit"
                  label="Pen"
                  @click="drawingTool = 'pen'"
                />
                <q-btn
                  :outline="drawingTool !== 'eraser'"
                  :unelevated="drawingTool === 'eraser'"
                  color="primary"
                  icon="cleaning_services"
                  label="Eraser"
                  @click="drawingTool = 'eraser'"
                />
              </q-btn-group>

              <q-separator vertical />

              <!-- Pen Color -->
              <div class="row items-center q-gutter-xs">
                <span class="text-caption text-grey-7">Color:</span>
                <q-btn
                  v-for="color in colors"
                  :key="color"
                  :style="{ backgroundColor: color }"
                  :outline="penColor !== color"
                  size="sm"
                  round
                  @click="penColor = color"
                  class="color-btn"
                >
                  <q-icon v-if="penColor === color" name="check" color="white" size="xs" />
                </q-btn>
              </div>

              <q-separator vertical />

              <!-- Pen Size -->
              <div class="row items-center q-gutter-xs">
                <span class="text-caption text-grey-7">Size:</span>
                <q-slider
                  v-model="penSize"
                  :min="1"
                  :max="10"
                  :step="1"
                  style="width: 100px"
                  color="primary"
                />
                <span class="text-caption">{{ penSize }}</span>
              </div>

              <q-space />

              <q-btn flat color="negative" icon="delete" label="Clear" @click="clearCanvas" />
            </div>

            <!-- Canvas -->
            <div class="canvas-container">
              <canvas
                ref="canvas"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart="startDrawing"
                @touchmove="draw"
                @touchend="stopDrawing"
                class="drawing-canvas"
              ></canvas>
            </div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </q-card-section>

    <q-separator />

    <q-card-actions align="right">
      <q-btn flat label="Cancel" color="grey" v-close-popup />
      <q-btn
        unelevated
        label="Submit"
        color="primary"
        @click="submitPractice"
        :loading="isSubmitting"
        :disable="!canSubmit"
      />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  progressId: {
    type: Number,
    required: true
  },
  existingSubmission: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['submitted', 'close']);

// Tab state
const activeTab = ref('upload');

// Upload state
const uploadedFile = ref(null);
const imagePreview = ref(null);

// Drawing state
const canvas = ref(null);
const ctx = ref(null);
const isDrawing = ref(false);
const drawingTool = ref('pen');
const penColor = ref('#000000');
const penSize = ref(3);
const colors = ['#000000', '#FF0000', '#0000FF', '#00FF00', '#FFFF00', '#FF00FF'];

// Submission state
const isSubmitting = ref(false);

const canSubmit = computed(() => {
  if (activeTab.value === 'upload') {
    return uploadedFile.value !== null;
  } else {
    // Check if canvas has any drawings
    return canvas.value && !isCanvasBlank();
  }
});

// File Upload Methods
const onFileSelected = (file) => {
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    imagePreview.value = null;
  }
};

// Canvas Methods
const initCanvas = () => {
  if (canvas.value) {
    const container = canvas.value.parentElement;
    canvas.value.width = container.offsetWidth;
    canvas.value.height = 400;
    ctx.value = canvas.value.getContext('2d');
    ctx.value.lineCap = 'round';
    ctx.value.lineJoin = 'round';
    
    // Set white background
    ctx.value.fillStyle = '#FFFFFF';
    ctx.value.fillRect(0, 0, canvas.value.width, canvas.value.height);
  }
};

watch(activeTab, (newTab) => {
  if (newTab === 'draw') {
    nextTick(() => {
      initCanvas();
    });
  }
});

onMounted(() => {
  if (activeTab.value === 'draw') {
    nextTick(() => {
      initCanvas();
    });
  }
});

const getCoordinates = (event) => {
  const rect = canvas.value.getBoundingClientRect();
  const scaleX = canvas.value.width / rect.width;
  const scaleY = canvas.value.height / rect.height;

  if (event.touches) {
    return {
      x: (event.touches[0].clientX - rect.left) * scaleX,
      y: (event.touches[0].clientY - rect.top) * scaleY
    };
  } else {
    return {
      x: (event.clientX - rect.left) * scaleX,
      y: (event.clientY - rect.top) * scaleY
    };
  }
};

const startDrawing = (event) => {
  if (!ctx.value) return;
  event.preventDefault();
  isDrawing.value = true;
  const coords = getCoordinates(event);
  ctx.value.beginPath();
  ctx.value.moveTo(coords.x, coords.y);
};

const draw = (event) => {
  if (!isDrawing.value) return;
  event.preventDefault();

  const coords = getCoordinates(event);
  
  if (drawingTool.value === 'pen') {
    ctx.value.strokeStyle = penColor.value;
    ctx.value.lineWidth = penSize.value;
    ctx.value.globalCompositeOperation = 'source-over';
  } else if (drawingTool.value === 'eraser') {
    ctx.value.strokeStyle = '#FFFFFF';
    ctx.value.lineWidth = penSize.value * 2;
    ctx.value.globalCompositeOperation = 'destination-out';
  }

  ctx.value.lineTo(coords.x, coords.y);
  ctx.value.stroke();
};

const stopDrawing = () => {
  isDrawing.value = false;
};

const clearCanvas = () => {
  ctx.value.fillStyle = '#FFFFFF';
  ctx.value.fillRect(0, 0, canvas.value.width, canvas.value.height);
};

const isCanvasBlank = () => {
  const blank = document.createElement('canvas');
  blank.width = canvas.value.width;
  blank.height = canvas.value.height;
  const blankCtx = blank.getContext('2d');
  blankCtx.fillStyle = '#FFFFFF';
  blankCtx.fillRect(0, 0, blank.width, blank.height);
  
  return canvas.value.toDataURL() === blank.toDataURL();
};

const getCanvasDataURL = () => {
  return canvas.value.toDataURL('image/png');
};

// Submit Practice
const submitPractice = async () => {
  isSubmitting.value = true;

  try {
    const formData = new FormData();

    if (activeTab.value === 'upload') {
      formData.append('submission_type', 'upload');
      formData.append('file', uploadedFile.value);
    } else {
      formData.append('submission_type', 'drawing');
      formData.append('drawing_data', getCanvasDataURL());
    }

    const response = await axios.post(
      route('lesson-presentation.progress.submit-practice', { id: props.progressId }),
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    );

    $q.notify({
      type: 'positive',
      message: 'Practice submitted successfully!',
      position: 'top'
    });

    emit('submitted', response.data);
    emit('close');
  } catch (error) {
    console.error('Submission error:', error);
    $q.notify({
      type: 'negative',
      message: error.response?.data?.error || 'Failed to submit practice',
      position: 'top'
    });
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.preview-container {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 12px;
  background: #f5f5f5;
}

.preview-image {
  max-width: 100%;
  max-height: 400px;
  display: block;
  margin: 0 auto;
  border-radius: 4px;
}

.canvas-container {
  border: 2px solid #e0e0e0;
  border-radius: 4px;
  background: #ffffff;
  overflow: hidden;
}

.drawing-canvas {
  display: block;
  cursor: crosshair;
  touch-action: none;
}

.color-btn {
  width: 32px;
  height: 32px;
  border: 2px solid #e0e0e0;
}
</style>
