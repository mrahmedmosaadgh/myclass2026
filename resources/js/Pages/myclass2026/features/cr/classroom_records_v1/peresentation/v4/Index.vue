<script setup>
import { onMounted, onUnmounted } from 'vue';
import { usePresentationStore } from './stores/presentationStore';
import { useUIStore } from './stores/uiStore';
import { usePaste } from './composables/usePaste';
import EditorCanvas from './components/EditorCanvas.vue';
import Toolbar from './components/Toolbar.vue';
import SlideNavigation from './components/SlideNavigation.vue';

const presentation = usePresentationStore();
const ui = useUIStore();
const { handlePaste } = usePaste();

function handleKeydown(e) {
  if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;

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
  <div class="v4-container">
    <div class="header">
      <h1>V4 Presentation Feature</h1>
      <p>Minimal, working reference implementation according to plan</p>
      
      <div class="mode-toggle">
        <label class="switch">
          <input type="checkbox" v-model="ui.isEditMode" @change="ui.clearSelection">
          <span class="slider round"></span>
        </label>
        <span class="mode-label">{{ ui.isEditMode ? 'Edit Mode (Build)' : 'Present Mode (View)' }}</span>
      </div>
    </div>
    
    <div v-if="ui.isEditMode" style="max-width: 1000px; margin: 0 auto;">
      <Toolbar />
    </div>

    <div class="editor-layout">
      <!-- Sidebar Navigation -->
      <SlideNavigation />

      <!-- Canvas Area -->
      <EditorCanvas />
    </div>
  </div>
</template>

<style scoped>
.v4-container {
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

.header p {
  color: #4b5563;
}

.mode-toggle {
  margin-top: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
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
</style>
