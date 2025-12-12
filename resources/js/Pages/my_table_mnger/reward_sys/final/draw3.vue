<!-- resources/js/Pages/my_table_mnger/reward_sys/final/Draw3.vue -->
<template>
  <div class="min-h-screen  bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50">

    <!-- Sticky Toolbar -->
    <CanvasToolbar
      :tool="tool"
      @update:tool="tool = $event"
      :color="color"
      @update:color="color = $event"
      :lineWidth="lineWidth"
      @update:lineWidth="lineWidth = $event"
      :bg-mode="bgMode"
      @update:bg-mode="bgMode = $event"
      :bg-align="bgAlign"
      @update:bg-align="bgAlign = $event"
      :is-drawing="isDrawing"
      :is-replaying="isReplaying"
      :replay-paused="replayPaused"
      :replay-progress="replayProgress"
      :strokes-length="strokes.length"
      :has-bg="!!backgroundImage"
      :history-step="historyStep"
      @add-breakpoint="addBreakpoint"
      @clear="clear"
      @undo="undo"
      @paste-bg="pasteFromClipboard"
      @remove-bg="removeBackground"
      @save="saveDrawing"
      @load="loadDrawing"
      @start-replay="startReplay"
      @stop-replay="stopReplay"
      @resume-replay="resumeReplay"
      @pause-replay="replayPaused = true"
    />

    <div class="max-w-5xl mx-auto px-4 pt-32 pb-10"> <!-- pt-32 = toolbar height -->

      <!-- Canvas -->
      <div class="bg-white/90  backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/40">
        <canvas
          ref="canvas"
          @pointerdown="startDrawing"
          @pointermove="draw"
          @pointerup="stopDrawing"
          @pointerleave="stopDrawing"
          @pointercancel="stopDrawing"
          class="w-full block touch-none"
          :class="tool === 'eraser' ? 'cursor-eraser' : 'cursor-crosshair'"
          style="height: calc(100vh - 280px); min-height: 520px;"
        />
      </div>

      <!-- Replay Panel (Removed, merged into toolbar) -->
      <!-- <div class="pt-412"> ... </div> -->

      <!-- Floating Record Button -->
      <button
        v-if="selectedBpIndex !== null && recordingIndex === null"
        @click="startRecording(selectedBpIndex)"
        class="  bottom-8 right-8 w-20 h-20 bg-red-600 hover:bg-red-700 rounded-full shadow-2xl flex items-center justify-center animate-bounce z-50"
        title="Start Voice Recording"
      >
        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
          <path d="M19 11c0 3.87-3.13 7-7 7s-7-3.13-7-7"/>
        </svg>
      </button>

      <!-- Recording Overlay -->
      <div v-if="recordingIndex !== null" class="  inset-0 bg-black/70 flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-12 text-center shadow-2xl">
          <div class="w-28 h-28 bg-red-600 rounded-full mx-auto mb-6 animate-pulse flex items-center justify-center">
            <div class="w-16 h-16 bg-white rounded-full"></div>
          </div>
          <p class="text-3xl font-bold text-red-600 mb-4">Recording...</p>
          <p class="text-gray-600 text-lg">(Max 10 seconds)</p>
          <button @click="stopRecording" class="mt-8 bg-gray-800 hover:bg-gray-900 text-white px-10 py-5 rounded-2xl text-xl font-bold transition">
            Stop & Save
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import CanvasToolbar from './draw/CanvasToolbar.vue'
// import ReplayPanel from './draw/ReplayPanel.vue' // Deprecated

const canvas = ref<HTMLCanvasElement | null>(null)
let ctx: CanvasRenderingContext2D | null = null
let mediaRecorder: MediaRecorder | null = null
let audioChunks: Blob[] = []

// Tools & Style
const tool = ref<'pen' | 'eraser'>('pen')
const color = ref('#8B5CF6')
const lineWidth = ref(6)

// Background Settings
const backgroundImage = ref<string | null>(null)
const bgMode = ref<'contain' | 'fill' | 'cover' | 'original'>('original')
const bgAlign = ref<'center' | 'top-center'>('top-center')

// Drawing State
const isDrawing = ref(false)
const isReplaying = ref(false)
const replayPaused = ref(false)
const replayProgress = ref(0)
const recordingIndex = ref<number | null>(null)
const selectedBpIndex = ref<number | null>(null)

