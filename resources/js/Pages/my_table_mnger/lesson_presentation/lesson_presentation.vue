<template>
  <Head :title="presentation.name ? `Editing: ${presentation.name}` : 'New Lesson'" />
  <q-layout view="hHh Lpr fFf" class="bg-grey-1">
    
    <!-- Colorful Header -->
    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="leftDrawerOpen = !leftDrawerOpen" />
        
        <q-btn flat round dense icon="arrow_back" @click="goBack">
           <q-tooltip>Back</q-tooltip>
        </q-btn>

        <q-toolbar-title class="q-ml-sm">
           <div class="row items-center q-gutter-x-md">
             <div class="cursor-pointer">
                {{ presentation.name || 'New Lesson' }}
                <q-popup-edit v-model="presentation.name" auto-save v-slot="scope">
                  <q-input v-model="scope.value" dense autofocus counter @keyup.enter="scope.set" label="Lesson Name" />
                </q-popup-edit>
                <q-icon name="edit" size="xs" class="q-ml-xs opacity-50" />
             </div>
             <!-- Unsaved changes indicator -->
             <q-badge 
               v-if="hasUnsavedChanges && !isSaving" 
               color="orange" 
               text-color="white" 
               icon="warning"
               class="q-ml-sm"
             >
               Unsaved changes
             </q-badge>
             <q-badge 
               v-else-if="isSaving" 
               color="blue" 
               text-color="white" 
               icon="sync"
               class="q-ml-sm"
             >
               Saving...
             </q-badge>
             <q-badge color="white" text-color="primary" :label="currentGradeName" v-if="presentation.grade_id" />
           </div>
           <div class="text-caption text-blue-2 cursor-pointer ellipsis" style="max-width: 300px;">
              {{ presentation.description || 'Add description...' }}
              <q-popup-edit v-model="presentation.description" auto-save v-slot="scope">
                  <q-input v-model="scope.value" dense autofocus type="textarea" @keyup.enter="scope.set" label="Description" />
                </q-popup-edit>
           </div>
        </q-toolbar-title>

        <div class="row items-center q-gutter-sm">
            <div class="column q-mr-md text-right text-caption text-grey-1">
               <div>Subject: <span class="text-weight-bold">{{ defaultContext?.subject_name }}</span></div>
               <div>Class: <span class="text-weight-bold">{{ defaultContext?.grade_name }}</span></div>
            </div>
            
            <q-separator vertical dark class="q-mx-sm" />

            <!-- Auto-save indicator -->
            <div class="column items-center q-mx-sm" v-if="autoSaveEnabled">
              <q-icon 
                :name="isAutoSaving ? 'sync' : hasUnsavedChanges ? 'circle' : 'check_circle'" 
                :color="isAutoSaving ? 'orange' : hasUnsavedChanges ? 'red' : 'green'"
                size="sm"
              >
                <q-tooltip>
                  {{ isAutoSaving ? 'Auto-saving...' : hasUnsavedChanges ? 'Unsaved changes' : 'All changes saved' }}
                </q-tooltip>
              </q-icon>
              <div class="text-caption text-grey-3" style="font-size: 10px;">
                {{ autoSaveStatus }}
              </div>
            </div>

            <q-btn 
              unelevated 
              color="positive" 
              icon="save" 
              label="Save" 
              @click="savePresentation" 
              :loading="isSaving"
              :disable="!hasUnsavedChanges || isSaving"
              size="sm"
              class="q-px-sm"
            >
              <q-tooltip v-if="!hasUnsavedChanges">
                No changes to save
              </q-tooltip>
              <q-tooltip v-else-if="isSaving">
                Saving...
              </q-tooltip>
              <q-tooltip v-else>
                Save lesson (Ctrl+S)
              </q-tooltip>
            </q-btn>

            <!-- Removed Reward System button as per request -->
            
            <q-btn flat round dense icon="visibility" @click="showPreview = true" color="white">
               <q-tooltip>Preview Full Lesson</q-tooltip>
            </q-btn>
        </div>
      </q-toolbar>

      <!-- Saving Progress Bar -->
      <q-linear-progress 
        v-if="isSaving || saveProgress.visible" 
        :value="saveProgress.percentage" 
        color="secondary" 
        class="saving-progress-bar"
        instant-feedback
      >
        <div class="absolute-full flex flex-center text-white text-caption">
          {{ saveProgress.message }}
        </div>
      </q-linear-progress>
    </q-header>

    <!-- Sidebar Drawer -->
    <q-drawer v-model="leftDrawerOpen" show-if-above bordered class="bg-white" :width="300">
       <LessonSidebar
          :sections="sections"
          v-model:currentSection_data="currentSection_data"
          v-model:currentSection="currentSection"
          :slides="slides"
          v-model:showDrawer="showSectionsDrawer"
          :can-edit="true"
          :active-slide="currentSlide"
          @selectSlide="(slide) => currentSlideIndex = filteredSlides.indexOf(slide)"
          @addSlide="addSlide"
          class="fit"
       />
    </q-drawer>

    <!-- Main Content -->
    <q-page-container>
      <q-page class="q-pa-md bg-grey-2 row justify-center" ref="pageContainer">
        <!-- Editor Area -->
         <div class="col-12 col-lg-10" style="max-width: 1200px">
           <transition
              appear
              enter-active-class="animated fadeIn"
              leave-active-class="animated fadeOut"
            >
              <div v-if="currentSlide" class="bg-white rounded-borders shadow-1 q-pa-lg">
                 <!-- Slide Toolbar -->
                 <div class="row items-center justify-between q-mb-md">
                    <div class="row items-center q-gutter-sm">
                       <q-icon name="edit_note" color="primary" size="md" />
                       <div class="text-h6 text-grey-8">Slide Editor</div>
                       
                       <!-- Slide Navigation -->
                       <q-separator vertical inset class="q-mx-sm" />
                       <q-btn-group outline>
                          <q-btn 
                            flat
                            dense
                            icon="chevron_left" 
                            @click="previousSlide"
                            :disable="currentSlideIndex === 0"
                            color="primary"
                          >
                            <q-tooltip>Previous Slide (←)</q-tooltip>
                          </q-btn>
                          
                          <q-btn 
                            flat
                            dense
                            no-caps
                            :label="`${currentSlideIndex + 1} / ${filteredSlides.length}`"
                            color="primary"
                            class="q-px-md"
                          >
                            <q-tooltip>Current Slide</q-tooltip>
                          </q-btn>
                          
                          <q-btn 
                            flat
                            dense
                            icon="chevron_right" 
                            @click="nextSlide"
                            :disable="currentSlideIndex >= filteredSlides.length - 1"
                            color="primary"
                          >
                            <q-tooltip>Next Slide (→)</q-tooltip>
                          </q-btn>
                       </q-btn-group>
                    </div>
                    
                    <div class="row items-center q-gutter-sm">
                       <q-select 
                        v-model="currentSlide.slide_type"
                        :options="[
                            { label: '📝 Text', value: 'text' },
                            { label: '🖼️ Image', value: 'image' },
                            { label: '🎥 Video', value: 'video' },
                            { label: '🎵 Audio', value: 'audio' },
                            { label: '📄 PDF', value: 'pdf' },
                            { label: '❓ Question', value: 'question' },
                            { label: '🎨 Drawing', value: 'drawing' }
                        ]"
                        dense
                        outlined
                        emit-value
                        map-options
                        options-dense
                        color="primary"
                        label="Slide Type"
                        style="min-width: 150px"
                       />
                       <q-btn flat round color="primary" icon="visibility" @click="showSingleSlidePreview = true">
                          <q-tooltip>Preview Slide</q-tooltip>
                       </q-btn>
                       <q-btn flat round color="negative" icon="delete" @click="deleteSlide(currentSlide)">
                          <q-tooltip>Delete Slide</q-tooltip>
                       </q-btn>
                   </div>
                </div>

                <q-separator class="q-mb-md" />

                <!-- Slide Content Component -->
                <div style="min-height: 500px">
                   <component 
                    :is="getSlideComponent(currentSlide.slide_type)" 
                    v-model="currentSlide.slide_content"
                    :key="currentSlide.id || currentSlideIndex"
                    :context="defaultContext"
                  />
                </div>
              </div>

              <!-- Empty State -->
              <div v-else class="column items-center justify-center text-grey-5 q-pa-xl bg-white rounded-borders shadow-1 fit" style="min-height: 500px">
                 <q-icon name="touch_app" size="80px" color="primary" class="opacity-20" />
                 <div class="text-h5 q-mt-md text-weight-medium">Select a Slide</div>
                 <div class="text-subtitle1 q-mt-sm">Choose a section on the left to start editing</div>
              </div>
          </transition>
           
           <!-- Place QPageScroller at end of page -->
           <q-page-scroller position="bottom-right" :scroll-offset="150" :offset="[18, 18]">
             <q-btn fab icon="keyboard_arrow_up" color="accent">
               <q-tooltip>Scroll to top</q-tooltip>
             </q-btn>
           </q-page-scroller>
         </div>
      </q-page>
    </q-page-container>

  </q-layout>

  <!-- Preview Dialog -->
  <q-dialog v-model="showPreview" maximized>
    <q-card class="bg-grey-1">
      <q-card-section class="row items-center q-pb-none bg-primary text-white">
        <div class="text-h6 flex items-center gap-2">
          <q-icon name="visibility" size="28px" />
          Student View Preview
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-pa-none bg-grey-1" style="height: calc(100vh - 60px)">
        <LessonPlayer
          :presentation="{ ...presentation, name: 'Preview: ' + presentation.name }"
          :sections="sections"
          :slides="slides"
          :is-preview="true"
        />
      </q-card-section>
    </q-card>
  </q-dialog>

  <!-- Single Slide Preview Dialog -->
  <q-dialog v-model="showSingleSlidePreview" maximized>
    <q-card class="bg-grey-1">
      <q-card-section class="row items-center q-pb-none bg-primary text-white">
        <div class="text-h6 flex items-center gap-2">
          <q-icon name="visibility" size="28px" />
          Single Slide Preview
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-pa-none bg-grey-1" style="height: calc(100vh - 60px)">
        <LessonPlayer
          v-if="currentSlide"
          :presentation="{ ...presentation, name: 'Preview: ' + presentation.name }"
          :sections="sections"
          :slides="[currentSlide]"
          :is-preview="true"
        />
      </q-card-section>
    </q-card>
  </q-dialog>

  <!-- AI Lesson Plan Generator -->
  <AILessonPlanGenerator
    ref="aiLessonGenerator"
    :lesson-config="lessonConfigForAI"
    @plan-accepted="handleAIPlanAccepted"
  />

  <!-- Print Lesson Plan -->
  <PrintLessonPlan
    ref="printLessonPlan"
    :presentation="presentation"
    :sections="sections"
    :slides="slides"
    :teacher-name="teacherStore.teacher?.name || 'Teacher'"
    :subject-name="defaultContext?.subject_name || 'Subject'"
    :grade-name="defaultContext?.grade_name || 'Grade'"
  />

  <!-- Reward System Dialog -->
  <q-dialog 
    v-model="showRewardDialog" 
    :maximized="!isRewardMinimized" 
    :seamless="isRewardMinimized"
    :position="isRewardMinimized ? 'bottom' : 'standard'"
    transition-show="slide-up" 
    transition-hide="slide-down"
  >
     <q-card v-bind:style="isRewardMinimized ? 'width: 300px' : ''">
        <q-bar class="bg-primary text-white">
           <q-icon name="emoji_events" />
           <div class="text-h6 q-ml-sm" v-if="!isRewardMinimized">Reward System</div>
           <div class="text-subtitle2 q-ml-sm" v-else>Rewards</div>
           <q-space />
           
           <!-- Explicit Maximize Button when Minimized -->
           <q-btn 
             v-if="isRewardMinimized" 
             dense 
             flat 
             no-caps
             label="Maximize"
             icon="open_in_full" 
             @click="isRewardMinimized = false"
           >
              <q-tooltip>Maximize</q-tooltip>
           </q-btn>
           
           <q-btn 
             v-else 
             dense 
             flat 
             icon="minimize" 
             @click="isRewardMinimized = true"
           >
              <q-tooltip>Minimize</q-tooltip>
           </q-btn>
           
           <q-btn dense flat icon="close" v-close-popup>
              <q-tooltip>Close</q-tooltip>
           </q-btn>
        </q-bar>
        
        <q-card-section v-show="!isRewardMinimized" class="q-pa-none" style="height: calc(100vh - 32px)">
           <keep-alive>
              <RewardSystem :isDialog="true" />
           </keep-alive>
        </q-card-section>
     </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import TextSlide from './components/slides/TextSlide.vue';
