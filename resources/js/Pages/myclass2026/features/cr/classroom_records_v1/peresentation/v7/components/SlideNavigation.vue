<script setup>
import { ref, computed } from 'vue';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import { useSectionStore } from '../stores/sectionStore';
import SectionManager from './SectionManager.vue';

const presentation = usePresentationStore();
const ui = useUIStore();
const sectionStore = useSectionStore();

// Reorder mode state
const isReorderMode = ref(false);
const draggedSlideIndex = ref(null);

function getSectionColor(sectionId) {
  const sec = sectionStore.sections.find(s => s.id === sectionId);
  return sec ? sec.color : 'transparent';
}

function getSectionName(sectionId) {
  const sec = sectionStore.sections.find(s => s.id === sectionId);
  return sec ? sec.name : '';
}

function getSlidesForPhase(sectionId) {
  return presentation.slides.filter(s => s.sectionId === sectionId);
}

function getOverallSlideIndex(slideId) {
  return presentation.slides.findIndex(s => s.id === slideId);
}

function enablePhases() {
  presentation.usePhases = true;
  presentation.hasInitializedPhases = true;
}

function ignorePhases() {
  presentation.usePhases = false;
  presentation.hasInitializedPhases = true;
}

// Slide management functions
function toggleReorderMode() {
  isReorderMode.value = !isReorderMode.value;
  if (!isReorderMode.value) {
    draggedSlideIndex.value = null;
  }
}

function addSlideBetween(index) {
  presentation.addSlideAtIndex(index + 1);
}

function onDragStart(event, slideIndex) {
  if (!isReorderMode.value) return;
  draggedSlideIndex.value = slideIndex;
  event.dataTransfer.effectAllowed = 'move';
}

function onDragOver(event) {
  if (!isReorderMode.value) return;
  event.preventDefault();
  event.dataTransfer.dropEffect = 'move';
}

function onDrop(event, targetIndex) {
  if (!isReorderMode.value || draggedSlideIndex.value === null) return;
  event.preventDefault();
  
  if (draggedSlideIndex.value !== targetIndex) {
    presentation.moveSlide(draggedSlideIndex.value, targetIndex);
  }
  draggedSlideIndex.value = null;
}

function onDragEnd() {
  draggedSlideIndex.value = null;
}
</script>

