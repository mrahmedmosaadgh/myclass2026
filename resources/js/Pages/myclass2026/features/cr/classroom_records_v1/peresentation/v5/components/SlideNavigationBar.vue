<script setup>
import { ref, computed } from 'vue';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import { useSectionStore } from '../stores/sectionStore';
import SectionManager from './SectionManager.vue';

const presentation = usePresentationStore();
const ui = useUIStore();
const sectionStore = useSectionStore();

const slideListRef = ref(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(false);

const currentSlidePhase = computed({
  get: () => presentation.currentSlide?.sectionId || null,
  set: (value) => {
    if (presentation.currentSlide) {
      presentation.currentSlide.sectionId = value;
    }
  }
});

function checkScrollButtons() {
  if (!slideListRef.value) return;
  const el = slideListRef.value;
  canScrollLeft.value = el.scrollLeft > 0;
  canScrollRight.value = el.scrollLeft < (el.scrollWidth - el.clientWidth - 5);
}

function scrollSlides(direction) {
  if (!slideListRef.value) return;
  const scrollAmount = 120;
  slideListRef.value.scrollBy({
    left: direction === 'left' ? -scrollAmount : scrollAmount,
    behavior: 'smooth'
  });
  setTimeout(checkScrollButtons, 300);
}

function enablePhases() {
  presentation.usePhases = true;
  presentation.hasInitializedPhases = true;
}

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
  <div class="slide-nav-bar">
    <!-- Phase Selector Card -->
    <div class="phase-selector-card">
      <div class="phase-header">
        <span class="phase-label">SLIDE PHASE:</span>
        <button 
          class="settings-icon-btn" 
          @click="ui.isSectionManagerOpen = true"
          title="Manage Phases"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
          </svg>
        </button>
      </div>
      
      <select v-model="currentSlidePhase" class="phase-dropdown">
        <option :value="null">No Phase</option>
        <option 
          v-for="section in sectionStore.sections" 
          :key="section.id" 
          :value="section.id"
        >
          {{ section.name }}
        </option>
      </select>
    </div>

    <!-- Slides Scroll Container -->
    <div class="slides-container">
      <!-- Scroll Left Button -->
      <button 
        v-if="canScrollLeft"
        class="scroll-btn scroll-left"
        @click="scrollSlides('left')"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </button>

      <!-- Slides List -->
      <div 
        ref="slideListRef" 
        class="slides-list"
        @scroll="checkScrollButtons"
      >
        <div
          v-for="(slide, index) in presentation.slides"
          :key="slide.id"
          class="slide-card"
          :class="{ active: presentation.currentSlideIndex === index }"
          @click="presentation.selectSlide(index)"
        >
          <!-- Top Toolbar -->
          <div v-if="ui.isEditMode" class="slide-toolbar slide-toolbar-top">
            <button
              class="toolbar-btn"
              @click.stop="presentation.addSlideAt(index)"
              title="Add Slide Before"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
            </button>
            
            <button
              v-if="index > 0"
              class="toolbar-btn"
              @click.stop="presentation.moveSlideUp(index)"
              title="Move Up"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"></polyline>
              </svg>
            </button>
            
            <button
              v-if="presentation.slides.length > 1"
              class="toolbar-btn toolbar-btn-delete"
              @click.stop="presentation.deleteSlide(index)"
              title="Delete Slide"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <div class="slide-number">{{ index + 1 }}</div>
          
          <div 
            class="slide-thumbnail"
            :style="{ 
              borderColor: getSectionColor(slide.sectionId) !== 'transparent' 
                ? getSectionColor(slide.sectionId) 
                : '#e5e7eb'
            }"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="14" rx="2" ry="2"></rect>
              <line x1="7" y1="8" x2="17" y2="8"></line>
              <line x1="7" y1="12" x2="14" y2="12"></line>
            </svg>
          </div>

          <!-- Bottom Toolbar -->
          <div v-if="ui.isEditMode" class="slide-toolbar slide-toolbar-bottom">
            <button
              class="toolbar-btn"
              @click.stop="presentation.addSlideAt(index + 1)"
              title="Add Slide After"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
            </button>
            
            <button
              v-if="index < presentation.slides.length - 1"
              class="toolbar-btn"
              @click.stop="presentation.moveSlideDown(index)"
              title="Move Down"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Scroll Right Button -->
      <button 
        v-if="canScrollRight"
        class="scroll-btn scroll-right"
        @click="scrollSlides('right')"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </button>

      <!-- Scroll Up Button (Top) -->
      <button 
        v-if="canScrollLeft"
        class="scroll-btn scroll-up"
        @click="scrollSlides('left')"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
      </button>

      <!-- Scroll Down Button (Bottom) -->
      <button 
        v-if="canScrollRight"
        class="scroll-btn scroll-down"
        @click="scrollSlides('right')"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>
    </div>

    <!-- New Slide Button -->
    <button class="new-slide-btn" @click="presentation.addSlide()">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
      New Slide
    </button>

    <!-- Enable Accordion Mode Link -->
    <button 
      v-if="!presentation.usePhases"
      class="accordion-mode-link"
      @click="enablePhases"
    >
      Enable Accordion Mode
    </button>

    <!-- Section Manager Modal -->
    <Teleport to="body">
      <SectionManager v-if="ui.isSectionManagerOpen" />
    </Teleport>
  </div>
