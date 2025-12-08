<template>
  <div class="ocr-comparison">
    <!-- File Upload -->
    <div class="upload-section mb-6">
      <label 
        for="image-upload" 
        class="upload-zone"
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
          <span class="upload-text">Drop image here or click to upload</span>
        </div>
      </label>
      <input 
        id="image-upload"
        type="file"
        accept="image/*"
        class="hidden"
        @change="handleFileSelect"
      />
    </div>

    <!-- Image Preview -->
    <div v-if="selectedImage" class="preview-section mb-6">
      <img :src="selectedImage" alt="Selected image" class="preview-image" />
      <button @click="clearImage" class="clear-button">
        Clear Image
      </button>
    </div>

    <!-- OCR Options -->
    <div v-if="selectedImage" class="ocr-options mb-6">
      <h3 class="text-lg font-semibold mb-4">Select OCR Method</h3>
      <div class="options-grid">
        <!-- Tesseract.js -->
        <button 
          @click="runTesseract"
          :disabled="tesseractProcessing"
          class="ocr-option"
          :class="{ 'processing': tesseractProcessing }"
        >
          <span class="option-title">Tesseract.js</span>
          <span class="option-desc">Client-side, Free, No API key needed</span>
          <span v-if="tesseractProcessing" class="processing-indicator">
            Processing... {{ tesseractProgress }}%
          </span>
        </button>

        <!-- OCR.Space -->
        <button 
          @click="runOcrSpace"
          :disabled="ocrSpaceProcessing"
          class="ocr-option"
          :class="{ 'processing': ocrSpaceProcessing }"
        >
          <span class="option-title">OCR.Space</span>
          <span class="option-desc">Cloud API, Limited free tier</span>
          <span v-if="ocrSpaceProcessing" class="processing-indicator">
            Processing...
          </span>
        </button>

        <!-- Google Cloud Vision -->
        <button 
          @click="runGoogleVision"
          :disabled="googleProcessing"
          class="ocr-option"
          :class="{ 'processing': googleProcessing }"
        >
          <span class="option-title">Google Cloud Vision</span>
          <span class="option-desc">Most accurate, Paid service</span>
          <span v-if="googleProcessing" class="processing-indicator">
            Processing...
          </span>
        </button>
      </div>
    </div>

    <!-- Results Comparison -->
    <div v-if="hasAnyResults" class="results-section">
      <h3 class="text-lg font-semibold mb-4">Results Comparison</h3>
      
      <!-- Tesseract Results -->
      <div v-if="tesseractResult" class="result-card">
        <div class="result-header">
          <h4 class="result-title">Tesseract.js Results</h4>
          <span class="result-time">Time: {{ tesseractTime }}ms</span>
        </div>
        <div class="result-content">
          {{ tesseractResult }}
        </div>
        <div class="result-metrics">
          <span>Confidence: {{ tesseractConfidence }}%</span>
        </div>
      </div>

      <!-- OCR.Space Results -->
      <div v-if="ocrSpaceResult" class="result-card">
        <div class="result-header">
          <h4 class="result-title">OCR.Space Results</h4>
          <span class="result-time">Time: {{ ocrSpaceTime }}ms</span>
        </div>
        <div class="result-content">
          {{ ocrSpaceResult }}
        </div>
      </div>

      <!-- Google Vision Results -->
      <div v-if="googleResult" class="result-card">
        <div class="result-header">
          <h4 class="result-title">Google Cloud Vision Results</h4>
          <span class="result-time">Time: {{ googleTime }}ms</span>
        </div>
        <div class="result-content">
          {{ googleResult }}
        </div>
        <div class="result-metrics">
          <span>Confidence: {{ googleConfidence }}%</span>
        </div>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script>
import Tesseract from 'tesseract.js'

