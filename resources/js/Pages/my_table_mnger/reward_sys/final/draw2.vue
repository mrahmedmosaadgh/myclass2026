<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 p-4">
    <div class="max-w-5xl mx-auto">
      <h1 class="text-center text-5xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
        Magic Canvas
      </h1>
      <p class="text-center text-gray-600 mb-8">Draw • Breakpoints • Cinematic Replay • Paste Background</p>

      <!-- Toolbar -->
      <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-6 mb-8 border border-white/30">
        <div class="flex flex-wrap gap-4 justify-center items-center text-sm">

          <!-- Tools -->
          <div class="flex rounded-xl overflow-hidden shadow-lg">
            <button @click="tool = 'pen'" :class="tool === 'pen' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white' : 'bg-gray-100 text-gray-700'"
              class="flex items-center gap-2 px-6 py-4 font-medium transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
              Pen
            </button>
            <button @click="tool = 'eraser'" :class="tool === 'eraser' ? 'bg-gradient-to-r from-red-500 to-orange-500 text-white' : 'bg-gray-100 text-gray-700'"
              class="flex items-center gap-2 px-6 py-4 font-medium transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v4m0 4v.01"/></svg>
              Eraser
            </button>
          </div>

          <div class="flex items-center gap-6">
            <input type="color" v-model="color" class="w-14 h-14 rounded-full cursor-pointer shadow-xl hover:scale-110 transition" />
            <div class="flex items-center gap-3">
              <input type="range" v-model.number="lineWidth" min="1" max="50" class="w-40 accent-purple-600" />
              <span class="font-bold text-lg w-12 text-right">{{ lineWidth }}</span>
            </div>
          </div>

          <!-- Add Breakpoint -->
          <button v-if="!isReplaying && strokes.length > 0 && !isDrawing"
            @click="addBreakpoint"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-xl shadow-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/></svg>
            Add Breakpoint
          </button>

          <div class="flex gap-3">
            <button @click="clear" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-xl shadow-lg transition">Clear All</button>
            <button @click="undo" :disabled="historyStep <= 0" class="bg-gray-600 hover:bg-gray-700 disabled:opacity-40 text-white px-6 py-3 rounded-xl shadow-lg transition">Undo</button>
          </div>

          <div class="flex gap-3">
            <button @click="pasteFromClipboard"
              class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-6 py-3 rounded-xl shadow-lg transition flex items-center gap-2">
              Paste BG
            </button>
            <button v-if="backgroundImage" @click="removeBackground"
              class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl shadow-lg transition">
              Remove BG
            </button>
            <button @click="saveDrawing" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl shadow-lg transition">Save JSON</button>
            <label class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl shadow-lg cursor-pointer transition">
              Load JSON
              <input type="file" @change="loadDrawing" accept=".json" class="hidden" />
            </label>
          </div>
        </div>
      </div>

      <!-- Canvas -->
      <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/40">
        <canvas ref="canvas"
          @pointerdown="startDrawing"
          @pointermove="draw"
          @pointerup="stopDrawing"
          @pointerleave="stopDrawing"
          @pointercancel="stopDrawing"
          class="w-full block touch-none"
          :class="tool === 'eraser' ? 'cursor-eraser' : 'cursor-crosshair'"
          style="height: calc(100vh - 380px); min-height: 500px;"
        ></canvas>
      </div>

      <!-- Cinematic Replay Panel -->
      <div v-if="strokes.length > 0" class="mt-8 bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30">
        <h3 class="text-center text-2xl font-bold mb-6 text-purple-700">Cinematic Replay</h3>

        <!-- Progress Bar -->
        <div class="relative h-14 bg-gray-800 rounded-full overflow-hidden cursor-pointer shadow-inner" @click="seekTo">
          <div class="absolute inset-0 bg-gradient-to-r from-cyan-500 to-purple-600 transition-all duration-300 ease-out"
               :style="{ width: replayProgress + '%' }"></div>

          <!-- Breakpoint Markers -->
          <div v-for="(bp, i) in breakpoints" :key="i"
               class="absolute top-0 bottom-0 w-2 bg-yellow-400 shadow-lg transform -translate-x-1/2"
               :style="{ left: (bp.progress * 100) + '%' }"
               :title="`Breakpoint ${i + 1}`">
            <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-yellow-500 text-white text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
              {{ i + 1 }}
            </div>
          </div>

          <div class="absolute inset-0 flex items-center justify-center text-white text-xl font-bold">
            {{ Math.round(replayProgress) }}%
          </div>
        </div>

        <!-- Controls -->
        <div class="flex justify-center gap-6 mt-8">
          <button @click="startReplay" :disabled="isReplaying"
            class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-10 py-5 rounded-2xl shadow-2xl text-2xl font-bold transition flex items-center gap-4 disabled:opacity-50">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-6.718-3.873A1 1 0 007 8.135v7.73a1 1 0 001.034.968l6.718-3.873a1 1 0 000-1.792z"/></svg>
            {{ isReplaying && !replayPaused ? 'Playing...' : 'Start Replay' }}
          </button>

          <button v-if="replayPaused" @click="resumeReplay"
            class="bg-green-500 hover:bg-green-600 text-white px-10 py-5 rounded-2xl shadow-2xl text-2xl font-bold transition">
            Continue
          </button>

          <button v-if="isReplaying" @click="stopReplay"
            class="bg-red-500 hover:bg-red-600 text-white px-10 py-5 rounded-2xl shadow-2xl text-2xl font-bold transition">
            Stop
          </button>

          <button v-if="replayPaused && breakpoints.length > 0" @click="skipToNextBreakpoint"
            class="bg-purple-500 hover:bg-purple-600 text-white px-8 py-5 rounded-2xl shadow-2xl text-lg font-bold transition">
            Skip Next →
          </button>
        </div>

        <p class="text-center mt-6 text-gray-600 text-lg">
          {{ breakpoints.length }} breakpoint{{ breakpoints.length !== 1 ? 's' : '' }} • Auto-pauses at markers • Click bar to seek
        </p>
      </div>

      <div class="text-center mt-6 text-sm text-gray-500">
        Copy image → Ctrl+V to paste as background • Add breakpoints after sections for epic pauses!
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const canvas = ref<HTMLCanvasElement | null>(null)
let ctx: CanvasRenderingContext2D | null = null

