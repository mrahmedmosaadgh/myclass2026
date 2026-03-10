<template>
  <div class="pdf-annotator-app">
    <!-- Upload -->
    <div v-if="!pdfUrl" class="upload-area">
      <label class="upload-box">
        <input type="file" accept=".pdf" @change="loadPdf" hidden />
        <div class="icon">📄</div>
        <p>Click or drop a PDF file here</p>
        <small>Maximum 50 MB</small>
      </label>
    </div>

    <!-- Viewer + Tools -->
    <div v-else class="viewer-area">
      <!-- Toolbar -->
      <div class="toolbar">
        <div class="toolbar-group">
          <span class="tool-label">Tools:</span>
          <button @click="setTool('pen')" :class="{active: tool==='pen'}" title="Pen">✏️ Pen</button>
          <button @click="setTool('eraser')" :class="{active: tool==='eraser'}" title="Eraser">🧹 Eraser</button>
          <button @click="undoLastStroke" title="Undo">↶ Undo</button>
          <button @click="clearCurrentPage" title="Clear Page">🗑️ Clear</button>
        </div>
        
        <div class="toolbar-group colors-group">
          <span class="tool-label">Colors:</span>
          <button 
            v-for="c in colors" 
            :key="c.value"
            @click="color = c.value" 
            :class="{active: color===c.value && tool==='pen'}"
            :style="{ background: c.value, border: '2px solid ' + (color===c.value && tool==='pen' ? '#fff' : '#666') }"
            :title="c.name"
            class="color-btn"
          >
            <span v-if="color===c.value && tool==='pen'" class="check">✓</span>
          </button>
        </div>
        
        <div class="toolbar-group zoom-controls">
          <button @click="zoomOut" :disabled="zoomLevel <= 0.5" title="Zoom Out">🔍-</button>
          <span class="zoom-display">{{ Math.round(zoomLevel * 100) }}%</span>
          <button @click="zoomIn" :disabled="zoomLevel >= 3" title="Zoom In">🔍+</button>
          <button @click="fitWidth" title="Fit Width">↔️ Width</button>
          <button @click="fitHeight" title="Fit Height">↕️ Height</button>
          <button @click="resetZoom" title="Reset Zoom">⊡ 100%</button>
        </div>
        
        <div class="toolbar-group quality-controls">
          <span class="quality-label">Quality:</span>
          <button @click="renderScale = 1" :class="{active: renderScale === 1}" title="Low Quality">Low</button>
          <button @click="renderScale = 2" :class="{active: renderScale === 2}" title="High Quality">High</button>
          <button @click="renderScale = 3" :class="{active: renderScale === 3}" title="Ultra Quality">Ultra</button>
        </div>
        
        <div class="toolbar-group">
          <button @click="downloadCurrentPage">Save Page</button>
          <button @click="downloadAllPages" class="save-all">Save All Pages</button>
          <button @click="resetPdf" class="reset">New PDF</button>
        </div>
      </div>

      <!-- Navigation -->
      <div class="nav-bar">
        <button @click="prevPage" :disabled="currentPage <= 1">Previous</button>
        <span>Page {{ currentPage }} of {{ numPages }}</span>
        <button @click="nextPage" :disabled="currentPage >= numPages">Next</button>
      </div>

      <!-- PDF + Canvas Overlay -->
      <div class="page-wrapper" ref="pageWrapper">
        <div 
          class="page-container" 
          ref="pageContainer"
          :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top center' }"
        >
          <VuePdfEmbed
            :key="`page-${currentPage}`"
            :source="pdfUrl"
            :page="currentPage"
            :width="1200"
            :scale="renderScale"
            @loaded="onPDFLoaded"
            @rendered="onPageRendered"
            class="pdf-embed"
          />
          <canvas ref="drawCanvas" class="draw-canvas" :key="`canvas-${currentPage}`"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import VuePdfEmbed from 'vue-pdf-embed'
import html2canvas from 'html2canvas'
import { jsPDF } from 'jspdf'

