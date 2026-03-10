<template>
  <div class="camera-capture q-pa-md">
    <!-- Upload or Camera -->
    <div v-if="!imageLoaded" class="column items-center q-gutter-y-md full-width">
      
      <!-- Upload Section -->
      <q-card flat bordered class="full-width">
        <q-card-section>
          <div class="text-subtitle1 text-primary text-center q-mb-sm font-bold">
            <q-icon name="folder_open" class="q-mr-sm" />Upload Image
          </div>
          <q-file
            filled
            bottom-slots
            v-model="file"
            label="Choose image"
            accept="image/*"
            @update:model-value="onFileChange"
            class="full-width"
          >
            <template v-slot:prepend>
              <q-icon name="cloud_upload" @click.stop.prevent />
            </template>
            <template v-slot:append>
              <q-icon name="close" @click.stop.prevent="file = null" class="cursor-pointer" />
            </template>
          </q-file>
        </q-card-section>
      </q-card>

      <div class="text-caption text-grey-7">OR</div>

      <!-- Camera Section -->
      <q-card flat bordered class="full-width">
        <q-card-section class="column items-center q-gutter-y-sm">
          <div class="text-subtitle1 text-primary text-center font-bold">
            <q-icon name="camera_alt" class="q-mr-sm" />Camera
          </div>
          
          <div class="row q-gutter-sm full-width justify-center" v-if="!showVideo">
            <q-btn
              color="primary"
              icon="videocam"
              label="Start Camera"
              class="col-12 col-sm-auto"
              @click="startCamera"
            />
          </div>

          <div v-show="showVideo" class="column items-center full-width q-gutter-y-sm">
            
            <div class="row q-gutter-sm full-width justify-center">
              <q-btn
                color="warning"
                text-color="dark"
                icon="cameraswitch"
                label="Switch"
                class="col-grow"
                @click="toggleCamera"
              />
              <q-btn
                color="positive"
                icon="camera"
                label="Capture"
                class="col-grow"
                @click="captureFromCamera"
              />
            </div>

            <video ref="video" autoplay playsinline class="video rounded-borders shadow-2"></video>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Crop Area -->
    <div v-show="imageLoaded" class="full-width column items-center q-gutter-y-md">
      <div v-if="!croppedDataUrl" class="text-h6 text-primary">
        <q-icon name="crop" class="q-mr-sm" />Crop Image
      </div>
      
      <q-banner v-if="!croppedDataUrl && !cropRect" rounded class="bg-blue-1 text-blue-9 full-width text-center">
        <template v-slot:avatar>
          <q-icon name="touch_app" color="primary" />
        </template>
        Drag on image to crop
      </q-banner>

      <q-banner v-if="!croppedDataUrl && cropRect" rounded class="bg-green-1 text-green-9 full-width text-center">
        <template v-slot:avatar>
          <q-icon name="check_circle" color="positive" />
        </template>
        Ready to crop!
      </q-banner>

      <!-- Canvas Container -->
      <div v-show="!croppedDataUrl" class="canvas-container relative-position flex flex-center full-width">
        <canvas
          ref="canvas"
          :width="canvasWidth"
          :height="canvasHeight"
          @mousedown="startCrop"
          @mousemove="moveCrop"
          @mouseup="endCrop"
          @touchstart.prevent="startCropTouch"
          @touchmove.prevent="moveCropTouch"
          @touchend.prevent="endCropTouch"
          class="crop-canvas shadow-3 rounded-borders"
        ></canvas>
      </div>

      <!-- Edit Actions -->
      <div v-if="!croppedDataUrl" class="column q-gutter-y-sm full-width">
        <q-btn
          color="positive"
          icon="crop"
          label="Crop Selection"
          class="full-width"
          @click="cropImage"
          :disable="!canCrop"
        />
        <q-btn
          color="primary"
          outline
          icon="skip_next"
          label="Skip Crop (Use Original)"
          class="full-width"
          @click="useOriginal"
        />
        <q-btn
          color="negative"
          flat
          icon="close"
          label="Cancel"
          class="full-width"
          @click="reset"
        />
      </div>

      <!-- Preview Section -->
      <div v-if="croppedDataUrl" class="column items-center q-gutter-y-md full-width">
        <div class="text-h6 text-positive">
          <q-icon name="preview" class="q-mr-sm" />Preview
        </div>
        
        <q-img
          :src="croppedDataUrl"
          class="rounded-borders shadow-3"
          style="max-width: 200px; max-height: 200px"
          fit="contain"
        />
        
        <div class="column q-gutter-y-sm full-width">
          <q-btn
            color="primary"
            icon="save"
            label="Save Photo"
            class="full-width"
            size="lg"
            @click="emitCropped"
          />
          <q-btn
            color="warning"
            text-color="dark"
            flat
            icon="replay"
            label="Retake"
            class="full-width"
            @click="reset"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, computed } from 'vue'
