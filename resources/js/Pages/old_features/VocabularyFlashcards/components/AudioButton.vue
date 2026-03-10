<template>
  <button 
    @click.stop="handleClick"
    class="audio-btn modern-btn" 
    :class="{ 'playing': isPlaying }"
    :disabled="isPlaying" 
    :aria-label="`Listen to pronunciation of ${text}`"
  >
    <div class="btn-icon">
      <svg v-if="!isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.816L4.846 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.846l3.537-3.816a1 1 0 011.617.816zM16 8a2 2 0 11-4 0 2 2 0 014 0zM14 8a2 2 0 012-2v4a2 2 0 01-2-2z" clip-rule="evenodd" />
      </svg>
      <div v-else class="loading-spinner"></div>
    </div>
    <span class="btn-text">{{ buttonText }}</span>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  isPlaying: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['play-audio'])

const buttonText = computed(() => {
  return props.isPlaying ? 'Playing...' : 'Listen'
})

const handleClick = () => {
  emit('play-audio', { text: props.text })
}
</script>

<style scoped>
.audio-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 2rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  font-weight: 600;
  min-width: 120px;
}

.audio-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.audio-btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
}

.audio-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.audio-btn.playing {
  background: rgba(34, 197, 94, 0.3);
  border-color: rgba(34, 197, 94, 0.5);
  animation: pulse 1.5s ease-in-out infinite;
}

.btn-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-text {
  font-size: 0.875rem;
}

.loading-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 640px) {
  .audio-btn {
    padding: 0.5rem 1rem;
    min-width: 100px;
  }
  
  .btn-text {
    font-size: 0.8125rem;
  }
}
</style>