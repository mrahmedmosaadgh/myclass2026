<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { usePresentationStore } from './stores/presentationStore';
import { useUIStore } from './stores/uiStore';
import { useGameStore } from './stores/gameStore';
import { useClipboardStore } from './stores/clipboardStore';
import { usePaste } from './composables/usePaste';
import EditorCanvas from './components/EditorCanvas.vue';
import SlideCanvasReadonly from './components/SlideCanvasReadonly.vue';
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
import SlideContextMenu from './components/SlideContextMenu.vue';
import DrawingToolbar from './components/drawing/DrawingToolbar.vue';
import DrawingCanvasOverlay from './components/drawing/DrawingCanvasOverlay.vue';
import LiveQuestionOverlay from './components/LiveQuestionOverlay.vue';
import { useDrawingStore } from './stores/drawingStore';

const presentation = usePresentationStore();
const ui = useUIStore();
const gameStore = useGameStore();
const clipboard = useClipboardStore();
const { handlePaste, pasteElement } = usePaste();
const drawingStore = useDrawingStore();

function promptCustomZoom() {
  const raw = window.prompt('Zoom % (50 - 200)', String(ui.zoomLevel));
  if (raw === null) return;
  const val = Number(String(raw).trim());
  if (!Number.isFinite(val)) return;
  ui.setZoom(val);
}

function handlePageSelect(idx) {
  presentation.selectSlide(idx);
  ui.isPagesView = false;
}

// Slide context menu state
const slideContextMenu = ref({
  show: false,
  x: 0,
  y: 0
});

let prevPrintState = null;

function handleBeforePrint() {
  prevPrintState = {
    isEditMode: ui.isEditMode,
    presentModeLayout: ui.presentModeLayout,
    zoomLevel: ui.zoomLevel
  };

  ui.isEditMode = false;
  ui.presentModeLayout = 'continuous';
  ui.resetZoom();
}

function handleAfterPrint() {
  if (!prevPrintState) return;
  ui.isEditMode = prevPrintState.isEditMode;
  ui.presentModeLayout = prevPrintState.presentModeLayout;
  ui.zoomLevel = prevPrintState.zoomLevel;
  prevPrintState = null;
}

function handleSlideContextMenu(e) {
  if (!ui.isEditMode) return;
  e.preventDefault();
  
  slideContextMenu.value = {
    show: true,
    x: e.clientX,
    y: e.clientY
  };
}

function closeSlideContextMenu() {
  slideContextMenu.value.show = false;
}

const toolShortcutMap = {
  p: 'pen',
  h: 'highlighter',
  r: 'rectangle',
  c: 'circle',
  l: 'line',
  a: 'arrow',
  t: 'text',
  e: 'eraser'
};

function handleKeydown(e) {
  if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;

  const key = e.key.toLowerCase();

  // Copy/Cut/Paste shortcuts
  if ((e.ctrlKey || e.metaKey) && key === 'c') {
    e.preventDefault();
    if (ui.selectedElementId) {
      const element = presentation.currentSlide.elements.find(el => el.id === ui.selectedElementId);
      if (element) {
        clipboard.copyElement(element, presentation.currentSlide.id);
      }
    }
    return;
  }

  if ((e.ctrlKey || e.metaKey) && key === 'x') {
    e.preventDefault();
    if (ui.selectedElementId) {
      const element = presentation.currentSlide.elements.find(el => el.id === ui.selectedElementId);
      if (element) {
        clipboard.cutElement(element, presentation.currentSlide.id);
        presentation.deleteElement(ui.selectedElementId);
        ui.clearSelection();
      }
    }
    return;
  }

  if ((e.ctrlKey || e.metaKey) && key === 'v') {
    e.preventDefault();
    pasteElement();
    return;
  }

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

  // Drawing shortcuts
  if ((e.ctrlKey || e.metaKey) && key === 'z') {
    e.preventDefault();
    if (e.shiftKey) {
      drawingStore.redo();
    } else {
      drawingStore.undo();
    }
    return;
  }

  if ((e.ctrlKey || e.metaKey) && (key === 'y')) {
    e.preventDefault();
    drawingStore.redo();
    return;
  }

  if (e.shiftKey && key === 'd') {
    e.preventDefault();
    drawingStore.toggleDrawingMode();
    drawingStore.toggleToolbar(true);
    return;
  }

  if (!e.ctrlKey && !e.metaKey && drawingStore.isDrawingMode) {
    if (key === 'escape') {
      drawingStore.toggleDrawingMode(false);
      drawingStore.toggleToolbar(false);
      return;
    }

    if (key === 'l' && e.shiftKey) {
      drawingStore.setTool('laser');
      return;
    }

    if (toolShortcutMap[key]) {
      drawingStore.setTool(toolShortcutMap[key]);
      return;
    }
  }

  // Slide navigation
  if (e.key === 'ArrowRight' || e.key === 'ArrowDown' || e.key === 'PageDown') {
    presentation.selectSlide(presentation.currentSlideIndex + 1);
  } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp' || e.key === 'PageUp') {
    presentation.selectSlide(presentation.currentSlideIndex - 1);
  }
}