const tool = ref<'pen' | 'eraser'>('pen')
const color = ref('#8B5CF6')
const lineWidth = ref(6)
const isDrawing = ref(false)
const isReplaying = ref(false)
const replayPaused = ref(false)
const replayProgress = ref(0)
const backgroundImage = ref<string | null>(null)

// Breakpoints: {globalPointIndex: number, progress: number}
const breakpoints = ref<{ globalIndex: number; progress: number }[]>([])

interface Point { x: number; y: number }
interface Stroke { tool: 'pen' | 'eraser'; color: string | null; width: number; path: Point[] }

const strokes = ref<Stroke[]>([])
const history = ref<ImageData[]>([])
const historyStep = ref(-1)

let replayFrame: number | null = null
let replayDrawnPoints = 0
let replayCurrentStrokeIndex = 0
let replayCurrentPathIndex = 0
let replayBreakpointIndex = 0

onMounted(() => {
  setupCanvas()
  window.addEventListener('resize', resizeCanvas)
  window.addEventListener('paste', handleGlobalPaste)
})

onUnmounted(() => {
  window.removeEventListener('resize', resizeCanvas)
  window.removeEventListener('paste', handleGlobalPaste)
  if (replayFrame) cancelAnimationFrame(replayFrame)
})

function setupCanvas() {
  if (!canvas.value) return
  // FIXED: Add willReadFrequently to eliminate the warning
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
  const s = stroke || { tool: tool.value, color: color.value, width: lineWidth.value }
  if (s.tool === 'eraser') {
    ctx.globalCompositeOperation = 'destination-out'
    // FIXED: No strokeStyle for eraser, just width
    ctx.lineWidth = s.width
  } else {
    ctx.globalCompositeOperation = 'source-over'
    ctx.strokeStyle = s.color!
    ctx.lineWidth = s.width
  }
}

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
  if (!ctx || !canvas.value) return
  ctx.putImageData(history.value[historyStep.value], 0, 0)
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

function redrawAll() {
  if (!ctx || !canvas.value) return
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)
  ctx.globalCompositeOperation = 'source-over' // Reset
  if (backgroundImage.value) {
    const img = new Image()
    img.onload = () => {
      ctx.drawImage(img, 0, 0, canvas.value!.width / devicePixelRatio, canvas.value!.height / devicePixelRatio)
      drawAllStrokes()
    }
    img.src = backgroundImage.value
    return
  }
  drawAllStrokes()
}

function drawAllStrokes() {
  if (!ctx) return
  strokes.value.forEach(stroke => {
    if (stroke.path.length < 2) return
    ctx.beginPath()
    ctx.moveTo(stroke.path[0].x / devicePixelRatio, stroke.path[0].y / devicePixelRatio)
    setStrokeStyle(stroke)
    for (let i = 1; i < stroke.path.length; i++) {
      ctx.lineTo(stroke.path[i].x / devicePixelRatio, stroke.path[i].y / devicePixelRatio)
    }
    ctx.stroke()
  })
}

function addBreakpoint() {
  if (strokes.value.length === 0) return
  // FIXED: Calculate global point index for accurate progress
  let totalPoints = 0
  for (const stroke of strokes.value) {
    totalPoints += stroke.path.length
  }
  let currentGlobalIndex = 0
  for (let i = 0; i < strokes.value.length - 1; i++) {
    currentGlobalIndex += strokes.value[i].path.length
  }
  currentGlobalIndex += strokes.value[strokes.value.length - 1].path.length

  const progress = totalPoints > 0 ? currentGlobalIndex / totalPoints : 0

  breakpoints.value.push({
    globalIndex: currentGlobalIndex,
    progress
  })
  // Sort by progress
  breakpoints.value.sort((a, b) => a.progress - b.progress)
}

