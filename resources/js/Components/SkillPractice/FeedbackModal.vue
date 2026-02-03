<template>
  <div 
    v-if="isOpen" 
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click="closeModal"
  >
    <div 
      class="bg-white rounded-lg p-8 max-w-md w-full mx-4 transform transition-all duration-300"
      :class="isCorrect ? 'scale-100 opacity-100' : 'scale-95 opacity-0'"
      @click.stop
    >
      <div :class="isCorrect ? 'text-green-600' : 'text-red-600'">
        <div class="flex items-center justify-center mb-4">
          <div v-if="isCorrect" class="bg-green-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div v-else class="bg-red-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>
        
        <h3 class="text-xl font-bold text-center mb-2">
          {{ isCorrect ? 'Correct!' : 'Incorrect' }}
        </h3>
        
        <p class="text-center mb-4">{{ feedbackText }}</p>
        
        <div class="flex items-center justify-center mb-6">
          <div 
            class="text-lg font-bold px-4 py-2 rounded-full"
            :class="isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
          >
            SmartScore: {{ scoreChange >= 0 ? '+' : '' }}{{ scoreChange }}
          </div>
        </div>
        
        <div class="flex justify-center">
          <button
            @click="onContinue"
            class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-6 rounded-md font-medium transition-colors duration-300"
          >
            {{ continueText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineProps, defineEmits } from 'vue';

export default {
  name: 'FeedbackModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    isCorrect: {
      type: Boolean,
      default: false
    },
    feedbackText: {
      type: String,
      default: ''
    },
    scoreChange: {
      type: Number,
      default: 0
    },
    continueText: {
      type: String,
      default: 'Continue'
    }
  },
  emits: ['continue', 'close'],
  setup(props, { emit }) {
    const onContinue = () => {
      emit('continue');
    };
    
    const closeModal = () => {
      emit('close');
    };
    
    return {
      onContinue,
      closeModal
    };
  }
};
</script>