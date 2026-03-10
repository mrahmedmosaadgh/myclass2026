<script setup>
import { computed } from 'vue';

const props = defineProps({
  studentId: String,
  studentName: String,
  stagedChoice: String, // A, B, C, D
  status: {
    type: String,
    default: 'idle' // idle, staged, success, error
  },
  message: String,
});

const isIdle = computed(() => props.status === 'idle');
const isStaged = computed(() => props.status === 'staged');
const isSuccess = computed(() => props.status === 'success');
const isError = computed(() => props.status === 'error');
</script>

<template>
  <div class="h-full w-full rounded-xl flex flex-col items-center justify-center p-6 text-center transition-colors duration-300"
    :class="{
      'bg-gray-100 border-2 border-dashed border-gray-300': isIdle,
      'bg-blue-50 border-2 border-blue-400': isStaged,
      'bg-green-100 border-2 border-green-500': isSuccess,
      'bg-red-50 border-2 border-red-400': isError,
    }">
    
    <div v-if="isIdle" class="text-gray-500">
      <svg class="h-16 w-16 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
      </svg>
      <h3 class="text-xl font-semibold mb-2">Ready to Scan</h3>
      <p>Show your QR card to the camera</p>
    </div>
    
    <div v-if="isStaged" class="w-full">
      <h3 class="text-lg text-blue-800 mb-1">Staged Answer</h3>
      <div class="text-4xl font-bold text-gray-900 mb-2">{{ studentName || studentId }}</div>
      <div class="text-6xl font-black text-blue-600 mb-6">{{ stagedChoice }}</div>
      
      <div class="flex items-center justify-center space-x-4">
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-bold flex items-center animate-pulse">
          <span class="mr-2">Scan ✅ to Confirm</span>
        </div>
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded-lg font-bold flex items-center">
          <span class="mr-2">Scan ❌ to Cancel</span>
        </div>
      </div>
    </div>
    
    <div v-if="isSuccess" class="text-green-800">
      <svg class="h-20 w-20 mx-auto mb-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <div class="text-3xl font-bold mb-2">Confirmed!</div>
      <div class="text-xl">{{ studentName || studentId }} answered {{ stagedChoice }}</div>
    </div>
    
    <div v-if="isError" class="text-red-800">
      <svg class="h-16 w-16 mx-auto mb-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <div class="text-2xl font-bold mb-2">Error</div>
      <div class="text-lg">{{ message }}</div>
    </div>
    
  </div>
</template>