// State
const pdfUrl = ref('')
const currentPage = ref(1)
const numPages = ref(1)
const tool = ref('pen') // 'pen' or 'eraser'
const color = ref('black')
const colors = ref([
  { name: 'Black', value: 'black' },
  { name: 'Red', value: '#e74c3c' },
  { name: 'Blue', value: '#3498db' },
  { name: 'Green', value: '#27ae60' },
  { name: 'Yellow', value: '#f1c40f' },
  { name: 'Orange', value: '#e67e22' },
  { name: 'Purple', value: '#9b59b6' },
  { name: 'Pink', value: '#e91e63' },
  { name: 'Brown', value: '#795548' },
  { name: 'Gray', value: '#95a5a6' },
])
const drawings = ref({}) // { page: [{color, points: [{x,y}], tool}] }
const zoomLevel = ref(1)
const zoomMode = ref('custom') // 'custom', 'fitWidth', 'fitHeight'
const renderScale = ref(2) // Higher = better quality (1-4)

// Canvas
const pageWrapper = ref(null)
const pageContainer = ref(null)
const drawCanvas = ref(null)
let ctx = null
let isDrawing = false
let currentPath = []

// Load PDF
const loadPdf = (e) => {
  const file = e.target.files[0]
  if (!file || !file.type.includes('pdf')) return alert('Please select a PDF file')
  if (file.size > 50 * 1024 * 1024) return alert('File too big (max 50 MB)')

  pdfUrl.value = URL.createObjectURL(file)
  currentPage.value = 1
  numPages.value = 1
  drawings.value = {}
}

// PDF Loaded → Get Total Pages
const onPDFLoaded = (pdf) => {
  numPages.value = pdf.numPages
  console.log('PDF loaded with', numPages.value, 'pages')
}

// Page Rendered → Setup Canvas + Restore Drawings
const onPageRendered = async () => {
  await nextTick()
  console.log('Page', currentPage.value, 'rendered')
  setupCanvas()
  restoreDrawings()
}

// Setup Canvas Over PDF
const setupCanvas = () => {
  const pdfCanvas = pageContainer.value?.querySelector('.pdf-embed canvas')
  if (!pdfCanvas || !drawCanvas.value) return

  const rect = pdfCanvas.getBoundingClientRect()
  drawCanvas.value.width = rect.width
  drawCanvas.value.height = rect.height
  drawCanvas.value.style.width = `${rect.width}px`
  drawCanvas.value.style.height = `${rect.height}px`

  ctx = drawCanvas.value.getContext('2d', { willReadFrequently: true })
  ctx.lineWidth = 3
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'

  // Clear old events
  const canvas = drawCanvas.value
  canvas.onmousedown = canvas.ontouchstart = null

  // Mouse events
  canvas.addEventListener('mousedown', startDrawing)
  canvas.addEventListener('mousemove', drawing)
  canvas.addEventListener('mouseup', stopDrawing)
  canvas.addEventListener('mouseout', stopDrawing)

  // Touch events (mobile)
  canvas.addEventListener('touchstart', e => { e.preventDefault(); startDrawing(e.touches[0]) }, { passive: false })
  canvas.addEventListener('touchmove', e => { e.preventDefault(); drawing(e.touches[0]) }, { passive: false })
  canvas.addEventListener('touchend', stopDrawing)

  restoreDrawings()
}

// Tool Selection
const setTool = (newTool) => {
  tool.value = newTool
  if (newTool === 'eraser') {
    if (drawCanvas.value) {
      drawCanvas.value.style.cursor = 'crosshair'
    }
  } else {
    if (drawCanvas.value) {
      drawCanvas.value.style.cursor = 'crosshair'
    }
  }
}

// Drawing (with zoom support)
const getCanvasCoordinates = (e) => {
  const rect = drawCanvas.value.getBoundingClientRect()
  const x = (e.clientX - rect.left) / zoomLevel.value
  const y = (e.clientY - rect.top) / zoomLevel.value
  return { x, y }
}