export default {
  name: 'OcrComparison',
  data() {
    return {
      isDragging: false,
      selectedImage: null,
      fileToProcess: null,
      
      // Tesseract.js state
      tesseractProcessing: false,
      tesseractProgress: 0,
      tesseractResult: null,
      tesseractTime: null,
      tesseractConfidence: null,
      
      // OCR.Space state
      ocrSpaceProcessing: false,
      ocrSpaceResult: null,
      ocrSpaceTime: null,
      
      // Google Vision state
      googleProcessing: false,
      googleResult: null,
      googleTime: null,
      googleConfidence: null,
      
      error: null
    }
  },
  computed: {
    hasAnyResults() {
      return this.tesseractResult || this.ocrSpaceResult || this.googleResult
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
      
      const reader = new FileReader()
      reader.onload = (e) => {
        this.selectedImage = e.target.result
      }
      reader.readAsDataURL(file)
    },
    clearImage() {
      this.selectedImage = null
      this.fileToProcess = null
      this.tesseractResult = null
      this.ocrSpaceResult = null
      this.googleResult = null
      this.error = null
      this.tesseractProgress = 0
    },
    async runTesseract() {
      if (!this.fileToProcess) return

      this.tesseractProcessing = true
      this.tesseractResult = null
      this.error = null
      const startTime = performance.now()

      try {
        const { data: { text, confidence } } = await Tesseract.recognize(
          this.fileToProcess,
          'eng',
          {
            logger: m => {
              if (m.status === 'recognizing text') {
                this.tesseractProgress = Math.round(m.progress * 100)
              }
            }
          }
        )

        this.tesseractResult = text
        this.tesseractConfidence = Math.round(confidence)
        this.tesseractTime = Math.round(performance.now() - startTime)
      } catch (err) {
        this.error = 'Tesseract.js error: ' + err.message
      } finally {
        this.tesseractProcessing = false
      }
    },
    async runOcrSpace() {
      if (!this.fileToProcess) return

      this.ocrSpaceProcessing = true
      this.ocrSpaceResult = null
      this.error = null
      const startTime = performance.now()

      const formData = new FormData()
      formData.append('file', this.fileToProcess)
      formData.append('apikey', 'K89883067088957') // Free API key
      formData.append('language', 'eng')
      formData.append('detectOrientation', true)

      try {
        const response = await fetch('https://api.ocr.space/parse/image', {
          method: 'POST',
          body: formData
        })

        const result = await response.json()

        if (result.ErrorMessage) {
          throw new Error(result.ErrorMessage)
        }

        if (result.ParsedResults && result.ParsedResults.length > 0) {
          this.ocrSpaceResult = result.ParsedResults[0].ParsedText
          this.ocrSpaceTime = Math.round(performance.now() - startTime)
        } else {
          throw new Error('No text detected')
        }
      } catch (err) {
        this.error = 'OCR.Space error: ' + err.message
      } finally {
        this.ocrSpaceProcessing = false
      }
    },
    async runGoogleVision() {
      // Note: This is a placeholder for Google Cloud Vision API integration
      // You would need to set up Google Cloud credentials and handle the API calls
      // through your backend to keep the API key secure
      this.error = 'Google Cloud Vision integration requires API setup'
    }
  }
}
</script>

<style scoped>
.ocr-comparison {
  max-width: 800px;
  margin: 0 auto;
  padding: 1rem;
}

.upload-zone {
  border: 2px dashed #e2e8f0;
  border-radius: 0.5rem;
  padding: 2rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.upload-zone.dragging {
  background-color: #f8fafc;
  border-color: #3b82f6;
}

.upload-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.upload-icon {
  width: 3rem;
  height: 3rem;
  color: #64748b;
}

.hidden {
  display: none;
}

.preview-section {
  text-align: center;
}

.preview-image {
  max-height: 300px;
  margin: 0 auto;
  border-radius: 0.5rem;
}

.clear-button {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background-color: #ef4444;
  color: white;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.ocr-option {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.ocr-option:not(:disabled):hover {
  background-color: #f1f5f9;
  transform: translateY(-2px);
}

.ocr-option.processing {
  background-color: #e2e8f0;
  cursor: not-allowed;
}

.option-title {
  font-weight: 600;
  font-size: 1.125rem;
}

.option-desc {
  font-size: 0.875rem;
  color: #64748b;
}

.processing-indicator {
  font-size: 0.875rem;
  color: #3b82f6;
}

.result-card {
  margin-bottom: 1rem;
  padding: 1rem;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
}

.result-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.result-title {
  font-weight: 600;
}

.result-time {
  font-size: 0.875rem;
  color: #64748b;
}

.result-content {
  white-space: pre-wrap;
  font-family: monospace;
  padding: 1rem;
  background-color: white;
  border-radius: 0.375rem;
  margin: 0.5rem 0;
}

.result-metrics {
  font-size: 0.875rem;
  color: #64748b;
}

.error-message {
  margin-top: 1rem;
  padding: 1rem;
  background-color: #fef2f2;
  color: #dc2626;
  border-radius: 0.375rem;
}
</style>
