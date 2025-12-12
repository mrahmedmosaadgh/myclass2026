<template>
  <div class="pdf-draw-local">
    <!-- Upload Section -->
    <div class="upload-section">
      <h2 class="title">Upload PDF to Draw on Any Page</h2>
      <label class="upload-box">
        <input type="file" accept=".pdf" @change="onFileSelected" class="file-input" />
        <div v-if="!selectedFileName" class="upload-content">
          <div class="icon">📄</div>
          <p>Drag & drop or click to upload</p>
          <small>Max 50 MB</small>
        </div>
        <div v-else class="selected-file">
          <div class="icon">✅</div>
          <p>Loaded: <strong>{{ selectedFileName }}</strong></p>
          <button @click.prevent="clearFile" class="clear-btn">Change File</button>
        </div>
      </label>
    </div>

    <!-- PDF Section (Only After Upload) -->
    <div v-if="pdfObjectUrl" class="pdf-section">
      <!-- Toolbar for Drawing -->
      <div class="toolbar">
        <button @click="setColor('black')" :class="{ active: color === 'black' }">Black</button>
        <button @click="setColor('red')" :class="{ active: color === 'red' }">Red</button>
        <button @click="undo" :disabled="!hasDrawings">Undo</button>
        <button @click="clearPage">Clear Page</button>
        <button @click="saveCurrentPageAsImage">Save Page as Image</button>
        <button @click="saveAllAsPdf">Save All as PDF</button>
      </div>

      <!-- Navigation -->
      <div class="navigation-bar">
        <button @click="prevPage" :disabled="currentPage <= 1" class="nav-btn">← Previous</button>
        <span class="page-info">
          Page 
          <input 
            v-model.number="currentPage" 
            @input="gotoPage" 
            type="number" 
            min="1" 
            :max="totalPages" 
            class="page-input"
          /> 
          of {{ totalPages }}
        </span>
        <button @click="nextPage" :disabled="currentPage >= totalPages" class="nav-btn">Next →</button>
      </div>

      <!-- PDF Viewer + Canvas -->
      <div class="viewer-wrapper">
        <vue-pdf-embed
          :source="pdfObjectUrl"
          :page-number="currentPage"
          @progress="onProgress"
          @rendered="onRendered"
          class="pdf-embed"
          :enable-annotation="false"
        />
        <canvas ref="canvas" class="draw-canvas" @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"></canvas>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="no-pdf">
      <p>Upload a PDF to start drawing on any page</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import VuePdfEmbed from 'vue-pdf-embed'
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'

const pdfObjectUrl = ref('')
const selectedFileName = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const color = ref('black')
const allDrawings = ref({}) // { pageNum: [paths] }
const isDrawing = ref(false)
const currentPath = ref([])

const canvas = ref(null)
let ctx = null

// File Upload
const onFileSelected = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  if (file.type !== 'application/pdf') {
    alert('Please select a PDF file')
    return
  }
  if (file.size > 50 * 1024 * 1024) {
    alert('File too large (max 50 MB)')
    return
  }

  if (pdfObjectUrl.value) URL.revokeObjectURL(pdfObjectUrl.value)
  pdfObjectUrl.value = URL.createObjectURL(file)
  selectedFileName.value = file.name
  allDrawings.value = {}
  currentPage.value = 1
}

const clearFile = () => {
  if (pdfObjectUrl.value) URL.revokeObjectURL(pdfObjectUrl.value)
  pdfObjectUrl.value = ''
  selectedFileName.value = ''
  currentPage.value = 1
  totalPages.value = 1
  allDrawings.value = {}
  if (canvas.value) canvas.value.getContext('2d').clearRect(0, 0, canvas.value.width, canvas.value.height)
}

// PDF Events
const onProgress = (progress) => {
  // Extract total pages from progress if available
  if (progress && progress.totalPages) totalPages.value = progress.totalPages
}

const onRendered = () => {
  nextTick(setupCanvas)
}

// Canvas Setup
const setupCanvas = () => {
  if (!canvas.value) return
  const embed = canvas.value.parentElement.querySelector('.pdf-embed')
  if (!embed) return

  const rect = embed.getBoundingClientRect()
  canvas.value.width = rect.width
  canvas.value.height = rect.height
  canvas.value.style.width = rect.width + 'px'
  canvas.value.style.height = rect.height + 'px'

  ctx = canvas.value.getContext('2d')
  ctx.lineWidth = 3
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
  ctx.strokeStyle = color.value

  redrawCurrentPage()
}

// Drawing Logic
const startDrawing = (e) => {
  if (e.type === 'mousedown') e.preventDefault()
  isDrawing.value = true
  currentPath.value = []
  const rect = canvas.value.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  currentPath.value.push({ x, y })

  if (!allDrawings.value[currentPage.value]) allDrawings.value[currentPage.value] = []
  allDrawings.value[currentPage.value].push({ color: color.value, points: currentPath.value })
}

const draw = (e) => {
  if (!isDrawing.value || !ctx) return
  e.preventDefault()
  const rect = canvas.value.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  currentPath.value.push({ x, y })

  const lastPoint = currentPath.value[currentPath.value.length - 2]
  ctx.beginPath()
  ctx.moveTo(lastPoint.x, lastPoint.y)
  ctx.lineTo(x, y)
  ctx.stroke()
}

const stopDrawing = (e) => {
  isDrawing.value = false
}