import MediaSlide from './components/slides/MediaSlide.vue';
import QuestionSlide from './components/slides/QuestionSlide.vue';
import PDFSlide from './components/slides/PDFSlide.vue';
import VideoSlide from './components/slides/VideoSlide.vue';
import SectionIndicator from './components/SectionIndicator.vue';
import LessonSidebar from './components/LessonSidebar.vue';
import LessonPlayer from './components/LessonPlayer.vue';
import QuizSelector from './components/QuizSelector.vue';
import FingerDrawingSlide from './components/FingerDrawingSlide.vue';
import AILessonPlanGenerator from '@/Components/Common/ai/AILessonPlanGenerator.vue';
import PrintLessonPlan from '@/Components/Common/PrintLessonPlan.vue';
import { useTeacherStore } from '@/Stores/teacherStore';
import RewardSystem from '../reward_sys/reward_sys.vue';

const props = defineProps({
  presentationId: {
    type: [Number, String],
    default: null
  },
  defaultContext: {
    type: Object,
    default: () => ({
      school_id: 1,
      teacher_id: 1,
      subject_id: 1
    })
  },
  sections: {
    type: Array,
    default: () => []
  }
});

const teacherStore = useTeacherStore();
const $q = useQuasar();

// Get ID from URL if not passed as prop (e.g. query param or route param handled by wrapper)
const urlParams = new URLSearchParams(window.location.search);
const idFromUrl = urlParams.get('id');
// Convert to number if it's a string, or keep null/undefined
const initialId = props.presentationId || (idFromUrl ? parseInt(idFromUrl) : null);
const activeId = ref(initialId);

