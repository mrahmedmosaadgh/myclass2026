<script setup>
import { onMounted, onUnmounted } from 'vue';
import { usePresentationStore } from './stores/presentationStore';
import { useUIStore } from './stores/uiStore';
import { useGameStore } from './stores/gameStore';
import { usePaste } from './composables/usePaste';
import EditorCanvas from './components/EditorCanvas.vue';
import Toolbar from './components/Toolbar.vue';
import SlideNavigationBar from './components/SlideNavigationBar.vue';
import AIPasteDialog from './components/AIPasteDialog.vue';
import GroupSetupModal from './components/GroupSetupModal.vue';
import GroupQuizGenerator from './components/GroupQuizGenerator.vue';
import LeaderboardOverlay from './components/LeaderboardOverlay.vue';
import FloatingAnalytics from './components/FloatingAnalytics.vue';
import LiveQuestionPanel from './components/LiveQuestionPanel.vue';

const presentation = usePresentationStore();
const ui = useUIStore();
const gameStore = useGameStore();
const { handlePaste } = usePaste();

function handleKeydown(e) {
  if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;

  // Zoom shortcuts
  if ((e.ctrlKey || e.metaKey) && e.key === '=') {
    e.preventDefault();
    ui.zoomIn();
    return;
  }
  if ((e.ctrlKey || e.metaKey) && e.key === '-') {
    e.preventDefault();
    ui.zoomOut();
    return;
  }
  if ((e.ctrlKey || e.metaKey) && e.key === '0') {
    e.preventDefault();
    ui.resetZoom();
    return;
  }

  // Slide navigation
  if (e.key === 'ArrowRight' || e.key === 'ArrowDown' || e.key === 'PageDown') {
    presentation.selectSlide(presentation.currentSlideIndex + 1);
  } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp' || e.key === 'PageUp') {
    presentation.selectSlide(presentation.currentSlideIndex - 1);
  }
}

onMounted(() => {
  document.addEventListener('paste', handlePaste);
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('paste', handlePaste);
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <div class="v5-container">
    <div class="header">
      <div v-if="ui.isEditMode" class="title-editor">
        <input 
          v-model="presentation.title" 
          type="text" 
          class="title-input" 
          placeholder="Presentation Title..."
          title="Click to edit title"
        >
      </div>
      <h1 v-else>{{ presentation.title }}</h1>
      <p>Minimal, working reference implementation according to plan</p>
      
      <div class="header-controls">
        <div class="mode-toggle">
          <label class="switch">
            <input type="checkbox" v-model="ui.isEditMode" @change="ui.clearSelection">
            <span class="slider round"></span>
          </label>
          <span class="mode-label">{{ ui.isEditMode ? 'Edit Mode (Build)' : 'Present Mode (View)' }}</span>
        </div>

        <!-- Zoom Controls -->
        <div class="zoom-controls">
          <button @click="ui.zoomOut" :disabled="ui.zoomLevel <= 50" class="zoom-btn" title="Zoom Out">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
          
          <button @click="ui.resetZoom" class="zoom-display" title="Reset to 100%">
            {{ ui.zoomLevel }}%
          </button>
          
          <button @click="ui.zoomIn" :disabled="ui.zoomLevel >= 200" class="zoom-btn" title="Zoom In">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <div v-if="ui.isEditMode" style="max-width: 1000px; margin: 0 auto;">
      <Toolbar />
    </div>

    <div class="editor-layout">
      <!-- Sidebar Navigation -->
      <SlideNavigationBar />

      <!-- Canvas Area -->
      <EditorCanvas />
    </div>

    <!-- Modals & Overlays -->
    <AIPasteDialog />
    <GroupSetupModal />
    <GroupQuizGenerator />
    <LeaderboardOverlay />
    <LiveQuestionPanel />

    <!-- Presentation Mode Floating HUD -->
    <div v-if="!ui.isEditMode" class="presentation-hud">
      <FloatingAnalytics />
      <LiveQuestionOverlay />
      
      <button 
        class="fab-leaderboard" 
        @click="gameStore.isLeaderboardOpen = true"
        title="Show Live Leaderboard"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg>
      </button>
    </div>
  </div>
</template>

<style scoped>
.v5-container {
  padding: 2rem;
  min-height: 100vh;
  background-color: #f3f4f6;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #111827;
  margin-bottom: 0.5rem;
}

.title-editor {
  margin-bottom: 0.5rem;
  display: flex;
  justify-content: center;
}

.title-input {
  font-size: 2rem;
  font-weight: bold;
  color: #111827;
  text-align: center;
  border: 1px dashed transparent;
  background: transparent;
  padding: 4px 12px;
  border-radius: 8px;
  width: 100%;
  max-width: 600px;
  transition: all 0.2s;
}

.title-input:hover {
  border-color: #d1d5db;
  background: white;
}

.title-input:focus {
  outline: none;
  border-color: #6366f1;
  background: white;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.header p {
  color: #4b5563;
}

.header-controls {
  margin-top: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
  flex-wrap: wrap;
}

.mode-toggle {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Zoom Controls */
.zoom-controls {
  display: flex;
  align-items: center;
  gap: 4px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.zoom-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: transparent;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: #374151;
  transition: all 0.2s;
}

.zoom-btn:hover:not(:disabled) {
  background: #f3f4f6;
  color: #111827;
}

.zoom-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.zoom-display {
  min-width: 60px;
  height: 32px;
  background: transparent;
  border: none;
  font-weight: 600;
  font-size: 14px;
  color: #111827;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
}

.zoom-display:hover {
  background: #f3f4f6;
}

.mode-label {
  font-weight: 500;
  color: #374151;
}

.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 28px;
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
  transition: .4s;
}

.switch input:checked + .slider {
  background-color: #6366f1;
}

.switch input:focus + .slider {
  box-shadow: 0 0 1px #6366f1;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
}

.switch input:checked + .slider:before {
  transform: translateX(22px);
}

.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

.editor-layout {
  display: flex;
  gap: 20px;
  max-width: 1160px;
  margin: 0 auto;
  align-items: flex-start;
  justify-content: center;
}

@media (max-width: 1024px) {
  .v5-container {
    padding: 1rem;
  }
  .editor-layout {
    flex-direction: column-reverse;
    align-items: center;
  }
}

/* Floating Action Button (Present Mode) */
/* Floating HUD (Present Mode) */
.presentation-hud {
  position: fixed;
  bottom: 25px;
  right: 25px;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 12px;
}

.fab-leaderboard {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: white;
  border: 4px solid #fef3c7;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.fab-leaderboard:hover {
  transform: scale(1.1) translateY(-5px);
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);
  border-color: #fde68a;
}
</style>
