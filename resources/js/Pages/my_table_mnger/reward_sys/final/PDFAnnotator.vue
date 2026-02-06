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
          <component
            :key="`page-${currentPage}`"
            :is="VuePdfEmbed"
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
// import html2canvas from 'html2canvas'  // DISABLED
// import { jsPDF } from 'jspdf'          // DISABLED

// ... (rest of imports)

// Save Current Page
const downloadCurrentPage = async () => {
  // const data = await html2canvas(pageContainer.value, { scale: 2, useCORS: true })
  // const link = document.createElement('a')
  // link.download = `page-${currentPage.value}.png`
  // link.href = data.toDataURL()
  // link.click()
  alert('Sorry, saving page is temporarily disabled.')
}

// Save All Pages as PDF
const downloadAllPages = async () => {
    alert('Sorry, saving PDF is temporarily disabled.')
//   const pdf = new jsPDF()
//   const originalPage = currentPage.value

//   for (let p = 1; p <= numPages.value; p++) {
//     currentPage.value = p
//     await nextTick()
//     await new Promise(r => setTimeout(r, 400)) // Wait for render

//     const data = await html2canvas(pageContainer.value, {
//       scale: 2,
//       useCORS: true,
//       backgroundColor: '#ffffff',
//       onclone: (doc) => {
//         // Fix modern CSS issues
//         doc.querySelectorAll('*').forEach(el => {
//           const s = el.style
//           if (s.background?.includes('oklch')) s.background = '#ffffff'
//           if (s.color?.includes('oklch')) s.color = '#000000'
//         })
//       }
//     })

//     const img = data.toDataURL('image/jpeg', 0.95)
//     if (p > 1) pdf.addPage()
//     pdf.addImage(img, 'JPEG', 0, 0, 210, 297)
//   }

//   pdf.save('annotated-document.pdf')
//   currentPage.value = originalPage
}

// ...

onMounted(async () => {
  try {
    // const module = await import('vue-pdf-embed') // DISABLED
    // VuePdfEmbed.value = module.default
    console.warn('PDF Viewer in Annotator is temporarily disabled.')
  } catch (err) {
    console.error('Failed to load PDF viewer:', err)
  }
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