onMounted(() => {
  const timestamp = new Date().toISOString();
  console.log(`[${timestamp}] V7 Builder - onMounted started`);
  console.log(`[${timestamp}] Drawing store initialized:`, drawingStore);
  console.log(`[${timestamp}] Drawing mode status:`, drawingStore.isDrawingMode);
  console.log(`[${timestamp}] Drawing toolbar status:`, drawingStore.isToolbarOpen);
  console.log(`[${timestamp}] Active drawing tool:`, drawingStore.activeTool);
  console.log(`[${timestamp}] Build version: 2026-04-01-19-04-debug`);
  
  // Immediate UI debug
  console.log(`[${timestamp}] === IMMEDIATE UI DEBUG ===`);
  console.log(`[${timestamp}] Document ready state:`, document.readyState);
  console.log(`[${timestamp}] App element:`, document.getElementById('app'));
  console.log(`[${timestamp}] Body children:`, document.body.children.length);
  
  // Debug UI visibility
  console.log(`[${timestamp}] Setting up UI debug timeout...`);
  setTimeout(() => {
    console.log(`[${timestamp}] === UI VISIBILITY DEBUG (timeout fired) ===`);
    
    // Check main container
    const mainContainer = document.querySelector('.main-container');
    if (mainContainer) {
      const styles = window.getComputedStyle(mainContainer);
      console.log(`[${timestamp}] Main container found:`, {
        display: styles.display,
        visibility: styles.visibility,
        opacity: styles.opacity,
        width: styles.width,
        height: styles.height,
        offsetWidth: mainContainer.offsetWidth,
        offsetHeight: mainContainer.offsetHeight
      });
    } else {
      console.log(`[${timestamp}] ❌ Main container NOT found`);
    }
    
    // Check canvas area
    const canvasArea = document.querySelector('.canvas-area');
    if (canvasArea) {
      const styles = window.getComputedStyle(canvasArea);
      console.log(`[${timestamp}] Canvas area found:`, {
        display: styles.display,
        visibility: styles.visibility,
        opacity: styles.opacity,
        width: styles.width,
        height: styles.height,
        offsetWidth: canvasArea.offsetWidth,
        offsetHeight: canvasArea.offsetHeight
      });
    } else {
      console.log(`[${timestamp}] ❌ Canvas area NOT found`);
    }
    
    // Check slide container
    const slideContainer = document.querySelector('.slide-container');
    if (slideContainer) {
      const styles = window.getComputedStyle(slideContainer);
      console.log(`[${timestamp}] Slide container found:`, {
        display: styles.display,
        visibility: styles.visibility,
        opacity: styles.opacity,
        width: styles.width,
        height: styles.height,
        offsetWidth: slideContainer.offsetWidth,
        offsetHeight: slideContainer.offsetHeight
      });
    } else {
      console.log(`[${timestamp}] ❌ Slide container NOT found`);
    }
    
    // Check body
    const bodyStyles = window.getComputedStyle(document.body);
    console.log(`[${timestamp}] Body styles:`, {
      backgroundColor: bodyStyles.backgroundColor,
      backgroundImage: bodyStyles.backgroundImage,
      display: bodyStyles.display,
      visibility: bodyStyles.visibility,
      opacity: bodyStyles.opacity
    });
    
    // Check all direct children
    const appElement = document.getElementById('app');
    if (appElement) {
      console.log(`[${timestamp}] App element children:`, appElement.children.length);
      for (let i = 0; i < appElement.children.length; i++) {
        const child = appElement.children[i];
        console.log(`[${timestamp}] Child ${i}:`, {
          tagName: child.tagName,
          className: child.className,
          display: window.getComputedStyle(child).display,
          visibility: window.getComputedStyle(child).visibility
        });
      }
    }
    
    console.log(`[${timestamp}] === END UI DEBUG ===`);
  }, 1000);
  
  document.addEventListener('paste', handlePaste);
  document.addEventListener('keydown', handleKeydown);
  document.addEventListener('click', closeSlideContextMenu);
  window.addEventListener('beforeprint', handleBeforePrint);
  window.addEventListener('afterprint', handleAfterPrint);
  
  console.log(`[${timestamp}] V7 Builder - event listeners added`);
});