interface Point { x: number; y: number }
interface Stroke { tool: 'pen' | 'eraser'; color: string | null; width: number; path: Point[] }
interface Breakpoint { globalIndex: number; progress: number; audio?: string }

const strokes = ref<Stroke[]>([])
const breakpoints = ref<Breakpoint[]>([])
const history = ref<ImageData[]>([])
const historyStep = ref(-1)
let replayDrawnPoints = 0
let replayFrame: number | null = null

onMounted(() => {
  setupCanvas()
  window.addEventListener('resize', resizeCanvas)
  window.addEventListener('paste', handleGlobalPaste as EventListener)
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('resize', resizeCanvas)
  window.removeEventListener('paste', handleGlobalPaste as EventListener)
  window.removeEventListener('keydown', handleKeydown)
  if (replayFrame) cancelAnimationFrame(replayFrame)
})

// ==================== KEYBOARD SHORTCUTS ====================
function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'F7') {
    e.preventDefault() // Prevent caret browsing or other defaults
    if (!isReplaying.value) {
      startReplay()
    } else {
      // Toggle pause/resume
      replayPaused.value = !replayPaused.value
    }
  }
  else if (e.key === 'F8') {
    e.preventDefault()
    stopReplay()
  }
  else if (e.key === 'F9') {
    e.preventDefault()
    // Force restart
    stopReplay()
    // Small delay to ensure state reset before starting again
    setTimeout(() => startReplay(), 10)
  }
}

// ==================== CANVAS SETUP ====================
function setupCanvas() {
  if (!canvas.value) return
  ctx = canvas.value.getContext('2d', { willReadFrequently: true })
  if (!ctx) return
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
  resizeCanvas()
  saveToHistory()
}

function resizeCanvas() {
  if (!canvas.value || !ctx) return
  const rect = canvas.value.getBoundingClientRect()
  canvas.value.width = rect.width * devicePixelRatio
  canvas.value.height = rect.height * devicePixelRatio
  ctx.scale(devicePixelRatio, devicePixelRatio)
  redrawAll()
}

function getPoint(e: PointerEvent): Point {
  if (!canvas.value) return { x: 0, y: 0 }
  const rect = canvas.value.getBoundingClientRect()
  return {
    x: (e.clientX - rect.left) * devicePixelRatio,
    y: (e.clientY - rect.top) * devicePixelRatio
  }
}

// ==================== DRAWING ====================
function startDrawing(e: PointerEvent) {
  if (isReplaying.value || !ctx) return
  isDrawing.value = true
  const point = getPoint(e)
  strokes.value.push({
    tool: tool.value,
    color: tool.value === 'pen' ? color.value : null,
    width: lineWidth.value,
    path: [point]
  })
  ctx.beginPath()
  ctx.moveTo(point.x / devicePixelRatio, point.y / devicePixelRatio)
  setStrokeStyle()
}

function draw(e: PointerEvent) {
  if (!isDrawing.value || !ctx || !strokes.value.length) return
  const point = getPoint(e)
  strokes.value[strokes.value.length - 1].path.push(point)
  ctx.lineTo(point.x / devicePixelRatio, point.y / devicePixelRatio)
  ctx.stroke()
}

function stopDrawing() {
  if (!isDrawing.value) return
  isDrawing.value = false
  saveToHistory()
}

function setStrokeStyle(stroke?: Stroke) {
  if (!ctx) return
  const s = stroke ?? { tool: tool.value, color: color.value, width: lineWidth.value }
  ctx.globalCompositeOperation = s.tool === 'eraser' ? 'destination-out' : 'source-over'
  if (s.tool !== 'eraser') ctx.strokeStyle = s.color!
  ctx.lineWidth = s.width
}

// ==================== HISTORY ====================
function saveToHistory() {
  if (!ctx || !canvas.value) return
  const data = ctx.getImageData(0, 0, canvas.value.width, canvas.value.height)
  history.value = history.value.slice(0, historyStep.value + 1)
  history.value.push(data)
  historyStep.value = history.value.length - 1
}

function undo() {
  if (historyStep.value <= 0) return
  historyStep.value--
  if (ctx && canvas.value) ctx.putImageData(history.value[historyStep.value], 0, 0)
}