<template>
  <div class="slide-nav-container">
    
    <!-- Initialization Modal (only shows for fresh presentations in Edit Mode) -->
    <div v-if="ui.isEditMode && !presentation.hasInitializedPhases" class="init-phases-overlay">
      <div class="init-phases-card">
        <h3>Classroom Phases</h3>
        <p>Would you like to formally organize your slides into structured Lesson Phases (e.g., Introduction, Core Concept, Assessment)?</p>
        <div class="init-actions">
          <button @click="enablePhases" class="btn-phases yes">Yes, Group by Phases</button>
          <button @click="ignorePhases" class="btn-phases no">No, Use Basic List</button>
        </div>
      </div>
    </div>

    <!-- Slide Management Controls -->
    <div v-if="ui.isEditMode" class="slide-management">
      <button 
        @click="toggleReorderMode" 
        :class="['reorder-toggle', { active: isReorderMode }]"
        :title="isReorderMode ? 'Exit reorder mode' : 'Enable slide reordering'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 12h18m-9-9v18"></path>
        </svg>
        {{ isReorderMode ? 'Done' : 'Reorder' }}
      </button>
      <div v-if="isReorderMode" class="reorder-hint">
        Drag slides to reorder
      </div>
    </div>

    <div class="slide-nav" :class="{ 'present-mode': !ui.isEditMode, 'reorder-mode': isReorderMode }">
      
      <!-- Standard Mode (No Phases) -->
      <template v-if="!presentation.usePhases">
        <div class="section-assigner" v-if="ui.isEditMode && presentation.currentSlide">
        <div class="row">
          <label>Slide Phase:</label>
          <button class="settings-btn" @click="ui.isSectionManagerOpen = true" title="Manage Default Phases">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
          </button>
        </div>
        <select v-model="presentation.currentSlide.sectionId" class="section-select">
          <option :value="undefined">No Phase</option>
          <option v-for="sec in sectionStore.sections" :key="sec.id" :value="sec.id">
            {{ sec.name }}
          </option>
        </select>
      </div>

      <!-- Slide List -->
      <div class="slide-list">
        <div 
          v-for="(slide, index) in presentation.slides" 
          :key="slide.id"
          class="slide-thumb"
          :class="{ 
            active: presentation.currentSlideIndex === index,
            'dragging': draggedSlideIndex === index,
            'reorderable': isReorderMode
          }"
          @click="!isReorderMode && presentation.selectSlide(index)"
          :draggable="isReorderMode"
          @dragstart="onDragStart($event, index)"
          @dragover="onDragOver"
          @drop="onDrop($event, index)"
          @dragend="onDragEnd"
        >
          <!-- Drag Handle (visible in reorder mode) -->
          <div v-if="isReorderMode" class="drag-handle">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 12h18m-9-9v18"></path>
            </svg>
          </div>
          
          <span class="slide-number">{{ index + 1 }}</span>
          
          <div class="slide-preview" :style="{ borderLeftColor: getSectionColor(slide.sectionId), borderLeftWidth: slide.sectionId ? '4px' : '1px' }">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="14" rx="2" ry="2"></rect>
              <line x1="7" y1="8" x2="17" y2="8"></line>
              <line x1="7" y1="12" x2="14" y2="12"></line>
            </svg>
          </div>
          
          <div v-if="getSectionName(slide.sectionId)" class="slide-section-label" :style="{ color: getSectionColor(slide.sectionId) !== 'transparent' ? '#4b5563' : 'inherent' }">
            {{ getSectionName(slide.sectionId) }}
          </div>

          <div class="slide-actions">
            <!-- Add Slide Between Button -->
            <button 
              v-if="ui.isEditMode && !isReorderMode" 
              class="add-between-btn" 
              @click.stop="addSlideBetween(index)"
              title="Add slide after this one"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
            </button>
            
            <!-- Delete Button -->
            <button 
              v-if="ui.isEditMode && presentation.slides.length > 1 && !isReorderMode" 
              class="delete-btn" 
              @click.stop="presentation.deleteSlide(index)" 
              title="Delete Slide"
            >
              &times;
            </button>
          </div>
        </div>
      </div>
      
        <button v-if="ui.isEditMode" class="add-slide-btn" @click="presentation.addSlide()">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          New Slide
        </button>
        
        <div class="phase-settings-link" v-if="ui.isEditMode" style="margin-top: 15px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: center;">
          <button @click="enablePhases">Enable Accordion Mode</button>
        </div>
      </template>

      <!-- Advanced Mode (Grouped Phases) -->
      <template v-else>
        <div class="grouped-phases-container">
          <div class="phase-group" v-for="section in sectionStore.sections" :key="section.id">
             <!-- Phase Header -->
             <div class="phase-header" :style="{ borderLeftColor: section.color || '#4f46e5' }">
                <span class="phase-name" :style="{ color: section.color || '#4f46e5' }">{{ section.name }}</span>
                <button v-if="ui.isEditMode" class="add-phase-slide-btn" @click="presentation.addSlideToPhase(section.id)" title="Add Slide to this Phase">+</button>
             </div>
             
             <!-- Phase Slides -->
             <div class="phase-slides-list">
               <div 
                  v-for="slide in getSlidesForPhase(section.id)" 
                  :key="slide.id"
                  class="slide-thumb phase-thumb"
                  :class="{ active: presentation.currentSlide?.id === slide.id }"
                  @click="presentation.selectSlideById(slide.id)"
                >
                  <span class="slide-number">{{ getOverallSlideIndex(slide.id) + 1 }}</span>
                  <div class="slide-preview phase-preview" :style="{ borderColor: section.color, opacity: presentation.currentSlide?.id === slide.id ? 1 : 0.7 }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#3b82f6" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="3" width="18" height="14" rx="2" ry="2" fill="#bfdbfe"></rect><circle cx="8" cy="8" r="2.5" fill="#fcd34d" stroke="none"></circle><path d="M21 13l-5-5L5 21" fill="#60a5fa" stroke="none"></path>
                    </svg>
                  </div>
                  <button v-if="ui.isEditMode && presentation.slides.length > 1" class="delete-btn" @click.stop="presentation.deleteSlideById(slide.id)">&times;</button>
               </div>
               <div v-if="getSlidesForPhase(section.id).length === 0" class="empty-phase">No slides</div>
             </div>
          </div>

          <div class="phase-settings-link" v-if="ui.isEditMode">
            <button @click="ui.isSectionManagerOpen = true">Manage Phase Types</button>
            <br><br>
            <button @click="ignorePhases" style="color: #6b7280;">Disable Accordion Mode</button>
          </div>
        </div>
      </template>

    </div>

    <!-- Manager modal -->
    <Teleport to="body">
      <SectionManager v-if="ui.isSectionManagerOpen" />
    </Teleport>
  </div>