const emit = defineEmits(['captured'])

const video = ref(null)
const canvas = ref(null)
const showVideo = ref(false)
const imageLoaded = ref(false)
const cropping = ref(false)
const cropStart = ref({ x: 0, y: 0 })
const cropEnd = ref({ x: 0, y: 0 })
const cropRect = ref(null)
const canCrop = computed(() => cropRect.value && cropRect.value.w > 2 && cropRect.value.h > 2)
const image = ref(null)
const croppedDataUrl = ref(null)
const canvasWidth = ref(300)
const canvasHeight = ref(300)
let stream = null

const file = ref(null)

function onFileChange(newFile) {
  if (!newFile) return
  const reader = new FileReader()
  reader.onload = ev => loadImage(ev.target.result)
  reader.readAsDataURL(newFile)
}


const facingMode = ref('user') // 'user' for front, 'environment' for back

function startCamera() {
  if (stream) {
    stream.getTracks().forEach(t => t.stop())
  }

  navigator.mediaDevices.getUserMedia({
    video: { facingMode: facingMode.value }
  }).then(s => {
    stream = s
    showVideo.value = true
    nextTick(() => (video.value.srcObject = stream))
  })
}

function toggleCamera() {
  facingMode.value = facingMode.value === 'user' ? 'environment' : 'user'
  startCamera()
}


// function startCamera() {
//   navigator.mediaDevices.getUserMedia({ video: true }).then(s => {
//     stream = s
//     showVideo.value = true
//     nextTick(() => (video.value.srcObject = stream))
//   })
// }

function captureFromCamera() {
  const v = video.value
  const c = canvas.value
  if (!c || !v) return // guard against null

  c.width = v.videoWidth
  c.height = v.videoHeight
  const ctx = c.getContext('2d')
  ctx.drawImage(v, 0, 0, c.width, c.height)

  image.value = new Image()
  image.value.src = c.toDataURL('image/png')
  image.value.onload = () => {
    imageLoaded.value = true
    showVideo.value = false
    if (stream) stream.getTracks().forEach(t => t.stop())
    nextTick(drawImage)
  }
}

function loadImage(src) {
  image.value = new Image()
  image.value.src = src
  image.value.onload = () => {
    imageLoaded.value = true
    canvasWidth.value = image.value.width
    canvasHeight.value = image.value.height
    nextTick(drawImage)
  }
}

function drawImage() {
  const c = canvas.value
  if (!c || !image.value) return
  const ctx = c.getContext('2d')
  ctx.clearRect(0, 0, c.width, c.height)
  ctx.drawImage(image.value, 0, 0, c.width, c.height)

  if (cropRect.value) {
    // Draw dark overlay over entire image
    ctx.fillStyle = 'rgba(0, 0, 0, 0.6)'
    ctx.fillRect(0, 0, c.width, c.height)
    
    // Clear the selected area to show the original image
    ctx.clearRect(cropRect.value.x, cropRect.value.y, cropRect.value.w, cropRect.value.h)
    
    // Redraw the image in the selected area
    ctx.drawImage(
      image.value,
      cropRect.value.x, cropRect.value.y, cropRect.value.w, cropRect.value.h,
      cropRect.value.x, cropRect.value.y, cropRect.value.w, cropRect.value.h
    )
    
    // Draw semi-transparent green overlay on selected area
    ctx.fillStyle = 'rgba(76, 175, 80, 0.4)'
    ctx.fillRect(cropRect.value.x, cropRect.value.y, cropRect.value.w, cropRect.value.h)
    
    // Draw bright border around selection
    ctx.strokeStyle = '#4CAF50'
    ctx.lineWidth = 3
    ctx.strokeRect(cropRect.value.x, cropRect.value.y, cropRect.value.w, cropRect.value.h)
    
    // Draw corner handles for better mobile interaction
    const handleSize = 20
    const handles = [
      { x: cropRect.value.x, y: cropRect.value.y }, // top-left
      { x: cropRect.value.x + cropRect.value.w, y: cropRect.value.y }, // top-right
      { x: cropRect.value.x, y: cropRect.value.y + cropRect.value.h }, // bottom-left
      { x: cropRect.value.x + cropRect.value.w, y: cropRect.value.y + cropRect.value.h } // bottom-right
    ]
    
    ctx.fillStyle = '#4CAF50'
    ctx.strokeStyle = '#ffffff'
    ctx.lineWidth = 2
    
    handles.forEach(handle => {
      ctx.beginPath()
      ctx.arc(handle.x, handle.y, handleSize / 2, 0, Math.PI * 2)
      ctx.fill()
      ctx.stroke()
    })
    
    // Draw center crosshair
    const centerX = cropRect.value.x + cropRect.value.w / 2
    const centerY = cropRect.value.y + cropRect.value.h / 2
    const crossSize = 15
    
    ctx.strokeStyle = '#ffffff'
    ctx.lineWidth = 2
    ctx.beginPath()
    ctx.moveTo(centerX - crossSize, centerY)
    ctx.lineTo(centerX + crossSize, centerY)
    ctx.moveTo(centerX, centerY - crossSize)
    ctx.lineTo(centerX, centerY + crossSize)
    ctx.stroke()
  }
}

