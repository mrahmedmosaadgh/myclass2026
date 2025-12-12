<template>
  <div class="qr-scanner-container">
    <!-- Mode Selection -->
    <div class="controls mb-4">
      <button
        v-for="mode in modes"
        :key="mode.id"
        @click="setMode(mode.id)"
        :class="['px-4 py-2 rounded font-semibold mr-2',
                currentMode === mode.id ? 'bg-blue-500 text-white' : 'bg-gray-200']"
      >
        {{ mode.label }}
      </button>
    </div>

    <!-- QR Camera Scanner -->
    <div v-if="currentMode === 'camera'" class="camera-container">
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p class="mt-2">Initializing camera...</p>
      </div>

      <div v-show="!isLoading" class="camera-view">
        <qr-stream
          v-if="isCameraActive"
          @decode="onDecode"
          @init="onInit"
          :track="true"
          :constraints="{
            facingMode: { ideal: 'environment' },
            aspectRatio: { ideal: 1.7777777778 }
          }"
        >
          <div class="overlay-element">
            <div class="scan-region-highlight" willReadFrequently="true"></div>
            <div class="scan-region-highlight-svg"></div>
          </div>
        </qr-stream>
      </div>
    </div>

    <!-- Barcode Scanner -->
    <div v-if="currentMode === 'barcode'" class="camera-container">
      <BarCodeScanner
        v-if="isBarcodeActive"
        :is-active="true"
        @barcode-detected="handleBarcode"
        @error="handleError"
      />
    </div>

    <!-- Manual Input -->
    <div v-else-if="currentMode === 'manual'" class="manual-input">
      <input
        type="text"
        ref="manualInput"
        v-model="qrCode"
        @input="handleManualInput"
        class="w-full p-2 border rounded"
        placeholder="Waiting for QR code input..."
        :disabled="!isReceiving"
      />
    </div>

    <!-- Status Messages -->
    <div v-if="statusMessage" :class="['mt-4 p-4 rounded', statusClass]">
      {{ statusMessage }}
    </div>

    <!-- Scanned Codes History -->
    <div class="history-section mt-6">
      <h3 class="text-lg font-semibold mb-2">Scan History</h3>
      <div class="history-list max-h-60 overflow-y-auto">
        <div
          v-for="(scan, index) in scanHistory"
          :key="index"
          class="history-item p-3 bg-gray-50 rounded mb-2 flex justify-between items-center"
        >
          <div>
            <span class="font-medium">Student: {{ scan.studentCode }}</span>
            <span class="mx-2">|</span>
            <span>Mark: {{ scan.mark }}</span>
          </div>
          <span class="text-sm text-gray-500">{{ scan.timestamp }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { QrStream } from 'vue3-qr-reader'
import BarCodeScanner from '@/Components/barCodeScanner.vue'

export default {
  name: 'QrCodeScanner',
  components: {
    QrStream,
    BarCodeScanner
  },
  data() {
    return {
      isCameraActive: false,
      isBarcodeActive: false,
      modes: [
        { id: 'camera', label: 'QR Scanner' },
        { id: 'barcode', label: 'Barcode Scanner' },
        { id: 'manual', label: 'Manual Input' }
      ],
      currentMode: 'manual',
      isLoading: false,
      isReceiving: true,
      qrCode: '',
      statusMessage: '',
      statusClass: '',
      scanHistory: [],
      speechSynthesis: null
    }
  },
  mounted() {
    this.speechSynthesis = window.speechSynthesis
  },
  methods: {
    async setMode(mode) {
      // Deactivate all scanners first
      this.isCameraActive = false
      this.isBarcodeActive = false

      // Wait a bit for cleanup
      await new Promise(resolve => setTimeout(resolve, 300))

      this.currentMode = mode
      this.statusMessage = ''
      this.isReceiving = true

      // Activate appropriate scanner
      if (mode === 'camera') {
        this.isCameraActive = true
      } else if (mode === 'barcode') {
        this.isBarcodeActive = true
      } else if (mode === 'manual') {
        this.$nextTick(() => {
          this.$refs.manualInput?.focus()
        })
      }
    },

    async onInit(promise) {
      this.isLoading = true
      try {
        await promise
      } catch (error) {
        this.statusMessage = `Failed to initialize camera: ${error.message}`
        this.statusClass = 'bg-red-100 text-red-700'
      } finally {
        this.isLoading = false
      }
    },

    onDecode(result) {
        console.log('onDecode',result);
      this.processQrCode(result)
    },

    handleManualInput() {
        console.log(this.qrCode);

      if (this.qrCode.includes('-')) {
        this.processQrCode(this.qrCode)
      }
    },

    handleBarcode(result) {
      console.log('Barcode detected:', result)
      this.processCode(result.text)
    },

    handleError(error) {
      this.statusMessage = `Scanner error: ${error.message}`
      this.statusClass = 'bg-red-100 text-red-700'
    },

    processQrCode(code) {
      console.log('processQrCode', code)
      this.processCode(code)
    },

    processCode(code) {
      console.log('processCode', code)

      if (!this.isReceiving) return

      const parts = code.split('-')
      if (parts.length === 3 && parts[2] === 'p') {
        const scanData = {
          studentCode: parts[0],
          mark: parts[1],
          timestamp: new Date().toLocaleTimeString()
        }

        // Add to history
        this.scanHistory.unshift(scanData)

        // Emit the data
        this.$emit('code-scanned', scanData)

        // Speak the result
        this.speakResult(scanData)

        // Show success message
        this.statusMessage = `Successfully scanned: Student ${scanData.studentCode}`
        this.statusClass = 'bg-green-100 text-green-700'

        // Clear manual input if needed
        if (this.currentMode === 'manual') {
          this.qrCode = ''
          this.$refs.manualInput?.focus()
        }
      } else {
        this.statusMessage = 'Invalid QR code format'
        this.statusClass = 'bg-yellow-100 text-yellow-700'
      }
    },

    speakResult(data) {
      if (this.speechSynthesis) {
        this.speechSynthesis.cancel()
        const utterance = new SpeechSynthesisUtterance(
          `Student ${data.studentCode}, Mark ${data.mark}`
        )
        this.speechSynthesis.speak(utterance)
      }
    }
  }
}
</script>

<style scoped>
.qr-scanner-container {
  max-width: 42rem;
  margin-left: auto;
  margin-right: auto;
  padding: 1rem;
}

.camera-container {
  position: relative;
  aspect-ratio: 16/9;
  background-color: #f3f4f6;
  border-radius: 0.25rem;
  overflow: hidden;
}

.loading-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: rgba(243, 244, 246, 0.9);
}

.spinner {
  width: 3rem;
  height: 3rem;
  border: 4px solid #3b82f6;
  border-top-color: transparent;
  border-radius: 9999px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.camera-view {
  height: 100%;
}

.overlay-element {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.scan-region-highlight {
  border: 2px solid #3b82f6;
  border-radius: 0.25rem;
  width: 16rem;
  height: 16rem;
}

.history-item:hover {
  background-color: #f3f4f6;
}
</style>
