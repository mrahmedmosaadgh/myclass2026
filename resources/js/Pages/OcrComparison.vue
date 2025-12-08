<template>
  <Head title="OCR Comparison" />
    <div>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        OCR Methods Comparison
      </h2>
    </div>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Compare Different OCR Methods</h3>
            <p class="text-gray-600">
              Upload an image to compare the results from different OCR services:
            </p>
            <ul class="list-disc ml-6 mt-2 text-gray-600">
              <li><strong>Tesseract.js:</strong> Client-side processing, works offline, free but slower</li>
              <li><strong>OCR.Space:</strong> Cloud API, fast, limited free tier</li>
              <li><strong>Google Cloud Vision:</strong> Most accurate, paid service (requires setup)</li>
            </ul>
          </div>
          
          <OcrComparison />
        </div>
      </div>
    </div>

</template>

<script>
import { defineComponent } from 'vue'
// import AppLayout from '@/Layouts/AppLayout.vue'
import OcrComparison from '@/Components/OcrComparison.vue'

export default defineComponent({
  components: {
    // AppLayout,
    OcrComparison
  }
})
</script>




