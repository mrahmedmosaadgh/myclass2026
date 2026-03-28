<script setup>
import { computed } from 'vue';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';

const presentation = usePresentationStore();
const ui = useUIStore();

const currentSlideNumber = computed(() => presentation.currentSlideIndex + 1);
const totalSlides = computed(() => presentation.slides.length);
const progress = computed(() => (currentSlideNumber.value / totalSlides.value) * 100);

const canGoPrevious = computed(() => presentation.currentSlideIndex > 0);
const canGoNext = computed(() => presentation.currentSlideIndex < totalSlides.value - 1);

function goToPrevious() {
  if (canGoPrevious.value) {
    presentation.selectSlide(presentation.currentSlideIndex - 1);
  }
}

function goToNext() {
  if (canGoNext.value) {
    presentation.selectSlide(presentation.currentSlideIndex + 1);
  }
}

function goToFirst() {
  presentation.selectSlide(0);
}

function goToLast() {
  presentation.selectSlide(totalSlides.value - 1);
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
}
</script>

<template>
  <div class="presentation-nav-bar">
    <!-- Progress Bar -->
    <div class="progress-bar-container">
      <div class="progress-bar" :style="{ width: progress + '%' }"></div>
    </div>

    <!-- Navigation Controls -->
    <div class="nav-controls">
      <!-- Left Section: Navigation Buttons -->
      <div class="nav-section left">
        <button 
          @click="goToFirst" 
          :disabled="!canGoPrevious"
          class="nav-btn"
          title="First Slide"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="11 17 6 12 11 7"></polyline>
            <polyline points="18 17 13 12 18 7"></polyline>
          </svg>
        </button>

        <button 
          @click="goToPrevious" 
          :disabled="!canGoPrevious"
          class="nav-btn primary"
          title="Previous Slide"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>

        <button 
          @click="goToNext" 
          :disabled="!canGoNext"
          class="nav-btn primary"
          title="Next Slide"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>

        <button 
          @click="goToLast" 
          :disabled="!canGoNext"
          class="nav-btn"
          title="Last Slide"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="13 17 18 12 13 7"></polyline>
            <polyline points="6 17 11 12 6 7"></polyline>
          </svg>
        </button>
      </div>

      <!-- Center Section: Slide Counter -->
      <div class="nav-section center">
        <div class="slide-counter">
          <span class="current-slide">{{ currentSlideNumber }}</span>
          <span class="separator">/</span>
          <span class="total-slides">{{ totalSlides }}</span>
        </div>
        <div class="slide-title" v-if="presentation.currentSlide?.title">
          {{ presentation.currentSlide.title }}
        </div>
      </div>

      <!-- Right Section: Utility Buttons -->
      <div class="nav-section right">
        <button 
          @click="toggleFullscreen" 
          class="nav-btn"
          title="Toggle Fullscreen"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
          </svg>
        </button>

        <button 
          @click="ui.isEditMode = true" 
          class="nav-btn edit-btn"
          title="Exit Presentation Mode"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
          </svg>
          <span>Edit</span>
        </button>
      </div>
    </div>

    <!-- Keyboard Shortcuts Hint -->
    <div class="keyboard-hint">
      <span>← →</span> Navigate
      <span class="separator">•</span>
      <span>F</span> Fullscreen
      <span class="separator">•</span>
      <span>Esc</span> Exit
    </div>
  </div>
</template>

<style scoped>
.presentation-nav-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 9999;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.85) 100%);
  backdrop-filter: blur(10px);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.presentation-nav-bar:hover {
  background: linear-gradient(to top, rgba(0, 0, 0, 0.98) 0%, rgba(0, 0, 0, 0.92) 100%);
}

/* Progress Bar */
.progress-bar-container {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: rgba(255, 255, 255, 0.1);
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
  transition: width 0.3s ease;
  box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
}

/* Navigation Controls */
.nav-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 24px;
  gap: 20px;
}

.nav-section {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-section.left {
  flex: 1;
  justify-content: flex-start;
}

.nav-section.center {
  flex: 2;
  justify-content: center;
  flex-direction: column;
  gap: 4px;
}

.nav-section.right {
  flex: 1;
  justify-content: flex-end;
}

/* Navigation Buttons */
.nav-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px 14px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  font-weight: 500;
  min-width: 44px;
}

.nav-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.nav-btn:active:not(:disabled) {
  transform: translateY(0);
}

.nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.nav-btn.primary {
  background: rgba(99, 102, 241, 0.3);
  border-color: rgba(99, 102, 241, 0.5);
}

.nav-btn.primary:hover:not(:disabled) {
  background: rgba(99, 102, 241, 0.5);
  border-color: rgba(99, 102, 241, 0.7);
}

.nav-btn.edit-btn {
  background: rgba(16, 185, 129, 0.3);
  border-color: rgba(16, 185, 129, 0.5);
}

.nav-btn.edit-btn:hover {
  background: rgba(16, 185, 129, 0.5);
  border-color: rgba(16, 185, 129, 0.7);
}

/* Slide Counter */
.slide-counter {
  display: flex;
  align-items: baseline;
  gap: 6px;
  font-weight: 600;
  color: white;
  font-size: 20px;
  letter-spacing: 0.5px;
}

.current-slide {
  font-size: 28px;
  color: #6366f1;
  text-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
}

.separator {
  color: rgba(255, 255, 255, 0.4);
  font-size: 18px;
}

.total-slides {
  color: rgba(255, 255, 255, 0.6);
  font-size: 18px;
}

.slide-title {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.7);
  text-align: center;
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Keyboard Hints */
.keyboard-hint {
  position: absolute;
  top: -32px;
  right: 24px;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  background: rgba(0, 0, 0, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.6);
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.presentation-nav-bar:hover .keyboard-hint {
  opacity: 1;
}

.keyboard-hint span:not(.separator) {
  padding: 2px 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  font-family: monospace;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.keyboard-hint .separator {
  color: rgba(255, 255, 255, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .nav-controls {
    padding: 10px 16px;
    gap: 12px;
  }

  .nav-btn {
    padding: 8px 10px;
    min-width: 40px;
  }

  .nav-btn span {
    display: none;
  }

  .slide-counter {
    font-size: 16px;
  }

  .current-slide {
    font-size: 22px;
  }

  .total-slides {
    font-size: 14px;
  }

  .slide-title {
    font-size: 11px;
    max-width: 150px;
  }

  .keyboard-hint {
    display: none;
  }
}

/* Auto-hide on inactivity */
@keyframes fadeOut {
  from { opacity: 1; transform: translateY(0); }
  to { opacity: 0; transform: translateY(100%); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(100%); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
