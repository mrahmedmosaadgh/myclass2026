<template>
  <div class="ocr-scanner">
    <!-- File Upload Section -->
    <div class="upload-section mb-4">
      <label 
        :for="uploadId" 
        class="upload-label"
        :class="{ 'dragging': isDragging }"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop"
      >
        <div class="upload-content">
          <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          <span class="upload-text">
            Drop image here or click to upload
          </span>
          <span class="upload-hint">
            Supported formats: PNG, JPG, GIF, TIFF
          </span>
        </div>
      </label>
      <input 
        :id="uploadId"
        type="file"
        accept="image/*"
        class="hidden"
        @change="handleFileSelect"
      />
    </div>

    <!-- Preview Section -->
    <div v-if="selectedImage" class="preview-section mb-4">
      <div class="preview-header">
        <h3 class="preview-title">Selected Image</h3>
        <button @click="clearImage" class="clear-button">
          Clear
        </button>
      </div>
      <div class="preview-image-container">
        <img :src="selectedImage" alt="Selected image" class="preview-image" />
      </div>
    </div>

    <!-- Processing Status -->
    <div v-if="isProcessing" class="processing-status">
      <div class="spinner"></div>
      <span>Processing image...</span>
    </div>

    <!-- Results Section -->
    <div v-if="ocrResult" class="results-section">
      <h3 class="results-title">OCR Results</h3>
      <div class="results-content">
        <p v-for="(line, index) in ocrResult.ParsedResults[0].ParsedText.split('\n')" 
           :key="index"
           class="result-line"
        >
          {{ line }}
        </p>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'OcrScanner',
  props: {
    apiKey: {
      type: String,
      required: true,
      default: 'K89883067088957' // Free API key for testing
    },
    language: {
      type: String,
      default: 'eng'
    },
    detectOrientation: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      uploadId: `file-upload-${Math.random().toString(36).substr(2, 9)}`,
      selectedImage: null,
      isDragging: false,
      isProcessing: false,
      ocrResult: null,
      error: null,
      fileToProcess: null
    }
  },
  methods: {
    handleDrop(event) {
      this.isDragging = false
      const file = event.dataTransfer.files[0]
      if (file && this.isValidImageFile(file)) {
        this.processFile(file)
      }
    },
    handleFileSelect(event) {
      const file = event.target.files[0]
      if (file && this.isValidImageFile(file)) {
        this.processFile(file)
      }
    },
    isValidImageFile(file) {
      const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/tiff']
      if (!validTypes.includes(file.type)) {
        this.error = 'Please select a valid image file (PNG, JPG, GIF, TIFF)'
        return false
      }
      return true
    },
    processFile(file) {
      this.error = null
      this.fileToProcess = file
      
      // Create preview
      const reader = new FileReader()
      reader.onload = (e) => {
        this.selectedImage = e.target.result
        this.performOcr()
      }
      reader.readAsDataURL(file)
    },
    async performOcr() {
      if (!this.fileToProcess) return

      this.isProcessing = true
      this.ocrResult = null
      this.error = null

      const formData = new FormData()
      formData.append('file', this.fileToProcess)
      formData.append('apikey', this.apiKey)
      formData.append('language', this.language)
      formData.append('detectOrientation', this.detectOrientation)
      formData.append('isOverlayRequired', true)

      try {
        const response = await fetch('https://api.ocr.space/parse/image', {
          method: 'POST',
          body: formData
        })

        const result = await response.json()

        if (result.ErrorMessage) {
          throw new Error(result.ErrorMessage)
        }

        if (!result.ParsedResults || result.ParsedResults.length === 0) {
          throw new Error('No text was detected in the image')
        }

        this.ocrResult = result
        this.$emit('ocr-complete', result)
      } catch (err) {
        this.error = err.message || 'An error occurred while processing the image'
        this.$emit('ocr-error', err)
      } finally {
        this.isProcessing = false
      }
    },
    clearImage() {
      this.selectedImage = null
      this.fileToProcess = null
      this.ocrResult = null
      this.error = null
      this.$emit('clear')
    }
  }
}
</script>

<style scoped>
.ocr-scanner {
  max-width: 800px;
  margin: 0 auto;
  padding: 1rem;
}

.upload-section {
  border: 2px dashed #e2e8f0;
  border-radius: 0.5rem;
  transition: all 0.3s ease;
}

.upload-label {
  display: block;
  padding: 2rem;
  cursor: pointer;
}

.upload-label.dragging {
  background-color: #f8fafc;
  border-color: #3b82f6;
}

.upload-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.upload-icon {
  width: 3rem;
  height: 3rem;
  color: #64748b;
}

.upload-text {
  font-size: 1.125rem;
  color: #1e293b;
}

.upload-hint {
  font-size: 0.875rem;
  color: #64748b;
}

.hidden {
  display: none;
}

.preview-section {
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 1rem;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.preview-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
}

.clear-button {
  padding: 0.5rem 1rem;
  background-color: #ef4444;
  color: white;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.preview-image-container {
  max-height: 300px;
  overflow: hidden;
  border-radius: 0.375rem;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.processing-status {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background-color: #f8fafc;
  border-radius: 0.375rem;
}

.spinner {
  width: 1.5rem;
  height: 1.5rem;
  border: 2px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.results-section {
  margin-top: 1rem;
  padding: 1rem;
  background-color: #f8fafc;
  border-radius: 0.375rem;
}

.results-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 1rem;
}

.result-line {
  margin-bottom: 0.5rem;
  line-height: 1.5;
}

.error-message {
  margin-top: 1rem;
  padding: 1rem;
  background-color: #fef2f2;
  color: #dc2626;
  border-radius: 0.375rem;
}
</style>
