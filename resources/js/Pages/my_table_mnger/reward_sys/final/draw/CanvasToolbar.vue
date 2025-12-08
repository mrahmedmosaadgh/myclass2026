<!-- resources/js/Pages/my_table_mnger/reward_sys/final/components/CanvasToolbar.vue -->
<template>
  <!-- Floating Island Toolbar -->
  <div class="fixed top-6 left-1/2 -translate-x-1/2 z-50 max-w-[95vw] pt-12">
    <div class="bg-slate-900/90 backdrop-blur-xl text-white rounded-2xl shadow-2xl border border-white/10 p-2 flex flex-wrap gap-2 items-center justify-center transition-all duration-300 hover:bg-slate-900/95">
      
      <!-- Group 1: Drawing Tools -->
      <div class="flex bg-slate-800/50 rounded-xl p-1 gap-1">
        <button @click="$emit('update:tool', 'pen')"
          :class="tool === 'pen' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-white/10'"
          class="p-2.5 rounded-lg transition-all duration-200 group relative" title="Pen Tool">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        </button>
        <button @click="$emit('update:tool', 'eraser')"
          :class="tool === 'eraser' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/30' : 'text-slate-400 hover:text-white hover:bg-white/10'"
          class="p-2.5 rounded-lg transition-all duration-200" title="Eraser">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v4m0 4v.01"/></svg>
        </button>
      </div>

      <!-- Divider -->
      <div class="w-px h-8 bg-white/10 mx-1"></div>

      <!-- Group 2: Properties -->
      <div class="flex items-center gap-3 bg-slate-800/50 rounded-xl p-1.5 px-3">
        <div class="relative group">
          <input type="color" :value="color" @input="$emit('update:color', ($event.target as HTMLInputElement).value)"
            class="w-8 h-8 rounded-full cursor-pointer border-2 border-white/20 p-0.5 bg-transparent hover:scale-110 transition" />
        </div>
        <div class="flex items-center gap-2">
          <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="12"/></svg>
          <input type="range" :value="lineWidth" @input="$emit('update:lineWidth', Number(($event.target as HTMLInputElement).value))"
            min="1" max="50" class="w-24 h-1.5 bg-slate-600 rounded-lg appearance-none cursor-pointer accent-indigo-500" />
          <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="12"/></svg>
        </div>
      </div>

      <!-- Divider -->
      <div class="w-px h-8 bg-white/10 mx-1"></div>

      <!-- Group 3: Actions -->
      <div class="flex gap-1">
        <button @click="$emit('undo')" :disabled="historyStep <= 0"
          class="p-2.5 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg disabled:opacity-30 disabled:hover:bg-transparent transition" title="Undo">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
        </button>
        <button @click="$emit('clear')"
          class="p-2.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition" title="Clear All">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
      </div>

      <!-- Divider -->
      <div class="w-px h-8 bg-white/10 mx-1"></div>

      <!-- Group 4: Background & Save -->
      <div class="flex gap-1">
        <div class="relative group">
          <button class="p-2.5 text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 rounded-lg transition flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-xs font-bold">BG</span>
          </button>
          
          <!-- Dropdown Menu for Background -->
          <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-48 bg-slate-800 rounded-xl shadow-xl border border-white/10 p-2 hidden group-hover:block">
            <button @click="$emit('paste-bg')" class="w-full text-left px-3 py-2 text-sm text-slate-300 hover:bg-white/10 rounded-lg">Paste from Clipboard</button>
            <button v-if="hasBg" @click="$emit('remove-bg')" class="w-full text-left px-3 py-2 text-sm text-rose-400 hover:bg-rose-500/10 rounded-lg">Remove Background</button>
            <div v-if="hasBg" class="my-1 border-t border-white/10"></div>
            <div v-if="hasBg" class="px-2 py-1 text-xs text-slate-500 font-bold">MODE</div>
            <div v-if="hasBg" class="grid grid-cols-2 gap-1">
              <button v-for="mode in ['contain', 'fill', 'cover', 'original']" :key="mode"
                @click="$emit('update:bgMode', mode as any)"
                :class="bgMode === mode ? 'bg-indigo-500 text-white' : 'bg-slate-700 text-slate-400'"
                class="px-2 py-1 rounded text-xs capitalize">{{ mode }}</button>
            </div>
          </div>
        </div>

        <button @click="$emit('save')"
          class="p-2.5 text-slate-400 hover:text-cyan-400 hover:bg-cyan-500/10 rounded-lg transition" title="Save Project">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
        </button>
        
        <label class="p-2.5 text-slate-400 hover:text-cyan-400 hover:bg-cyan-500/10 rounded-lg transition cursor-pointer" title="Load Project">
          <input type="file" @change="$emit('load', $event)" accept=".json" class="hidden" />
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        </label>
      </div>

      <!-- Group 5: Replay Controls -->
      <div class="w-px h-8 bg-white/10 mx-1"></div>
      <div class="flex gap-1">
        <button v-if="!isReplaying" @click="$emit('start-replay')" :disabled="strokesLength === 0"
          class="p-2.5 text-slate-400 hover:text-green-400 hover:bg-green-500/10 rounded-lg transition disabled:opacity-30" title="Play">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-6.718-3.873A1 1 0 007 8.135v7.73a1 1 0 001.034.968l6.718-3.873a1 1 0 000-1.792z"/></svg>
        </button>
        <button v-else-if="replayPaused" @click="$emit('resume-replay')"
          class="p-2.5 text-green-400 hover:text-green-300 hover:bg-green-500/20 rounded-lg transition" title="Resume">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-6.718-3.873A1 1 0 007 8.135v7.73a1 1 0 001.034.968l6.718-3.873a1 1 0 000-1.792z"/></svg>
        </button>
        <button v-else @click="$emit('pause-replay')" 
          class="p-2.5 text-amber-400 hover:text-amber-300 hover:bg-amber-500/20 rounded-lg transition" title="Pause">
           <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </button>
        
        <button v-if="isReplaying" @click="$emit('stop-replay')"
          class="p-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/20 rounded-lg transition" title="Stop">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
        </button>
      </div>

      <!-- Special: Add Breakpoint -->
      <button v-if="!isReplaying && strokesLength > 0 && !isDrawing" @click="$emit('add-breakpoint')"
        class="ml-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white px-4 py-2 rounded-xl shadow-lg shadow-orange-500/20 transition flex items-center gap-2 text-sm font-bold animate-pulse">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Add Marker
      </button>

    </div>
  </div>

  <!-- Padding at top so content doesn't hide under the sticky toolbar -->
  <div class="h-32"></div>
</template>

<script setup lang="ts">
defineProps<{
  tool: 'pen' | 'eraser'
  color: string
  lineWidth: number
  isDrawing: boolean
  isReplaying: boolean
  replayPaused: boolean
  replayProgress: number
  strokesLength: number
  hasBg: boolean
  historyStep?: number
  bgMode: 'contain' | 'fill' | 'cover' | 'original'
  bgAlign: 'center' | 'top-center'
}>()

defineEmits<{
  'update:tool': [value: 'pen' | 'eraser']
  'update:color': [value: string]
  'update:lineWidth': [value: number]
  'update:bgMode': [value: 'contain' | 'fill' | 'cover' | 'original']
  'update:bgAlign': [value: 'center' | 'top-center']
  'add-breakpoint': []
  'clear': []
  'undo': []
  'paste-bg': []
  'remove-bg': []
  'save': []
  'load': [event: Event]
  'start-replay': []
  'stop-replay': []
  'resume-replay': []
  'pause-replay': []
}>()
</script>

<style scoped>
/* Optional: smooth shadow when scrolling */
.fixed {
  transition: all 0.3s ease;
}
</style>