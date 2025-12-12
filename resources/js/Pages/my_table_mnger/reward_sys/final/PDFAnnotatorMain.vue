<template>
 
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h1 class="text-2xl font-bold text-center mb-8 text-gray-800 dark:text-white">
            Draw & Sign on Your Certificate
          </h1>
          <div class="pdf-annotator-wrapper">
            <PDFAnnotator />
          </div>
        </div>
      </div>
    </div>
 
</template>

<script setup>
 
import PDFAnnotator from './PDFAnnotator.vue'  // Adjust path if in /final/ folder
</script>

<style scoped>
.pdf-annotator-wrapper { min-height: 80vh; background: #f9fafb; border-radius: 12px; padding: 20px; }
</style>