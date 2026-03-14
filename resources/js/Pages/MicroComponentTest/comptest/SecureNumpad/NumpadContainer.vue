<template>
  <div 
    class="p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl animate-enter"
  >
    <div class="grid grid-cols-3 gap-3">
      <!-- Numbers 1-9 -->
      <button 
        v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]" 
        :key="num"
        @click="$emit('press', num)"
        class="h-14 text-xl font-semibold bg-zinc-50 hover:bg-white active:bg-blue-50 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:active:bg-blue-900/30 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm transition-all active:scale-95 text-zinc-700 dark:text-zinc-200"
      >
        {{ num }}
      </button>

      <!-- Bottom Row -->
      <button 
        @click="$emit('clear')"
        class="h-14 flex items-center justify-center text-red-500 bg-red-50 hover:bg-red-100 dark:bg-red-900/10 dark:hover:bg-red-900/20 border border-red-100 dark:border-red-900/30 rounded-xl transition-all active:scale-95"
      >
        <!-- Trash/Clear Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
      </button>

      <button 
        @click="$emit('press', 0)"
        class="h-14 text-xl font-semibold bg-zinc-50 hover:bg-white active:bg-blue-50 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:active:bg-blue-900/30 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm transition-all active:scale-95 text-zinc-700 dark:text-zinc-200"
      >
        0
      </button>

      <button 
        @click="$emit('backspace')"
        class="h-14 flex items-center justify-center text-zinc-600 dark:text-zinc-400 bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 rounded-xl transition-all active:scale-95"
      >
        <!-- Backspace Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><line x1="18" x2="12" y1="9" y2="15"/><line x1="12" x2="18" y1="9" y2="15"/></svg>
      </button>
    </div>

    <!-- Close Button -->
    <button 
      @click="$emit('close')"
      class="mt-4 w-full h-10 flex items-center justify-center gap-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-500 rounded-lg shadow-sm shadow-blue-500/20 transition-all active:scale-95"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      Done
    </button>
  </div>
</template>

<script setup>
defineEmits(['press', 'clear', 'backspace', 'close']);
</script>

<style scoped>
.animate-enter {
  animation: slideUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
