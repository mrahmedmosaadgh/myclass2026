<template>
  <div class="pdf-draw-local">
    <!-- Upload Area -->
    <div class="upload-section">
      <h2 class="title">Upload a PDF to Draw on It</h2>

      <label class="upload-box">
        <input
          type="file"
          accept=".pdf,application/pdf"
          @change="onFileSelected"
          class="file-input"
        />
        <div class="upload-content" v-if="!selectedFileName">
          <div class="icon">Upload PDF</div>
          <p>Drag & drop your PDF here or click to browse</p>
          <small>Supports files up to 50 MB</small>
        </div>
        <div class="selected-file" v-else>
          <div class="icon">Checkmark</div>
          <p>Selected file:</p>
          <strong>{{ selectedFileName }}</strong>
          <button @click.prevent="clearFile" class="clear-btn">Change File</button>
        </div>
      </label>
    </div>

    <!-- Annotator (appears only after upload) -->
    <div v-if="pdfObjectUrl" class="annotator-section">
      <PDFAnnotator :pdf-url="pdfObjectUrl" />
    </div>

    <!-- Empty state -->
    <div v-else class="no-pdf-message">
      <p>Please upload a PDF file to start drawing</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import PDFAnnotator from './PDFAnnotator.vue' // The drawing component from previous step

const pdfObjectUrl = ref('')        // Temporary URL for uploaded file
const selectedFileName = ref('')    // Display name

const onFileSelected = (e) => {
  const file = e.target.files[0]
  if (!file) return

  // Validate PDF
  if (file.type !== 'application/pdf') {
    alert('Please upload a PDF file only')
    return
  }

  // Max 50 MB
  if (file.size > 50 * 1024 * 1024) {
    alert('File is too large. Maximum size is 50 MB')
    return
  }

  // Clean previous URL
  if (pdfObjectUrl.value) {
    URL.revokeObjectURL(pdfObjectUrl.value)
  }

  // Create object URL and show instantly
  pdfObjectUrl.value = URL.createObjectURL(file)
  selectedFileName.value = file.name
}

const clearFile = () => {
  if (pdfObjectUrl.value) URL.revokeObjectURL(pdfObjectUrl.value)
  pdfObjectUrl.value = ''
  selectedFileName.value = ''
  document.querySelector('.file-input').value = ''
}

onUnmounted(() => {
  if (pdfObjectUrl.value) URL.revokeObjectURL(pdfObjectUrl.value)
})
</script>

<style scoped>
.pdf-draw-local {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
  font-family: system-ui, -apple-system, sans-serif;
}

.title {
  text-align: center;
  margin-bottom: 30px;
  color: #1a1a1a;
  font-size: 1.8rem;
}

.upload-box {
  cursor: pointer;
  display: block;
  border: 3px dashed #4a90e2;
  border-radius: 16px;
  padding: 40px;
  text-align: center;
  transition: all 0.3s;
  background: #f8fbff;
}

.upload-box:hover {
  border-color: #007bff;
  background: #f0f8ff;
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0,123,255,0.2);
}

.file-input { display: none; }

.upload-content .icon,
.selected-file .icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.selected-file {
  color: #27ae60;
}

.clear-btn {
  margin-top: 12px;
  padding: 8px 20px;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.annotator-section {
  margin-top: 20px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  background: #2d2d2d;
}

.no-pdf-message {
  text-align: center;
  padding: 80px 20px;
  color: #777;
  font-size: 1.4rem;
}
</style>