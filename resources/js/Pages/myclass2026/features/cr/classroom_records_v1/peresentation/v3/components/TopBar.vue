<template>
  <div class="fixed top-0 left-0 right-0 h-16 bg-gray-800 border-b border-gray-700 z-50 flex items-center justify-between px-6">
    <!-- Left: Logo/Title -->
    <div class="flex items-center space-x-3">
      <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
        </svg>
      </div>
      <span class="text-white font-semibold">Presentation Builder V3</span>
    </div>

    <!-- Center: Mode Toggle -->
    <div class="flex items-center bg-gray-700 rounded-lg p-1">
      <button
        @click="$emit('mode-change', 'edit')"
        :class="[
          'px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2',
          mode === 'edit' 
            ? 'bg-indigo-500 text-white' 
            : 'text-gray-300 hover:text-white hover:bg-gray-600'
        ]"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        <span>Edit</span>
      </button>
      <button
        @click="$emit('mode-change', 'present')"
        :class="[
          'px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2',
          mode === 'present' 
            ? 'bg-indigo-500 text-white' 
            : 'text-gray-300 hover:text-white hover:bg-gray-600'
        ]"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>Present</span>
      </button>
    </div>

    <!-- Right: Actions -->
    <div class="flex items-center space-x-3">
      <!-- Height Selector -->
      <select
        :value="slideHeight"
        @change="$emit('height-change', parseInt($event.target.value))"
        class="bg-gray-700 text-white border border-gray-600 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        <option value="500">Normal (500px)</option>
        <option value="800">Medium (800px)</option>
        <option value="1123">A4 (1123px)</option>
        <option value="1200">Large (1200px)</option>
      </select>

      <!-- Export -->
      <button
        @click="$emit('export')"
        class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded-md transition-colors"
        title="Export"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
      </button>

      <!-- Import -->
      <label class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded-md transition-colors cursor-pointer" title="Import">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
        </svg>
        <input
          type="file"
          accept=".json"
          @change="handleImport"
          class="hidden"
        />
      </label>

      <!-- Delete Slide -->
      <button
        @click="$emit('delete-slide', currentSlideIndex)"
        :disabled="totalSlides <= 1"
        :class="[
          'p-2 rounded-md transition-colors',
          totalSlides <= 1
            ? 'text-gray-600 cursor-not-allowed'
            : 'text-gray-300 hover:text-red-400 hover:bg-gray-700'
        ]"
        title="Delete Slide"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  mode: {
    type: String,
    required: true
  },
  currentSlideIndex: {
    type: Number,
    required: true
  },
  totalSlides: {
    type: Number,
    required: true
  },
  slideHeight: {
    type: Number,
    required: true
  }
})

const emit = defineEmits([
  'mode-change',
  'export',
  'import',
  'delete-slide',
  'height-change'
])

const handleImport = (event) => {
  const file = event.target.files[0]
  if (file) {
    emit('import', file)
  }
  // Reset input
  event.target.value = ''
}
</script>