const presentation = ref({
  name: 'New Lesson',
  description: 'Lesson description',
  grade_id: null,
  quiz_id: null,
  slides: []
});

// Sections configuration
// Sections configuration
const sections = ref(props.sections);

const currentSection = ref(''); // Default to 'learn' section
const leftDrawerOpen = ref(true);
const currentSection_data = ref(null);
const slides = ref([]);
const currentSlideIndex = ref(0);
const isSaving = ref(false);
const saveProgress = ref({
  visible: false,
  percentage: 0,
  message: ''
});
const showPreview = ref(false);
const aiLessonGenerator = ref(null);
const printLessonPlan = ref(null);
const showSectionsDrawerRaw = ref(true);
const showSlideListDialog = ref(true);
const showSingleSlidePreview = ref(false);
const showRewardDialog = ref(false);
const isRewardMinimized = ref(false);

// Auto-save functionality
const autoSaveEnabled = ref(true);
const autoSaveTimer = ref(null);
const autoSaveInterval = ref(300000); // Increased to 5 minutes (300,000 ms)
const isAutoSaving = ref(false);
const autoSaveStatus = ref('Auto-save every 5 minutes');
const lastSavedData = ref(null);
const hasUnsavedChanges = ref(false); // Track overall unsaved changes state

// Computed config for AI Generator
const lessonConfigForAI = computed(() => ({
  lessonTitle: presentation.value.name || 'New Lesson',
  subject: props.defaultContext?.subject_name || 'General',
  grade: props.defaultContext?.grade_name || 'General',
  sections: sections.value
}));

// Computed property to make dialog always hidden on small screens
const showSectionsDrawer = computed({
  get() {
    return $q.screen.lt.sm ? false : showSectionsDrawerRaw.value;
  },
  set(value) {
    showSectionsDrawerRaw.value = value;
  }
});

// Delete current slide
const deleteCurrentSlide = () => {
 
};

const currentSectionTitle = computed(() => {
  const section = sections.value.find(s => s.id === currentSection.value);
  return section ? `Section: ${section.title}` : 'Select a section';
});

const getSectionSlideCount = (sectionId) => {
  return slides.value.filter(slide => slide.section === sectionId).length;
};

// Filter slides by current section
const filteredSlides = computed(() => {
  return slides.value.filter(slide => slide.section === currentSection.value);
});

const currentSlide = computed(() => {
  if (filteredSlides.value.length === 0) return null;
  return filteredSlides.value[currentSlideIndex.value];
});

const currentGradeName = computed(() => {
  if (!presentation.value.grade_id) return 'No Grade';
  const grade = teacherStore.grades.find(g => g.id === presentation.value.grade_id);
  return grade ? grade.name : 'Unknown Grade';
});

// Computed properties for slide change tracking
const slidesWithChanges = computed(() => {
  return slides.value.filter(slide => slide.hasChanges || isSlideChanged(slide));
});

