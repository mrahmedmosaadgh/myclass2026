<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 p-4">
    <div class="max-w-5xl mx-auto">
      <h1 class="text-center text-5xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
        Magic Canvas
      </h1>
      <p class="text-center text-gray-600 mb-8">Draw • Erase • Replay • Save as JSON</p>

      <!-- Toolbar -->
      <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-6 mb-8 border border-white/30">
        <div class="flex flex-wrap gap-4 justify-center items-center">

          <!-- Pen / Eraser -->
          <div class="flex rounded-xl overflow-hidden shadow-lg">
            <button
              @click="tool = 'pen'"
              :class="tool === 'pen' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white' : 'bg-gray-100 text-gray-700'"
              class="flex items-center gap-2 px-6 py-4 font-medium transition"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
              Pen
            </button>
            <button
              @click="tool = 'eraser'"
              :class="tool === 'eraser' ? 'bg-gradient-to-r from-red-500 to-orange-500 text-white' : 'bg-gray-100 text-gray-700'"
              class="flex items-center gap-2 px-6 py-4 font-medium transition"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v4m0 4v.01"/></svg>
              Eraser
            </button>
          </div>

          <!-- Color & Size -->
          <div class="flex items-center gap-6">
            <input type="color" v-model="color" class="w-14 h-14 rounded-full cursor-pointer shadow-xl hover:scale-110 transition" />
            <div class="flex items-center gap-3">
              <input type="range" v-model.number="lineWidth" min="1" max="50" class="w-40 accent-purple-600" />
              <span class="font-bold text-lg w-12 text-right">{{ lineWidth }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3">
            <button @click="clear" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-xl shadow-lg transition">Clear</button>
            <button @click="undo" :disabled="historyStep === 0" class="bg-gray-600 hover:bg-gray-700 disabled:opacity-40 text-white px-6 py-3 rounded-xl shadow-lg transition">Undo</button>
            <button @click="startReplay" :disabled="strokes.length === 0" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl shadow-lg transition flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-6.718-3.873A1 1 0 007 8.135v7.73a1 1 0 001.034.968l6.718-3.873a1 1 0 000-1.792z"/></svg>
              Replay
            </button>
          </div>

          <!-- Save / Load -->
          <div class="flex gap-3">
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
        <canvas
          ref="canvas"
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
      <div v-if="isReplaying" class="fixed inset-0 pointer-events-none flex items-end justify-center pb-12">
        <div class="bg-black/80 text-white px-8 py-4 rounded-full text-xl font-bold shadow-2xl animate-pulse">
          Replaying... {{ Math.round(replayProgress) }}%
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'

const canvas = ref<HTMLCanvasElement | null>(null)
let ctx: CanvasRenderingContext2D | null = null

const tool = ref<'pen' | 'eraser'>('pen')
const color = ref('#8B5CF6')
const lineWidth = ref(6)

const isDrawing = ref(false)
const isReplaying = ref(false)
const replayProgress = ref(0)

interface Point { x: number; y: number }
interface Stroke {
  tool: 'pen' | 'eraser'
  color: string | null
  width: number
  path: Point[]
}

const strokes = ref<Stroke[]>([])
const history = ref<ImageData[]>([])
const historyStep = ref(0)  // for proper undo

onMounted(() => {
  setupCanvas()
  window.addEventListener('resize', resizeCanvas)
})

onUnmounted(() => {
  window.removeEventListener('resize', resizeCanvas)
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
  const oldWidth = canvas.value.width
  const oldHeight = canvas.value.height

  canvas.value.width = rect.width * devicePixelRatio
  canvas.value.height = rect.height * devicePixelRatio

  ctx?.scale(devicePixelRatio, devicePixelRatio)

  // Redraw everything after resize
  if (oldWidth > 0 && oldHeight > 0) {
    redrawAll()
  }
  saveToHistory()
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
  const newStroke: Stroke = {
    tool: tool.value,
    color: tool.value === 'pen' ? color.value : null,
    width: lineWidth.value,
    path: [point]
  }
  strokes.value.push(newStroke)

  ctx.beginPath()
  ctx.moveTo(point.x / devicePixelRatio, point.y / devicePixelRatio)

  if (tool.value === 'eraser') {
    ctx.globalCompositeOperation = 'destination-out'
  } else {
    ctx.globalCompositeOperation = 'source-over'
    ctx.strokeStyle = color.value
    ctx.lineWidth = lineWidth.value
  }
}

function draw(e: PointerEvent) {
  if (!isDrawing.value || !ctx || strokes.value.length === 0) return

  const point = getPoint(e)
  const currentStroke = strokes.value[strokes.value.length - 1]
  currentStroke.path.push(point)

  ctx.lineTo(point.x / devicePixelRatio, point.y / devicePixelRatio)
  ctx.stroke()
}

function stopDrawing() {
  if (!isDrawing.value) return
  isDrawing.value = false
  saveToHistory()
}

function saveToHistory() {
  if (!ctx || !canvas.value) return
  const imageData = ctx.getImageData(0, 0, canvas.value.width, canvas.value.height)
  history.value = history.value.slice(0, historyStep.value + 1)
  history.value.push(imageData)
  historyStep.value = history.value.length - 1
}

function undo() {
  if (historyStep.value <= 0) return
  historyStep.value--
  restoreFromHistory()
}

function restoreFromHistory() {
  if (!ctx || !canvas.value || historyStep.value < 0) return
  ctx.putImageData(history.value[historyStep.value], 0, 0)
}

function clear() {
  if (!ctx || !canvas.value) return
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)
  strokes.value = []
  history.value = []
  historyStep.value = -1
  saveToHistory()
}

function redrawAll() {
  if (!ctx || !canvas.value) return
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)

  strokes.value.forEach(stroke => {
    if (stroke.path.length < 2) return

    ctx.beginPath()
    ctx.moveTo(stroke.path[0].x / devicePixelRatio, stroke.path[0].y / devicePixelRatio)

    if (stroke.tool === 'eraser') {
      ctx.globalCompositeOperation = 'destination-out'
    } else {
      ctx.globalCompositeOperation = 'source-over'
      ctx.strokeStyle = stroke.color!
      ctx.lineWidth = stroke.width
    }

    for (let i = 1; i < stroke.path.length; i++) {
      ctx.lineTo(stroke.path[i].x / devicePixelRatio, stroke.path[i].y / devicePixelRatio)
    }
    ctx.stroke()
  })
}

