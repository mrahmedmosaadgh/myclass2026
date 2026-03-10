<template>
  <div class="pdf-viewer-container">
    <!-- Upload Section -->
    <div v-if="!pdfFile" class="upload-section">
      <div class="upload-box">
        <input 
          type="file" 
          accept="application/pdf" 
          @change="handleFileUpload" 
          ref="fileInput"
          class="hidden"
        />
        <button @click="$refs.fileInput.click()" class="upload-button">
          📄 Choose PDF File
        </button>
        <p class="upload-hint">or drag and drop here</p>
      </div>
    </div>

    <!-- PDF Viewer Section -->
    <div v-else class="viewer-section">
      <!-- Toolbar -->
      <div class="toolbar">
        <button @click="resetPDF" class="btn btn-secondary">
          🔄 New PDF
        </button>
        <div class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </div>
        <button @click="downloadPDF" class="btn btn-primary">
          💾 Download
        </button>
      </div>

      <!-- Navigation -->
      <div class="navigation">
        <button 
          @click="previousPage" 
          :disabled="currentPage <= 1"
          class="nav-btn"
        >
          ← Previous
        </button>
        
        <input 
          type="number" 
          v-model.number="currentPage" 
          :min="1" 
          :max="totalPages"
          class="page-input"
        />
        
        <button 
          @click="nextPage" 
          :disabled="currentPage >= totalPages"
          class="nav-btn"
        >
          Next →
        </button>
      </div>

      <!-- PDF Display -->
      <div class="pdf-display">
        <VuePdfEmbed
          :source="pdfFile"
          :page="currentPage"
          @loaded="onPDFLoaded"
          class="pdf-page"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import VuePdfEmbed from 'vue-pdf-embed'

const pdfFile = ref(null)
const currentPage = ref(1)
const totalPages = ref(0)
const fileInput = ref(null)

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file && file.type === 'application/pdf') {
    pdfFile.value = URL.createObjectURL(file)
    currentPage.value = 1
  } else {
    alert('Please select a valid PDF file')
  }
}

const onPDFLoaded = (pdf) => {
  totalPages.value = pdf.numPages
  console.log('PDF loaded:', totalPages.value, 'pages')
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const resetPDF = () => {
  if (pdfFile.value) {
    URL.revokeObjectURL(pdfFile.value)
  }
  pdfFile.value = null
  currentPage.value = 1
  totalPages.value = 0
}

const downloadPDF = () => {
  if (pdfFile.value) {
    const link = document.createElement('a')
    link.href = pdfFile.value
    link.download = 'document.pdf'
    link.click()
  }
}
</script>

<style scoped>
.pdf-viewer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: system-ui, -apple-system, sans-serif;
}

/* Upload Section */
.upload-section {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.upload-box {
  text-align: center;
  padding: 60px;
  border: 3px dashed #3b82f6;
  border-radius: 16px;
  background: #eff6ff;
  transition: all 0.3s ease;
}

.upload-box:hover {
  border-color: #2563eb;
  background: #dbeafe;
}

.upload-button {
  padding: 16px 32px;
  font-size: 18px;
  font-weight: 600;
  color: white;
  background: #3b82f6;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.upload-button:hover {
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.upload-hint {
  margin-top: 16px;
  color: #6b7280;
  font-size: 14px;
}

.hidden {
  display: none;
}

/* Viewer Section */
.viewer-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Toolbar */
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #1f2937;
  border-radius: 12px;
  color: white;
}

.page-info {
  font-size: 16px;
  font-weight: 600;
}

.btn {
  padding: 10px 20px;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary {
  background: #10b981;
  color: white;
}

.btn-primary:hover {
  background: #059669;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
}

/* Navigation */
.navigation {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f3f4f6;
  border-radius: 12px;
}

.nav-btn {
  padding: 10px 24px;
  font-weight: 600;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.nav-btn:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-2px);
}

.nav-btn:disabled {
  background: #d1d5db;
  cursor: not-allowed;
  opacity: 0.5;
}

.page-input {
  width: 80px;
  padding: 8px;
  text-align: center;
  font-size: 16px;
  font-weight: 600;
  border: 2px solid #d1d5db;
  border-radius: 8px;
}

.page-input:focus {
  outline: none;
  border-color: #3b82f6;
}

/* PDF Display */
.pdf-display {
  display: flex;
  justify-content: center;
  padding: 20px;
  background: #f9fafb;
  border-radius: 12px;
  min-height: 600px;
}

.pdf-page {
  max-width: 100%;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  border-radius: 8px;
}
</style>