const changedSlidesCount = computed(() => {
  return slidesWithChanges.value.length;
});

// Helper functions for slide change tracking
const isSlideChanged = (slide) => {
  // New slides without initial state are always considered changed
  if (!slide._initialState) {
    return slide.hasChanges || true;
  }
  
  try {
    const currentState = {
      slide_type: slide.slide_type,
      slide_content: slide.slide_content,
      section: slide.section,
      order_index: slide.order_index
    };
    
    return JSON.stringify(currentState) !== JSON.stringify(slide._initialState);
  } catch (error) {
    console.warn('Error comparing slide state:', error);
    return true; // Assume changed if comparison fails
  }
};

const updateSlideChangeStatus = (slide) => {
  slide.hasChanges = isSlideChanged(slide);
};

const resetSlideChangeTracking = () => {
  slides.value.forEach(slide => {
    if (slide.id) {
      // For existing slides, update their initial state
      slide._initialState = JSON.parse(JSON.stringify({
        slide_type: slide.slide_type,
        slide_content: slide.slide_content,
        section: slide.section,
        order_index: slide.order_index
      }));
    }
    slide.hasChanges = false;
  });
};

const getSlideComponent = (type) => {
  switch (type) {
    case 'text': return TextSlide;
    case 'question': return QuestionSlide;
    case 'pdf': return PDFSlide;
    case 'video': return VideoSlide;
    case 'image':
    case 'audio':
      return MediaSlide;
    case 'drawing': return FingerDrawingSlide;
    default: return TextSlide;
  }
};

const getSlideSummary = (slide) => {
  if (slide.slide_type === 'text') {
    // Strip HTML for summary
    const div = document.createElement('div');
    div.innerHTML = slide.slide_content?.text || '';
    return div.textContent || 'Text content...';
  }
  if (slide.slide_type === 'question') {
    const count = slide.slide_content?.questions?.length || 0;
    return `${count} Question${count !== 1 ? 's' : ''}`;
  }
  if (slide.slide_type === 'drawing') return 'Drawing Canvas';
  return `${slide.slide_type} content`;
};

// Enhanced slide initialization with change tracking
const initializeSlideWithTracking = (slideData) => {
  return {
    ...slideData,
    section: slideData.section || 'learn',
    // Add change tracking property
    hasChanges: false,
    // Store initial state for comparison
    _initialState: JSON.parse(JSON.stringify({
      slide_type: slideData.slide_type,
      slide_content: slideData.slide_content || {},
      section: slideData.section || 'learn',
      order_index: slideData.order_index || 0
    }))
  };
};

const addSlide = () => {
  const newSlide = {
    slide_type: 'text',
    slide_content: {},
    section: currentSection.value,
    hasChanges: true, // New slides are considered changed
    _initialState: null // No initial state for new slides
  };
  slides.value.push(newSlide);
  // Set index to the last slide in the filtered list
  currentSlideIndex.value = filteredSlides.value.length - 1;
  
  // Mark overall lesson as having changes
  hasUnsavedChanges.value = true;
};

const deleteSlide = (slideToDelete) => {
  $q.dialog({
    title: 'Delete Slide',
    message: 'Are you sure you want to delete this slide? This action cannot be undone.',
    cancel: true,
    persistent: true,
    ok: {
      label: 'Delete',
      color: 'negative',
      flat: true
    },
      cancel: {
      label: 'Cancel',
      color: 'grey',
      flat: true
    }
  }).onOk(() => {
    const globalIndex = slides.value.indexOf(slideToDelete);
    if (globalIndex !== -1) {
      slides.value.splice(globalIndex, 1);
      
      // Adjust current slide index
      if (filteredSlides.value.length > 0) {
        // If we deleted the last slide, go to the new last slide
        if (currentSlideIndex.value >= filteredSlides.value.length) {
          currentSlideIndex.value = filteredSlides.value.length - 1;
        }
      } else {
        // No slides left in this section
        currentSlideIndex.value = 0;
      }
      
      // Mark overall lesson as having changes
      hasUnsavedChanges.value = true;
      
      $q.notify({
        type: 'positive',
        message: 'Slide deleted successfully',
        icon: 'check_circle',
        position: 'top',
        timeout: 1500
      });
    }
  });
};

const stripHtml = (html) => {
  const tmp = document.createElement('DIV');
  tmp.innerHTML = html || '';
  return tmp.textContent || tmp.innerText || '';
};

// Navigation functions
const goBack = () => {
  if (activeId.value) {
    router.visit(route('lesson-presentation.manage', { id: activeId.value }));
  } else {
    router.visit(route('lesson-presentation.index'));
  }
};

const previousSlide = () => {
  if (currentSlideIndex.value > 0) {
    currentSlideIndex.value--;
  }
};

const nextSlide = () => {
  if (currentSlideIndex.value < filteredSlides.value.length - 1) {
    currentSlideIndex.value++;
  }
};

