<!-- DrawingPad.vue -->
<template>
  <div class="pad">
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
      <button @click="clear">Clear</button>
      <button @click="saveJson">Save JSON</button>
      <button :disabled="isReplaying" @click="replay">Replay</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

/**
 * Data model:
 * strokes: [
 *   { points: [{x, y, t}], color, size }
 * ]
 * t is ms timestamp offset from stroke start, enabling time-accurate replay.
 */

const canvas = ref(null)
let ctx = null
const width = 800
const height = 500

// Drawing state
const strokes = ref([])       // All completed strokes
let currentStroke = null      // Stroke being drawn
let isDrawing = false
const isReplaying = ref(false)

// Style
const penColor = ref('#111')
const penSize = ref(3)

// For high-DPI crispness
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
  ctx.strokeStyle = penColor.value
  ctx.lineWidth = penSize.value
  clearCanvas()
}

function clearCanvas() {
  ctx.clearRect(0, 0, width, height)
}

// Pointer helpers
function getPos(ev) {
  if (ev.touches && ev.touches[0]) {
    const rect = canvas.value.getBoundingClientRect()
    return {
      x: ev.touches[0].clientX - rect.left,
      y: ev.touches[0].clientY - rect.top,
    }
  } else {
    const rect = canvas.value.getBoundingClientRect()
    return {
      x: ev.clientX - rect.left,
      y: ev.clientY - rect.top,
    }
  }
}

// Smoothing: convert raw points to a smooth path using quadratic midpoints.
// This produces a visually pleasing stroke without heavy computation.
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

  // Last segment to the final point
  ctx.lineTo(pts[pts.length - 1].x, pts[pts.length - 1].y)
  ctx.stroke()
}

// Live drawing preview for the active stroke
function drawLive() {
  clearCanvas()
  // Draw existing strokes
  for (const s of strokes.value) drawSmoothStroke(s)
  // Draw current stroke
  if (currentStroke) drawSmoothStroke(currentStroke)
}

function onPointerDown(ev) {
  isDrawing = true
  const pos = getPos(ev)
  const startTime = performance.now()

  currentStroke = {
    points: [{ x: pos.x, y: pos.y, t: 0 }],
    color: penColor.value,
    size: penSize.value,
    _startTime: startTime, // internal for timing
  }
  drawLive()
}

function onPointerMove(ev) {
  if (!isDrawing || !currentStroke) return
  const pos = getPos(ev)
  const t = performance.now() - currentStroke._startTime

  // Optional micro-smoothing: skip points too close to reduce jitter
  const last = currentStroke.points[currentStroke.points.length - 1]
  const dx = pos.x - last.x
  const dy = pos.y - last.y
  const dist2 = dx * dx + dy * dy
  if (dist2 < 1.5) return

  currentStroke.points.push({ x: pos.x, y: pos.y, t })
  drawLive()
}

function onPointerUp() {
  if (!isDrawing) return
  isDrawing = false
  if (currentStroke && currentStroke.points.length > 0) {
    // Remove internal field before storing
    delete currentStroke._startTime
    strokes.value.push(currentStroke)
  }
  currentStroke = null
  drawLive()
}

function clear() {
  strokes.value = []
  currentStroke = null
  clearCanvas()
}

function saveJson() {
  const payload = {
    version: 1,
    width,
    height,
    pen: { color: penColor.value, size: penSize.value },
    strokes: strokes.value.map(s => ({
      points: s.points,
      color: s.color,
      size: s.size,
    })),
  }
  const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `drawing-${Date.now()}.json`
  a.click()
  URL.revokeObjectURL(url)
}

// Replay using each point's t (ms offset). Uses requestAnimationFrame for timing fidelity.
async function replay() {
  if (isReplaying.value || strokes.value.length === 0) return
  isReplaying.value = true
  clearCanvas()

  const start = performance.now()
  let strokeIndex = 0
  let pointIndex = 0

  function drawUpTo(stroke, upToTime) {
    // Find how many points should be drawn for this stroke
    const pts = []
    for (const p of stroke.points) {
      if (p.t <= upToTime) pts.push(p)
      else break
    }
    if (pts.length > 1) {
      ctx.save()
      drawSmoothStroke({ ...stroke, points: pts })
      ctx.restore()
    }
    return pts.length
  }

  function frame(now) {
    clearCanvas()
    // Draw fully completed strokes before current one
    for (let i = 0; i < strokeIndex; i++) {
      drawSmoothStroke(strokes.value[i])
    }

    const elapsed = now - start

    if (strokeIndex >= strokes.value.length) {
      isReplaying.value = false
      return
    }

    const stroke = strokes.value[strokeIndex]
    const strokeDuration = stroke.points[stroke.points.length - 1].t
    const localTime = Math.min(elapsed - pointIndex, strokeDuration)

    const drawnPoints = drawUpTo(stroke, Math.max(0, localTime))

    // If we've reached end of this stroke, advance to next
    if (localTime >= strokeDuration) {
      strokeIndex++
      pointIndex = elapsed // sync offset
    }

    // Also draw remaining future strokes faintly (optional): skip to keep focus
    requestAnimationFrame(frame)
  }

  requestAnimationFrame(frame)
}

onMounted(() => {
  setupCanvas()
  window.addEventListener('resize', setupCanvas)
})
onBeforeUnmount(() => {
  window.removeEventListener('resize', setupCanvas)
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
.canvas {
  touch-action: none; /* Important for smooth finger drawing */
  display: block;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 6px;
}
.controls {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}
button {
  padding: 6px 10px;
  border: 1px solid #ccc;
  background: #f4f4f4;
  border-radius: 4px;
  cursor: pointer;
}
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