async function startReplay() {
  if (strokes.value.length === 0 || !ctx || !canvas.value) return

  isReplaying.value = true
  replayProgress.value = 0

  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)

  let totalPoints = 0
  strokes.value.forEach(s => totalPoints += s.path.length)
  let drawn = 0

  for (const stroke of strokes.value) {
    if (stroke.path.length < 2) continue

    ctx.beginPath()
    ctx.moveTo(stroke.path[0].x / devicePixelRatio, stroke.path[0].y / devicePixelRatio)

    if (stroke.tool === 'eraser') {
      ctx.globalCompositeOperation = 'destination-out'
    } else {
      ctx.globalCompositeOperation = 'source-over'
      ctx.strokeStyle = stroke.color!
      ctx.lineWidth = stroke.width
    }

    for (let i = 1; i < stroke.path.length; i++) {
      const p = stroke.path[i]
      ctx.lineTo(p.x / devicePixelRatio, p.y / devicePixelRatio)
      ctx.stroke()

      drawn++
      replayProgress.value = (drawn / totalPoints) * 100
      await new Promise(requestAnimationFrame)
    }
  }

  isReplaying.value = false
  replayProgress.value = 0
}

function saveDrawing() {
  const data = {
    version: 2,
    strokes: strokes.value,
    createdAt: new Date().toISOString()
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
      if (data.strokes && Array.isArray(data.strokes)) {
        strokes.value = data.strokes
        redrawAll()
        saveToHistory()
      }
    } catch (err) {
      alert('Invalid file!')
    }
  }
  reader.readAsText(file)
}
</script>

<style>
.cursor-eraser { cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="12" fill="none" stroke="%23ff3b30" stroke-width="3"/><path d="M8 8 L24 24" stroke="%23ff3b30" stroke-width="3"/></svg>') 16 16, auto; }
</style>