// Keyboard navigation and shortcuts
const handleKeyboard = (event) => {
  // Ctrl+S to save
  if (event.ctrlKey && event.key === 's') {
    event.preventDefault();
    if (hasUnsavedChanges.value && !isSaving.value) {
      savePresentation();
    }
    return;
  }
  
  // Ctrl+Shift+S for auto-save toggle
  if (event.ctrlKey && event.shiftKey && event.key === 'S') {
    event.preventDefault();
    toggleAutoSave();
    return;
  }
  
  // Arrow key navigation (only when not in input fields)
  if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return;
  
  switch(event.key) {
    case 'ArrowLeft':
      if (currentSlideIndex.value > 0) {
        currentSlideIndex.value--;
      } else if (sections.value.findIndex(s => s.id === currentSection.value) > 0) {
        // Move to previous section's last slide
        const currentSectionIndex = sections.value.findIndex(s => s.id === currentSection.value);
        currentSection.value = sections.value[currentSectionIndex - 1].id;
        currentSlideIndex.value = filteredSlides.value.length - 1;
        // Scroll to top when switching sections
        setTimeout(() => scrollToTop(), 100);
      }
      break;
    case 'ArrowRight':
      if (currentSlideIndex.value < filteredSlides.value.length - 1) {
        currentSlideIndex.value++;
      } else if (sections.value.findIndex(s => s.id === currentSection.value) < sections.value.length - 1) {
        // Move to next section's first slide
        const currentSectionIndex = sections.value.findIndex(s => s.id === currentSection.value);
        currentSection.value = sections.value[currentSectionIndex + 1].id;
        currentSlideIndex.value = 0;
        // Scroll to top when switching sections
        setTimeout(() => scrollToTop(), 100);
      }
      break;
  }
};

const validatePresentation = () => {
  if (!presentation.value.name.trim()) {
    $q.notify({
      type: 'warning',
      message: 'Please enter a lesson name',
      icon: 'warning',
      position: 'top'
    });
    return false;
  }
  
  if (!presentation.value.grade_id) {
    $q.notify({
      type: 'warning',
      message: 'Please select a grade',
      icon: 'warning',
      position: 'top'
    });
    return false;
  }

  for (let sIdx = 0; sIdx < slides.value.length; sIdx++) {
    const slide = slides.value[sIdx];
    if (slide.slide_type === 'question') {
      const questions = slide.slide_content?.questions || [];
      if (questions.length === 0) {
        $q.notify({
          type: 'warning',
          message: `Slide ${sIdx + 1}: Please add at least one question`,
          icon: 'warning',
          position: 'top'
        });
        currentSlideIndex.value = sIdx;
        return false;
      }

      for (let qIdx = 0; qIdx < questions.length; qIdx++) {
        const q = questions[qIdx];
        if (!stripHtml(q.text).trim()) {
          $q.notify({
            type: 'warning',
            message: `Slide ${sIdx + 1}, Question ${qIdx + 1}: Question text cannot be empty`,
            icon: 'warning',
            position: 'top'
          });
          currentSlideIndex.value = sIdx;
          return false;
        }

        if (['short_answer', 'number'].includes(q.type) && !q.correct_answer) {
          $q.notify({
            type: 'warning',
            message: `Slide ${sIdx + 1}, Question ${qIdx + 1}: Please provide a correct answer`,
            icon: 'warning',
            position: 'top'
          });
          currentSlideIndex.value = sIdx;
          return false;
        }

        if (q.type === 'single_choice' && !q.correct_answer) {
           $q.notify({
             type: 'warning',
             message: `Slide ${sIdx + 1}, Question ${qIdx + 1}: Please select a correct option`,
             icon: 'warning',
             position: 'top'
           });
           currentSlideIndex.value = sIdx;
           return false;
        }

        if (['single_choice', 'multiple_choice'].includes(q.type)) {
          if (!q.options || q.options.length < 2) {
            $q.notify({
              type: 'warning',
              message: `Slide ${sIdx + 1}, Question ${qIdx + 1}: Must have at least 2 options`,
              icon: 'warning',
              position: 'top'
            });
            currentSlideIndex.value = sIdx;
            return false;
          }

          for (let oIdx = 0; oIdx < q.options.length; oIdx++) {
            if (!stripHtml(q.options[oIdx].text).trim()) {
              $q.notify({
                type: 'warning',
                message: `Slide ${sIdx + 1}, Question ${qIdx + 1}, Option ${oIdx + 1}: Option text cannot be empty`,
                icon: 'warning',
                position: 'top'
              });
              currentSlideIndex.value = sIdx;
              return false;
            }
          }
        }
      }
    } else if (slide.slide_type === 'text') {
       // Only validate text slides if they have slide_content with text property
       // Allow empty slides to be saved (they can be filled in later)
       if (slide.slide_content?.text && !stripHtml(slide.slide_content.text).trim()) {
          $q.notify({
            type: 'warning',
            message: `Slide ${sIdx + 1}: Text content cannot be empty. Please add content or delete the slide.`,
            icon: 'warning',
            position: 'top'
          });
          currentSlideIndex.value = sIdx;
          return false;
       }
    }
  }
  return true;
};

// Enhanced change detection that works with slide-level tracking
let changeDetectionTimeout = null;
let lastChangeDetectionTime = 0;

const checkForChanges = () => {
  const currentTime = Date.now();
  
  // Throttle change detection to prevent excessive calls
  if (currentTime - lastChangeDetectionTime < 500) {
    return;
  }
  
  lastChangeDetectionTime = currentTime;
  
  if (!lastSavedData.value) {
    hasUnsavedChanges.value = true;
    return;
  }
  
  try {
    // Check for slide-level changes
    slides.value.forEach(updateSlideChangeStatus);
    
    // Overall change detection using slide change status
    const hasSlideChanges = slides.value.some(slide => 
      slide.hasChanges || isSlideChanged(slide)
    );
    
    // Check presentation-level changes
    const presentationChanged = (
      presentation.value.name !== lastSavedData.value.presentation.name ||
      presentation.value.description !== lastSavedData.value.presentation.description ||
      presentation.value.grade_id !== lastSavedData.value.presentation.grade_id ||
      presentation.value.quiz_id !== lastSavedData.value.presentation.quiz_id
    );
    
    hasUnsavedChanges.value = hasSlideChanges || presentationChanged;
    
    if (hasUnsavedChanges.value) {
      console.log(`Detected unsaved changes: ${changedSlidesCount.value} slides changed, presentation changed: ${presentationChanged}`);
    }
  } catch (error) {
    console.warn('Error in change detection:', error);
    hasUnsavedChanges.value = true;
  }
};

