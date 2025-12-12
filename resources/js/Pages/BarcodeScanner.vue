<template>
  <Head title="Barcode Scanner" />
    <div>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Barcode Scanner
      </h2>
    </div>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- Scanner Component -->
          <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Scan Barcodes</h3>
            <barCodeScanner
              :is-active="true"
              @barcode-detected="handleBarcode"
              @error="handleError"
            />
            <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
          </div>

          <!-- Last Scanned -->
          <div v-if="lastScanned" class="mb-8 p-4 bg-green-50 rounded-lg">
            <h4 class="font-medium text-green-800 mb-2">Last Scanned</h4>
            <div class="flex justify-between items-center">
              <div>
                <p class="text-green-700">Code: {{ lastScanned.text }}</p>
                <p class="text-sm text-green-600">Format: {{ lastScanned.format }}</p>
                <p class="text-xs text-green-500">{{ formatTime(lastScanned.timestamp) }}</p>
              </div>
              <button
                @click="copyToClipboard(lastScanned.text)"
                class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200"
              >
                Copy
              </button>
            </div>
          </div>

          <!-- Scan History -->
          <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Scan History</h3>
            <div class="space-y-4 max-h-96 overflow-y-auto">
              <div
                v-for="(scan, index) in scanHistory"
                :key="index"
                class="p-4 bg-gray-50 rounded-lg"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <p class="font-medium text-gray-900">{{ scan.text }}</p>
                    <p class="text-sm text-gray-600">Format: {{ scan.format }}</p>
                    <p class="text-xs text-gray-500">{{ formatTime(scan.timestamp) }}</p>
                  </div>
                  <div class="flex space-x-2">
                    <button
                      @click="copyToClipboard(scan.text)"
                      class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                    >
                      Copy
                    </button>
                    <button
                      @click="removeScan(index)"
                      class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Clear History Button -->
            <div v-if="scanHistory.length > 0" class="mt-4 text-right">
              <button
                @click="clearHistory"
                class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
              >
                Clear History
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
 
</template>

<script>
import { defineComponent } from 'vue'
 
import barCodeScanner from '@/Components/barCodeScanner.vue'

export default defineComponent({
  name: 'BarcodeScannerPage',
  
  components: {
 
    barCodeScanner
  },

  data() {
    return {
      error: null,
      lastScanned: null,
      scanHistory: [],
    }
  },

  methods: {
    handleBarcode(result) {
      this.error = null
      this.lastScanned = result
      this.scanHistory.unshift(result)

      // Play a success sound
      const audio = new Audio('/audio/beep.mp3')
      audio.play()
    },

    handleError(error) {
      this.error = error.message || 'Scanner error occurred'
    },

    formatTime(timestamp) {
      return new Date(timestamp).toLocaleString()
    },

    async copyToClipboard(text) {
      try {
        await navigator.clipboard.writeText(text)
      } catch (error) {
        console.error('Failed to copy:', error)
      }
    },

    removeScan(index) {
      this.scanHistory.splice(index, 1)
    },

    clearHistory() {
      this.scanHistory = []
      this.lastScanned = null
    }
  }
})
</script>

<style scoped>
/* Add any additional styles here */
</style>
