<script setup>
import { usePresentationStore } from '../../../stores/presentationStore';
import { useGameStore } from '../../../stores/gameStore';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import axios from 'axios';

const presentation = usePresentationStore();
const gameStore = useGameStore();

const nextSlide = async () => {
  if (presentation.currentSlideIndex < presentation.slides.length - 1) {
    const newIndex = presentation.currentSlideIndex + 1;
    presentation.selectSlide(newIndex);
    await syncSlide(newIndex);
  }
};

const prevSlide = async () => {
  if (presentation.currentSlideIndex > 0) {
    const newIndex = presentation.currentSlideIndex - 1;
    presentation.selectSlide(newIndex);
    await syncSlide(newIndex);
  }
};

const syncSlide = async (index) => {
  if (!gameStore.accessCode) return;
  
  try {
    // Send signal via Laravel API which triggers RealtimeEvent
    await axios.post(`/api/cr/sessions/${gameStore.sessionId}/sync-slide`, {
      slideIndex: index
    });
    console.log('Slide synced to:', index);
  } catch (err) {
    console.error('Failed to sync slide:', err);
  }
};
</script>

<template>
  <div class="slide-remote shadow-lg">
    <div class="nav-buttons">
      <button 
        class="nav-btn prev" 
        :disabled="presentation.currentSlideIndex === 0"
        @click="prevSlide"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      </button>

      <div class="progress-info">
        <span class="current">{{ presentation.currentSlideIndex + 1 }}</span>
        <span class="separator">/</span>
        <span class="total">{{ presentation.slides.length }}</span>
      </div>

      <button 
        class="nav-btn next" 
        :disabled="presentation.currentSlideIndex === presentation.slides.length - 1"
        @click="nextSlide"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-9-6"/></svg>
      </button>
    </div>
  </div>
</template>

<style scoped>
.slide-remote {
  position: absolute;
  bottom: 1.5rem;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(15, 23, 42, 0.9);
  backdrop-filter: blur(8px);
  padding: 0.5rem 1.5rem;
  border-radius: 100px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  z-index: 50;
}

.nav-buttons {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-btn {
  background: none;
  border: none;
  color: white;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border-radius: 50%;
  transition: all 0.2s;
}

.nav-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
  transform: scale(1.1);
}

.nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.progress-info {
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 700;
  font-size: 1.1rem;
}

.separator {
  opacity: 0.5;
  font-size: 0.9rem;
}

.total {
  opacity: 0.7;
  font-size: 0.9rem;
}
</style>