async function startReplay() {
  if (strokes.value.length === 0 || !ctx || !canvas.value) return

  // Reset replay state
  isReplaying.value = true
  replayPaused.value = false
  replayProgress.value = 0
  replayDrawnPoints = 0
  replayCurrentStrokeIndex = 0
  replayBreakpointIndex = 0
  replayFrame = null

  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)
  ctx.globalCompositeOperation = 'source-over'

  // Draw background first
  if (backgroundImage.value) {
    const img = new Image()
    img.src = backgroundImage.value
    await new Promise(resolve => img.onload = resolve)
    ctx.drawImage(img, 0, 0, canvas.value.width / devicePixelRatio, canvas.value.height / devicePixelRatio)
  }

  const totalPoints = strokes.value.reduce((sum, s) => sum + s.path.length, 0)
  if (totalPoints === 0) return

  // FIXED: Proper point-by-point replay with pauses
  const replayStep = () => {
    if (!isReplaying.value || replayPaused.value) {
      replayFrame = requestAnimationFrame(replayStep)
      return
    }

    if (replayDrawnPoints >= totalPoints) {
      isReplaying.value = false
      return
    }

    // Find current stroke and path index
    let pointCount = 0
    let strokeIndex = -1
    let pathIndex = -1
    for (let si = 0; si < strokes.value.length; si++) {
      const stroke = strokes.value[si]
      if (replayDrawnPoints < pointCount + stroke.path.length) {
        strokeIndex = si
        pathIndex = replayDrawnPoints - pointCount
        break
      }
      pointCount += stroke.path.length
    }

    if (strokeIndex !== -1 && pathIndex !== -1) {
      const stroke = strokes.value[strokeIndex]
      const point = stroke.path[pathIndex]

      if (pathIndex === 0) {
        ctx.beginPath()
        ctx.moveTo(point.x / devicePixelRatio, point.y / devicePixelRatio)
      } else {
        ctx.lineTo(point.x / devicePixelRatio, point.y / devicePixelRatio)
      }

      setStrokeStyle(stroke)
      ctx.stroke() // Stroke after each line segment for smooth animation
    }

    replayDrawnPoints++
    replayProgress.value = (replayDrawnPoints / totalPoints) * 100

    // FIXED: Check for breakpoint pause
    while (replayBreakpointIndex < breakpoints.value.length) {
      const bp = breakpoints.value[replayBreakpointIndex]
      if (replayDrawnPoints >= bp.globalIndex) {
        replayPaused.value = true
        replayBreakpointIndex++
        break
      } else {
        break
      }
    }

    replayFrame = requestAnimationFrame(replayStep)
  }

  replayFrame = requestAnimationFrame(replayStep)
}

function resumeReplay() {
  replayPaused.value = false
}

function stopReplay() {
  isReplaying.value = false
  replayPaused.value = false
  if (replayFrame) cancelAnimationFrame(replayFrame)
  replayDrawnPoints = 0
  redrawAll()
}

function skipToNextBreakpoint() {
  if (replayBreakpointIndex >= breakpoints.value.length) return
  const nextBp = breakpoints.value[replayBreakpointIndex]
  replayDrawnPoints = nextBp.globalIndex
  replayProgress.value = nextBp.progress * 100
  // Quick redraw up to here
  stopReplay()
  redrawAll()
  // Restart from next
  replayBreakpointIndex++
  startReplay()
}

function seekTo(e: MouseEvent) {
  const rect = (e.currentTarget as HTMLElement).getBoundingClientRect()
  const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width))
  const totalPoints = strokes.value.reduce((sum, s) => sum + s.path.length, 0)
  replayDrawnPoints = Math.round(percent * totalPoints)
  replayProgress.value = percent * 100
  // For now, just update progress; full seek redraw is advanced
}

function loadBackgroundImage(blobUrl: string) {
  const img = new Image()
  img.onload = () => {
    const temp = document.createElement('canvas')
    temp.width = img.width
    temp.height = img.height
    const tctx = temp.getContext('2d')!
    tctx.drawImage(img, 0, 0)
    backgroundImage.value = temp.toDataURL('image/png')
    redrawAll()
  }
  img.onerror = () => alert('Failed to load pasted image')
  img.src = blobUrl
}

async function pasteFromClipboard() {
  try {
    const items = await navigator.clipboard.read()
    for (const item of items) {
      const type = item.types.find(t => t.startsWith('image/'))
      if (type) {
        const blob = await item.getType(type)
        loadBackgroundImage(URL.createObjectURL(blob))
        return
      }
    }
    alert('No image in clipboard')
  } catch (e) {
    alert('Clipboard access denied or no image')
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

function saveDrawing() {
  const data = {
    version: 6,
    background: backgroundImage.value,
    strokes: strokes.value,
    breakpoints: breakpoints.value,
    createdAt: new Date().toISOString()
  }
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `magic-canvas-${new Date().toISOString().slice(0,10)}.json`
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
      strokes.value = data.strokes || []
      breakpoints.value = data.breakpoints || []
      redrawAll()
      saveToHistory()
    } catch (err) {
      alert('Invalid file!')
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