// Utility function for deep equality comparison
function deepEqual(obj1, obj2) {
  if (obj1 === obj2) return true;
  
  if (obj1 == null || obj2 == null) return false;
  if (typeof obj1 !== 'object' || typeof obj2 !== 'object') return false;
  
  const keys1 = Object.keys(obj1);
  const keys2 = Object.keys(obj2);
  
  if (keys1.length !== keys2.length) return false;
  
  for (let key of keys1) {
    if (!keys2.includes(key)) return false;
    if (!deepEqual(obj1[key], obj2[key])) return false;
  }
  
  return true;
}

// Highly debounced change detection to prevent excessive checking
const debouncedCheckForChanges = () => {
  if (changeDetectionTimeout) {
    clearTimeout(changeDetectionTimeout);
  }
  
  changeDetectionTimeout = setTimeout(() => {
    checkForChanges();
  }, 1500); // Increased to 1.5 seconds for better performance
};

// Watch for changes with optimized debouncing
watch([presentation, slides], () => {
  debouncedCheckForChanges();
}, { 
  deep: true,
  flush: 'post' // Run after DOM updates
});

// Initialize slide change tracking when slides are loaded or created
watch(slides, (newSlides) => {
  if (newSlides && newSlides.length > 0) {
    // Only initialize tracking for slides that don't have it yet
    newSlides.forEach(slide => {
      if (slide._initialState === undefined) {
        slide._initialState = slide.id ? JSON.parse(JSON.stringify({
          slide_type: slide.slide_type,
          slide_content: slide.slide_content,
          section: slide.section,
          order_index: slide.order_index
        })) : null;
        slide.hasChanges = !slide.id || true; // New slides are considered changed
      }
    });
  }
}, { deep: true, immediate: true });

const createNewLesson = () => {
  window.location.href = '/lesson-presentation/edit';
};

const duplicateLesson = async () => {
  if (!activeId.value) return;
  
  // Prevent multiple simultaneous duplications
  if (isSaving.value) {
    $q.notify({
      type: 'warning',
      message: 'Please wait for current operation to complete',
      position: 'top',
      timeout: 2000
    });
    return;
  }

  try {
    $q.notify({
      type: 'info',
      message: 'Duplicating lesson...',
      icon: 'content_copy',
      position: 'top',
      timeout: 1000
    });

    // Create a copy of current presentation
    const duplicateData = {
      ...presentation.value,
      name: `${presentation.value.name} (Copy)`,
      school_id: props.defaultContext.school_id,
      teacher_id: props.defaultContext.teacher_id,
      subject_id: props.defaultContext.subject_id,
    };

    const response = await axios.post(route('lesson-presentation.store'), duplicateData);
    const newPresentation = response.data;

    // Copy all slides using bulk operation
    const slidesData = slides.value.map(slide => ({
      slide_type: slide.slide_type,
      slide_content: slide.slide_content,
      section: slide.section,
      order_index: slide.order_index || 0
    }));

    await axios.put(route('lesson-presentation.slides.bulk-update', { id: newPresentation.id }), {
      slides: slidesData
    });

    $q.notify({
      type: 'positive',
      message: 'Lesson duplicated successfully!',
      icon: 'check_circle',
      position: 'top',
      timeout: 2000
    });

    // Navigate to the new lesson
    setTimeout(() => {
      window.location.href = `/lesson-presentation/edit?id=${newPresentation.id}`;
    }, 500);
  } catch (error) {
    console.error('Duplicate failed:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to duplicate lesson',
      icon: 'error',
      position: 'top',
      timeout: 3000
    });
  }
};

// Enhanced auto-save with better interval and conditions
const startAutoSave = () => {
  if (!autoSaveEnabled.value) return;
  
  stopAutoSave(); // Clear any existing timer
  
  // Use much longer interval and stricter conditions
  autoSaveTimer.value = setInterval(async () => {
    const currentTime = Date.now();
    
    // Only auto-save if:
    // 1. There are actual changes
    // 2. Not currently saving
    // 3. Not auto-saving already
    // 4. Sufficient time has passed since last manual save
    if (hasUnsavedChanges.value && 
        !isSaving.value && 
        !isAutoSaving.value) {
      
      try {
        console.log('Starting auto-save...');
        await savePresentation(true); // true indicates auto-save
      } catch (error) {
        console.error('Auto-save failed:', error);
        // Don't retry immediately, let the next interval handle it
      }
    }
  }, 300000); // Increased to 5 minutes (300,000 ms)
  
  autoSaveStatus.value = 'Auto-save every 5 minutes';
  console.log('Auto-save initialized with 5-minute interval');
};

const stopAutoSave = () => {
  if (autoSaveTimer.value) {
    clearInterval(autoSaveTimer.value);
    autoSaveTimer.value = null;
    console.log('Auto-save stopped');
  }
};

const toggleAutoSave = () => {
  autoSaveEnabled.value = !autoSaveEnabled.value;
  
  if (autoSaveEnabled.value) {
    startAutoSave();
    $q.notify({
      type: 'info',
      message: 'Auto-save enabled',
      position: 'bottom-right',
      timeout: 1000
    });
  } else {
    stopAutoSave();
    autoSaveStatus.value = 'Auto-save disabled';
    $q.notify({
      type: 'info',
      message: 'Auto-save disabled',
      position: 'bottom-right',
      timeout: 1000
    });
  }
};

