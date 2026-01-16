<template>
  <Head :title="presentation.name ? `Editing: ${presentation.name}` : 'New Lesson'" />
  <q-layout view="hHh Lpr fFf" class="bg-grey-1">
    
    <!-- Colorful Header -->
    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="leftDrawerOpen = !leftDrawerOpen" />
        
        <q-btn flat round dense icon="arrow_back" component="a" href="/lesson-presentation/dashboard">
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

            <q-btn flat round dense icon="content_copy" @click="duplicateLesson" v-if="activeId">
               <q-tooltip>Duplicate</q-tooltip>
            </q-btn>
            
            <q-separator vertical dark class="q-mx-sm" />
            
            <q-btn flat round dense icon="download" @click="exportLessonAsJSON" color="accent">
               <q-tooltip>Export Lesson as JSON</q-tooltip>
            </q-btn>
            <q-btn flat round dense icon="upload" @click="importLessonFromJSON" color="secondary">
               <q-tooltip>Import Lesson from JSON</q-tooltip>
            </q-btn>
            
            <q-separator vertical dark class="q-mx-sm" />
            
            <q-btn flat round dense icon="visibility" @click="showPreview = true" :disable="!activeId">
               <q-tooltip>Preview</q-tooltip>
            </q-btn>
            <q-btn unelevated color="positive" icon="save" label="Save" @click="savePresentation" :loading="isSaving" />
        </div>
      </q-toolbar>
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
      <q-page class="q-pa-md bg-grey-2 row justify-center">
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
         </div>
      </q-page>
    </q-page-container>

  </q-layout>

  <!-- Preview Dialog -->
  <q-dialog v-model="showPreview" maximized>
    <q-card class="bg-white">
      <q-card-section class="row items-center q-pb-none bg-primary text-white">
        <div class="text-h6 flex items-center gap-2">
          <q-icon name="visibility" size="28px" />
          Student View Preview
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-pa-none" style="height: calc(100vh - 60px)">
        <LessonPlayer
          :presentation="{ ...presentation, name: 'Preview: ' + presentation.name }"
          :sections="sections"
          :slides="slides"
          :is-preview="true"
        />
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
import { useTeacherStore } from '@/Stores/teacherStore';

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
const showPreview = ref(false);
const showSectionsDrawerRaw = ref(true); // Closed by default on mobile, show-if-above handles desktop
const showSlideListDialog = ref(true);

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

const addSlide = () => {
  const newSlide = {
    slide_type: 'text',
    slide_content: {},
    section: currentSection.value // Assign to current section
  };
  slides.value.push(newSlide);
  // Set index to the last slide in the filtered list
  currentSlideIndex.value = filteredSlides.value.length - 1;
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

// Keyboard navigation
const handleKeyboard = (e) => {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
  
  if (e.key === 'ArrowLeft') {
    e.preventDefault();
    previousSlide();
  } else if (e.key === 'ArrowRight') {
    e.preventDefault();
    nextSlide();
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

const savePresentation = async () => {
  if (!validatePresentation()) return;

  isSaving.value = true;
  try {
    const payload = {
      ...presentation.value,
      // Ensure we send necessary fields for validation
      school_id: props.defaultContext.school_id,
      teacher_id: props.defaultContext.teacher_id,
      subject_id: props.defaultContext.subject_id,
      grade_id: presentation.value.grade_id || props.defaultContext.grade_id,
    };

    let response;
    if (activeId.value) {
      // Update existing
      response = await axios.put(route('lesson-presentation.update', { id: activeId.value }), payload);
    } else {
      // Create new
      response = await axios.post(route('lesson-presentation.store'), payload);
    }

    const savedPresentation = response.data;
    
    // Now save slides
    // Strategy: Delete all and recreate? Or update one by one?
    // For simplicity in this prototype, we'll update the presentation ID on slides and save them.
    // A better approach for production is a bulk sync endpoint.
    
    // For now, let's just notify success as the backend controller for 'update' doesn't handle slides bulk save yet.
    // We need to iterate and save slides if they are new or updated.
    // To keep it simple for this task, we will assume the user saves, and we might need a bulk save endpoint or loop.
    
    // Let's loop for now (inefficient but works for prototype)
    for (const slide of slides.value) {
      // Ensure all required fields are present
      const slideData = {
        slide_type: slide.slide_type,
        slide_content: slide.slide_content || {},
        section: slide.section || currentSection.value || 'learn',
        order_index: slide.order_index || 0
      };
      
      if (slide.id) {
        await axios.put(route('lesson-presentation.slides.update', { id: savedPresentation.id, slideId: slide.id }), slideData);
      } else {
        await axios.post(route('lesson-presentation.slides.add', { id: savedPresentation.id }), slideData);
      }
    }

    $q.notify({
      type: 'positive',
      message: 'Lesson saved successfully! Redirecting...',
      icon: 'check_circle',
      position: 'top',
      timeout: 1500
    });
    
    // Redirect to Dashboard
    setTimeout(() => {
        window.location.href = '/lesson-presentation/dashboard';
    }, 1500);
  } catch (error) {
    console.error('Save failed:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to save lesson. Please try again.',
      icon: 'error',
      position: 'top',
      timeout: 3000
    });
  } finally {
    isSaving.value = false;
  }
};

const createNewLesson = () => {
  window.location.href = '/lesson-presentation/edit';
};

const duplicateLesson = async () => {
  if (!activeId.value) return;
  
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

    // Copy all slides
    for (const slide of slides.value) {
      const slideData = {
        slide_type: slide.slide_type,
        slide_content: slide.slide_content,
        section: slide.section
      };
      await axios.post(route('lesson-presentation.slides.add', { id: newPresentation.id }), slideData);
    }

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


const fetchPresentation = async (id) => {
  try {
    const response = await axios.get(route('lesson-presentation.show', { id }));
    presentation.value = {
      name: response.data.name,
      description: response.data.description,
      grade_id: response.data.grade_id,
      quiz_id: response.data.quiz_id,
    };
    
    // Ensure slides have all necessary properties
    slides.value = (response.data.slides || []).map(slide => ({
      ...slide,
      section: slide.section || 'learn' // Fallback if section is missing
    }));
    
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

// Watch for section changes and reset slide index
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
});

// Cleanup keyboard listener on unmount
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyboard);
});
</script>

<style scoped>
/* Add any specific styles here */
</style>
