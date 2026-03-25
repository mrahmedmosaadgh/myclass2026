<script setup>
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import { useSectionStore } from '../stores/sectionStore';
import SectionManager from './SectionManager.vue';

const presentation = usePresentationStore();
const ui = useUIStore();
const sectionStore = useSectionStore();

function getSectionColor(sectionId) {
  const sec = sectionStore.sections.find(s => s.id === sectionId);
  return sec ? sec.color : 'transparent';
}

function getSectionName(sectionId) {
  const sec = sectionStore.sections.find(s => s.id === sectionId);
  return sec ? sec.name : '';
}
</script>

<template>
  <div class="slide-nav-container">
    <div class="slide-nav" :class="{ 'present-mode': !ui.isEditMode }">
      
      <!-- Section Assigner UI -->
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
          :class="{ active: presentation.currentSlideIndex === index }"
          @click="presentation.selectSlide(index)"
        >
          <span class="slide-number">{{ index + 1 }}</span>
          
          <div class="slide-preview" :style="{ borderLeftColor: getSectionColor(slide.sectionId), borderLeftWidth: slide.sectionId ? '6px' : '1px' }">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="#3b82f6" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="14" rx="2" ry="2" fill="#bfdbfe"></rect>
              <circle cx="8" cy="8" r="2.5" fill="#fcd34d" stroke="none"></circle>
              <path d="M21 13l-5-5L5 21" fill="#60a5fa" stroke="none"></path>
            </svg>
          </div>
          
          <div v-if="getSectionName(slide.sectionId)" class="slide-section-label" :style="{ color: getSectionColor(slide.sectionId) !== 'transparent' ? '#4b5563' : 'inherent' }">
            {{ getSectionName(slide.sectionId) }}
          </div>

          <button 
            v-if="ui.isEditMode && presentation.slides.length > 1" 
            class="delete-btn" 
            @click.stop="presentation.deleteSlide(index)" 
            title="Delete Slide"
          >
            &times;
          </button>
        </div>
      </div>
      
      <button v-if="ui.isEditMode" class="add-slide-btn" @click="presentation.addSlide()">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        New Slide
      </button>
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
}

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
  opacity: 0.15;
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
  padding: 6px;
  border-radius: 6px;
  border: 2px solid transparent;
  transition: all 0.2s;
}

.slide-thumb:hover {
  background: #f3f4f6;
}

.slide-thumb.active {
  background: #e0e7ff;
  border-color: #6366f1;
}

.slide-number {
  font-size: 12px;
  font-weight: bold;
  color: #6b7280;
  margin-bottom: 4px;
}

.slide-preview {
  width: 100px;
  height: 60px;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  transition: border-width 0.2s, border-color 0.2s;
}

.slide-section-label {
  font-size: 10px;
  font-weight: 500;
  margin-top: 5px;
  text-align: center;
  max-width: 100%;
  padding: 2px 6px;
  background: white;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
}

.delete-btn {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 14px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s;
  box-shadow: 0 2px 4px rgb(0 0 0 / 0.2);
}

.slide-thumb:hover .delete-btn {
  opacity: 1;
}

.delete-btn:hover {
  background: #dc2626;
  transform: scale(1.1);
}

.add-slide-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #10b981;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.2s;
  width: 100%;
}

.add-slide-btn:hover {
  background: #059669;
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
</style>
