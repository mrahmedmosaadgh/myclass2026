<script setup>
import { computed, ref } from 'vue';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';

const presentation = usePresentationStore();
const ui = useUIStore();

const currentSlideNumber = computed(() => presentation.currentSlideIndex + 1);
const totalSlides = computed(() => presentation.slides.length);
const progress = computed(() => (currentSlideNumber.value / totalSlides.value) * 100);

const canGoPrevious = computed(() => presentation.currentSlideIndex > 0);
const canGoNext = computed(() => presentation.currentSlideIndex < totalSlides.value - 1);

// Title editing state
const isEditingTitle = ref(false);
const titleInput = ref('');

function startEditingTitle() {
  titleInput.value = presentation.title;
  isEditingTitle.value = true;
}

function saveTitle() {
  if (titleInput.value.trim()) {
    presentation.title = titleInput.value.trim();
  }
  isEditingTitle.value = false;
}

function cancelEditTitle() {
  titleInput.value = presentation.title;
  isEditingTitle.value = false;
}

function clearTitle() {
  presentation.clearTitle();
}

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

function toggleMode() {
  ui.isEditMode = !ui.isEditMode;
  if (ui.isEditMode) {
    ui.clearSelection();
  }
}
</script>

<template>
  <div class="presentation-nav-bar">
    <!-- Progress Bar -->
    <div class="progress-bar-container">
      <div class="progress-bar" :style="{ width: progress + '%' }"></div>
    </div>

    <!-- Main Content -->
    <div class="nav-content">
      <!-- Left: Title & Details -->
      <div class="title-section">
        <div v-if="!isEditingTitle" class="title-display" @click="startEditingTitle">
          <h1 class="presentation-title">{{ presentation.title }}</h1>
          <div class="title-actions">
            <div class="edit-hint">✏️ Click to edit</div>
            <button v-if="presentation.title" @click.stop="clearTitle" class="clear-title-btn" title="Clear title">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18"></path>
                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
              </svg>
            </button>
          </div>
        </div>
        <div v-else class="title-edit">
          <input 
            v-model="titleInput" 
            @keyup.enter="saveTitle"
            @keyup.escape="cancelEditTitle"
            @blur="saveTitle"
            class="title-input"
            ref="titleInputRef"
            placeholder="Enter presentation title"
          />
          <div class="title-edit-actions">
            <button @click="saveTitle" class="btn-save">✓</button>
            <button @click="cancelEditTitle" class="btn-cancel">✕</button>
          </div>
        </div>
        
        <!-- Description in Present Mode -->
        <div v-if="!ui.isEditMode && presentation.description && presentation.showDescriptionInPresentMode" class="description-display-present">
          <div class="description-content" v-html="presentation.description"></div>
        </div>
        
        <p class="presentation-subtitle">Minimal, working reference implementation according to plan</p>
      </div>

      <!-- Right: Controls -->
      <div class="controls-section">
        <!-- Navigation Controls (Present Mode) -->
        <div v-if="!ui.isEditMode" class="navigation-controls">
          <button 
            @click="goToFirst" 
            :disabled="!canGoPrevious"
            class="nav-btn"
            title="Go to First Slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="11 17 6 12 11 7"></polyline>
              <polyline points="17 17 12 12 17 7"></polyline>
            </svg>
          </button>
          
          <button 
            @click="goToPrevious" 
            :disabled="!canGoPrevious"
            class="nav-btn"
            title="Previous Slide (←)"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>
          
          <div class="slide-counter">
            <span class="current-slide">{{ currentSlideNumber }}</span>
            <span class="slide-separator">/</span>
            <span class="total-slides">{{ totalSlides }}</span>
          </div>
          
          <button 
            @click="goToNext" 
            :disabled="!canGoNext"
            class="nav-btn"
            title="Next Slide (→)"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
          
          <button 
            @click="goToLast" 
            :disabled="!canGoNext"
            class="nav-btn"
            title="Go to Last Slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="13 17 18 12 13 7"></polyline>
              <polyline points="7 17 12 12 7 7"></polyline>
            </svg>
          </button>
        </div>

        <!-- Edit Mode Controls -->
        <div v-else class="edit-controls">
          <div class="mode-indicator">
            <span class="mode-dot edit-mode"></span>
            <span class="mode-text">Edit Mode</span>
          </div>
        </div>

        <!-- Common Controls -->
        <div class="common-controls">
          <button 
            @click="toggleMode" 
            class="control-btn mode-toggle"
            :title="ui.isEditMode ? 'Switch to Present Mode' : 'Switch to Edit Mode'"
          >
            <svg v-if="ui.isEditMode" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </button>
          
          <button 
            @click="toggleFullscreen" 
            class="control-btn"
            title="Toggle Fullscreen (F)"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.presentation-nav-bar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(229, 231, 235, 0.5);
  padding: 16px 24px;
  z-index: 1000;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.progress-bar-container {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: rgba(229, 231, 235, 0.5);
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  transition: width 0.3s ease;
  border-radius: 0 0 2px 2px;
}

