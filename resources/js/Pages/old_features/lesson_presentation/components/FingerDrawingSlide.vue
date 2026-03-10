<template>
  <div class="finger-drawing-container">
    <!-- Modern Toolbar -->
    <q-card flat bordered class="toolbar-card q-mb-md">
      <q-card-section class="q-pa-sm">
        <div class="row items-center q-gutter-sm">
          <!-- Drawing Tools Section -->
          <div class="col-auto">
            <div class="text-caption text-grey-7 q-mb-xs">
              <q-icon name="brush" size="xs" class="q-mr-xs" />
              Drawing Tools
            </div>
            <q-btn-group flat>
              <q-btn 
                flat 
                dense 
                icon="undo" 
                :disable="!canUndo"
                @click="undo"
                color="primary"
              >
                <q-tooltip>Undo ({{ strokes.length }} strokes)</q-tooltip>
              </q-btn>
              
              <q-btn 
                flat 
                dense 
                icon="redo" 
                :disable="!canRedo"
                @click="redo"
                color="primary"
              >
                <q-tooltip>Redo ({{ undone.length }} available)</q-tooltip>
              </q-btn>
              
              <q-btn 
                flat 
                dense 
                icon="delete" 
                @click="clear"
                color="negative"
              >
                <q-tooltip>Clear All</q-tooltip>
              </q-btn>
            </q-btn-group>
          </div>

          <q-separator vertical inset />

          <!-- Brush Settings -->
          <div class="col-auto">
            <div class="text-caption text-grey-7 q-mb-xs">
              <q-icon name="palette" size="xs" class="q-mr-xs" />
              Brush Settings
            </div>
            <div class="row items-center q-gutter-sm">
              <q-btn
                flat
                dense
                round
                :style="{ backgroundColor: penColor, border: '2px solid #ccc' }"
                size="md"
              >
                <q-popup-proxy>
                  <q-color v-model="penColor" no-header no-footer />
                </q-popup-proxy>
                <q-tooltip>Pen Color</q-tooltip>
              </q-btn>
              
              <div class="brush-size-control">
                <q-slider
                  v-model="penSize"
                  :min="1"
                  :max="50"
                  :step="1"
                  label
                  label-always
                  color="primary"
                  style="min-width: 120px"
                  class="q-mx-sm"
                >
                  <template v-slot:label="{ value }">
                    {{ value }}px
                  </template>
                </q-slider>
              </div>
            </div>
          </div>

          <q-separator vertical inset />

          <!-- Playback & Export -->
          <div class="col-auto">
            <div class="text-caption text-grey-7 q-mb-xs">
              <q-icon name="movie" size="xs" class="q-mr-xs" />
              Actions
            </div>
            <q-btn-group flat>
              <q-btn 
                flat 
                dense 
                icon="play_arrow" 
                :disable="isReplaying || !hasStrokes"
                @click="replay"
                color="accent"
                :loading="isReplaying"
              >
                <q-tooltip>Replay Drawing</q-tooltip>
              </q-btn>
              
              <q-btn 
                flat 
                dense 
                icon="content_paste" 
                @click="pasteFromClipboard"
                color="secondary"
              >
                <q-tooltip>Paste Background Image</q-tooltip>
              </q-btn>
            </q-btn-group>
          </div>

          <q-separator vertical inset />

          <!-- File Operations -->
          <div class="col-auto">
            <div class="text-caption text-grey-7 q-mb-xs">
              <q-icon name="folder" size="xs" class="q-mr-xs" />
              File
            </div>
            <q-btn-group flat>
              <q-btn 
                flat 
                dense 
                icon="save_alt" 
                :disable="!hasStrokes"
                @click="saveJson"
                color="positive"
              >
                <q-tooltip>Download as JSON</q-tooltip>
              </q-btn>
              
              <q-btn 
                flat 
                dense 
                icon="folder_open" 
                @click="loadJson"
                color="info"
              >
                <q-tooltip>Load JSON File</q-tooltip>
              </q-btn>
            </q-btn-group>
          </div>

          <q-space />

          <!-- Info Badge -->
          <div class="col-auto">
            <q-badge 
              v-if="hasStrokes" 
              color="primary" 
              :label="`${strokes.length} stroke${strokes.length !== 1 ? 's' : ''}`"
              class="q-px-sm"
            />
            <q-badge 
              v-else 
              color="grey-5" 
              label="Empty Canvas"
              class="q-px-sm"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Canvas Area -->
    <q-card flat bordered class="canvas-card">
      <div class="canvas-wrapper">
        <canvas
          ref="canvas"
          class="drawing-canvas"
          @mousedown="onPointerDown"
          @mousemove="onPointerMove"
          @mouseup="onPointerUp"
          @mouseleave="onPointerUp"
          @touchstart.prevent="onPointerDown"
          @touchmove.prevent="onPointerMove"
          @touchend.prevent="onPointerUp"
        ></canvas>
      </div>
    </q-card>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['export-json', 'update:modelValue'])