</template>

<style scoped>
.slide-nav-container {
  display: flex;
  position: relative;
}

.init-phases-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}
.init-phases-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.init-phases-card h3 {
  margin-top: 0;
  font-size: 20px;
  color: #1f2937;
}
.init-phases-card p {
  color: #4b5563;
  margin: 15px 0;
  line-height: 1.5;
}
.init-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 20px;
}
.btn-phases {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.2s;
}
.btn-phases:hover { transform: translateY(-2px); }
.btn-phases.yes { background: #4f46e5; color: white; }
.btn-phases.no { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }

/* Grouped Phase Styles */
.grouped-phases-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
  width: 100%;
}
.phase-group {
  display: flex;
  flex-direction: column;
}
.phase-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-left: 4px solid #d1d5db;
  padding-left: 8px;
  margin-bottom: 8px;
}
.phase-name {
  font-size: 13px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.add-phase-slide-btn {
  background: transparent;
  color: #6b7280;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.add-phase-slide-btn:hover {
  background: #e2e8f0;
  color: #111827;
}
.phase-slides-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.empty-phase {
  font-size: 11px;
  color: #9ca3af;
  font-style: italic;
  padding-left: 12px;
}
.phase-thumb {
  flex-direction: row;
  gap: 8px;
  justify-content: flex-start;
  padding: 4px 8px;
}
.phase-preview {
  width: 50px;
  height: 35px;
  border-width: 2px;
}
.phase-settings-link {
  margin-top: 10px;
  text-align: center;
  border-top: 1px solid #e5e7eb;
  padding-top: 10px;
}
.phase-settings-link button {
  background: transparent;
  border: none;
  color: #4f46e5;
  font-size: 11px;
  font-weight: bold;
  cursor: pointer;
}
.phase-settings-link button:hover { text-decoration: underline; }

.slide-nav {
  display: flex;
  flex-direction: column;
  width: 140px;
  background: white;
  border-radius: 8px;
  padding: 10px;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  border: 1px solid #e5e7eb;
  flex-shrink: 0;
  transition: opacity 0.3s, pointer-events 0.3s;
}

.slide-nav.present-mode {
  opacity: 0;
}

.slide-nav.present-mode:hover {
  opacity: 1;
}

.section-assigner {
  margin-bottom: 12px;
  background: #f9fafb;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}
.row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}
.row label {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.settings-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #9ca3af;
  padding: 2px;
}
.settings-btn:hover { color: #4f46e5; transform: scale(1.1); }
.section-select {
  width: 100%;
  padding: 4px;
  font-size: 11px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
}

.slide-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  overflow-y: auto;
  max-height: 550px;
  margin-bottom: 12px;
}

.slide-thumb {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  padding: 10px 8px;
  border-radius: 8px;
  border: 1.5px solid transparent;
  transition: all 0.15s ease;
  background: white;
  margin-bottom: 4px;
}

.slide-thumb:hover {
  background: #f9fafb;
  border-color: #e5e7eb;
  transform: translateX(2px);
}

.slide-thumb.active {
  background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
  border-color: #818cf8;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
}

.slide-number {
  font-size: 11px;
  font-weight: 600;
  color: #9ca3af;
  margin-bottom: 6px;
  letter-spacing: 0.5px;
}

.slide-thumb.active .slide-number {
  color: #6366f1;
}

.slide-preview {
  width: 100px;
  height: 60px;
  background: #fafafa;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.slide-thumb:hover .slide-preview {
  border-color: #d1d5db;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.slide-thumb.active .slide-preview {
  border-color: #818cf8;
  background: white;
}

.slide-section-label {
  font-size: 9px;
  font-weight: 500;
  margin-top: 6px;
  text-align: center;
  max-width: 100%;
  padding: 3px 8px;
  background: #f3f4f6;
  border-radius: 10px;
  color: #6b7280;
  letter-spacing: 0.3px;
}

.delete-btn {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #ef4444;
  color: white;
  border: 2px solid white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  font-size: 14px;
  font-weight: 600;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  opacity: 0;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
  z-index: 20;
}

.delete-btn:hover {
  background: #dc2626;
  transform: scale(1.15);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.5);
}