function clear() {
  if (!ctx || !canvas.value) return
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)
  strokes.value = []
  breakpoints.value = []
  backgroundImage.value = null
  history.value = []
  historyStep.value = -1
  saveToHistory()
}

function removeBackground() {
  backgroundImage.value = null
  redrawAll()
}

// ==================== BACKGROUND RENDERING (Used in normal view AND replay) ====================
function drawBackground() {
  if (!ctx || !canvas.value || !backgroundImage.value) return
  const img = new Image()
  img.src = backgroundImage.value
  img.onload = () => {
    const canvasW = canvas.value!.width / devicePixelRatio
    const canvasH = canvas.value!.height / devicePixelRatio

    let x = 0, y = 0, w = img.width, h = img.height

    if (bgMode.value === 'contain') {
      const ratio = Math.min(canvasW / img.width, canvasH / img.height)
      w = img.width * ratio
      h = img.height * ratio
      x = (canvasW - w) / 2
      y = bgAlign.value === 'top-center' ? 0 : (canvasH - h) / 2
    }
    else if (bgMode.value === 'fill') {
      ctx!.drawImage(img, 0, 0, canvasW, canvasH)
      return
    }
    else if (bgMode.value === 'cover') {
      const ratio = Math.max(canvasW / img.width, canvasH / img.height)
      w = img.width * ratio
      h = img.height * ratio
      x = (canvasW - w) / 2
      y = (canvasH - h) / 2
    }
    else if (bgMode.value === 'original') {
      x = (canvasW - img.width) / 2
      y = bgAlign.value === 'top-center' ? 0 : (canvasH - img.height) / 2
    }

    ctx!.drawImage(img, x, y, w, h)
  }
}

function redrawAll() {
  if (!ctx || !canvas.value) return
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)
  drawBackground()
  drawAllStrokes()
}

function drawAllStrokes() {
  if (!ctx) return
  for (const stroke of strokes.value) {
    if (stroke.path.length < 2) continue
    ctx.beginPath()
    ctx.moveTo(stroke.path[0].x / devicePixelRatio, stroke.path[0].y / devicePixelRatio)
    setStrokeStyle(stroke)
    for (let i = 1; i < stroke.path.length; i++) {
      ctx.lineTo(stroke.path[i].x / devicePixelRatio, stroke.path[i].y / devicePixelRatio)
    }
    ctx.stroke()
  }
}

// ==================== BREAKPOINTS & RECORDING ====================
function addBreakpoint() {
  if (strokes.value.length === 0) return
  let total = 0
  for (const s of strokes.value) total += s.path.length
  let current = 0
  for (let i = 0; i < strokes.value.length - 1; i++) current += strokes.value[i].path.length
  current += strokes.value[strokes.value.length - 1].path.length
  const progress = total > 0 ? current / total : 0
  breakpoints.value.push({ globalIndex: current, progress })
  breakpoints.value.sort((a, b) => a.progress - b.progress)
}

async function startRecording(index: number) {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    mediaRecorder = new MediaRecorder(stream)
    audioChunks = []
    mediaRecorder.ondataavailable = e => audioChunks.push(e.data)
    mediaRecorder.onstop = () => {
      const blob = new Blob(audioChunks, { type: 'audio/webm' })
      const reader = new FileReader()
      reader.onload = () => {
        breakpoints.value[index].audio = reader.result as string
        recordingIndex.value = null
        selectedBpIndex.value = null
      }
      reader.readAsDataURL(blob)
      stream.getTracks().forEach(t => t.stop())
    }
    recordingIndex.value = index
    mediaRecorder.start()
    setTimeout(() => mediaRecorder?.state === 'recording' && mediaRecorder.stop(), 10000)
  } catch (err) {
    alert('Please allow microphone access')
    selectedBpIndex.value = null
  }
}

function stopRecording() {
  if (mediaRecorder?.state === 'recording') mediaRecorder.stop()
}