.nav-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 100%;
  margin: 0 auto;
}

.title-section {
  flex: 1;
  min-width: 0;
}

.title-display {
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background-color 0.2s;
  position: relative;
}

.title-display:hover {
  background: rgba(59, 130, 246, 0.05);
}

.title-display:hover .edit-hint {
  opacity: 1;
}

.presentation-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.edit-hint {
  position: absolute;
  top: 50%;
  right: 8px;
  transform: translateY(-50%);
  font-size: 0.75rem;
  color: #6b7280;
  opacity: 0;
  transition: opacity 0.2s;
  background: white;
  padding: 2px 6px;
  border-radius: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.title-edit {
  display: flex;
  align-items: center;
  gap: 8px;
}

.title-input {
  flex: 1;
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  border: 2px solid #3b82f6;
  border-radius: 6px;
  padding: 4px 8px;
  background: white;
  outline: none;
}

.title-input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.title-edit-actions {
  display: flex;
  gap: 4px;
}

.btn-save, .btn-cancel {
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-save {
  background: #10b981;
  color: white;
}

.btn-save:hover {
  background: #059669;
}

.btn-cancel {
  background: #ef4444;
  color: white;
}

.btn-cancel:hover {
  background: #dc2626;
}

.presentation-subtitle {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 2px 0 8px 0;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.controls-section {
  display: flex;
  align-items: center;
  gap: 16px;
}

.navigation-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
}

.nav-btn:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
  color: #374151;
}

.nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.slide-counter {
  display: flex;
  align-items: center;
  padding: 6px 12px;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-weight: 600;
  min-width: 60px;
  justify-content: center;
}

.current-slide {
  color: #3b82f6;
  font-size: 0.875rem;
}

.slide-separator {
  color: #9ca3af;
  margin: 0 4px;
  font-size: 0.75rem;
}

.total-slides {
  color: #6b7280;
  font-size: 0.875rem;
}

.edit-controls {
  display: flex;
  align-items: center;
  gap: 12px;
}

.mode-indicator {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: #f0f9ff;
  border: 1px solid #bfdbfe;
  border-radius: 20px;
}

.mode-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #3b82f6;
  animation: pulse 2s infinite;
}

.mode-text {
  font-size: 0.75rem;
  font-weight: 600;
  color: #1e40af;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.common-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.control-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
}

.control-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
  color: #374151;
}

.mode-toggle {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-color: transparent;
  color: white;
}

.mode-toggle:hover {
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Description Display in Present Mode */
.description-display-present {
  margin-top: 8px;
  max-width: 800px;
}

.description-content {
  font-size: 0.9rem;
  line-height: 1.5;
  color: #374151;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(229, 231, 235, 0.8);
  border-radius: 8px;
  padding: 12px 16px;
  max-height: 120px;
  overflow-y: auto;
}

/* Improved title display for better visibility */
.presentation-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
  line-height: 1.2;
  max-width: 800px;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.title-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
}

.edit-hint {
  font-size: 0.75rem;
  color: #9ca3af;
  opacity: 0;
  transition: opacity 0.2s;
}

.title-display:hover .edit-hint {
  opacity: 1;
}

.clear-title-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border: none;
  border-radius: 4px;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
  transition: all 0.2s;
  opacity: 0;
}

.title-display:hover .clear-title-btn {
  opacity: 1;
}

.clear-title-btn:hover {
  background: #fef2f2;
  color: #dc2626;
}

/* Responsive */
@media (max-width: 768px) {
  .presentation-nav-bar {
    padding: 12px 16px;
  }
  
  .nav-content {
    flex-direction: column;
    gap: 12px;
  }
  
  .title-section {
    width: 100%;
  }
  
  .controls-section {
    width: 100%;
    justify-content: center;
  }
  
  .nav-btn {
    width: 32px;
    height: 32px;
  }
  
  .slide-counter {
    font-size: 0.875rem;
  }
}
</style>
