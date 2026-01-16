<template>
  <div class="slides-sidebar">
    <!-- Header -->
    <div class="sidebar-header">
      <div class="text-subtitle2 text-weight-bold text-grey-8">Slides</div>
      <q-btn 
        v-if="canEdit"
        flat 
        dense 
        round 
        size="sm" 
        icon="add" 
        color="primary"
        @click="$emit('addSlide')"
      >
        <q-tooltip>Add New Slide</q-tooltip>
      </q-btn>
    </div>

    <!-- Slides List with Sections -->
    <q-scroll-area class="slides-scroll-area">
      <div class="slides-container">
        <!-- Loop through sections -->
        <div 
          v-for="section in sections" 
          :key="section.id"
          class="section-group"
        >
          <!-- Section Header -->
          <div 
            class="section-header"
            :class="{ 'active-section': currentSection === section.id }"
            @click="onSectionClick(section)"
          >
            <div class="section-header-content">
              <q-icon 
                :name="section.icon || section.qIcon || 'folder'" 
                size="18px"
                :color="currentSection === section.id ? 'white' : 'grey-7'"
              />
              <div class="section-info">
                <div class="section-title">{{ section.title }}</div>
                <div class="section-count">{{ getSectionSlideCount(section.id) }} slides</div>
              </div>
            </div>
            <q-icon 
              :name="isSectionExpanded(section.id) ? 'expand_less' : 'expand_more'" 
              size="20px"
              :color="currentSection === section.id ? 'white' : 'grey-6'"
            />
          </div>

          <!-- Section Slides (collapsible) -->
          <transition
            enter-active-class="animated fadeIn"
            leave-active-class="animated fadeOut"
          >
            <div 
              v-show="isSectionExpanded(section.id)"
              class="section-slides"
            >
              <div
                v-for="(slide, index) in getSectionSlides(section.id)"
                :key="slide.id || index"
                class="slide-thumbnail"
                :class="{ 'active': isSlideActive(slide) }"
                @click="selectSlide(slide)"
              >
                <!-- Slide Number Badge -->
                <div class="slide-number">{{ getGlobalSlideIndex(slide) + 1 }}</div>
                
                <!-- Thumbnail Preview Box -->
                <div class="thumbnail-box">
                  <div class="thumbnail-content">
                    <!-- Icon representing slide type -->
                    <q-icon 
                      :name="getSlideIcon(slide.slide_type)" 
                      size="28px"
                      :color="isSlideActive(slide) ? 'primary' : 'grey-6'"
                    />
                    
                    <!-- Slide Type Label -->
                    <div class="slide-type-label">
                      {{ getSlideTypeLabel(slide.slide_type) }}
                    </div>
                  </div>
                </div>
                
                <!-- Delete Button (on hover) -->
                <div class="delete-overlay" v-if="canEdit">
                  <q-btn
                    round
                    dense
                    size="xs"
                    icon="delete"
                    color="negative"
                    @click.stop="$emit('deleteSlide', slide)"
                  >
                    <q-tooltip>Delete Slide</q-tooltip>
                  </q-btn>
                </div>
              </div>

              <!-- Add Slide Button for this section -->
              <div 
                v-if="canEdit"
                class="add-slide-button"
                @click="addSlideToSection(section)"
              >
                <q-icon name="add_circle" size="20px" color="positive" />
                <span>Add slide to {{ section.title }}</span>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </q-scroll-area>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    sections: Array,
    currentSection: String,
    slides: Array,
    showDrawer: Boolean,
    canEdit: Boolean,
    activeSlide: Object
});

const emit = defineEmits([
    "update:currentSection", 
    "update:currentSection_data",
    "addSlide",
    "deleteSlide",
    "selectSlide",
]);

// Track which sections are expanded
const expandedSections = ref(new Set());

// Initialize with current section expanded
if (props.currentSection) {
  expandedSections.value.add(props.currentSection);
}

// Get slides for a specific section
const getSectionSlides = (sectionId) => {
  return props.slides.filter(s => s.section === sectionId);
};

const getSectionSlideCount = (sectionId) => {
  return getSectionSlides(sectionId).length;
};

const isSectionExpanded = (sectionId) => {
  return expandedSections.value.has(sectionId);
};

const onSectionClick = (section) => {
  // Toggle expansion
  if (expandedSections.value.has(section.id)) {
    expandedSections.value.delete(section.id);
  } else {
    expandedSections.value.add(section.id);
  }
  
  // Update current section
  emit('update:currentSection', section.id);
  emit('update:currentSection_data', section);
};

