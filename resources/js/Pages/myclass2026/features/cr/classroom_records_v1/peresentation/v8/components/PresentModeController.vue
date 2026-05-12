<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import PresentModeAnnotationOverlay from './PresentModeAnnotationOverlay.vue'

const props = defineProps({
  currentIndex: { type: Number, required: true },
  totalSlides: { type: Number, required: true },
  slideTitle: { type: String, default: '' },
  slideDescription: { type: String, default: '' },
  annotationsVisible: { type: Boolean, default: true }
})

const emit = defineEmits(['prev', 'next', 'goToSlide', 'exit', 'toggleAnnotations'])

const showHints = ref(true)
const hintTimer = ref(null)

const progress = computed(() => {
  if (props.totalSlides <= 1) return 100
  return ((props.currentIndex + 1) / props.totalSlides) * 100
})

const canGoPrev = computed(() => props.currentIndex > 0)
const canGoNext = computed(() => props.currentIndex < props.totalSlides - 1)

function goPrev() {
  if (canGoPrev.value) emit('prev')
}

function goNext() {
  if (canGoNext.value) emit('next')
}

function handleKeydown(e) {
  // Don't intercept if typing in an input
  if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return

  const key = e.key

  switch (key) {
    case 'ArrowRight':
    case 'ArrowDown':
    case 'PageDown':
    case ' ':
      e.preventDefault()
      if (e.shiftKey && key === ' ') {
        goPrev()
      } else {
        goNext()
      }
      break

    case 'ArrowLeft':
    case 'ArrowUp':
    case 'PageUp':
      e.preventDefault()
      goPrev()
      break

    case 'Home':
      e.preventDefault()
      emit('goToSlide', 0)
      break

    case 'End':
      e.preventDefault()
      emit('goToSlide', props.totalSlides - 1)
      break

    case 'Escape':
      e.preventDefault()
      emit('exit')
      break

    case 'n':
      if (!e.ctrlKey && !e.metaKey) {
        e.preventDefault()
        emit('toggleAnnotations')
      }
      break

    case 'f':
      if (!e.ctrlKey && !e.metaKey) {
        e.preventDefault()
        toggleFullscreen()
      }
      break
  }
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {})
  } else {
    document.exitFullscreen().catch(() => {})
  }
}

function handleZoneClick(direction) {
  if (direction === 'prev') goPrev()
  else goNext()
}

function dismissHints() {
  showHints.value = false
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  hintTimer.value = setTimeout(() => { showHints.value = false }, 6000)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  if (hintTimer.value) clearTimeout(hintTimer.value)
})
</script>

