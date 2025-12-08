<template>
  <AppLayout title="OCR Scanner Test">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        OCR Scanner Test
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <OcrScanner
            @ocr-complete="handleOcrComplete"
            @ocr-error="handleOcrError"
          />
        </div>
        <div class="p-4">
            <OcrComparison></OcrComparison>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { defineComponent } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import OcrScanner from '@/Components/OcrScanner.vue'
import OcrComparison from '@/Components/OcrComparison.vue'
// resources/js/Components/OcrComparison.vue

export default defineComponent({
  components: {
    AppLayout,
    OcrScanner,OcrComparison
  },
  methods: {
    handleOcrComplete(result) {
      console.log('OCR completed:', result)
    },
    handleOcrError(error) {
      console.error('OCR error:', error)
    }
  }
})
</script>