.slide-thumb:hover .delete-btn {
  opacity: 1;
}

.add-slide-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

.add-slide-btn:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.add-slide-btn:active {
  transform: translateY(0);
}

@media (max-width: 1024px) {
  .slide-nav-container {
    width: 100%;
    margin-top: 15px;
  }
  .slide-nav {
    width: 100%;
    flex-direction: row;
    overflow-x: auto;
    align-items: center;
    padding: 10px;
  }
  .slide-list {
    flex-direction: row;
    max-height: none;
    margin-bottom: 0;
    margin-right: 15px;
  }
  .slide-thumb {
    flex-shrink: 0;
  }
  .section-assigner {
    min-width: 140px;
    margin-bottom: 0;
    margin-right: 15px;
    flex-shrink: 0;
  }
  .add-slide-btn {
    width: auto;
    padding: 8px 14px;
    white-space: nowrap;
    margin-left: auto;
    flex-shrink: 0;
  }
}

/* Slide Management Styles */
.slide-management {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  padding: 8px;
  background: #f3f4f6;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.reorder-toggle {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.reorder-toggle:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.reorder-toggle.active {
  background: #10b981;
  color: white;
  border-color: #10b981;
}

.reorder-hint {
  font-size: 11px;
  color: #6b7280;
  font-style: italic;
}

.slide-nav.reorder-mode {
  background: #fef3c7;
  border-color: #f59e0b;
}

.slide-thumb.reorderable {
  cursor: grab;
  user-select: none;
}

.slide-thumb.reorderable:active {
  cursor: grabbing;
}

.slide-thumb.dragging {
  opacity: 0.5;
  transform: scale(0.95);
}

.drag-handle {
  position: absolute;
  top: 4px;
  left: 4px;
  padding: 2px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 4px;
  opacity: 0.7;
  transition: opacity 0.2s;
}

.slide-thumb.reorderable:hover .drag-handle {
  opacity: 1;
}

.slide-actions {
  position: absolute;
  bottom: -6px;
  right: -6px;
  display: flex;
  gap: 6px;
  z-index: 10;
}

.add-between-btn {
  width: 24px;
  height: 24px;
  background: #10b981;
  border: 2px solid white;
  border-radius: 50%;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  opacity: 0;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
}

.add-between-btn svg {
  width: 12px;
  height: 12px;
}

.slide-thumb:hover .add-between-btn {
  opacity: 1;
}

.add-between-btn:hover {
  background: #059669;
  transform: scale(1.15);
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.5);
}

/* Drag and drop visual feedback */
.slide-thumb[draggable="true"]:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.slide-thumb[draggable="true"]:hover .slide-preview {
  border-color: #f59e0b;
}
</style>
