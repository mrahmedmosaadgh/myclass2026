<template>
  <div class="pdf-annotator">
    <!-- Toolbar -->
    <div class="toolbar">
      <button @click="setColor('black')" :class="{ active: color === 'black' }">أسود</button>
      <button @click="setColor('red')" :class="{ active: color === 'red' }">أحمر</button>
      <button @click="undo">تراجع</button>
      <button @click="clear">مسح الكل</button>
      <button @click="saveAsImage">حفظ كصورة</button>
      <button @click="saveAsPdf">حفظ كـ PDF</button>
    </div>

    <!-- PDF + Canvas Overlay -->
    <div class="viewer-container" ref="viewerContainer">
      <vue-pdf-embed
        :source="pdfUrl"
        @loaded="onPdfLoaded"
        class="pdf-embed"
      />
      <canvas ref="canvas" class="drawing-canvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VuePdfEmbed from 'vue-pdf-embed'
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'

const props = defineProps({
  pdfUrl: { type: String, required: true }
})

const viewerContainer = ref(null)
const canvas = ref(null)
const color = ref('black')
const isDrawing = ref(false)
const paths = ref([]) // For undo: array of path objects
const currentPath = ref([])

let ctx = null

// Initialize canvas after PDF loads
const onPdfLoaded = () => {
  const container = viewerContainer.value
  const pdfEmbed = container.querySelector('.pdf-embed')
  
  // Match canvas size to PDF viewer
  const { width, height } = pdfEmbed.getBoundingClientRect()
  canvas.value.width = width
  canvas.value.height = height
  
  ctx = canvas.value.getContext('2d')
  ctx.lineWidth = 2
  ctx.lineCap = 'round'
  ctx.strokeStyle = color.value
  
  // Add event listeners
  canvas.value.addEventListener('mousedown', startDrawing)
  canvas.value.addEventListener('mousemove', draw)
  canvas.value.addEventListener('mouseup', stopDrawing)
  canvas.value.addEventListener('mouseout', stopDrawing)
  
  // Touch support
  canvas.value.addEventListener('touchstart', startDrawing)
  canvas.value.addEventListener('touchmove', draw)
  canvas.value.addEventListener('touchend', stopDrawing)
}

// Start drawing
const startDrawing = (e) => {
  isDrawing.value = true
  currentPath.value = []
  paths.value.push({ color: color.value, points: currentPath.value })
  draw(e)
}

// Draw line
const draw = (e) => {
  if (!isDrawing.value) return
  
  const rect = canvas.value.getBoundingClientRect()
  const x = (e.clientX || e.touches[0].clientX) - rect.left
  const y = (e.clientY || e.touches[0].clientY) - rect.top
  
  currentPath.value.push({ x, y })
  
  ctx.strokeStyle = color.value
  ctx.beginPath()
  ctx.moveTo(currentPath.value[currentPath.value.length - 2]?.x || x, currentPath.value[currentPath.value.length - 2]?.y || y)
  ctx.lineTo(x, y)
  ctx.stroke()
}

// Stop drawing
const stopDrawing = () => {
  isDrawing.value = false
}

// Set color
const setColor = (newColor) => {
  color.value = newColor
  if (ctx) ctx.strokeStyle = newColor
}

// Undo last path
const undo = () => {
  if (paths.value.length === 0) return
  paths.value.pop()
  redraw()
}

// Clear all
const clear = () => {
  paths.value = []
  redraw()
}

// Redraw all paths
const redraw = () => {
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)
  paths.value.forEach(path => {
    ctx.strokeStyle = path.color
    ctx.beginPath()
    path.points.forEach((point, i) => {
      if (i === 0) ctx.moveTo(point.x, point.y)
      else ctx.lineTo(point.x, point.y)
    })
    ctx.stroke()
  })
}

// Save as Image (PNG)
const saveAsImage = () => {
  html2canvas(viewerContainer.value).then(canvas => {
    const link = document.createElement('a')
    link.download = 'annotated-pdf.png'
    link.href = canvas.toDataURL()
    link.click()
  })
}

// Save as PDF
const saveAsPdf = () => {
  html2canvas(viewerContainer.value).then(canvas => {
    const imgData = canvas.toDataURL('image/png')
    const pdf = new jsPDF({
      orientation: canvas.width > canvas.height ? 'l' : 'p',
      unit: 'px',
      format: [canvas.width, canvas.height]
    })
    pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height)
    pdf.save('annotated-pdf.pdf')
  })
}

onMounted(() => {
  // Resize listener (for responsive)
  window.addEventListener('resize', onPdfLoaded)
})
</script>

<style scoped>
.pdf-annotator {
  direction: rtl; /* Arabic RTL */
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.toolbar {
  display: flex;
  gap: 10px;
  padding: 10px;
  background: #f0f0f0;
  border-bottom: 1px solid #ddd;
  flex-wrap: wrap;
}

.toolbar button {
  padding: 8px 16px;
  border: 1px solid #ccc;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.toolbar button.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.toolbar button:hover {
  background: #e0e0e0;
}

.viewer-container {
  position: relative;
  width: 100%;
  height: 80vh;
  overflow: hidden;
  background: #525659;
}

.pdf-embed {
  width: 100%;
  height: 100%;
}

.drawing-canvas {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  cursor: crosshair;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .toolbar {
    background: #333;
    border-color: #444;
  }
  .toolbar button {
    border-color: #555;
    color: #ddd;
  }
  .toolbar button:hover {
    background: #444;
  }
  .toolbar button.active {
    background: #0056b3;
  }
}
</style>