// Redraw
const redrawCurrentPage = () => {
  if (!ctx) return
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)
  const pageDrawings = allDrawings.value[currentPage.value] || []
  pageDrawings.forEach(path => {
    ctx.strokeStyle = path.color
    ctx.beginPath()
    path.points.forEach((point, index) => {
      if (index === 0) ctx.moveTo(point.x, point.y)
      else ctx.lineTo(point.x, point.y)
    })
    ctx.stroke()
  })
}

const hasDrawings = () => allDrawings.value[currentPage.value]?.length > 0

// Toolbar Actions
const setColor = (newColor) => {
  color.value = newColor
  if (ctx) ctx.strokeStyle = newColor
}

const undo = () => {
  if (allDrawings.value[currentPage.value]?.length > 0) {
    allDrawings.value[currentPage.value].pop()
    redrawCurrentPage()
  }
}

const clearPage = () => {
  allDrawings.value[currentPage.value] = []
  redrawCurrentPage()
}

// Save Functions
const saveCurrentPageAsImage = async () => {
  const wrapper = canvas.value?.parentElement
  if (!wrapper) return
  const captured = await html2canvas(wrapper, { scale: 1 })
  const link = document.createElement('a')
  link.download = `annotated-page-${currentPage.value}.png`
  link.href = captured.toDataURL()
  link.click()
}

const saveAllAsPdf = async () => {
  const pdf = new jsPDF('p', 'mm', 'a4')
  let yOffset = 0

  for (let page = 1; page <= totalPages.value; page++) {
    currentPage.value = page // Trigger render
    await nextTick()
    await nextTick() // Double wait for render
    const wrapper = canvas.value?.parentElement
    if (!wrapper) continue

    const captured = await html2canvas(wrapper, { scale: 1 })
    const imgData = captured.toDataURL('image/png')
    const imgWidth = 210 // A4 width in mm
    const pageHeight = 295 // A4 height in mm
    const imgHeight = (captured.height * imgWidth) / captured.width
    let heightLeft = imgHeight

    if (yOffset > 0) pdf.addPage()

    do {
      const remainingHeight = pageHeight - yOffset
      if (newHeight > remainingHeight) {
        pdf.addPage()
        yOffset = 0
      }
      pdf.addImage(imgData, 'PNG', 0, yOffset, imgWidth, newHeight)
      heightLeft -= remainingHeight
      yOffset = 0
    } while (heightLeft > 0)

    yOffset = heightLeft - pageHeight
  }

  pdf.save('annotated-pdf-full.pdf')
}

// Navigation
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const gotoPage = () => {
  if (currentPage.value < 1) currentPage.value = 1
  if (currentPage.value > totalPages.value) currentPage.value = totalPages.value
}

// Watch page changes to redraw
watch(currentPage, () => {
  nextTick(() => {
    setupCanvas()
  })
})

onMounted(() => {
  // Touch support
  canvas.value?.addEventListener('touchstart', (e) => { e.preventDefault(); startDrawing(e.touches[0]) }, { passive: false })
  canvas.value?.addEventListener('touchmove', (e) => { e.preventDefault(); draw(e.touches[0]) }, { passive: false })
  canvas.value?.addEventListener('touchend', stopDrawing, { passive: false })
})

onUnmounted(() => {
  if (pdfObjectUrl.value) URL.revokeObjectURL(pdfObjectUrl.value)
})
</script>

<style scoped>
.pdf-draw-local { max-width: 1000px; margin: 0 auto; padding: 20px; font-family: system-ui, sans-serif; }

.title { text-align: center; margin-bottom: 30px; font-size: 1.8rem; color: #333; }

.upload-box { 
  display: block; cursor: pointer; border: 2px dashed #4a90e2; border-radius: 12px; 
  padding: 40px; text-align: center; background: #f8f9fa; transition: all 0.3s; 
}
.upload-box:hover { border-color: #007bff; background: #e3f2fd; }
.file-input { display: none; }
.icon { font-size: 3rem; margin-bottom: 1rem; }
.selected-file { color: #28a745; }
.clear-btn { padding: 0.5rem 1rem; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; }

.pdf-section { background: #f8f9fa; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }

.toolbar {
  display: flex; gap: 10px; padding: 15px; background: #495057; color: white; flex-wrap: wrap;
}
.toolbar button { 
  padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; background: #6c757d; color: white; transition: 0.2s; 
}
.toolbar button:hover:not(:disabled) { background: #007bff; }
.toolbar button.active { background: #dc3545; }
.toolbar button:disabled { opacity: 0.5; cursor: not-allowed; }

.navigation-bar {
  display: flex; justify-content: center; align-items: center; gap: 15px; padding: 10px; background: #e9ecef;
}
.nav-btn { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; }
.nav-btn:disabled { background: #6c757d; cursor: not-allowed; }
.page-input { width: 50px; text-align: center; padding: 4px; border: 1px solid #ddd; border-radius: 4px; }

.viewer-wrapper { position: relative; height: 70vh; background: #fff; overflow: auto; }
.pdf-embed { display: block; margin: 0 auto; max-width: 100%; }
.draw-canvas { 
  position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: crosshair; pointer-events: all; 
}

.no-pdf { text-align: center; padding: 60px 20px; color: #6c757d; font-size: 1.2rem; background: #f8f9fa; border-radius: 12px; }
</style>