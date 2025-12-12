<template>
  <div class="mt-8 bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30">
    <h3 class="text-center text-4xl font-bold mb-6 text-purple-700">Director's Commentary Mode</h3>


    <!-- Controls -->
    <div class="flex justify-center gap-8 mt-10">
      <button @click="$emit('start-replay')" :disabled="isReplaying"
        class="bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white px-12 py-6 rounded-3xl shadow-2xl text-3xl font-bold transition flex items-center gap-4 disabled:opacity-50">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-6.718-3.873A1 1 0 007 8.135v7.73a1 1 0 001.034.968l6.718-3.873a1 1 0 000-1.792z"/>
        </svg>
        Start Director Mode
      </button>

      <button v-if="isPaused" @click="$emit('resume')"
        class="bg-green-600 hover:bg-green-700 text-white px-12 py-6 rounded-3xl shadow-2xl text-3xl font-bold transition">
        Continue
      </button>

      <button v-if="isReplaying" @click="$emit('stop')"
        class="bg-red-600 hover:bg-red-700 text-white px-12 py-6 rounded-3xl shadow-2xl text-3xl font-bold transition">
        Stop
      </button>
    </div>

    <p class="text-center mt-8 text-gray-600 text-xl">
      {{ breakpoints.length }} scene{{ breakpoints.length !== 1 ? 's' : '' }} with voice commentary
    </p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  progress: number
  breakpoints: { globalIndex: number; progress: number; audio?: string }[]
  recordingIndex: number | null
  isReplaying: boolean
  isPaused: boolean
}>()

defineEmits<{
  'select-breakpoint': [index: number]
  'start-replay': []
  'resume': []
  'stop': []
  'seek': []
}>()
</script>