// Enhanced save presentation that tracks slide-level changes
const savePresentation = async (isAutoSave = false) => {
  // Prevent multiple simultaneous saves
  if (isSaving.value || (isAutoSave && isAutoSaving.value)) {
    console.log('Save already in progress, skipping...');
    return;
  }

  if (!validatePresentation()) return;

  const startTime = Date.now();
  isSaving.value = true;
  if (isAutoSave) {
    isAutoSaving.value = true;
    autoSaveStatus.value = 'Saving...';
  }
  saveProgress.value.visible = true;
  saveProgress.value.percentage = 0;
  saveProgress.value.message = isAutoSave ? 'Auto-saving lesson...' : 'Saving lesson...';

  try {
    // Step 1: Save presentation metadata (30%)
    saveProgress.value.percentage = 0.3;
    saveProgress.value.message = 'Saving lesson information...';
    
    const payload = {
      ...presentation.value,
      school_id: props.defaultContext.school_id,
      teacher_id: props.defaultContext.teacher_id,
      subject_id: props.defaultContext.subject_id,
      grade_id: presentation.value.grade_id || props.defaultContext.grade_id,
    };

    let response;
    if (activeId.value) {
      response = await axios.put(route('lesson-presentation.update', { id: activeId.value }), payload);
    } else {
      response = await axios.post(route('lesson-presentation.store'), payload);
      activeId.value = response.data.id;
    }

    const savedPresentation = response.data;
    
    // Step 2: Save slides using bulk operation (60%)
    saveProgress.value.percentage = 0.4;
    saveProgress.value.message = `Saving ${slides.value.length} slides...`;

    // Prepare slides data for bulk operation with change tracking
    const slidesData = slides.value.map(slide => ({
      id: slide.id || null,
      slide_type: slide.slide_type,
      slide_content: slide.slide_content || {},
      section: slide.section || currentSection.value || 'learn',
      order_index: slide.order_index || 0
    }));

    // Use bulk update endpoint to minimize HTTP requests
    await axios.put(route('lesson-presentation.slides.bulk-update', { id: savedPresentation.id }), {
      slides: slidesData
    });

    // Final step (10%)
    saveProgress.value.percentage = 1;
    saveProgress.value.message = 'Save complete!';
    
    // Reset slide change tracking after successful save
    resetSlideChangeTracking();
    
    // Update last saved state for overall change detection
    lastSavedData.value = {
      presentation: {
        name: presentation.value.name,
        description: presentation.value.description,
        grade_id: presentation.value.grade_id,
        quiz_id: presentation.value.quiz_id
      },
      slides: slides.value.map(slide => ({
        id: slide.id,
        slide_type: slide.slide_type,
        slide_content: slide.slide_content,
        section: slide.section,
        order_index: slide.order_index
      }))
    };
    
    hasUnsavedChanges.value = false;

    const duration = Date.now() - startTime;
    
    if (!isAutoSave) {
      $q.notify({
        type: 'positive',
        message: `Lesson saved successfully in ${duration}ms! (${changedSlidesCount.value} slides had changes)`,
        icon: 'check_circle',
        position: 'top',
        timeout: 2000
      });
    } else {
      autoSaveStatus.value = `Last saved ${new Date().toLocaleTimeString()} (${changedSlidesCount.value} changed slides)`;
      console.log(`Auto-save completed in ${duration}ms with ${changedSlidesCount.value} changed slides`);
    }
    
  } catch (error) {
    console.error('Save failed:', error);
    saveProgress.value.message = 'Save failed!';
    
    $q.notify({
      type: 'negative',
      message: 'Failed to save lesson. Please try again.',
      icon: 'error',
      position: 'top',
      timeout: 3000
    });
  } finally {
    setTimeout(() => {
      isSaving.value = false;
      isAutoSaving.value = false;
      saveProgress.value.visible = false;
    }, isAutoSave ? 500 : 1500);
  }
};

// Export lesson as JSON file
const exportLessonAsJSON = () => {
  try {
    // Create descriptive filename
    const lessonName = presentation.value.name || 'Untitled_Lesson';
    const date = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
    const sanitizedName = lessonName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
    const filename = `lesson_${sanitizedName}_${date}.json`;
    
    // Prepare export data
    const exportData = {
      version: '1.0',
      exportDate: new Date().toISOString(),
      lesson: {
        name: presentation.value.name,
        description: presentation.value.description,
        grade_id: presentation.value.grade_id,
        quiz_id: presentation.value.quiz_id,
      },
      sections: sections.value,
      slides: slides.value,
      metadata: {
        subject_name: props.defaultContext?.subject_name,
        grade_name: props.defaultContext?.grade_name,
        totalSlides: slides.value.length,
      }
    };
    
    // Create and download file
    const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
    
    $q.notify({
      type: 'positive',
      message: `Lesson exported as ${filename}`,
      icon: 'download',
      position: 'top',
      timeout: 2000
    });
  } catch (error) {
    console.error('Export failed:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to export lesson',
      icon: 'error',
      position: 'top'
    });
  }
};

// Import lesson from JSON file
const importLessonFromJSON = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.json';
  
  input.onchange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    try {
      const text = await file.text();
      const data = JSON.parse(text);
      
      // Validate data structure
      if (!data.lesson || !data.slides) {
        throw new Error('Invalid lesson file format');
      }
      
      // Confirm import
      $q.dialog({
        title: 'Import Lesson',
        message: `Import "${data.lesson.name}"? This will replace current unsaved changes.`,
        cancel: true,
        persistent: true,
        ok: {
          label: 'Import',
          color: 'primary'
        }
      }).onOk(() => {
        // Load lesson data
        presentation.value.name = data.lesson.name;
        presentation.value.description = data.lesson.description;
        presentation.value.grade_id = data.lesson.grade_id;
        presentation.value.quiz_id = data.lesson.quiz_id;
        
        // Load slides
        slides.value = data.slides.map(slide => ({
          ...slide,
          id: null // Remove IDs so they'll be created as new
        }));
        
        // Load sections if available
        if (data.sections && data.sections.length > 0) {
          sections.value = data.sections;
          // Set first section as current
          if (!currentSection.value && sections.value.length > 0) {
            currentSection.value = sections.value[0].id;
          }
        }
        
        $q.notify({
          type: 'positive',
          message: `Lesson "${data.lesson.name}" imported successfully!`,
          icon: 'upload',
          position: 'top',
          timeout: 2000
        });
      });
    } catch (error) {
      console.error('Import failed:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to import lesson. Please check the file format.',
        icon: 'error',
        position: 'top',
        timeout: 3000
      });
    }
  };
  
  input.click();
};

