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
          <div class="edit-hint">✏️ Click to edit</div>
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
        <p class="presentation-subtitle">Minimal, working reference implementation according to plan</p>
      </div>

      <!-- Right: Controls -->
      <div class="controls-section">
        <!-- Distribution Button -->
        <button @click="ui.showDistributionModal = true" class="btn-distribution" title="Distribution Settings">
          📤 Share/Export
        </button>

        <!-- Mode Toggle -->
        <div class="mode-toggle">
          <label class="switch">
            <input type="checkbox" v-model="ui.isEditMode" @change="ui.clearSelection">
            <span class="slider round"></span>
          </label>
          <span class="mode-label">{{ ui.isEditMode ? 'Edit Mode (Build)' : 'Present Mode (View)' }}</span>
        </div>

        <!-- Zoom Controls -->
        <div class="zoom-controls">
          <button @click="ui.zoomOut" :disabled="ui.zoomLevel <= 50" class="zoom-btn" title="Zoom Out (Ctrl/Cmd -)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
          
          <button @click="ui.resetZoom" class="zoom-display" title="Reset to 100% (Ctrl/Cmd 0)">
            {{ ui.zoomLevel }}%
          </button>
          
          <button @click="ui.zoomIn" :disabled="ui.zoomLevel >= 200" class="zoom-btn" title="Zoom In (Ctrl/Cmd +)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
        </div>

        <!-- Slide Phase Label -->
        <div class="slide-phase">
          <span class="phase-label">SLIDE PHASE:</span>
          <div class="phase-indicator">
            <div class="phase-bar"></div>
          </div>
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
  z-index: 9999;
  background: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Progress Bar */
.progress-bar-container {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: #e5e7eb;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #10b981 0%, #059669 100%);
  transition: width 0.3s ease;
}

/* Main Content */
.nav-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 24px;
  gap: 24px;
  min-height: 60px;
}

/* Title Section */
.title-section {
  flex: 1;
  min-width: 0;
}

.title-display {
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
}

.title-display:hover .edit-hint {
  opacity: 1;
}

.edit-hint {
  font-size: 0.688rem;
  color: #6b7280;
  opacity: 0;
  transition: opacity 0.2s;
  margin-top: 2px;
}

.title-edit {
  display: flex;
  align-items: center;
  gap: 8px;
}

.title-input {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  border: 2px solid #3b82f6;
  border-radius: 6px;
  padding: 4px 8px;
  background: white;
  outline: none;
  flex: 1;
  min-width: 200px;
}

.title-edit-actions {
  display: flex;
  gap: 4px;
}

.btn-save, .btn-cancel {
  width: 24px;
  height: 24px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
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

.presentation-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.presentation-subtitle {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 2px 0 0 0;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Controls Section */
.controls-section {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
}

/* Distribution Button */
.btn-distribution {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: #8b5cf6;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-distribution:hover {
  background: #7c3aed;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(139, 92, 246, 0.2);
}

/* Mode Toggle */
.mode-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
}

.mode-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  white-space: nowrap;
}

.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  flex-shrink: 0;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #10b981;
  transition: .3s;
}

.switch input:checked + .slider {
  background-color: #6366f1;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .3s;
}

.switch input:checked + .slider:before {
  transform: translateX(20px);
}

.slider.round {
  border-radius: 24px;
}

.slider.round:before {
  border-radius: 50%;
}

/* Zoom Controls */
.zoom-controls {
  display: flex;
  align-items: center;
  gap: 4px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 3px;
}

.zoom-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: transparent;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  color: #374151;
  transition: all 0.2s;
}

.zoom-btn:hover:not(:disabled) {
  background: #e5e7eb;
  color: #111827;
}

.zoom-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.zoom-display {
  min-width: 50px;
  height: 28px;
  background: transparent;
  border: none;
  font-weight: 600;
  font-size: 0.813rem;
  color: #111827;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
  text-align: center;
}

.zoom-display:hover {
  background: #e5e7eb;
}

/* Slide Phase */
.slide-phase {
  display: flex;
  align-items: center;
  gap: 8px;
}

.phase-label {
  font-size: 0.688rem;
  font-weight: 600;
  color: #6b7280;
  letter-spacing: 0.05em;
}

.phase-indicator {
  width: 120px;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.phase-bar {
  height: 100%;
  width: 0%;
  background: linear-gradient(90deg, #10b981 0%, #059669 100%);
  transition: width 0.3s ease;
}

/* Responsive */
@media (max-width: 1024px) {
  .nav-content {
    padding: 8px 16px;
    gap: 16px;
  }

  .presentation-title {
    font-size: 1.125rem;
  }

  .presentation-subtitle {
    font-size: 0.688rem;
  }

  .controls-section {
    gap: 12px;
  }

  .mode-label {
    display: none;
  }

  .slide-phase {
    display: none;
  }
}

@media (max-width: 640px) {
  .nav-content {
    padding: 6px 12px;
    gap: 12px;
    min-height: 50px;
  }

  .presentation-title {
    font-size: 1rem;
  }

  .presentation-subtitle {
    display: none;
  }

  .zoom-controls {
    gap: 2px;
    padding: 2px;
  }

  .zoom-btn {
    width: 24px;
    height: 24px;
  }

  .zoom-display {
    min-width: 45px;
    height: 24px;
    font-size: 0.75rem;
  }
}
</style>
