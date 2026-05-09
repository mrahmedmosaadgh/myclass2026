<script setup>
import { computed } from 'vue'
import { usePresentationStore } from '../stores/presentationStore.js'
import { useUIStore } from '../stores/uiStore.js'

const presentation = usePresentationStore()
const ui = useUIStore()

const currentSlide = computed(() => presentation.currentSlideIndex)
const totalSlides = computed(() => presentation.totalSlides)

function selectSlide(index) {
  presentation.selectSlide(index)
  ui.isPagesView = false
}

function deleteSlide(index) {
  if (totalSlides.value > 1) {
    presentation.deleteSlide(index)
  }
}

function addNewSlide() {
  presentation.addSlide()
}
</script>

<template>
  <div class="slide-navigation">
    <div class="slide-header">
      <h3>Slides</h3>
      <button @click="addNewSlide" class="add-slide-btn" title="Add New Slide">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
      </button>
    </div>
    
    <div class="slide-list">
      <div
        v-for="(slide, index) in presentation.slides"
        :key="slide.id"
        class="slide-item"
        :class="{ active: currentSlide === index }"
        @click="selectSlide(index)"
      >
        <div class="slide-thumbnail">
          <div class="slide-number">{{ index + 1 }}</div>
          <div class="slide-preview">
            <div
              v-for="element in slide.elements.slice(0, 3)"
              :key="element.id"
              class="preview-element"
              :class="`preview-${element.type}`"
              :style="{
                left: (element.x / 8) + 'px',
                top: (element.y / 8) + 'px',
                width: (element.width / 8) + 'px',
                height: (element.height / 8) + 'px'
              }"
            />
          </div>
        </div>
        
        <div class="slide-info">
          <div class="slide-title">Slide {{ index + 1 }}</div>
          <div class="slide-element-count">{{ slide.elements.length }} elements</div>
        </div>
        
        <button
          v-if="totalSlides > 1"
          @click.stop="deleteSlide(index)"
          class="delete-slide-btn"
          title="Delete Slide"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <div class="slide-footer">
      <div class="slide-counter">
        {{ currentSlide + 1 }} / {{ totalSlides }}
      </div>
    </div>
  </div>
</template>

<style scoped>
.slide-navigation {
  width: 280px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  height: fit-content;
  max-height: 600px;
}

.slide-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.slide-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.add-slide-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: white;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
}

.add-slide-btn:hover {
  background: #f9fafb;
  border-color: #d1d5db;
  color: #374151;
}

.slide-list {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 16px;
}

.slide-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.slide-item:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.slide-item.active {
  background: #eff6ff;
  border-color: #6366f1;
}

.slide-thumbnail {
  width: 60px;
  height: 45px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
}

.slide-number {
  position: absolute;
  top: 2px;
  left: 2px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 4px;
  border-radius: 2px;
  z-index: 2;
}

.slide-preview {
  width: 100%;
  height: 100%;
  position: relative;
}

.preview-element {
  position: absolute;
  background: #6366f1;
  border-radius: 1px;
}

.preview-text {
  background: #374151;
}

.preview-image {
  background: #10b981;
}

.preview-rectangle {
  background: #f59e0b;
}

.preview-html {
  background: #ef4444;
}

.slide-info {
  flex: 1;
  min-width: 0;
}

.slide-title {
  font-size: 14px;
  font-weight: 500;
  color: #111827;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.slide-element-count {
  font-size: 12px;
  color: #6b7280;
}

.delete-slide-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border: none;
  border-radius: 4px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
  opacity: 0;
}

.slide-item:hover .delete-slide-btn {
  opacity: 1;
}

.delete-slide-btn:hover {
  background: #fef2f2;
  color: #dc2626;
}

.slide-footer {
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.slide-counter {
  text-align: center;
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
}

@media (max-width: 768px) {
  .slide-navigation {
    width: 100%;
    max-height: 300px;
  }
  
  .slide-item {
    padding: 8px;
  }
  
  .slide-thumbnail {
    width: 50px;
    height: 38px;
  }
}
</style>