// AI Lesson Plan Generator Methods
const openAILessonGenerator = () => {
  if (!presentation.value.name || presentation.value.name === 'New Lesson') {
    $q.notify({
      type: 'warning',
      message: 'Please set a lesson title first',
      icon: 'warning',
      position: 'top'
    });
    return;
  }
  
  aiLessonGenerator.value?.open();
};

const handleAIPlanAccepted = (plan) => {
  // Add all generated slides to the slides array with change tracking
  let addedCount = 0;
  
  plan.sections.forEach(section => {
    section.slides.forEach(slideData => {
      const newSlide = initializeSlideWithTracking({
        slide_type: slideData.slide_type,
        slide_content: slideData.slide_content,
        section: section.sectionId
      });
      // Mark AI-generated slides as changed
      newSlide.hasChanges = true;
      slides.value.push(newSlide);
      addedCount++;
    });
  });
  
  $q.notify({
    type: 'positive',
    message: `Successfully added ${addedCount} slides from AI! Don't forget to save.`,
    icon: 'check_circle',
    position: 'top',
    timeout: 3000
  });
  
  // Switch to the first section that has slides
  if (plan.sections.length > 0) {
    currentSection.value = plan.sections[0].sectionId;
    currentSlideIndex.value = 0;
  }
  
  // Mark overall lesson as having changes
  hasUnsavedChanges.value = true;
};

// Print Lesson Plan
const openPrintDialog = () => {
  printLessonPlan.value?.open();
};


const fetchPresentation = async (id) => {
  try {
    const response = await axios.get(route('lesson-presentation.show', { id }));
    presentation.value = {
      name: response.data.name,
      description: response.data.description,
      grade_id: response.data.grade_id,
      quiz_id: response.data.quiz_id,
    };
    
    // Initialize slides with change tracking
    slides.value = (response.data.slides || []).map(slide => 
      initializeSlideWithTracking(slide)
    );
    
    // Initialize saved state after fetching
    lastSavedData.value = {
      presentation: JSON.parse(JSON.stringify(presentation.value)),
      slides: JSON.parse(JSON.stringify(slides.value))
    };
    hasUnsavedChanges.value = false;
    
    console.log(`Loaded lesson with ${slides.value.length} slides, all marked as unchanged`);
    
  } catch (error) {
    console.error('Fetch failed:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load lesson',
      icon: 'error',
      position: 'top'
    });
  }
};

watch(currentSection, () => {
  currentSlideIndex.value = 0;
});

onMounted(async () => {
  await teacherStore.fetchTeacherData();
  
  // Add keyboard navigation
  window.addEventListener('keydown', handleKeyboard);
  
  if (activeId.value) {
    await fetchPresentation(activeId.value);
    // Set first section as default if sections exist
    if (sections.value.length > 0 && !currentSection.value) {
      currentSection.value = sections.value[0].id;
    }
  } else {
    if (slides.value.length === 0) {
      addSlide();
    }
    
    // Set first section as default
    if (sections.value.length > 0) {
      currentSection.value = sections.value[0].id;
    }
    
    // Priority 1: Use grade_id passed from URL (via defaultContext)
    if (props.defaultContext.grade_id) {
        presentation.value.grade_id = parseInt(props.defaultContext.grade_id);
    } 
    // Priority 2: Fallback to first grade in store
    else if (teacherStore.grades.length > 0) {
      presentation.value.grade_id = teacherStore.grades[0].id;
    }
  }
  
  // Initialize auto-save
  if (autoSaveEnabled.value) {
    startAutoSave();
  }
  
  // Store initial state
  lastSavedData.value = {
    presentation: JSON.parse(JSON.stringify(presentation.value)),
    slides: JSON.parse(JSON.stringify(slides.value))
  };
});

// Cleanup keyboard listener and timeouts on unmount
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyboard);
  stopAutoSave();
  
  // Clear any pending timeouts
  if (changeDetectionTimeout) {
    clearTimeout(changeDetectionTimeout);
  }
  
  if (autoSaveTimer.value) {
    clearInterval(autoSaveTimer.value);
  }
});

// Add scroll to top functionality
const scrollToTop = () => {
  const pageContainer = document.querySelector('.q-page');
  if (pageContainer) {
    pageContainer.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// Enhanced section switching with auto scroll to top
const switchToSection = (sectionId) => {
  if (sectionId !== currentSection.value) {
    currentSection.value = sectionId;
    currentSlideIndex.value = 0;
    // Scroll to top when switching sections
    scrollToTop();
  }
};

// Watch for section changes to trigger scroll to top
watch(currentSection, (newSection, oldSection) => {
  if (newSection !== oldSection) {
    // Small delay to ensure DOM updates before scrolling
    setTimeout(() => {
      scrollToTop();
    }, 100);
  }
});
</script>

<style scoped>
/* Add any specific styles here */

.saving-progress-bar {
  height: 24px !important;
  position: relative;
}

.saving-progress-bar .q-linear-progress__model {
  transition: width 0.3s ease;
}

.saving-progress-bar .absolute-full {
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

/* Auto-save indicator animations */
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.saving-progress-bar :deep(.q-linear-progress__model--with-transition) {
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.auto-save-indicator.pulse {
  animation: pulse 2s infinite;
}
</style>