<template>
  <!-- Left click zone (fixed edge, doesn't block center) -->
  <div
    v-if="canGoPrev"
    class="pm-click-zone pm-zone-left"
    @click="handleZoneClick('prev')"
  >
    <svg class="pm-zone-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <polyline points="15 18 9 12 15 6" />
    </svg>
  </div>

  <!-- Right click zone (fixed edge, doesn't block center) -->
  <div
    v-if="canGoNext"
    class="pm-click-zone pm-zone-right"
    @click="handleZoneClick('next')"
  >
    <svg class="pm-zone-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <polyline points="9 18 15 12 9 6" />
    </svg>
  </div>

  <!-- Top bar: slide counter + exit -->
  <div class="pm-top-bar">
    <div class="pm-counter">
      <span class="pm-counter-current">{{ currentIndex + 1 }}</span>
      <span class="pm-counter-sep">/</span>
      <span class="pm-counter-total">{{ totalSlides }}</span>
    </div>
    <button class="pm-exit-btn" @click="$emit('exit')" title="Exit Present Mode (Esc)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18" />
        <line x1="6" y1="6" x2="18" y2="18" />
      </svg>
    </button>
  </div>

  <!-- Bottom bar: progress + controls -->
  <div class="pm-bottom-bar">
    <div class="pm-progress-track">
      <div class="pm-progress-fill" :style="{ width: progress + '%' }" />
    </div>

    <div class="pm-controls">
      <button class="pm-btn" :disabled="!canGoPrev" @click="goPrev" title="Previous (←)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="15 18 9 12 15 6" />
        </svg>
      </button>

      <span class="pm-slide-label">Slide {{ currentIndex + 1 }} of {{ totalSlides }}</span>

      <button class="pm-btn" :disabled="!canGoNext" @click="goNext" title="Next (→ or Space)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9 18 15 12 9 6" />
        </svg>
      </button>
    </div>
  </div>

  <!-- Annotation Overlay -->
  <PresentModeAnnotationOverlay
    :title="slideTitle"
    :description="slideDescription"
    :visible="annotationsVisible"
    @toggle="emit('toggleAnnotations')"
  />

  <!-- Keyboard hints (auto-dismiss) -->
  <Transition name="pm-fade">
    <div v-if="showHints" class="pm-hints" @click="dismissHints">
      <div class="pm-hint">
        <kbd>←</kbd><kbd>→</kbd> Navigate
      </div>
      <div class="pm-hint">
        <kbd>Space</kbd> Next
      </div>
      <div class="pm-hint">
        <kbd>Esc</kbd> Exit
      </div>
      <div class="pm-hint">
        <kbd>N</kbd> Annotations
      </div>
      <div class="pm-hint">
        <kbd>Click sides</kbd> Navigate
      </div>
      <div class="pm-hint-dismiss">Click to dismiss</div>
    </div>
  </Transition>
</template>

<style scoped>
/* Click zones — fixed edge strips, center is free for slide interaction */
.pm-click-zone {
  position: fixed;
  top: 60px;       /* below top bar */
  bottom: 70px;    /* above bottom bar */
  width: 12%;
  z-index: 40;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.3s;
  pointer-events: auto;
}

.pm-click-zone:hover {
  opacity: 1;
}

.pm-zone-left {
  left: 0;
  background: linear-gradient(to right, rgba(0,0,0,0.12), transparent);
}

.pm-zone-right {
  right: 0;
  background: linear-gradient(to left, rgba(0,0,0,0.12), transparent);
}

.pm-zone-arrow {
  width: 40px;
  height: 40px;
  color: rgba(255,255,255,0.7);
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

/* Top bar */
.pm-top-bar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 45;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  background: linear-gradient(to bottom, rgba(0,0,0,0.4), transparent);
  pointer-events: none;
}

.pm-counter {
  display: flex;
  align-items: baseline;
  gap: 4px;
  color: rgba(255,255,255,0.85);
  font-size: 14px;
  font-weight: 500;
  pointer-events: auto;
}

.pm-counter-current {
  font-size: 18px;
  font-weight: 700;
  color: white;
}

.pm-counter-sep {
  opacity: 0.5;
}

.pm-counter-total {
  opacity: 0.7;
}

.pm-exit-btn {
  pointer-events: auto;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.15);
  background: rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.7);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.pm-exit-btn:hover {
  background: rgba(255,255,255,0.15);
  color: white;
  border-color: rgba(255,255,255,0.3);
}

/* Bottom bar */
.pm-bottom-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 45;
  background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);
  padding: 16px 20px 12px;
  pointer-events: none;
}

.pm-progress-track {
  width: 100%;
  height: 3px;
  background: rgba(255,255,255,0.15);
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 12px;
  pointer-events: auto;
}

.pm-progress-fill {
  height: 100%;
  background: #63b3ed;
  border-radius: 2px;
  transition: width 0.3s ease;
}

.pm-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  pointer-events: auto;
}

.pm-btn {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.15);
  background: rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.7);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.pm-btn:hover:not(:disabled) {
  background: rgba(255,255,255,0.15);
  color: white;
  border-color: rgba(255,255,255,0.3);
}

.pm-btn:disabled {
  opacity: 0.2;
  cursor: not-allowed;
}

.pm-slide-label {
  color: rgba(255,255,255,0.6);
  font-size: 13px;
  font-weight: 500;
  min-width: 100px;
  text-align: center;
}

/* Keyboard hints */
.pm-hints {
  position: fixed;
  bottom: 90px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 50;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
  align-items: center;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(8px);
  padding: 12px 20px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.1);
  cursor: pointer;
  pointer-events: auto;
}

.pm-hint {
  color: rgba(255,255,255,0.8);
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.pm-hint kbd {
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 4px;
  padding: 2px 6px;
  font-family: ui-monospace, monospace;
  font-size: 11px;
  color: white;
}

.pm-hint-dismiss {
  width: 100%;
  text-align: center;
  color: rgba(255,255,255,0.4);
  font-size: 11px;
  margin-top: 4px;
}

/* Transitions */
.pm-fade-enter-active,
.pm-fade-leave-active {
  transition: opacity 0.5s, transform 0.5s;
}

.pm-fade-enter-from,
.pm-fade-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(10px);
}
</style>
