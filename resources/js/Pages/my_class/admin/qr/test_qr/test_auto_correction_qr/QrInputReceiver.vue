<template>
  <div class="qr-input-receiver">
    <div class="controls mb-4">
      <button 
        @click="toggleReceivingMode" 
        :class="['px-4 py-2 rounded font-semibold', isReceiving ? 'bg-red-500 text-white' : 'bg-green-500 text-white']">
        {{ isReceiving ? 'Stop Receiving' : 'Start Receiving' }}
      </button>
    </div>

    <div v-if="!hasFocus && isReceiving" class="mb-4 p-4 bg-yellow-100 border border-yellow-400 rounded text-yellow-700">
      ⚠️ Window is not in focus! QR codes won't be received. Click anywhere in the window to restore focus.
    </div>

    <input
      type="text"
      ref="qrInput"
      v-model="qrCode"
      @input="handleQrInput"
      style="position: absolute; opacity: 0;"
      :autofocus="isReceiving"
    />

    <div v-if="isReceiving" class="status-indicator mb-4 p-4 bg-blue-100 border border-blue-400 rounded">
      📱 Ready to receive QR codes
    </div>

    <div v-if="lastScannedCode" class="mt-4 p-4 bg-green-100 border border-green-400 rounded">
      ✅ Last scanned: {{ lastScannedCode }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'QrInputReceiver',
  data() {
    return {
      qrCode: '',
      lastScannedCode: '',
      isReceiving: false,
      hasFocus: true,
      speechSynthesis: null
    }
  },
  mounted() {
    window.addEventListener('focus', this.handleFocus)
    window.addEventListener('blur', this.handleBlur)
    document.addEventListener('click', this.handleDocumentClick)
    this.speechSynthesis = window.speechSynthesis
  },
  beforeUnmount() {
    window.removeEventListener('focus', this.handleFocus)
    window.removeEventListener('blur', this.handleBlur)
    document.removeEventListener('click', this.handleDocumentClick)
  },
  methods: {
    toggleReceivingMode() {
      this.isReceiving = !this.isReceiving
      if (this.isReceiving) {
        this.$nextTick(() => {
          this.focusInput()
        })
      }
    },
    focusInput() {
      if (this.isReceiving) {
        this.$refs.qrInput.focus()
      }
    },
    handleFocus() {
      this.hasFocus = true
      this.focusInput()
    },
    handleBlur() {
      this.hasFocus = false
    },
    handleDocumentClick() {
      this.focusInput()
    },
    speakText(text) {
      // Cancel any ongoing speech
      this.speechSynthesis.cancel()
      
      const utterance = new SpeechSynthesisUtterance(text)
      utterance.rate = 1.0
      utterance.pitch = 1.0
      utterance.volume = 1.0
      this.speechSynthesis.speak(utterance)
    },

    handleQrInput() {
      if (!this.isReceiving) return

      // Check if the input matches the expected format: 'student code'-'mark'-p
      if (this.qrCode.includes('-')) {
        const parts = this.qrCode.split('-')
        if (parts.length === 3 && parts[2] === 'p') {
          this.lastScannedCode = this.qrCode
          
          // Prepare the data
          const data = {
            studentCode: parts[0],
            mark: parts[1]
          }
          
          // Emit the scanned data to parent component
          this.$emit('qr-scanned', data)
          
          // Speak the student code and mark
          this.speakText(`Student ${data.studentCode}, Mark ${data.mark}`)
          
          // Clear the input for next scan
          this.qrCode = ''
          // Ensure input stays focused
          this.focusInput()
        }
      }
    }
  }
}
</script>

<style scoped>
.qr-input-receiver {
  min-height: 100px;
  padding: 1rem;
  border: 2px dashed #ccc;
  border-radius: 0.5rem;
  margin: 1rem 0;
}

.controls {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
</style>
