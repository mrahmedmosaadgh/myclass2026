<template>
  <div class="barcode-scanner relative">
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
      <p class="mt-2">Initializing camera...</p>
    </div>

    <!-- Interactive Area -->
    <div class="viewport-container relative h-[300px] bg-black rounded-lg overflow-hidden">
      <div 
        id="interactive" 
        class="viewport w-full h-full"
        :class="{ 'hidden': !isActive || isLoading }"
      ></div>
      <!-- Scanner Overlay -->
      <div v-show="isActive && !isLoading" class="absolute inset-0 pointer-events-none">
        <div class="laser"></div>
        <div class="scanner-region"></div>
      </div>
    </div>

    <!-- Status Messages -->
    <div 
      v-if="error" 
      class="error-message absolute bottom-0 left-0 right-0 p-4 bg-red-500 bg-opacity-90 text-white text-center"
    >
      {{ error }}
    </div>
  </div>
</template>

<script>
import Quagga from '@ericblade/quagga2'

export default {
  name: 'BarCodeScanner',
  props: {
    isActive: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      error: null,
      isLoading: false,
      isScanning: false,
      stream: null
    }
  },
  watch: {
    isActive: {
      immediate: false,
      handler(newValue) {
        if (newValue) {
          this.$nextTick(() => this.startScanner())
        } else {
          this.stopScanner()
        }
      }
    }
  },
  mounted() {
    if (this.isActive) {
      this.startScanner()
    }
  },
  beforeMount() {
    // Initialize Quagga event listeners
    if (typeof Quagga !== 'undefined') {
      Quagga.onDetected(this.onDetected)
    }
  },
  
  beforeUnmount() {
    // Clean up Quagga event listeners
    if (typeof Quagga !== 'undefined') {
      Quagga.offDetected(this.onDetected)
    }
    this.stopScanner()
  },
  methods: {
    async startScanner() {
      try {
        if (this.isScanning) return
        this.isScanning = true
        await this.initScanner()
      } catch (error) {
        console.error('Start scanner error:', error)
        this.error = `Failed to start scanner: ${error.message || error}`
        this.isLoading = false
        this.isScanning = false
      }
    },

    async checkCameraPermission() {
      try {
        const devices = await navigator.mediaDevices.enumerateDevices()
        const videoDevices = devices.filter(device => device.kind === 'videoinput')
        
        if (videoDevices.length === 0) {
          throw new Error('No camera found')
        }

        const stream = await navigator.mediaDevices.getUserMedia({
          video: true
        })
        
        this.stream = stream
        return true
      } catch (error) {
        console.error('Camera permission error:', error)
        return false
      }
    },

    async initScanner() {
      if (!this.isActive) {
        return
      }

      this.isLoading = true
      this.error = null

      // Check camera permission first
      const hasPermission = await this.checkCameraPermission()
      if (!hasPermission) {
        this.error = 'Camera access denied. Please allow camera access and try again.'
        this.isLoading = false
        return
      }

      // Get available video devices
      const devices = await navigator.mediaDevices.enumerateDevices()
      const videoDevices = devices.filter(device => device.kind === 'videoinput')
      console.log('Available video devices:', videoDevices)

      // Initialize Quagga with the first available camera
      Quagga.init({
        inputStream: {
          name: "Live",
          type: "LiveStream",
          target: document.querySelector("#interactive"),
          constraints: {
            width: 640,
            height: 480,
            facingMode: "user"
          },
          area: {
            top: "0%",
            right: "0%",
            left: "0%",
            bottom: "0%"
          },
          singleChannel: false
        },
        locator: {
          patchSize: "medium",
          halfSample: true
        },
        numOfWorkers: 4,
        decoder: {
          readers: [
            "code_128_reader",
            "ean_reader",
            "ean_8_reader",
            "code_39_reader",
            "upc_reader",
            "upc_e_reader"
          ],
          debug: false
        },
        locate: true,
      }, (err) => {
        if (err) {
          console.error('Quagga initialization error:', err)
          this.error = `Failed to start scanner: ${err.message || err}`
          this.isLoading = false
          return
        }
        console.log('Quagga initialized successfully')
        Quagga.start()
        this.isLoading = false
      })
    },

    async onDetected(result) {
      console.log('Barcode detected:', result)
      if (!this.isScanning) return

      if (result.codeResult) {
        this.$emit('barcode-detected', {
          text: result.codeResult.code,
          format: result.codeResult.format,
          timestamp: new Date().getTime()
        })
      }
    },
    stopScanner() {
      if (this.stream) {
        this.stream.getTracks().forEach(track => track.stop())
        this.stream = null
      }
      if (this.isScanning) {
        Quagga.stop()
        this.isScanning = false
      }
    }
  }
}
</script>

<style scoped>
.viewport-container {
  position: relative;
}

.viewport {
  position: relative;
}

#interactive.viewport > video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

#interactive.viewport > canvas.drawingBuffer {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.loading-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.8);
  color: white;
  z-index: 10;
}

.spinner {
  width: 2.5rem;
  height: 2.5rem;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.scanner-region {
  position: absolute;
  left: 50%;
  top: 50%;
  width: 280px;
  height: 160px;
  transform: translate(-50%, -50%);
  border: 2px solid rgba(255, 255, 255, 0.5);
  border-radius: 4px;
  box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
}

.laser {
  position: absolute;
  left: 50%;
  top: 50%;
  width: 30px;
  height: 2px;
  transform: translate(-50%, -50%);
  background: #3498db;
  box-shadow: 0 0 4px #3498db;
  animation: scanning 2s infinite;
}

@keyframes scanning {
  0% {
    transform: translate(-50%, -100px);
  }
  50% {
    transform: translate(-50%, 100px);
  }
  100% {
    transform: translate(-50%, -100px);
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.hidden {
  display: none !important;
}
</style>
