<script setup>
import { computed, ref, nextTick } from 'vue';
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

// Description editing state
const isEditingDescription = ref(false);
const descriptionContent = ref('');
const descriptionEditor = ref(null);

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

function startEditingDescription() {
  descriptionContent.value = presentation.description || '';
  isEditingDescription.value = true;
  nextTick(() => {
    if (descriptionEditor.value) {
      descriptionEditor.value.focus();
    }
  });
}

function updateDescription(event) {
  descriptionContent.value = typeof event.target.innerHTML === 'string' ? event.target.innerHTML : String(event.target.innerHTML || '');
}

function saveDescription() {
  presentation.description = typeof descriptionContent.value === 'string' ? descriptionContent.value : String(descriptionContent.value || '');
  isEditingDescription.value = false;
}

function cancelEditDescription() {
  descriptionContent.value = presentation.description || '';
  isEditingDescription.value = false;
}

// Math rendering function
function renderMath() {
  if (window.katex) {
    const mathElements = descriptionEditor.value?.querySelectorAll('.math-render');
    mathElements?.forEach(element => {
      try {
        const mathText = element.textContent;
        window.katex.render(mathText, element, {
          throwOnError: false,
          displayMode: element.classList.contains('display-mode')
        });
      } catch (error) {
        console.warn('Math rendering error:', error);
      }
    });
  }
}

// Markdown rendering function
function renderMarkdown() {
  if (window.marked) {
    let content = descriptionContent.value;
    // Convert markdown to HTML
    content = window.marked(content);
    // Update editor content
    if (descriptionEditor.value) {
      descriptionEditor.value.innerHTML = content;
      renderMath();
    }
  }
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
        
        <!-- Rich Content Description Area -->
        <div class="description-section">
          <div v-if="!isEditingDescription && !presentation.description" 
               class="description-placeholder" 
               @click="startEditingDescription">
            <div class="placeholder-content">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              <span>Add description, notes, math formulas, or rich content...</span>
            </div>
          </div>
          
          <div v-else-if="!isEditingDescription && presentation.description" 
               class="description-display" 
               @click="startEditingDescription"
               v-html="presentation.description">
          </div>
          
          <div v-else class="description-edit">
            <div class="description-editor-wrapper">
              <div 
                ref="descriptionEditor"
                contenteditable="true"
                @input="updateDescription"
                @blur="saveDescription"
                @keyup.escape="cancelEditDescription"
                class="description-editor"
                placeholder="Add description, notes, math formulas (use $...$ for inline math, $$...$$ for display math), markdown, or HTML content..."
              ></div>
              <div class="description-toolbar">
                <button @click="renderMarkdown" class="toolbar-btn" title="Render Markdown">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                  MD
                </button>
                <button @click="renderMath" class="toolbar-btn" title="Render Math">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 5h-7l-3 14H4"></path>
                    <path d="M14 10h5"></path>
                    <path d="M14 14h5"></path>
                  </svg>
                  ∑
                </button>
                <button @click="saveDescription" class="toolbar-btn btn-save" title="Save">
                  ✓
                </button>
                <button @click="cancelEditDescription" class="toolbar-btn btn-cancel" title="Cancel">
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>
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
  margin: 2px 0 8px 0;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Description Section */
.description-section {
  margin-top: 8px;
  position: relative;
}

.description-placeholder {
  cursor: pointer;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  padding: 16px;
  background: #f9fafb;
  transition: all 0.2s;
  min-height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.description-placeholder:hover {
  border-color: #6366f1;
  background: #f0f9ff;
}

.placeholder-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: #9ca3af;
  font-size: 0.875rem;
  text-align: center;
}

.description-display {
  cursor: pointer;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px 16px;
  background: white;
  min-height: 60px;
  max-height: 200px;
  overflow-y: auto;
  transition: all 0.2s;
  line-height: 1.5;
}

.description-display:hover {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.description-edit {
  border: 2px solid #3b82f6;
  border-radius: 8px;
  background: white;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.description-editor-wrapper {
  position: relative;
}

.description-editor {
  min-height: 120px;
  max-height: 300px;
  padding: 16px;
  font-size: 0.875rem;
  line-height: 1.6;
  color: #374151;
  overflow-y: auto;
  outline: none;
  border: none;
  background: transparent;
}

.description-editor:empty:before {
  content: attr(placeholder);
  color: #9ca3af;
  font-style: italic;
}

.description-editor:focus {
  outline: none;
}

.description-toolbar {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  gap: 4px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toolbar-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 4px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.2s;
}

.toolbar-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.toolbar-btn.btn-save {
  background: #10b981;
  color: white;
}

.toolbar-btn.btn-save:hover {
  background: #059669;
}

.toolbar-btn.btn-cancel {
  background: #ef4444;
  color: white;
}

.toolbar-btn.btn-cancel:hover {
  background: #dc2626;
}

/* Math rendering styles */
.math-render {
  font-family: 'KaTeX_Main', 'Times New Roman', serif;
}

.math-render.display-mode {
  display: block;
  text-align: center;
  margin: 16px 0;
}

/* Markdown rendering styles */
.description-display h1, .description-display h2, .description-display h3,
.description-display h4, .description-display h5, .description-display h6 {
  margin: 16px 0 8px 0;
  font-weight: 600;
  color: #111827;
}

.description-display h1 { font-size: 1.5rem; }
.description-display h2 { font-size: 1.25rem; }
.description-display h3 { font-size: 1.125rem; }

.description-display p {
  margin: 8px 0;
}

.description-display ul, .description-display ol {
  margin: 8px 0;
  padding-left: 24px;
}

.description-display li {
  margin: 4px 0;
}

.description-display code {
  background: #f3f4f6;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Monaco', 'Menlo', monospace;
  font-size: 0.875rem;
  color: #ef4444;
}

.description-display pre {
  background: #1f2937;
  color: #f9fafb;
  padding: 12px;
  border-radius: 6px;
  overflow-x: auto;
  margin: 8px 0;
}

.description-display blockquote {
  border-left: 4px solid #6366f1;
  padding-left: 16px;
  margin: 8px 0;
  font-style: italic;
  color: #6b7280;
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
