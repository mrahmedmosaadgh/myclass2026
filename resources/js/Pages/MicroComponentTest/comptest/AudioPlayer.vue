<template>
  <div 
    class="relative inline-flex flex-col"
    :class="{ 'w-full max-w-md': showControls }"
  >
    <!-- Audio Element (Hidden) -->
    <audio
      ref="audioRef"
      :src="src"
      :loop="isLooping"
      preload="metadata"
      @timeupdate="onTimeUpdate"
      @loadedmetadata="onLoadedMetadata"
      @ended="onEnded"
      class="hidden"
    ></audio>

    <div 
      v-if="showControls" 
      class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border border-zinc-200 dark:border-zinc-800 rounded-2xl p-4 shadow-xl ring-1 ring-black/5 transition-all duration-300 hover:shadow-2xl hover:border-blue-500/30 group"
    >
      <div class="flex items-center gap-4">
        <!-- Play/Pause Button (Circle) -->
        <button 
          @click="togglePlay"
          :disabled="isLocked"
          class="relative w-12 h-12 flex items-center justify-center rounded-full bg-blue-600 hover:bg-blue-500 text-white shadow-lg shadow-blue-500/30 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed group-hover:scale-105"
        >
          <div v-if="isPlaying" class="i-lucide-pause w-5 h-5 fill-current">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="4" height="16" x="6" y="4" rx="1"/><rect width="4" height="16" x="14" y="4" rx="1"/></svg>
          </div>
          <div v-else class="i-lucide-play w-5 h-5 fill-current ml-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="6 3 20 12 6 21 6 3"/></svg>
          </div>
        </button>

        <!-- Info & Progress -->
        <div class="flex-1 min-w-0">
          <div class="flex justify-between items-center mb-1">
             <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 truncate pr-2">
               {{ title || 'Audio Track' }}
             </h3>
             <div class="flex items-center gap-2">
               <!-- Loop Toggle (Small) -->
               <button 
                 @click="toggleLoop"
                 class="p-1 rounded-md transition-colors"
                 :class="isLooping ? 'text-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300'"
                 title="Toggle Loop"
               >
                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 10 5-6 5 6"/><path d="M21 10a9 9 0 0 1-9 9 9 9 0 0 1-2.99-.5"/><path d="M3 14a9 9 0 0 1 9-9 9 9 0 0 1 2.99.5"/></svg>
               </button>
               <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400">
                 {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
               </span>
             </div>
          </div>

          <!-- Progress Bar -->
          <div 
            class="relative h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden cursor-pointer group/progress"
            @click="seek"
          >
            <div 
              class="absolute top-0 left-0 h-full bg-linear-to-r from-blue-500 to-indigo-500 transition-all duration-100"
              :style="{ width: `${progress}%` }"
            ></div>
            <!-- Seek Indicator (Visible on Hover) -->
            <div 
              class="absolute top-0 h-full w-1 bg-white/50 opacity-0 group-hover/progress:opacity-100 transition-opacity"
              :style="{ left: `${progress}%` }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Minimal UI (Trigger Button Only) -->
    <button
      v-else
      @click="triggerAction"
      :disabled="isLocked"
      class="group relative inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-sm font-medium rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm hover:shadow-md hover:border-blue-500/50 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed overflow-hidden"
    >
      <!-- Background Progress (for blocking mode) -->
      <div 
        v-if="!allowReplayWhenPlaying && isPlaying"
        class="absolute bottom-0 left-0 h-0.5 bg-blue-500 transition-all duration-100"
        :style="{ width: `${progress}%` }"
      ></div>

      <span class="relative z-10 flex items-center gap-2">
        <span v-if="isPlaying" class="animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><path d="M12 2v20"/><path d="M2 10v4"/><path d="M22 10v4"/><path d="M7 6v12"/><path d="M17 6v12"/></svg>
        </span>
        <span v-else>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
        </span>
        {{ label || (isPlaying ? 'Playing...' : 'Play Audio') }}
      </span>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';

const props = defineProps({
  src: {
    type: String,
    required: true
  },
  // If true, clicking play while playing will restart the track immediately.
  // If false, it respects the natural behavior (or blocks if modeled that way).
  // Combining with the user require: "don't play another unless the current is done playing"
  // implies a Lock mode.
  allowReplayWhenPlaying: {
    type: Boolean,
    default: true
  },
  showControls: {
    type: Boolean,
    default: false
  },
  label: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: ''
  },
  loop: {
    type: Boolean,
    default: false
  }
});

const audioRef = ref(null);
const isPlaying = ref(false);
const isLooping = ref(props.loop);
const currentTime = ref(0);
const duration = ref(0);

watch(() => props.loop, (val) => {
  isLooping.value = val;
});

function toggleLoop() {
  isLooping.value = !isLooping.value;
}

const progress = computed(() => {
  if (!duration.value) return 0;
  return (currentTime.value / duration.value) * 100;
});

// "Locked" means we are playing AND we play mode says wait.
// If allowReplayWhenPlaying is FALSE, and isPlaying is TRUE, we are "locked" from playing AGAIN until done.
// But technically for the UI button, we might just want to show disabled state.
const isLocked = computed(() => {
  return !props.allowReplayWhenPlaying && isPlaying.value && !props.showControls; 
  // Note: If showControls is true, we usually want Play/Pause toggle behavior, which is standard.
  // The 'block' behavior is mostly relevant for "One-shot" triggers.
  // If showControls is true, the user expects Pause.
});

function togglePlay() {
  if (!audioRef.value) return;

  if (isPlaying.value) {
    if (props.showControls) {
      audioRef.value.pause();
    } else {
      // Trigger mode
      if (props.allowReplayWhenPlaying) {
        // Restart
        audioRef.value.currentTime = 0;
        audioRef.value.play();
      } else {
        // Blocked - do nothing
      }
    }
  } else {
    audioRef.value.play();
  }
}

function triggerAction() {
    togglePlay();
}

function onTimeUpdate() {
  if (!audioRef.value) return;
  currentTime.value = audioRef.value.currentTime;
}

function onLoadedMetadata() {
  if (!audioRef.value) return;
  duration.value = audioRef.value.duration;
}

function onEnded() {
  isPlaying.value = false;
  currentTime.value = 0;
  // If loop is on, it might auto-replay, but native loop handles that.
}

// Watchers to sync state
// We need to attach listeners for play/pause events just in case they are triggered externally or by auto-policies
watch(audioRef, (el) => {
  if (!el) return;
  el.onplay = () => isPlaying.value = true;
  el.onpause = () => isPlaying.value = false;
});

function seek(e) {
  if (!audioRef.value || !duration.value) return;
  // Simple click seek
  const rect = e.currentTarget.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const itemWidth = rect.width;
  const percent = Math.min(Math.max(0, x / itemWidth), 1);
  audioRef.value.currentTime = percent * duration.value;
}

function formatTime(seconds) {
  if (!seconds || isNaN(seconds)) return '0:00';
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);
  return `${m}:${s.toString().padStart(2, '0')}`;
}

onUnmounted(() => {
  if (audioRef.value) {
    audioRef.value.pause();
  }
});

defineExpose({
  triggerAction,
  togglePlay,
  toggleLoop
});
</script>

<style scoped>
/* Optional glass effect utility if Tailwind backdrop-blur isn't working for some reason, usually it is */
</style>
