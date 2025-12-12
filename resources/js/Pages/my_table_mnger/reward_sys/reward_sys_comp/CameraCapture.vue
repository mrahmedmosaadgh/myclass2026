 <template>
  <div class="camera-capture">
    <!-- Upload or Camera -->
    <div v-if="!imageLoaded">
      <div class="section">
        <div class="section-title">📂 Upload Image</div>
        <input type="file" accept="image/*" @change="onFileChange" />
      </div>
      <div class="section">
        <div class="section-title">📸 Camera</div>
        <div class="camera-controls">
          <button class="touch-btn primary" @click="startCamera">Start Camera</button>
          <button class="touch-btn warning" @click="toggleCamera">🔄 Switch Camera</button>
          <video ref="video" autoplay playsinline v-show="showVideo" class="video"></video>
          <button v-if="showVideo" class="touch-btn success" @click="captureFromCamera">Capture</button>
        </div>
      </div>
    </div>

    <!-- Crop Area -->
    <div v-show="true">
      <div v-if="imageLoaded" class="section-title">✂️ Crop Image</div>
      <div v-if="imageLoaded && !cropRect" class="crop-instruction">
        👆 Drag on the image to select the area you want to crop
      </div>
      <div v-if="imageLoaded && cropRect" class="crop-instruction success">
        ✅ Selection made! Adjust or tap "Crop" to continue
      </div>
 
 

      <canvas  v-if=" !croppedDataUrl"
        ref="canvas"
        :width="canvasWidth"
        :height="canvasHeight"
        v-show="imageLoaded"
        @mousedown="startCrop"
        @mousemove="moveCrop"
        @mouseup="endCrop"
        @touchstart.prevent="startCropTouch"
        @touchmove.prevent="moveCropTouch"
        @touchend.prevent="endCropTouch"
        class="crop-canvas   "
      ></canvas>
 <div v-if="imageLoaded&&!croppedDataUrl" class="crop-buttons">

     
        <button class="touch-btn success" @click="cropImage" :disabled="!canCrop">✂️ Crop</button>
        <button class="touch-btn danger" @click="reset">❌ Cancel</button>
      </div>
   

      <!-- Cropped Preview -->
      <div v-if="croppedDataUrl " class="cropped-preview">
        <div class="section-title">✅ Preview</div>
        <img :src="croppedDataUrl" class="preview-img" />
        <div class="cropped-actions">
          <button class="touch-btn primary" @click="emitCropped">💾 Save</button>
          <button class="touch-btn warning" @click="reset">🔄 Retake</button>
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

function onFileChange(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = ev => loadImage(ev.target.result)
  reader.readAsDataURL(file)
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
.camera-capture {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 12px;
}

.section {
  margin-bottom: 18px;
  text-align: center;
}

.section-title {
  font-weight: bold;
  margin-bottom: 8px;
  font-size: 1.2em;
  color: #1976d2;
}

.video {
  max-width: 100%;
  border-radius: 8px;
  border: 2px solid #ccc;
}

.crop-canvas {
  border: 3px solid #4CAF50;
  max-width: 100%;
  margin: 12px 0;
  border-radius: 12px;
  cursor: crosshair;
  touch-action: none; /* Prevent default touch behaviors */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.crop-instruction {
  padding: 12px 20px;
  margin: 8px 0;
  border-radius: 8px;
  font-size: 1.1em;
  font-weight: 500;
  text-align: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
  animation: pulse 2s ease-in-out infinite;
}

.crop-instruction.success {
  background: linear-gradient(135deg, #4CAF50 0%, #2e7d32 100%);
  box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
  animation: none;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.02);
    opacity: 0.95;
  }
}

.crop-buttons,
.cropped-actions {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin: 10px 0;
}

.preview-img {
  max-width: 200px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  margin: 10px 0;
}

/* Touch-friendly buttons with playful colors */
.touch-btn {
  font-size: 1.1em;
  padding: 12px 20px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: background 0.2s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.touch-btn.primary {
  background: #1976d2;
  color: #fff;
}
.touch-btn.primary:hover {
  background: #1565c0;
}

.touch-btn.success {
  background: #2e7d32;
  color: #fff;
}
.touch-btn.success:hover {
  background: #1b5e20;
}

.touch-btn.danger {
  background: #d32f2f;
  color: #fff;
}
.touch-btn.danger:hover {
  background: #b71c1c;
}

.touch-btn.warning {
  background: #f9a825;
  color: #fff;
}
.touch-btn.warning:hover {
  background: #f57f17;
}

/* Responsive adjustments for mobile */
@media (max-width: 600px) {
  .touch-btn {
    font-size: 1em;
    padding: 10px 16px;
    min-width: 100px;
  }
  .preview-img {
    max-width: 150px;
  }
}
</style>