const startDrawing = (e) => {
  isDrawing = true
  const { x, y } = getCanvasCoordinates(e)
  currentPath = [{ x, y }]

  if (!drawings.value[currentPage.value]) drawings.value[currentPage.value] = []
  
  if (tool.value === 'eraser') {
    // Eraser mode - we'll handle this in drawing()
    drawings.value[currentPage.value].push({ 
      tool: 'eraser', 
      color: 'white', 
      points: currentPath,
      lineWidth: 20 // Thicker for eraser
    })
  } else {
    // Pen mode
    drawings.value[currentPage.value].push({ 
      tool: 'pen',
      color: color.value, 
      points: currentPath,
      lineWidth: 3
    })
  }
}

const drawing = (e) => {
  if (!isDrawing || !ctx || currentPath.length === 0) return
  const { x, y } = getCanvasCoordinates(e)

  currentPath.push({ x, y })

  if (tool.value === 'eraser') {
    // Eraser mode - use destination-out composite
    ctx.globalCompositeOperation = 'destination-out'
    ctx.strokeStyle = 'rgba(0,0,0,1)'
    ctx.lineWidth = 20
  } else {
    // Pen mode
    ctx.globalCompositeOperation = 'source-over'
    ctx.strokeStyle = color.value
    ctx.lineWidth = 3
  }
  
  ctx.beginPath()
  ctx.moveTo(currentPath[currentPath.length - 2].x, currentPath[currentPath.length - 2].y)
  ctx.lineTo(x, y)
  ctx.stroke()
}

const stopDrawing = () => { isDrawing = false }

const restoreDrawings = () => {
  if (!ctx || !drawCanvas.value) return
  ctx.clearRect(0, 0, drawCanvas.value.width, drawCanvas.value.height)
  const strokes = drawings.value[currentPage.value] || []
  strokes.forEach(s => {
    if (s.tool === 'eraser') {
      ctx.globalCompositeOperation = 'destination-out'
      ctx.strokeStyle = 'rgba(0,0,0,1)'
      ctx.lineWidth = s.lineWidth || 20
    } else {
      ctx.globalCompositeOperation = 'source-over'
      ctx.strokeStyle = s.color
      ctx.lineWidth = s.lineWidth || 3
    }
    ctx.beginPath()
    s.points.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y))
    ctx.stroke()
  })
  // Reset to default
  ctx.globalCompositeOperation = 'source-over'
}

// Controls
const undoLastStroke = () => {
  if (drawings.value[currentPage.value]?.length) {
    drawings.value[currentPage.value].pop()
    restoreDrawings()
  }
}

const clearCurrentPage = () => {
  drawings.value[currentPage.value] = []
  restoreDrawings()
}

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < numPages.value) currentPage.value++ }

// Zoom Controls
const zoomIn = () => {
  if (zoomLevel.value < 3) {
    zoomLevel.value = Math.min(3, zoomLevel.value + 0.25)
    zoomMode.value = 'custom'
  }
}

const zoomOut = () => {
  if (zoomLevel.value > 0.5) {
    zoomLevel.value = Math.max(0.5, zoomLevel.value - 0.25)
    zoomMode.value = 'custom'
  }
}

const resetZoom = () => {
  zoomLevel.value = 1
  zoomMode.value = 'custom'
}

const fitWidth = async () => {
  await nextTick()
  const wrapper = pageWrapper.value
  const container = pageContainer.value
  if (!wrapper || !container) return
  
  const wrapperWidth = wrapper.clientWidth - 40 // padding
  const containerWidth = container.scrollWidth / zoomLevel.value // original width
  
  zoomLevel.value = wrapperWidth / containerWidth
  zoomMode.value = 'fitWidth'
}

const fitHeight = async () => {
  await nextTick()
  const wrapper = pageWrapper.value
  const container = pageContainer.value
  if (!wrapper || !container) return
  
  const wrapperHeight = wrapper.clientHeight - 40 // padding
  const containerHeight = container.scrollHeight / zoomLevel.value // original height
  
  zoomLevel.value = wrapperHeight / containerHeight
  zoomMode.value = 'fitHeight'
}

// Save Current Page
const downloadCurrentPage = async () => {
  const data = await html2canvas(pageContainer.value, { scale: 2, useCORS: true })
  const link = document.createElement('a')
  link.download = `page-${currentPage.value}.png`
  link.href = data.toDataURL()
  link.click()
}

