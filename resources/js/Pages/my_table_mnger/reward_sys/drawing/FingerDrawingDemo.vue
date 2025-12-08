<template>
  <div class="pad">
    <div class="toolbar">
      <label>
        Pen Color:
        <input type="color" v-model="penColor" />
      </label>
      <button @click="undo" :disabled="!canUndo">Undo</button>
      <button @click="redo" :disabled="!canRedo">Redo</button>
      <button @click="clear">Clear</button>
      <button @click="replay" :disabled="isReplaying || !hasStrokes">Replay</button>
      <button @click="emitJson" :disabled="!hasStrokes">Export JSON</button>
      <button @click="saveJson" :disabled="!hasStrokes">Save as JSON</button>
      <button @click="loadJson">Open JSON</button>
      <button @click="pasteFromClipboard">Paste Background</button>
    </div>

    <div class="canvas-wrapper">
      <canvas
        ref="canvas"
        class="canvas"
        @mousedown="onPointerDown"
        @mousemove="onPointerMove"
        @mouseup="onPointerUp"
        @mouseleave="onPointerUp"
        @touchstart.prevent="onPointerDown"
        @touchmove.prevent="onPointerMove"
        @touchend.prevent="onPointerUp"
      ></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const canvas = ref(null)
let ctx = null
const width = 800
const height = 500

// Drawing state
const strokes = ref([])
const undone = ref([])
let currentStroke = null
let isDrawing = false
const isReplaying = ref(false)

// Pen
const penColor = ref('#111111') // valid hex for <input type="color">
const penSize = ref(3)

// Background
let backgroundImage = null
let backgroundHTML = null
let bgWidth = null
let bgHeight = null

// Emit event to parent
const emit = defineEmits(['export-json'])

function setupCanvas() {
  const c = canvas.value
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
  isDrawing = true
  const pos = getPos(ev)
  currentStroke = {
    points: [{ x: pos.x, y: pos.y, t: 0 }],
    color: penColor.value,
    size: penSize.value,
    _startTime: performance.now(),
  }
}

function onPointerMove(ev) {
  if (!isDrawing || !currentStroke) return
  const pos = getPos(ev)
  const t = performance.now() - currentStroke._startTime
  currentStroke.points.push({ x: pos.x, y: pos.y, t })
  redraw()
}

function onPointerUp() {
  if (!isDrawing) return
  isDrawing = false
  if (currentStroke) {
    delete currentStroke._startTime
    strokes.value.push(currentStroke)
    undone.value = [] // clear redo stack
  }
  currentStroke = null
  redraw()
}

function undo() {
  if (strokes.value.length > 0) {
    undone.value.push(strokes.value.pop())
    redraw()
  }
}
function redo() {
  if (undone.value.length > 0) {
    strokes.value.push(undone.value.pop())
    redraw()
  }
}
function clear() {
  strokes.value = []
  undone.value = []
  redraw()
}
const canUndo = computed(() => strokes.value.length > 0)
const canRedo = computed(() => undone.value.length > 0)
const hasStrokes = computed(() => strokes.value.length > 0)

// Replay
function replay() {
  if (isReplaying.value || strokes.value.length === 0) return

  // Clear any existing animation
  isReplaying.value = true
  let strokeIndex = 0
  let progress = 0
  const REPLAY_SPEED = 1.5 // Adjust speed factor (higher = faster)

  function animate() {
    // Clear canvas and draw background
    ctx.clearRect(0, 0, width, height)
    if (backgroundImage) {
      ctx.drawImage(backgroundImage, 0, 0, bgWidth, bgHeight)
    } else if (backgroundHTML) {
      ctx.font = '16px sans-serif'
      ctx.fillStyle = '#888'
      ctx.fillText(backgroundHTML, 20, 40)
    }

    // Draw completed strokes
    for (let i = 0; i < strokeIndex; i++) {
      drawSmoothStroke(strokes.value[i])
    }

    // Draw current stroke in progress
    if (strokeIndex < strokes.value.length) {
      const currentStroke = strokes.value[strokeIndex]
      const points = currentStroke.points
      const numPoints = Math.floor(points.length * progress)
      
      if (numPoints > 0) {
        drawSmoothStroke({
          ...currentStroke,
          points: points.slice(0, numPoints + 1)
        })
      }

      // Update progress
      progress += 0.02 * REPLAY_SPEED
      
      if (progress >= 1) {
        progress = 0
        strokeIndex++
      }

      requestAnimationFrame(animate)
    } else {
      // Animation complete
      isReplaying.value = false
    }
  }

  // Start animation
  requestAnimationFrame(animate)
}

// Paste background via button
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
          }
          img.src = URL.createObjectURL(blob)
          return
        }
      }
    }
    // If no image, try text
    const text = await navigator.clipboard.readText()
    if (text) {
      backgroundHTML = text
      backgroundImage = null
      redraw()
    }
  } catch (err) {
    console.error('Clipboard paste failed:', err)
  }
}

// Emit JSON to parent
function emitJson() {
  const payload = {
    version: 1,
    width,
    height,
    strokes: strokes.value.map(s => ({
      points: s.points,
      color: s.color,
      size: s.size,
    })),
  }
  emit('export-json', payload)
}

// Save JSON to file
async function saveJson() {
  // Convert background image to base64 if it exists
  let backgroundData = null
  if (backgroundImage) {
    // Create a temporary canvas to draw the image
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
    backgroundData = {
      type: 'text',
      data: backgroundHTML
    }
  }

  const payload = {
    version: 1,
    width,
    height,
    strokes: strokes.value.map(s => ({
      points: s.points,
      color: s.color,
      size: s.size,
    })),
    background: backgroundData
  }
  
  const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `drawing-${Date.now()}.json`
  a.click()
  URL.revokeObjectURL(url)
}

// Load JSON from file
function loadJson() {
  // Create a hidden file input
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = '.json'
  
  input.onchange = async (e) => {
    const file = e.target.files[0]
    if (!file) return
    
    try {
      const text = await file.text()
      const data = JSON.parse(text)
      
      // Validate the JSON structure
      if (!data.version || !Array.isArray(data.strokes)) {
        throw new Error('Invalid drawing file format')
      }
      
      // Clear current drawing and background
      strokes.value = []
      undone.value = []
      backgroundImage = null
      backgroundHTML = null
      
      // Load the strokes
      strokes.value = data.strokes.map(s => ({
        points: s.points,
        color: s.color || '#111111',
        size: s.size || 3
      }))

      // Load background if it exists
      if (data.background) {
        if (data.background.type === 'image') {
          // Load image background
          const img = new Image()
          img.onload = () => {
            backgroundImage = img
            bgWidth = data.background.width
            bgHeight = data.background.height
            backgroundHTML = null
            redraw()
          }
          img.src = data.background.data
        } else if (data.background.type === 'text') {
          // Load text background
          backgroundHTML = data.background.data
          backgroundImage = null
          redraw()
        }
      }
      
      // Redraw
      redraw()
    } catch (err) {
      console.error('Failed to load drawing:', err)
      alert('Failed to load the drawing file. Please make sure it\'s a valid drawing JSON file.')
    }
  }
  
  // Trigger file selection
  input.click()
}

onMounted(() => {
  setupCanvas()
})
</script>
<style scoped>
.pad {
  display: inline-block;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 8px;
  background: #fafafa;
}

.toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 8px;
  align-items: center;
}

.canvas-wrapper {
  border: 1px solid #eee;
  border-radius: 6px;
  background: #fff;
}

.canvas {
  touch-action: none; /* important for touch devices */
  display: block;
  width: 800px;
  height: 500px;
}
</style>