const canvas = ref(null)
let ctx = null
const width = 800
const height = 500

// Drawing state
const strokes = ref([])
const undone = ref([])
let currentStroke = null
const isDrawing = ref(false)
const isReplaying = ref(false)

// Pen
const penColor = ref('#111111')
const penSize = ref(3)

// Background
let backgroundImage = null
let backgroundHTML = null
let bgWidth = null
let bgHeight = null

function setupCanvas() {
  const c = canvas.value
  if (!c) return;
  const dpr = window.devicePixelRatio || 1
  c.width = width * dpr
  c.height = height * dpr
  c.style.width = width + 'px'
  c.style.height = height + 'px'
  ctx = c.getContext('2d')
  ctx.scale(dpr, dpr)
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
  redraw()
}

function redraw() {
  if (!ctx) return;
  ctx.clearRect(0, 0, width, height)
  // Draw background
  if (backgroundImage) {
    ctx.drawImage(backgroundImage, 0, 0, bgWidth, bgHeight)
  } else if (backgroundHTML) {
    ctx.font = '16px sans-serif'
    ctx.fillStyle = '#888'
    ctx.fillText(backgroundHTML, 20, 40)
  }
  // Draw strokes
  for (const s of strokes.value) drawSmoothStroke(s)
  if (currentStroke) drawSmoothStroke(currentStroke)
}

function getPos(ev) {
  const rect = canvas.value.getBoundingClientRect()
  if (ev.touches && ev.touches[0]) {
    return { x: ev.touches[0].clientX - rect.left, y: ev.touches[0].clientY - rect.top }
  } else {
    return { x: ev.clientX - rect.left, y: ev.clientY - rect.top }
  }
}

function drawSmoothStroke(stroke) {
  const pts = stroke.points
  if (pts.length < 2) return
  ctx.strokeStyle = stroke.color
  ctx.lineWidth = stroke.size
  ctx.beginPath()
  ctx.moveTo(pts[0].x, pts[0].y)
  for (let i = 1; i < pts.length - 1; i++) {
    const midX = (pts[i].x + pts[i + 1].x) / 2
    const midY = (pts[i].y + pts[i + 1].y) / 2
    ctx.quadraticCurveTo(pts[i].x, pts[i].y, midX, midY)
  }
  ctx.lineTo(pts[pts.length - 1].x, pts[pts.length - 1].y)
  ctx.stroke()
}

function onPointerDown(ev) {
  isDrawing.value = true
  const pos = getPos(ev)
  currentStroke = {
    points: [{ x: pos.x, y: pos.y, t: 0 }],
    color: penColor.value,
    size: penSize.value,
    _startTime: performance.now(),
  }
}

function onPointerMove(ev) {
  if (!isDrawing.value || !currentStroke) return
  const pos = getPos(ev)
  const t = performance.now() - currentStroke._startTime
  currentStroke.points.push({ x: pos.x, y: pos.y, t })
  redraw()
}

function onPointerUp() {
  if (!isDrawing.value) return
  isDrawing.value = false
  if (currentStroke) {
    delete currentStroke._startTime
    strokes.value.push(currentStroke)
    undone.value = [] 
    saveToModel()
  }
  currentStroke = null
  redraw()
}

function saveToModel() {
    // Generate background data if needed
    let backgroundData = null
    if (backgroundImage) {
        // We don't want to re-serialize image every stroke if massive, but for now it's safest
        // Optimization: Store original data URL if possible
        const tempCanvas = document.createElement('canvas')
        tempCanvas.width = bgWidth
        tempCanvas.height = bgHeight
        const tempCtx = tempCanvas.getContext('2d')
        tempCtx.drawImage(backgroundImage, 0, 0, bgWidth, bgHeight)
        backgroundData = {
          type: 'image',
          data: tempCanvas.toDataURL(),
          width: bgWidth,
          height: bgHeight
        }
    } else if (backgroundHTML) {
        backgroundData = { type: 'text', data: backgroundHTML }
    }

    const payload = {
        strokes: strokes.value,
        background: backgroundData
    }
    emit('update:modelValue', payload)
}

function loadFromModel() {
    const data = props.modelValue || {}
    if (data.strokes && Array.isArray(data.strokes)) {
        strokes.value = data.strokes
    }
    if (data.background) {
        if (data.background.type === 'image') {
          const img = new Image()
          img.onload = () => {
            backgroundImage = img
            bgWidth = data.background.width
            bgHeight = data.background.height
            redraw()
          }
          img.src = data.background.data
        } else if (data.background.type === 'text') {
           backgroundHTML = data.background.data
        }
    }
    redraw() // Initial redraw
}