const selectSlide = (slide) => {
    // Update current section when selecting a slide
    if (slide.section && slide.section !== props.currentSection) {
        const section = props.sections.find(s => s.id === slide.section);
        if (section) {
            emit('update:currentSection', section.id);
            emit('update:currentSection_data', section);
            // Expand the section
            expandedSections.value.add(section.id);
        }
    }
    emit('selectSlide', slide);
};

const addSlideToSection = (section) => {
  // Switch to this section
  emit('update:currentSection', section.id);
  emit('update:currentSection_data', section);
  // Expand it
  expandedSections.value.add(section.id);
  // Add slide
  emit('addSlide');
};

const isSlideActive = (slide) => {
    if (!props.activeSlide) return false;
    if (props.activeSlide.id && slide.id) return props.activeSlide.id === slide.id;
    return props.activeSlide === slide;
};

const getSectionInfo = (sectionId) => {
    return props.sections.find(s => s.id === sectionId);
};

// Get global slide index across all sections
const getGlobalSlideIndex = (slide) => {
  return props.slides.indexOf(slide);
};

const getSlideIcon = (type) => {
    switch(type) {
        case 'text': return 'description';
        case 'image': return 'image';
        case 'video': return 'videocam';
        case 'audio': return 'audiotrack';
        case 'pdf': return 'picture_as_pdf';
        case 'question': return 'quiz';
        case 'drawing': return 'brush';
        default: return 'article';
    }
};

const getSlideTypeLabel = (type) => {
    const labels = {
        'text': 'Text',
        'image': 'Image',
        'video': 'Video',
        'audio': 'Audio',
        'pdf': 'PDF',
        'question': 'Quiz',
        'drawing': 'Drawing'
    };
    return labels[type] || 'Slide';
};
</script>

<style scoped>
.slides-sidebar {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: #f8f9fa;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: white;
  border-bottom: 1px solid #e0e0e0;
}

.slides-scroll-area {
  flex: 1;
  height: calc(100vh - 120px);
}

.slides-container {
  padding: 8px 0;
}

/* Section Styles */
.section-group {
  margin-bottom: 4px;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  background: white;
  border-left: 4px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  margin: 0 8px;
  border-radius: 4px;
}

.section-header:hover {
  background: #f5f5f5;
  border-left-color: #bdbdbd;
}

.section-header.active-section {
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
  border-left-color: #0d47a1;
  box-shadow: 0 2px 4px rgba(25, 118, 210, 0.3);
}

.section-header-content {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
}

.section-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.section-title {
  font-size: 13px;
  font-weight: 600;
  color: #333;
  line-height: 1.2;
}

.active-section .section-title {
  color: white;
}

.section-count {
  font-size: 10px;
  color: #666;
  font-weight: 500;
}

.active-section .section-count {
  color: rgba(255, 255, 255, 0.9);
}

/* Section Slides Container */
.section-slides {
  padding: 8px;
  background: rgba(0, 0, 0, 0.01);
}

/* Slide Thumbnail Styles */
.slide-thumbnail {
  position: relative;
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: 4px;
  padding: 6px;
  margin-bottom: 8px;
}

.slide-thumbnail:hover {
  background: rgba(0, 0, 0, 0.02);
}

.slide-thumbnail.active {
  background: #e3f2fd;
  box-shadow: 0 0 0 2px #1976d2;
}

.slide-thumbnail.active .thumbnail-box {
  border-color: #1976d2;
}

.slide-number {
  position: absolute;
  top: 2px;
  left: 2px;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 50%;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
  color: #666;
  z-index: 2;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.slide-thumbnail.active .slide-number {
  background: #1976d2;
  color: white;
  border-color: #1976d2;
}

.thumbnail-box {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 4px;
  aspect-ratio: 16 / 9;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.slide-thumbnail:hover .thumbnail-box {
  border-color: #bdbdbd;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
}

.thumbnail-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 8px;
  text-align: center;
}

.slide-type-label {
  font-size: 10px;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.slide-thumbnail.active .slide-type-label {
  color: #1976d2;
}

.delete-overlay {
  position: absolute;
  top: 50%;
  right: 6px;
  transform: translateY(-50%);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.slide-thumbnail:hover .delete-overlay {
  opacity: 1;
}

/* Add Slide Button */
.add-slide-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  margin: 4px 0;
  background: white;
  border: 2px dashed #bdbdbd;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 12px;
  color: #666;
  font-weight: 500;
}

.add-slide-button:hover {
  background: #f5f5f5;
  border-color: #4caf50;
  color: #4caf50;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .section-header {
    padding: 8px 12px;
  }
  
  .section-title {
    font-size: 12px;
  }
  
  .thumbnail-box {
    aspect-ratio: 16 / 10;
  }
}
</style>
