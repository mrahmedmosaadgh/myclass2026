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
      <button @click="replay" :disabled="isReplaying || strokes.length===0">Replay</button>
      <button @click="emitJson" :disabled="strokes.length===0">Export JSON</button>
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
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'

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
const penColor = ref('#111111') // ✅ valid 6-digit hex
const penSize = ref(3)

// Background
let backgroundImage = null
let backgroundHTML = null

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
    ctx.drawImage(backgroundImage, 0, 0, width, height)
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

// Replay
function replay() {
  if (isReplaying.value || strokes.value.length === 0) return
  isReplaying.value = true
  let strokeIndex = 0
  let strokeStartTime = performance.now()

  function frame(now) {
    redraw()
    // Draw completed strokes
    for (let i = 0; i < strokeIndex; i++) drawSmoothStroke(strokes.value[i])

    if (strokeIndex >= strokes.value.length) {
      isReplaying.value = false
      return
    }

    const stroke = strokes.value[strokeIndex]
    const elapsed = now - strokeStartTime
    const pts = stroke.points
    const lastTime = pts[pts.length - 1].t
    const count = pts.findIndex(p => p.t > elapsed)
    const upTo = count === -1 ? pts.length : count
    drawSmoothStroke({ ...stroke, points: pts.slice(0, upTo) })

    if (elapsed >= lastTime) {
      strokeIndex++
      strokeStartTime = now
    }
    requestAnimationFrame(frame)
  }
  requestAnimationFrame(frame)
}

// Paste background (image or text)
function onPaste(e) {
  const items = e.clipboardData.items
  for (const item of items) {
    if (item.type.startsWith('image/')) {
      const file = item.getAsFile()
      const img = new Image()
      img.onload = () => {
        backgroundImage = img
        backgroundHTML = null
        redraw()
      }
      img.src = URL.createObjectURL(file)
      return
    } else if (item.type === 'text/plain') {
      item.getAsString(str => {
        backgroundHTML = str
        backgroundImage = null
        redraw()
      })
      return
    }
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

onMounted(() => {
  setupCanvas()
  // ✅ Global paste listener
  window.addEventListener('paste', onPaste)
})
onBeforeUnmount(() => {
  window.removeEventListener('paste', onPaste)
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
  touch-action: none;
  display: block;
}
</style>