// Save All Pages as PDF
const downloadAllPages = async () => {
  const pdf = new jsPDF()
  const originalPage = currentPage.value

  for (let p = 1; p <= numPages.value; p++) {
    currentPage.value = p
    await nextTick()
    await new Promise(r => setTimeout(r, 400)) // Wait for render

    const data = await html2canvas(pageContainer.value, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
      onclone: (doc) => {
        // Fix modern CSS issues
        doc.querySelectorAll('*').forEach(el => {
          const s = el.style
          if (s.background?.includes('oklch')) s.background = '#ffffff'
          if (s.color?.includes('oklch')) s.color = '#000000'
        })
      }
    })

    const img = data.toDataURL('image/jpeg', 0.95)
    if (p > 1) pdf.addPage()
    pdf.addImage(img, 'JPEG', 0, 0, 210, 297)
  }

  pdf.save('annotated-document.pdf')
  currentPage.value = originalPage
}

const resetPdf = () => {
  if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value)
  pdfUrl.value = ''
  currentPage.value = 1
  drawings.value = {}
}

// Watch page changes
watch(currentPage, async () => {
  await nextTick()
  setupCanvas()
})

onMounted(() => {
  console.log('PDFAnnotator mounted')
})

onUnmounted(() => {
  if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value)
})
</script>

<style scoped>
.pdf-annotator-app { max-width: 1100px; margin: 0 auto; padding: 20px; font-family: system-ui, sans-serif; }
.upload-area { text-align: center; margin: 50px 0; }
.upload-box { cursor: pointer; padding: 60px; border: 4px dashed #007bff; border-radius: 20px; background: #f8f9ff; display: block; }
.icon { font-size: 4rem; margin-bottom: 16px; }

.toolbar { 
  display: flex; 
  flex-wrap: wrap; 
  gap: 15px; 
  padding: 15px; 
  background: #222; 
  border-radius: 8px; 
  margin-bottom: 10px;
  align-items: center;
  justify-content: space-between;
}

.toolbar-group {
  display: flex;
  gap: 8px;
  align-items: center;
}

.toolbar button { 
  padding: 10px 18px; 
  background: #444; 
  color: white; 
  border: none; 
  border-radius: 6px; 
  cursor: pointer;
  transition: all 0.2s ease;
}

.toolbar button:hover:not(:disabled) { 
  background: #555; 
  transform: translateY(-1px);
}

.toolbar button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.toolbar button.active { background: #007bff; }
.toolbar .save-all { background: #28a745; }
.toolbar .reset { background: #dc3545; }

.zoom-controls {
  background: #333;
  padding: 8px 12px;
  border-radius: 8px;
}

.zoom-display {
  color: white;
  font-weight: bold;
  font-size: 1rem;
  min-width: 60px;
  text-align: center;
}

.quality-controls {
  background: #333;
  padding: 8px 12px;
  border-radius: 8px;
}

.quality-label {
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
  margin-right: 8px;
}

.tool-label {
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
  margin-right: 8px;
}

.colors-group {
  background: #333;
  padding: 8px 12px;
  border-radius: 8px;
  display: flex;
  gap: 6px;
  align-items: center;
}

.color-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.color-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.color-btn .check {
  color: white;
  font-weight: bold;
  font-size: 18px;
  text-shadow: 0 0 3px rgba(0,0,0,0.5);
}

.nav-bar { text-align: center; padding: 12px; background: #f0f0f0; font-weight: bold; font-size: 1.1rem; }

.page-wrapper {
  overflow: auto;
  background: #e0e0e0;
  padding: 20px;
  min-height: 600px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.page-container { 
  position: relative; 
  display: inline-block; 
  background: white; 
  box-shadow: 0 10px 40px rgba(0,0,0,0.2); 
  transition: transform 0.2s ease;
}

.pdf-embed { 
  display: block !important; 
  max-width: 100%;
  height: auto;
}

.pdf-embed canvas {
  max-width: 100% !important;
  height: auto !important;
  /* High quality rendering */
  image-rendering: auto;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.draw-canvas { 
  position: absolute; 
  top: 0; 
  left: 0; 
  cursor: crosshair; 
  pointer-events: all; 
  z-index: 10; 
}
</style>