</template>

<style scoped>
.slide-nav-bar {
  display: flex;
  flex-direction: column;
  width: 200px;
  gap: 16px;
  flex-shrink: 0;
}

/* Phase Selector Card */
.phase-selector-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.phase-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.phase-label {
  font-size: 11px;
  font-weight: 700;
  color: #6b7280;
  letter-spacing: 0.5px;
}

.settings-icon-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #9ca3af;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.settings-icon-btn:hover {
  color: #6366f1;
  background: #f3f4f6;
}

.phase-dropdown {
  width: 100%;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.phase-dropdown:hover {
  border-color: #9ca3af;
}

.phase-dropdown:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Slides Container */
.slides-container {
  position: relative;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  min-height: 400px;
  max-height: 500px;
}

.slides-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  overflow-y: auto;
  overflow-x: hidden;
  max-height: 450px;
  padding: 4px;
  scroll-behavior: smooth;
}

/* Custom Scrollbar */
.slides-list::-webkit-scrollbar {
  width: 6px;
}

.slides-list::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 3px;
}

.slides-list::-webkit-scrollbar-thumb {
  background: #9ca3af;
  border-radius: 3px;
}

.slides-list::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}

/* Slide Card */
.slide-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 12px;
  background: #fafafa;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.slide-card:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
  transform: translateX(2px);
}

.slide-card.active {
  background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
  border-color: #818cf8;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
}

.slide-number {
  font-size: 12px;
  font-weight: 700;
  color: #9ca3af;
  margin-bottom: 8px;
  letter-spacing: 0.5px;
}

.slide-card.active .slide-number {
  color: #6366f1;
}

.slide-thumbnail {
  width: 100%;
  height: 100px;
  background: white;
  border: 3px solid #e5e7eb;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.slide-card:hover .slide-thumbnail {
  border-color: #d1d5db;
}

.slide-card.active .slide-thumbnail {
  border-color: #818cf8;
}

/* Slide Toolbars */
.slide-toolbar {
  position: absolute;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 4px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-radius: 6px;
  opacity: 0;
  transform: scaleY(0);
  transform-origin: top;
  transition: all 0.2s ease;
  z-index: 10;
}

.slide-toolbar-top {
  top: -2px;
  transform-origin: top;
}

.slide-toolbar-bottom {
  bottom: -2px;
  transform-origin: bottom;
}

.slide-card:hover .slide-toolbar {
  opacity: 1;
  transform: scaleY(1);
}

.toolbar-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 4px;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
  padding: 0;
}

.toolbar-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
  transform: scale(1.1);
}

.toolbar-btn-delete {
  background: rgba(239, 68, 68, 0.3);
  border-color: rgba(239, 68, 68, 0.5);
}

.toolbar-btn-delete:hover {
  background: rgba(239, 68, 68, 0.5);
  border-color: rgba(239, 68, 68, 0.7);
}

/* Scroll Buttons */
.scroll-btn {
  position: absolute;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #6b7280;
  transition: all 0.2s;
  z-index: 5;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.scroll-btn:hover {
  background: #f9fafb;
  color: #374151;
  border-color: #9ca3af;
}

.scroll-left {
  left: 8px;
  top: 50%;
  transform: translateY(-50%);
}

.scroll-right {
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
}

.scroll-up {
  top: 8px;
  right: 8px;
}

.scroll-down {
  bottom: 8px;
  right: 8px;
}

/* New Slide Button */
.new-slide-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 20px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
}

.new-slide-btn:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}

.new-slide-btn:active {
  transform: translateY(0);
}

/* Accordion Mode Link */
.accordion-mode-link {
  background: transparent;
  border: none;
  color: #6366f1;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  transition: all 0.2s;
  text-align: center;
}

.accordion-mode-link:hover {
  background: #eef2ff;
  color: #4f46e5;
}

/* Responsive */
@media (max-width: 1024px) {
  .slide-nav-bar {
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
  }

  .phase-selector-card {
    flex: 1;
    min-width: 200px;
  }

  .slides-container {
    flex: 2;
    min-width: 300px;
    min-height: 150px;
    max-height: 200px;
  }

  .slides-list {
    flex-direction: row;
    max-height: none;
  }

  .slide-card {
    flex-shrink: 0;
    width: 120px;
  }

  .new-slide-btn {
    flex: 1;
    min-width: 150px;
  }
}
</style>
