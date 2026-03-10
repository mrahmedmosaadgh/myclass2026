<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 p-4">
    <div class="max-w-5xl mx-auto">
      <h1 class="text-center text-5xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
        ✨ Magic Canvas ✨
      </h1>
      <p class="text-center text-gray-600 mb-8">Draw • Erase • Replay • Paste Background • Save/Load</p>

      <!-- Toolbar -->
      <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-6 mb-8 border border-white/30">
        <div class="flex flex-wrap gap-4 justify-center items-center text-sm">

          <!-- Pen / Eraser -->
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

          <!-- Color & Size -->
          <div class="flex items-center gap-6">
            <input type="color" v-model="color" class="w-14 h-14 rounded-full cursor-pointer shadow-xl hover:scale-110 transition" title="Color" />
            <div class="flex items-center gap-3">
              <input type="range" v-model.number="lineWidth" min="1" max="50" class="w-40 accent-purple-600" />
              <span class="font-bold text-lg w-12 text-right">{{ lineWidth }}</span>
            </div>
          </div>

          <!-- Main Actions -->
          <div class="flex gap-3">
            <button @click="clear" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-xl shadow-lg transition">Clear All</button>
            <button @click="undo" :disabled="historyStep <= 0" class="bg-gray-600 hover:bg-gray-700 disabled:opacity-40 text-white px-6 py-3 rounded-xl shadow-lg transition">Undo</button>
            <button @click="startReplay" :disabled="strokes.length === 0" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl shadow-lg transition flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-6.718-3.873A1 1 0 007 8.135v7.73a1 1 0 001.034.968l6.718-3.873a1 1 0 000-1.792z"/></svg>
              Replay
            </button>
          </div>

          <!-- Background + Save/Load -->
          <div class="flex gap-3">
            <button @click="pasteFromClipboard"
              class="relative bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-6 py-3 rounded-xl shadow-lg transition flex items-center gap-2 overflow-hidden group">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
              Paste BG
              <span class="absolute inset-0 bg-white/20 -translate-x-full group-hover:translate-x-0 transition-transform duration-500"></span>
            </button>

            <button v-if="backgroundImage" @click="removeBackground"
              class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl shadow-lg transition flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
          style="height: calc(100vh - 280px); min-height: 500px;"
        ></canvas>
      </div>

      <!-- Replay Indicator -->
      <div v-if="isReplaying" class="fixed inset-0 pointer-events-none flex items-end justify-center pb-12 z-50">
        <div class="bg-black/90 text-white px-10 py-5 rounded-full text-2xl font-bold shadow-2xl animate-pulse">
          Replaying... {{ Math.round(replayProgress) }}%
        </div>
      </div>

      <div class="text-center mt-4 text-sm text-gray-500">
        Tip: Copy any image → Ctrl+V (or click "Paste BG")
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
const replayProgress = ref(0)
const backgroundImage = ref<string | null>(null)

interface Point { x: number; y: number }
interface Stroke { tool: 'pen' | 'eraser'; color: string | null; width: number; path: Point[] }

const strokes = ref<Stroke[]>([])
const history = ref<ImageData[]>([])
const historyStep = ref(-1)

onMounted(() => {
  setupCanvas()
  window.addEventListener('resize', resizeCanvas)
  window.addEventListener('paste', handleGlobalPaste)
})

onUnmounted(() => {
  window.removeEventListener('resize', resizeCanvas)
  window.removeEventListener('paste', handleGlobalPaste)
})

function setupCanvas() {
  if (!canvas.value) return
  ctx = canvas.value.getContext('2d')
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
  strokes.value.push({ tool: tool.value, color: tool.value === 'pen' ? color.value : null, width: lineWidth.value, path: [point] })
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

  if (backgroundImage.value) {
    const img = new Image()
    img.onload = () => {
      ctx!.drawImage(img, 0, 0, canvas.value!.width / devicePixelRatio, canvas.value!.height / devicePixelRatio)
      drawAllStrokes()
    }
    img.src = backgroundImage.value
  } else {
    drawAllStrokes()
  }
}

function drawAllStrokes() {
  strokes.value.forEach(stroke => {
    if (stroke.path.length < 2) return
    ctx!.beginPath()
    ctx!.moveTo(stroke.path[0].x / devicePixelRatio, stroke.path[0].y / devicePixelRatio)
    setStrokeStyle(stroke)
    for (let i = 1; i < stroke.path.length; i++) {
      ctx!.lineTo(stroke.path[i].x / devicePixelRatio, stroke.path[i].y / devicePixelRatio)
    }
    ctx!.stroke()
  })
}

async function startReplay() {
  if (!strokes.value.length || !ctx || !canvas.value) return
  isReplaying.value = true
  replayProgress.value = 0
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)

  // Draw background first
  if (backgroundImage.value) {
    const img = new Image()
    img.src = backgroundImage.value
    await new Promise(r => img.onload = r)
    ctx.drawImage(img, 0, 0, canvas.value.width / devicePixelRatio, canvas.value.height / devicePixelRatio)
  }

  let total = strokes.value.reduce((a, s) => a + s.path.length, 0)
  let drawn = 0

  for (const stroke of strokes.value) {
    if (stroke.path.length < 2) continue
    ctx.beginPath()
    ctx.moveTo(stroke.path[0].x / devicePixelRatio, stroke.path[0].y / devicePixelRatio)
    setStrokeStyle(stroke)
    for (let i = 1; i < stroke.path.length; i++) {
      ctx.lineTo(stroke.path[i].x / devicePixelRatio, stroke.path[i].y / devicePixelRatio)
      ctx.stroke()
      drawn++
      replayProgress.value = (drawn / total) * 100
      await new Promise(requestAnimationFrame)
    }
  }
  isReplaying.value = false
}

// FIXED: Convert blob → permanent base64
function loadBackgroundImage(blobUrl: string) {
  const img = new Image()
  img.onload = () => {
    const temp = document.createElement('canvas')
    temp.width = img.width
    temp.height = img.height
    const tctx = temp.getContext('2d')!
    tctx.drawImage(img, 0, 0)
    backgroundImage.value = temp.toDataURL('image/png') // permanent base64
    redrawAll()
  }
  img.onerror = () => alert('Failed to load image')
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
    alert('No image found in clipboard')
  } catch (e) {
    alert('Clipboard access denied – copy an image first!')
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
    version: 4,
    background: backgroundImage.value,
    strokes: strokes.value,
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