// ==================== REPLAY (NOW RESPECTS BACKGROUND SETTINGS!) ====================
async function startReplay() {
  if (strokes.value.length === 0 || !ctx || !canvas.value) return

  isReplaying.value = true
  replayPaused.value = false
  replayProgress.value = 0
  replayDrawnPoints = 0
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)

  // Redraw background with correct mode & alignment
  if (backgroundImage.value) {
    const img = new Image()
    img.src = backgroundImage.value
    await new Promise(r => img.onload = r)
    drawBackground() // Uses bgMode + bgAlign
  }

  const totalPoints = strokes.value.reduce((a, s) => a + s.path.length, 0)

  const step = () => {
    if (!isReplaying.value || replayPaused.value) {
      replayFrame = requestAnimationFrame(step)
      return
    }
    if (replayDrawnPoints >= totalPoints) {
      isReplaying.value = false
      return
    }

    let count = 0
    let stroke: Stroke | null = null
    let pathIndex = 0
    for (const s of strokes.value) {
      if (replayDrawnPoints < count + s.path.length) {
        stroke = s
        pathIndex = replayDrawnPoints - count
        break
      }
      count += s.path.length
    }

    if (stroke && ctx) {
      const p = stroke.path[pathIndex]
      if (pathIndex === 0) {
        ctx.beginPath()
        ctx.moveTo(p.x / devicePixelRatio, p.y / devicePixelRatio)
      } else {
        ctx.lineTo(p.x / devicePixelRatio, p.y / devicePixelRatio)
      }
      setStrokeStyle(stroke)
      ctx.stroke()
    }

    replayDrawnPoints++
    replayProgress.value = (replayDrawnPoints / totalPoints) * 100

    const bp = breakpoints.value.find(b => b.globalIndex === replayDrawnPoints && b.audio)
    if (bp?.audio) new Audio(bp.audio).play()

    if (breakpoints.value.some(b => b.globalIndex === replayDrawnPoints)) {
      replayPaused.value = true
    }

    replayFrame = requestAnimationFrame(step)
  }
  replayFrame = requestAnimationFrame(step)
}

function resumeReplay() { replayPaused.value = false }

function stopReplay() {
  isReplaying.value = false
  replayPaused.value = false
  if (replayFrame) cancelAnimationFrame(replayFrame)
  replayDrawnPoints = 0
  redrawAll()
}

// ==================== BACKGROUND PASTE (No more warning) ====================
async function pasteFromClipboard() {
  if (isReplaying.value) return
  try {
    const items = await navigator.clipboard.read()
    for (const item of items) {
      if (item.types.some(t => t.startsWith('image/'))) {
        const blob = await item.getType(item.types.find(t => t.startsWith('image/'))!)
        loadBackgroundImage(URL.createObjectURL(blob))
        return
      }
    }
  } catch (err) {
    console.log('No image in clipboard or permission denied')
  }
}

function handleGlobalPaste(e: ClipboardEvent) {
  if (isReplaying.value) return
  const items = e.clipboardData?.items
  if (!items) return
  for (const item of items) {
    if (item.type.startsWith('image/')) {
      e.preventDefault()
      const file = item.getAsFile()
      if (file) loadBackgroundImage(URL.createObjectURL(file))
      return
    }
  }
}

function loadBackgroundImage(url: string) {
  const img = new Image()
  img.onload = () => {
    const temp = document.createElement('canvas')
    temp.width = img.width
    temp.height = img.height
    temp.getContext('2d')!.drawImage(img, 0, 0)
    backgroundImage.value = temp.toDataURL('image/png')
    redrawAll()
  }
  img.src = url
}

// ==================== SAVE / LOAD ====================
function saveDrawing() {
  const data = {
    version: 10,
    background: backgroundImage.value,
    bgMode: bgMode.value,
    bgAlign: bgAlign.value,
    strokes: strokes.value,
    breakpoints: breakpoints.value
  }
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `drawing-${new Date().toISOString().slice(0,10)}.json`
  a.click()
  URL.revokeObjectURL(url)
}

function loadDrawing(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = () => {
    try {
      const data = JSON.parse(reader.result as string)
      backgroundImage.value = data.background || null
      bgMode.value = data.bgMode || 'original'
      bgAlign.value = data.bgAlign || 'top-center'
      strokes.value = data.strokes || []
      breakpoints.value = data.breakpoints || []
      redrawAll()
      saveToHistory()
    } catch (err) {
      alert('Invalid file')
    }
  }
  reader.readAsText(file)
}
</script>
<style scoped>
.cursor-eraser {
  cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="12" fill="none" stroke="%23ff3b30" stroke-width="3"/><path d="M8 8 L24 24" stroke="%23ff3b30" stroke-width="3"/></svg>') 16 16, auto;
}
</style>