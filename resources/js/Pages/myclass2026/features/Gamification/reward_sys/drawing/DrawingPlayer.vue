<template>
  <div class="player">
    <input type="file" accept="application/json" @change="onFileChange" />

    <div v-if="error" class="error">{{ error }}</div>

    <canvas ref="canvas" class="canvas"></canvas>

    <div class="controls">
      <button :disabled="!isValid || isReplaying" @click="replay">Replay</button>
      <button @click="clear">Clear</button>
    </div>
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
const isReplaying = ref(false)

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
}

function clear() {
  clearCanvas()
  drawingData.value = null
  isValid.value = false
  error.value = ''
}

function onFileChange(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = () => {
    try {
      const json = JSON.parse(reader.result)
      validateJson(json)
    } catch (err) {
      error.value = 'Invalid JSON file'
      isValid.value = false
    }
  }
  reader.readAsText(file)
}

function validateJson(json) {
  // Basic schema check
  if (
    json &&
    Array.isArray(json.strokes) &&
    typeof json.width === 'number' &&
    typeof json.height === 'number'
  ) {
    // Check strokes structure
    const validStrokes = json.strokes.every(s =>
      Array.isArray(s.points) &&
      s.points.every(p => 'x' in p && 'y' in p && 't' in p)
    )
    if (validStrokes) {
      drawingData.value = json
      isValid.value = true
      error.value = ''
      return
    }
  }
  error.value = 'File structure not valid for replay'
  isValid.value = false
}

function drawSmoothStroke(stroke, upToIndex = null) {
  const pts = upToIndex ? stroke.points.slice(0, upToIndex) : stroke.points
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

function replay() {
  if (!isValid.value || !drawingData.value) return
  isReplaying.value = true
  clearCanvas()

  const strokes = drawingData.value.strokes
  const start = performance.now()
  let strokeIndex = 0
  let strokeStartTime = start

  function frame(now) {
    clearCanvas()
    // Draw completed strokes
    for (let i = 0; i < strokeIndex; i++) {
      drawSmoothStroke(strokes[i])
    }

    if (strokeIndex >= strokes.length) {
      isReplaying.value = false
      return
    }

    const stroke = strokes[strokeIndex]
    const elapsed = now - strokeStartTime
    const pts = stroke.points
    const lastTime = pts[pts.length - 1].t

    // Find how many points to draw
    const count = pts.findIndex(p => p.t > elapsed)
    const upTo = count === -1 ? pts.length : count
    drawSmoothStroke(stroke, upTo)

    if (elapsed >= lastTime) {
      strokeIndex++
      strokeStartTime = now
    }

    requestAnimationFrame(frame)
  }

  requestAnimationFrame(frame)
}

onMounted(() => {
  setupCanvas()
})
</script>

<style scoped>
.player {
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
