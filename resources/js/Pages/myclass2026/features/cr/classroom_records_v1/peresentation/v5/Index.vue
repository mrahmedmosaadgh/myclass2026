<script setup>
import { onMounted, onUnmounted } from 'vue';
import { usePresentationStore } from './stores/presentationStore';
import { useUIStore } from './stores/uiStore';
import { useGameStore } from './stores/gameStore';
import { usePaste } from './composables/usePaste';
import EditorCanvas from './components/EditorCanvas.vue';
import Toolbar from './components/Toolbar.vue';
import SlideNavigationBar from './components/SlideNavigationBar.vue';
import PresentationNavBar from './components/PresentationNavBar.vue';
import DescriptionEditor from './components/DescriptionEditor.vue';
import AIPasteDialog from './components/AIPasteDialog.vue';
import GroupSetupModal from './components/GroupSetupModal.vue';
import GroupQuizGenerator from './components/GroupQuizGenerator.vue';
import LeaderboardOverlay from './components/LeaderboardOverlay.vue';
import FloatingAnalytics from './components/FloatingAnalytics.vue';
import LiveQuestionPanel from './components/LiveQuestionPanel.vue';
import DistributionModal from './components/DistributionModal.vue';

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
    <!-- Fixed Top Navigation Bar (Always Visible) -->
    <PresentationNavBar />
    
    <!-- Rich Content Description Area (Above Toolbar) -->
    <div v-if="ui.isEditMode" class="description-container" style="max-width: 1000px; margin: 10px auto 0 auto;">
      <DescriptionEditor 
        v-model="presentation.description"
        placeholder="Add description, notes, math formulas, or rich content..."
        :show-present-mode-toggle="true"
        :show-in-present-mode="presentation.showDescriptionInPresentMode"
        @save="(value) => presentation.description = value"
        @toggle-present-mode="presentation.toggleDescriptionInPresentMode"
        @clear="presentation.clearDescription"
      />
    </div>
    
    <div v-if="ui.isEditMode" style="max-width: 1000px; margin: 10px auto 0 auto;">
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
    <DistributionModal />

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
  padding-top: 70px;
  padding-left: 2rem;
  padding-right: 2rem;
  padding-bottom: 2rem;
  min-height: 100vh;
  background-color: #f3f4f6;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
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
    padding-top: 70px;
    padding-left: 1rem;
    padding-right: 1rem;
    padding-bottom: 1rem;
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
  left: 25px;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
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

/* Description Container */
.description-container {
  padding-top: 20px; /* Reduced space for fixed navbar */
}
</style>