onUnmounted(() => {
  document.removeEventListener('paste', handlePaste);
  document.removeEventListener('keydown', handleKeydown);
  document.removeEventListener('click', closeSlideContextMenu);
  window.removeEventListener('beforeprint', handleBeforePrint);
  window.removeEventListener('afterprint', handleAfterPrint);
});
</script>

<template>
  <div class="v5-container" :class="{ 'has-fixed-description': !ui.isEditMode && presentation.description && presentation.showDescriptionInPresentMode !== false, 'is-focus-mode': ui.isFocusMode, 'has-fixed-toolbar': ui.isEditMode && ui.areEditToolsVisible }" @contextmenu="handleSlideContextMenu">
    <!-- Fixed Top Navigation Bar (Always Visible) -->
    <PresentationNavBar />
    
    <!-- Rich Content Description Area (Above Toolbar) -->
    <div v-if="ui.isEditMode && ui.areEditToolsVisible" class="description-container" style="max-width: 1000px; margin: 10px auto 0 auto;">
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
    
    <Toolbar v-if="ui.isEditMode && ui.areEditToolsVisible" />

    <!-- Description Display in Present Mode (Above Slides) -->
    <div v-if="!ui.isEditMode && presentation.description && presentation.showDescriptionInPresentMode !== false" class="description-present-container">
      <DescriptionEditor
        :model-value="presentation.description"
        :editable="false"
        :show-placeholder="false"
        :show-present-mode-toggle="false"
      />
    </div>

    <!-- Zoom Toolbar for Present Mode -->
    <div v-if="!ui.isEditMode" class="present-mode-zoom-toolbar">
      <button @click="ui.togglePresentModeLayout()" class="zoom-btn" :title="ui.presentModeLayout === 'continuous' ? 'Pages View (Continuous)' : 'Single Slide View'">
        <svg v-if="ui.presentModeLayout === 'continuous'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="3" y1="9" x2="21" y2="9"></line>
          <line x1="9" y1="21" x2="9" y2="9"></line>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <rect x="7" y="7" width="10" height="10" rx="1" ry="1"></rect>
        </svg>
        <span class="zoom-btn-text">{{ ui.presentModeLayout === 'continuous' ? 'Pages' : 'Slide' }}</span>
      </button>
      <button @click="ui.zoomIn()" class="zoom-btn" title="Zoom In (Ctrl +)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
          <line x1="11" y1="8" x2="11" y2="14"></line>
          <line x1="8" y1="11" x2="14" y2="11"></line>
        </svg>
      </button>
      <div class="zoom-level" @click="promptCustomZoom" title="Click to set custom zoom">
        {{ Math.round(ui.zoomLevel) }}%
      </div>
      <div class="zoom-presets">
        <button class="zoom-preset" @click="ui.setZoom(75)" title="75%">75</button>
        <button class="zoom-preset" @click="ui.setZoom(100)" title="100%">100</button>
        <button class="zoom-preset" @click="ui.setZoom(125)" title="125%">125</button>
        <button class="zoom-preset" @click="ui.setZoom(150)" title="150%">150</button>
      </div>
      <button @click="ui.zoomOut()" class="zoom-btn" title="Zoom Out (Ctrl -)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
          <line x1="8" y1="11" x2="14" y2="11"></line>
        </svg>
      </button>
      <button @click="ui.resetZoom()" class="zoom-btn" title="Reset Zoom (Ctrl 0)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
          <path d="M3 3v5h5"></path>
        </svg>
      </button>
    </div>

    <div class="editor-layout" :class="{ 'slide-nav-hidden': !ui.isSlideNavVisible }">
      <!-- Sidebar Navigation -->
      <SlideNavigationBar v-if="ui.isSlideNavVisible" />

      <button
        v-else
        class="slide-nav-toggle"
        @click="ui.toggleSlideNav()"
        title="Show slides"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </button>

      <!-- Canvas Area -->
      <div class="canvas-area">
        <div v-if="ui.isEditMode && ui.isPagesView" class="pages-view">
          <div
            v-for="(slide, idx) in presentation.slides"
            :key="slide.id"
            class="pages-view-slide"
            @click="handlePageSelect(idx)"
            title="Click to open this slide"
          >
            <div class="continuous-slide-label">Slide {{ idx + 1 }}</div>
            <SlideCanvasReadonly :slide="slide" />
          </div>
        </div>

        <EditorCanvas v-else-if="ui.isEditMode || ui.presentModeLayout === 'single'" />

        <div v-else class="continuous-slides">
          <div
            v-for="(slide, idx) in presentation.slides"
            :key="slide.id"
            class="continuous-slide"
          >
            <div class="continuous-slide-label">Slide {{ idx + 1 }}</div>
            <SlideCanvasReadonly :slide="slide" />
          </div>
        </div>
      </div>
    </div>

    <button
      v-if="ui.isEditMode"
      class="focus-fab"
      :class="{ active: ui.isFocusMode }"
      @click="ui.toggleFocusMode()"
      :title="ui.isFocusMode ? 'Exit Focus Mode' : 'Focus Mode (Clean Canvas)'"
    >
      <svg v-if="!ui.isFocusMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M16 3h3a2 2 0 0 1 2 2v3"/><path d="M8 21H5a2 2 0 0 1-2-2v-3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18H5a2 2 0 0 1-2-2v-4"/><path d="M15 6h4a2 2 0 0 1 2 2v4"/><path d="M3 10V8a2 2 0 0 1 2-2h2"/><path d="M21 14v2a2 2 0 0 1-2 2h-2"/></svg>
      <span>{{ ui.isFocusMode ? 'Focus' : 'Focus' }}</span>
    </button>

    <div v-if="ui.isEditMode && ui.isFocusMode" class="focus-mini-bar">
      <button class="mini-btn" @click="ui.toggleEditTools()" :title="ui.areEditToolsVisible ? 'Hide Tools' : 'Show Tools'">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a2 2 0 0 0 0-2.8l-1.2-1.2a2 2 0 0 0-2.8 0Z"/><path d="M6 20l2-2"/><path d="M2 22l6-6"/><path d="M14 10l-8 8"/></svg>
      </button>
      <button class="mini-btn" @click="ui.toggleSlideNav()" :title="ui.isSlideNavVisible ? 'Hide Slides' : 'Show Slides'">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="14" rx="2"/><path d="M7 21h10"/><path d="M12 17v4"/></svg>
      </button>
      <button class="mini-btn danger" @click="ui.toggleFocusMode()" title="Exit Focus Mode">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <!-- Modals & Overlays -->
    <AIPasteDialog />
    <GroupSetupModal />
    <GroupQuizGenerator />
    <LeaderboardOverlay />
    <LiveQuestionPanel />
    <DistributionModal />

    <!-- Slide Context Menu -->
    <SlideContextMenu 
      :show="slideContextMenu.show"
      :x="slideContextMenu.x"
      :y="slideContextMenu.y"
      @close="closeSlideContextMenu"
    />

    <DrawingToolbar />

    <!-- Drawing FAB - Only show in Edit Mode -->
    <button
      v-if="ui.isEditMode"
      class="drawing-fab"
      :class="{ active: drawingStore.isDrawingMode }"
      @click="() => { drawingStore.toggleDrawingMode(true); drawingStore.toggleToolbar(true); }"
      title="Toggle Drawing Toolbar (Shift + D)"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 19c1.5-1.5 3-3 5-3s3 1.5 5 3c-1.5 1.5-3 3-5 3s-3-1.5-5-3Z" />
        <path d="M12 5c1.5 1.5 3 3 5 3s3-1.5 5-3c-1.5-1.5-3-3-5-3s-3 1.5-5 3Z" />
        <path d="M2 12c1.5 1.5 3 3 5 3s3-1.5 5-3c-1.5-1.5-3-3-5-3S3.5 10.5 2 12Z" />
      </svg>
      <span>{{ drawingStore.isDrawingMode ? 'Drawing On' : 'Annotate' }}</span>
    </button>

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
  padding-top: 80px; /* Space for fixed navigation bar */
  padding-left: 2rem;
  padding-right: 2rem;
  padding-bottom: 2rem;
  min-height: 100vh;
  background-color: #f3f4f6;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.v5-container.has-fixed-toolbar {
  padding-top: 152px;
}

