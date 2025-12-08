<template>
  <div class="editor">
    <input type="file" accept="application/json" @change="onFileChange" />

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

    <div class="controls">
      <button @click="clearCanvas">Clear Canvas</button>
      <button :disabled="!isValid" @click="saveJson">Save New JSON</button>
    </div>

    <div v-if="error" class="error">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const canvas = ref(null)
let ctx = null
const width = 800
const height = 500

const drawingData = ref(null)
const isValid = ref(false)
const error = ref('')

// Drawing state
let isDrawing = false
let currentStroke = null
const newStrokes = ref([]) // only the new red strokes

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
  clearCanvas()
}

function clearCanvas() {
  ctx.clearRect(0, 0, width, height)
  if (drawingData.value) {
    for (const s of drawingData.value.strokes) drawSmoothStroke(s)
  }
  for (const s of newStrokes.value) drawSmoothStroke(s)
}

function onFileChange(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = () => {
    try {
      const json = JSON.parse(reader.result)
      validateJson(json)
    } catch {
      error.value = 'Invalid JSON file'
      isValid.value = false
    }
  }
  reader.readAsText(file)
}

function validateJson(json) {
  if (
    json &&
    Array.isArray(json.strokes) &&
    typeof json.width === 'number' &&
    typeof json.height === 'number'
  ) {
    drawingData.value = json
    isValid.value = true
    error.value = ''
    clearCanvas()
    return
  }
  error.value = 'File structure not valid'
  isValid.value = false
}

function getPos(ev) {
  const rect = canvas.value.getBoundingClientRect()
  if (ev.touches && ev.touches[0]) {
    return {
      x: ev.touches[0].clientX - rect.left,
      y: ev.touches[0].clientY - rect.top,
    }
  } else {
    return {
      x: ev.clientX - rect.left,
      y: ev.clientY - rect.top,
    }
  }
}

function drawSmoothStroke(stroke) {
  const pts = stroke.points
  if (pts.length < 2) return
  ctx.strokeStyle = stroke.color || '#111'
  ctx.lineWidth = stroke.size || 3
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
  if (!isValid.value) return
  isDrawing = true
  const pos = getPos(ev)
  currentStroke = {
    points: [{ x: pos.x, y: pos.y, t: 0 }],
    color: 'red',
    size: 3,
    _startTime: performance.now(),
  }
}

function onPointerMove(ev) {
  if (!isDrawing || !currentStroke) return
  const pos = getPos(ev)
  const t = performance.now() - currentStroke._startTime
  currentStroke.points.push({ x: pos.x, y: pos.y, t })
  clearCanvas()
  drawSmoothStroke(currentStroke)
}

function onPointerUp() {
  if (!isDrawing) return
  isDrawing = false
  if (currentStroke) {
    delete currentStroke._startTime
    newStrokes.value.push(currentStroke)
  }
  currentStroke = null
  clearCanvas()
}

function saveJson() {
  if (!isValid.value || !drawingData.value) return
  const merged = {
    ...drawingData.value,
    strokes: [...drawingData.value.strokes, ...newStrokes.value],
  }
  const blob = new Blob([JSON.stringify(merged)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `edited-drawing-${Date.now()}.json`
  a.click()
  URL.revokeObjectURL(url)
}

onMounted(() => {
  setupCanvas()
})
</script>

<style scoped>
.editor {
  display: inline-block;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 8px;
  background: #fafafa;
}
.canvas {
  touch-action: none;
  display: block;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 6px;
  margin-top: 8px;
}
.controls {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}
.error {
  color: red;
  margin-top: 6px;
}
</style>
