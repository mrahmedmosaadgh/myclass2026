<template>
  <div class="slide-toolbar flex items-center justify-between">
    <div class="flex items-center space-x-4">
      <!-- Slide navigation -->
      <div class="flex items-center space-x-2">
        <button
          @click="$emit('add-slide')"
          class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm transition-colors"
        >
          + New Slide
        </button>
        
        <div class="text-sm text-gray-600">
          Slide {{ currentSlideIndex + 1 }} of {{ totalSlides }}
        </div>
        
        <div class="flex space-x-1">
          <button
            v-if="hasSlides"
            @click="$emit('duplicate-slide')"
            class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded"
            title="Duplicate slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </button>
          
          <button
            v-if="hasSlides && totalSlides > 1"
            @click="$emit('delete-slide')"
            class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded"
            title="Delete slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Background color picker -->
    <div v-if="hasSlides" class="flex items-center space-x-2">
      <span class="text-sm text-gray-600">Background:</span>
      <input
        type="color"
        :value="backgroundColor"
        @input="updateBackgroundColor"
        class="w-8 h-8 cursor-pointer rounded border border-gray-300"
        title="Change background color"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  hasSlides: {
    type: Boolean,
    default: false
  },
  currentSlideIndex: {
    type: Number,
    default: 0
  },
  totalSlides: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['add-slide', 'delete-slide', 'duplicate-slide', 'update-background']);

const backgroundColor = ref('#ffffff');

const updateBackgroundColor = (event) => {
  backgroundColor.value = event.target.value;
  emit('update-background', event.target.value);
};

// Reset background color when slide changes
watch(() => props.currentSlideIndex, () => {
  backgroundColor.value = '#ffffff';
});
</script>