function undo() {
  if (strokes.value.length > 0) {
    undone.value.push(strokes.value.pop())
    redraw()
    saveToModel()
  }
}
function redo() {
  if (undone.value.length > 0) {
    strokes.value.push(undone.value.pop())
    redraw()
    saveToModel()
  }
}
function clear() {
  strokes.value = []
  undone.value = []
  backgroundImage = null
  backgroundHTML = null
  redraw()
  saveToModel()
}
const canUndo = computed(() => strokes.value.length > 0)
const canRedo = computed(() => undone.value.length > 0)
const hasStrokes = computed(() => strokes.value.length > 0)

// Replay logic (Unchanged mostly)
function replay() {
  if (isReplaying.value || strokes.value.length === 0) return
  isReplaying.value = true
  let strokeIndex = 0
  let progress = 0
  const REPLAY_SPEED = 1.5 

  function animate() {
    ctx.clearRect(0, 0, width, height)
    if (backgroundImage) ctx.drawImage(backgroundImage, 0, 0, bgWidth, bgHeight)
    else if (backgroundHTML) {
       ctx.font = '16px sans-serif'
       ctx.fillStyle = '#888'
       ctx.fillText(backgroundHTML, 20, 40)
    }

    for (let i = 0; i < strokeIndex; i++) drawSmoothStroke(strokes.value[i])

    if (strokeIndex < strokes.value.length) {
      const currentStroke = strokes.value[strokeIndex]
      const points = currentStroke.points
      const numPoints = Math.floor(points.length * progress)
      
      if (numPoints > 0) {
        drawSmoothStroke({ ...currentStroke, points: points.slice(0, numPoints + 1) })
      }
      progress += 0.02 * REPLAY_SPEED
      if (progress >= 1) { progress = 0; strokeIndex++ }
      requestAnimationFrame(animate)
    } else {
      isReplaying.value = false
    }
  }
  requestAnimationFrame(animate)
}

async function pasteFromClipboard() {
  try {
    const items = await navigator.clipboard.read()
    for (const item of items) {
      for (const type of item.types) {
        if (type.startsWith('image/')) {
          const blob = await item.getType(type)
          const img = new Image()
          img.onload = () => {
            backgroundImage = img
            bgWidth = img.width
            bgHeight = img.height
            backgroundHTML = null
            redraw()
            saveToModel()
          }
          img.src = URL.createObjectURL(blob)
          return
        }
      }
    }
    const text = await navigator.clipboard.readText()
    if (text) {
      backgroundHTML = text
      backgroundImage = null
      redraw()
      saveToModel()
    }
  } catch (err) {
    console.error('Clipboard paste failed:', err)
  }
}

function emitJson() {
    // Keep for button compatibility
    saveToModel()
}

function saveJson() {
  // Logic from original...
  // (We can assume the user just wants the browser download behavior here)
  const payload = props.modelValue || { strokes: strokes.value }
  const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `drawing-${Date.now()}.json`
  a.click()
  URL.revokeObjectURL(url)
}

function loadJson() {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = '.json'
  input.onchange = async (e) => {
    const file = e.target.files[0]
    if (!file) return
    try {
      const text = await file.text()
      const data = JSON.parse(text)
      emit('update:modelValue', data)
      loadFromModel() // reload UI
    } catch (err) {
      alert('Failed to load.')
    }
  }
  input.click()
}

onMounted(() => {
  setupCanvas()
  loadFromModel()
})

watch(() => props.modelValue, (newVal) => {
    // Only reload if not currently drawing (to avoid conflicts with local state during rapid updates)
    if (!isDrawing.value) {
        loadFromModel()
    }
}, { deep: true })
</script>
<style scoped>
.finger-drawing-container {
  width: 100%;
  max-width: 100%;
}

.toolbar-card {
  background: linear-gradient(to bottom, #fafafa, #ffffff);
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.brush-size-control {
  display: flex;
  align-items: center;
  min-width: 140px;
}

.canvas-card {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  background: white;
}

.canvas-wrapper {
  position: relative;
  width: 100%;
  height: 500px;
  background: 
    linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
    white;
  background-size: 20px 20px;
  background-position: -1px -1px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.drawing-canvas {
  touch-action: none;
  display: block;
  cursor: crosshair;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Nice hover effects for buttons */
.q-btn:hover {
  transform: translateY(-1px);
  transition: transform 0.2s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .canvas-wrapper {
    height: 400px;
  }
  
  .toolbar-card .row {
    flex-wrap: wrap;
  }
  
  .brush-size-control {
    min-width: 100px;
  }
}
</style>