function getMousePos(e) {
  const rect = canvas.value.getBoundingClientRect()
  const scaleX = canvas.value.width / rect.width
  const scaleY = canvas.value.height / rect.height
  return { x: (e.clientX - rect.left) * scaleX, y: (e.clientY - rect.top) * scaleY }
}

function getTouchPos(e) {
  const rect = canvas.value.getBoundingClientRect()
  const scaleX = canvas.value.width / rect.width
  const scaleY = canvas.value.height / rect.height
  const touch = e.touches[0] || e.changedTouches[0]
  return { x: (touch.clientX - rect.left) * scaleX, y: (touch.clientY - rect.top) * scaleY }
}

function startCrop(e) {
  if (!imageLoaded.value) return
  cropping.value = true
  cropStart.value = getMousePos(e)
  cropRect.value = null
}
function moveCrop(e) {
  if (!cropping.value) return
  cropEnd.value = getMousePos(e)
  updateCropRect()
}
function endCrop() {
  cropping.value = false
}

function startCropTouch(e) {
  if (!imageLoaded.value) return
  cropping.value = true
  cropStart.value = getTouchPos(e)
  cropRect.value = null
}
function moveCropTouch(e) {
  if (!cropping.value) return
  cropEnd.value = getTouchPos(e)
  updateCropRect()
}
function endCropTouch() {
  cropping.value = false
}

function updateCropRect() {
  cropRect.value = {
    x: Math.min(cropStart.value.x, cropEnd.value.x),
    y: Math.min(cropStart.value.y, cropEnd.value.y),
    w: Math.abs(cropEnd.value.x - cropStart.value.x),
    h: Math.abs(cropEnd.value.y - cropStart.value.y)
  }
  drawImage()
}
const view_crop=ref(0)

function useOriginal() {
  if (!imageLoaded.value || !image.value) return
  // Use original image directly
  croppedDataUrl.value = image.value.src
  
  // Clear any crop selection
  cropRect.value = null
  cropping.value = false
  
  // Show preview state
  view_crop.value = 1
  
  // Ensure drawn on canvas for visual consistency (though preview uses img tag)
  nextTick(drawImage)
}

function cropImage() {
  if (!canCrop.value) return
  const c = document.createElement('canvas')
  c.width = cropRect.value.w
  c.height = cropRect.value.h
  const ctx = c.getContext('2d')
  ctx.drawImage(image.value, cropRect.value.x, cropRect.value.y, cropRect.value.w, cropRect.value.h, 0, 0, c.width, c.height)
  croppedDataUrl.value = c.toDataURL('image/png')
  cropRect.value = null
  cropping.value = false
   view_crop.value=1

  nextTick(drawImage)
}

function emitCropped() {
  emit('captured', { dataUrl: croppedDataUrl.value })
  
  // Stop camera stream after saving
  if (stream) {
    stream.getTracks().forEach(t => t.stop())
    stream = null
  }
  showVideo.value = false
}

function reset() {
  // Clear crop state
  cropRect.value = null
  croppedDataUrl.value = null
  view_crop.value = 0
  cropping.value = false
  
  // Clear image state
  imageLoaded.value = false
  image.value = null
  
  // Stop camera stream if active
  if (stream) {
    stream.getTracks().forEach(t => t.stop())
    stream = null
  }
  showVideo.value = false
  
  // Clear canvas
  const c = canvas.value
  if (c) {
    const ctx = c.getContext('2d')
    ctx.clearRect(0, 0, c.width, c.height)
  }
  
  // Reset canvas dimensions
  canvasWidth.value = 300
  canvasHeight.value = 300
}
</script>
 
<style scoped>
.video {
  max-width: 100%;
}

.crop-canvas {
  border: 3px solid #21ba45; /* Quasar 'positive' color */
  max-width: 100%;
  margin: 12px 0;
  border-radius: 12px;
  cursor: crosshair;
  touch-action: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
</style>
