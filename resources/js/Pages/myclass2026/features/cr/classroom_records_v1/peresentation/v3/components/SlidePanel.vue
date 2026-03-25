<template>
  <!-- Desktop Slide Panel -->
  <div class="hidden md:flex md:w-48 bg-gray-800 border-r border-gray-700 flex-col">
    <!-- Header -->
    <div class="p-4 border-b border-gray-700">
      <h3 class="text-white font-medium text-sm">Slides</h3>
    </div>

    <!-- Slides List -->
    <div class="flex-1 overflow-y-auto p-3 space-y-2">
      <div
        v-for="(slide, index) in slides"
        :key="slide.id"
        @click="$emit('slide-select', index)"
        :class="[
          'relative aspect-[210/297] bg-gray-700 rounded-lg cursor-pointer transition-all duration-200 border-2',
          currentSlideIndex === index
            ? 'border-indigo-500 shadow-lg shadow-indigo-500/20'
            : 'border-transparent hover:border-gray-600'
        ]"
      >
        <!-- Slide Preview -->
        <div class="absolute inset-0 p-2">
          <div class="w-full h-full bg-white rounded-sm flex items-center justify-center">
            <span class="text-gray-400 text-xs font-medium">{{ index + 1 }}</span>
          </div>
        </div>

        <!-- Active Indicator -->
        <div
          v-if="currentSlideIndex === index"
          class="absolute -right-2 -top-2 w-4 h-4 bg-indigo-500 rounded-full flex items-center justify-center"
        >
          <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
          </svg>
        </div>

        <!-- Delete Button -->
        <button
          v-if="slides.length > 1"
          @click.stop="$emit('slide-delete', index)"
          class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 hover:opacity-100 transition-opacity duration-200 flex items-center justify-center group"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Slide Panel Overlay -->
  <div
    v-if="showMobilePanel"
    class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex"
    @click.self="closeMobilePanel"
  >
    <div class="w-80 bg-gray-800 flex flex-col">
      <!-- Header with Close Button -->
      <div class="p-4 border-b border-gray-700 flex items-center justify-between">
        <h3 class="text-white font-medium text-sm">Slides</h3>
        <button
          @click="closeMobilePanel"
          class="text-gray-400 hover:text-white transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Slides List -->
      <div class="flex-1 overflow-y-auto p-3 space-y-2">
        <div
          v-for="(slide, index) in slides"
          :key="slide.id"
          @click="$emit('slide-select', index); closeMobilePanel()"
          :class="[
            'relative aspect-[210/297] bg-gray-700 rounded-lg cursor-pointer transition-all duration-200 border-2',
            currentSlideIndex === index
              ? 'border-indigo-500 shadow-lg shadow-indigo-500/20'
              : 'border-transparent hover:border-gray-600'
          ]"
        >
          <!-- Slide Preview -->
          <div class="absolute inset-0 p-2">
            <div class="w-full h-full bg-white rounded-sm flex items-center justify-center">
              <span class="text-gray-400 text-xs font-medium">{{ index + 1 }}</span>
            </div>
          </div>

          <!-- Active Indicator -->
          <div
            v-if="currentSlideIndex === index"
            class="absolute -right-2 -top-2 w-4 h-4 bg-indigo-500 rounded-full flex items-center justify-center"
          >
            <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
          </div>

          <!-- Delete Button -->
          <button
            v-if="slides.length > 1"
            @click.stop="$emit('slide-delete', index)"
            class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 hover:opacity-100 transition-opacity duration-200 flex items-center justify-center group"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue'

const props = defineProps({
  slides: {
    type: Array,
    required: true
  },
  currentSlideIndex: {
    type: Number,
    required: true
  }
})

const emit = defineEmits([
  'slide-select',
  'slide-delete'
])

// Mobile panel state
const showMobilePanel = ref(false)

// Methods
const closeMobilePanel = () => {
  showMobilePanel.value = false
}

const openMobilePanel = () => {
  showMobilePanel.value = true
}

// Expose method to parent
defineExpose({
  openMobilePanel
})
</script>