.v5-container.is-focus-mode {
  padding-left: 1rem;
  padding-right: 1rem;
}

.canvas-area {
  width: 100%;
}

.continuous-slides {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 28px;
  padding: 12px 0;
}

.pages-view {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 28px;
  padding: 12px 0;
}

.pages-view-slide {
  width: 100%;
  cursor: pointer;
}

.pages-view-slide:hover {
  outline: 2px solid rgba(99, 102, 241, 0.35);
  outline-offset: 6px;
  border-radius: 10px;
}

.continuous-slide {
  width: 100%;
}

.continuous-slide-label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  margin: 0 0 8px 6px;
}

.zoom-btn-text {
  margin-left: 6px;
  font-size: 12px;
  font-weight: 600;
}

.zoom-presets {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0 2px;
}

.zoom-preset {
  border: 1px solid rgba(0, 0, 0, 0.08);
  background: rgba(255, 255, 255, 0.9);
  color: #374151;
  border-radius: 8px;
  padding: 6px 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.zoom-preset:hover {
  background: white;
  border-color: rgba(99, 102, 241, 0.4);
  color: #4f46e5;
}

@media print {
  @page {
    margin: 10mm;
  }

  /* Hide UI chrome */
  :global(.presentation-nav-bar),
  :global(.slide-nav-simple),
  :global(.present-mode-zoom-toolbar),
  :global(.drawing-fab),
  :global(.presentation-hud),
  :global(.drawing-toolbar) {
    display: none !important;
  }

  /* Flatten app container */
  .v5-container {
    padding: 0 !important;
    background: white !important;
    min-height: auto !important;
  }

  .v5-container.has-fixed-description {
    padding-top: 0 !important;
  }

  /* Ensure continuous slides print cleanly */
  .continuous-slides {
    gap: 0 !important;
    padding: 0 !important;
  }

  .continuous-slide {
    page-break-after: always;
    break-after: page;
    margin: 0 !important;
    padding: 0 !important;
  }

  .continuous-slide-label {
    display: none !important;
  }
}

/* Add space for fixed description in present mode */
.v5-container.has-fixed-description {
  padding-top: 200px; /* Space for nav + description */
}


.editor-layout {
  display: flex;
  gap: 10px;
  margin: 0 auto;
  align-items: flex-start;
  justify-content: center;
}

.editor-layout.slide-nav-hidden {
  justify-content: center;
}

.slide-nav-toggle {
  position: sticky;
  top: 90px;
  align-self: flex-start;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #374151;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
}

.slide-nav-toggle:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

/* Editor Layout */
.editor-layout {
  display: flex;
  gap: 10px;
  margin: 0 auto;
  align-items: flex-start;
  justify-content: center;
}

.editor-layout.slide-nav-hidden {
  justify-content: center;
}

/* Focus FAB */
.focus-fab {
  position: fixed;
  right: 24px;
  bottom: 26px;
  z-index: 10010;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  background: rgba(255,255,255,0.95);
  color: #0f172a;
  font-weight: 900;
  box-shadow: 0 12px 25px rgba(0,0,0,0.12);
  cursor: pointer;
}

.focus-fab.active {
  border-color: #c7d2fe;
  background: rgba(238, 242, 255, 0.95);
}

.focus-mini-bar {
  position: fixed;
  right: 24px;
  bottom: 86px;
  z-index: 10010;
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 10px;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  background: rgba(255,255,255,0.95);
  box-shadow: 0 12px 25px rgba(0,0,0,0.12);
}

.mini-btn {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #0f172a;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.mini-btn:hover {
  background: #f8fafc;
}

.mini-btn.danger {
  background: #fef2f2;
  border-color: #fecaca;
  color: #991b1b;
}

@media (max-width: 1024px) {
  .v5-container {
    padding-top: 10px; /* Space for fixed navigation bar */
    padding-left: 1rem;
    padding-right: 1rem;
    padding-bottom: 1rem;
  }

  .v5-container.has-fixed-description {
    padding-top: 220px; /* More space on smaller screens */
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

/* Description Display in Present Mode */
.description-present-container {
  position: fixed;
  top: 80px; /* Below the fixed navigation bar */
  left: 0;
  right: 0;
  max-width: 900px;
  margin: 0 auto;
  padding: 0 20px;
  z-index: 40; /* Below navigation bar (z-index: 50) but above content */
 
  backdrop-filter: blur(10px);
}

.description-present-content {
  font-size: 1rem;
  line-height: 1.6;
  color: #374151;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(229, 231, 235, 0.8);
  border-radius: 12px;
  padding: 20px 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Present Mode Zoom Toolbar */
.present-mode-zoom-toolbar {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(229, 231, 235, 0.8);
  border-radius: 12px;
  padding: 8px 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  z-index: 45;
}

.zoom-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.zoom-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

.zoom-level {
  font-size: 0.75rem;
  font-weight: 600;
  color: #374151;
  min-width: 40px;
  text-align: center;
}

/* Drawing FAB styles */
.drawing-fab {
  position: fixed;
  bottom: 120px;
  right: 24px;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  border-radius: 999px;
  border: none;
  font-weight: 600;
  background: linear-gradient(120deg, #6366f1, #8b5cf6);
  color: white;
  box-shadow: 0 20px 35px rgba(99, 102, 241, 0.35);
  cursor: pointer;
  z-index: 4100;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.drawing-fab span {
  font-size: 0.9rem;
}

.drawing-fab.active {
  background: linear-gradient(120deg, #10b981, #14b8a6);
  box-shadow: 0 24px 40px rgba(20, 184, 166, 0.35);
}

.drawing-fab:hover {
  transform: translateY(-3px);
}

@media (max-width: 768px) {
  .drawing-fab {
    bottom: 100px;
    right: 16px;
    padding: 10px 14px;
  }
  .drawing-fab span {
    display: none;
  }
}
</style>
