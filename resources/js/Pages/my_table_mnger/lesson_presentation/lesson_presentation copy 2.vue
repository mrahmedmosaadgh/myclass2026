<template>
  <div class="flex  bg-gray-50 overflow-hidden">
    <!-- Custom Sidebar (Replaces q-dialog) -->
    <!-- v-model:currentSection="currentSection" -->


    <!-- Main Area: Slide Editor -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Breadcrumb Navigation -->
      <div class="bg-white border-b border-gray-200 px-6 py-3">
        <div class="flex items-center justify-between">
          <q-breadcrumbs class="text-sm">
            <q-breadcrumbs-el icon="home" label="Dashboard" to="/dashboard" />
            <q-breadcrumbs-el icon="school" label="Lessons" to="/lesson-presentation" />
            <q-breadcrumbs-el 
              icon="edit" 
              :label="activeId ? 'Edit Lesson' : 'New Lesson'" 
            />
          </q-breadcrumbs>
 
          <!-- Quick Actions -->
          <div class="flex gap-2">
            <q-btn
              flat
              dense
              icon="list"
              label="All Lessons"
              color="primary"
              size="sm"
              to="/lesson-presentation"
            >
              <q-tooltip>View all lessons</q-tooltip>
            </q-btn>
            <q-btn
              flat
              dense
              icon="add_circle"
              label="New Lesson"
              color="positive"
              size="sm"
              @click="createNewLesson"
            >
              <q-tooltip>Create a new lesson</q-tooltip>
            </q-btn>
            <q-btn
              v-if="activeId"
              flat
              dense
              icon="content_copy"
              label="Duplicate"
              color="secondary"
              size="sm"
              @click="duplicateLesson"
            >
              <q-tooltip>Duplicate this lesson</q-tooltip>
            </q-btn>
          </div>
        </div>
      </div>
 
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 mr-4">
            <input 
              v-model="presentation.name" 
              class="text-xl font-bold text-gray-800 border-none focus:ring-0 p-0 w-full bg-transparent placeholder-gray-400"
              placeholder="Untitled Lesson"
            />
            <div class="flex gap-4 mt-2">
              <input 
                v-model="presentation.description" 
                class="text-sm text-gray-500 border-none focus:ring-0 p-0 flex-1 bg-transparent placeholder-gray-400"
                placeholder="Add a description..."
              />
              <select 
                v-model="presentation.grade_id"
                class="text-sm border-gray-200 rounded-md text-gray-600 py-1 pl-2 pr-8 bg-transparent focus:ring-0 focus:border-blue-500"
              >
                <option :value="null" disabled>Select Grade</option>
                <option 
                  v-for="grade in teacherStore.grades" 
                  :key="grade.id" 
                  :value="grade.id"
                >
                  {{ grade.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="flex gap-3 pt-24">
            <q-btn
              outline
              color="primary"
              icon="visibility"
              label="Preview"
              @click="showPreview = true"
              :disable="!activeId"
            >
              <q-tooltip v-if="!activeId">Save the lesson first to preview</q-tooltip>
            </q-btn>
            <q-btn
              color="positive"
              icon="save"
              label="Save"
              @click="savePresentation"
              :loading="isSaving"
            />
          </div>
        </div>
        
        <!-- Six Section Indicators
        <div class="flex gap-3 items-center flex-wrap">
          <SectionIndicator
            :number="1"
            title="Objectives"
            subtitle="Learning goals"
            icon="🎯"
            color="amber"
          />
          
          <SectionIndicator
            :number="2"
            title="Warm-Up"
            subtitle="Starter activity"
            icon="🔥"
            color="orange"
          />
          
          <SectionIndicator
            :number="3"
            title="Learn"
            :subtitle="`${slides.length} slide(s)`"
            icon="📚"
            color="blue"
          />
          
          <SectionIndicator
            :number="4"
            title="Practice"
            subtitle="Handwritten submission"
            icon="✍️"
            color="purple"
          />
          
          <SectionIndicator
            :number="5"
            title="Homework"
            subtitle="Assignment"
            icon="📖"
            color="indigo"
          />
          
          <SectionIndicator
            :number="6"
            title="Quiz"
            subtitle="Quiz ID (optional)"
            icon="📝"
            color="green"
          >
            <template #input>
              <input 
                v-model.number="presentation.quiz_id"
                type="number"
                placeholder="Enter ID"
                class="w-20 text-xs border-green-300 rounded px-2 py-1 focus:ring-1 focus:ring-green-500 focus:border-green-500"
              />
            </template>
          </SectionIndicator>
        </div> -->
      </div>


      <!-- -------------------------------------- -->


<div class="flex w-full flex-col">

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
  />
</div>

      
      <!-- -------------------------------------- -->
      <!-- Editor Content -->
      <div class="flex-1 overflow-y-auto p-8 bg-gray-50 flex justify-center">
        <div class="w-full max-w-4xl bg-white rounded-xl shadow-sm border border-gray-200 min-h-[600px] p-2">
          <div v-if="currentSlide" class="min-h-full flex flex-col">
            <div class="mb-2 flex justify-between items-center border-b pb-1">
              <h3 class="text-lg font-medium text-gray-700">Edit Slide Content</h3>
              <select 
                v-model="currentSlide.slide_type"
                class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="text">Text Slide</option>
                <option value="image">Image Slide</option>
                <option value="video">Video Slide</option>
                <option value="audio">Audio Slide</option>
                <option value="pdf">PDF Slide</option>
                <option value="question">Question Slide</option>
              </select>
            </div>

            <div class="flex-1">
              <!-- <div v-if="currentSection_data" 
                   class="mb-4 p-3 rounded-lg border flex items-center gap-3 transition-colors duration-300"
                   :style="{ 
                     backgroundColor: currentSection_data.bg, 
                     borderColor: currentSection_data.borderColor 
                   }"
              >
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white shadow-sm"
                     :style="{ color: currentSection_data.textColor }"
                >
                  <q-icon :name="currentSection_data.qIcon || currentSection_data.icon" size="20px" />
                </div>
                <div>
                  <div class="text-xs font-bold uppercase tracking-wider opacity-70"
                       :style="{ color: currentSection_data.textColor }"
                  >
                    Current Section
                  </div>
                  <div class="text-lg font-bold leading-none"
                       :style="{ color: currentSection_data.textColor }"
                  >
                    {{ currentSection_data.title }}
                  </div>
                </div>
              </div> -->

              <component 
                :is="getSlideComponent(currentSlide.slide_type)" 
                v-model="currentSlide.slide_content"
              />
            </div>
          </div>
          
          <div v-else class="h-full flex flex-col items-center justify-center text-gray-400">
            <i class="fas fa-layer-group text-4xl mb-4"></i>
            <p>Select a slide to edit or create a new one</p>
          </div>
        </div>
      </div>
    </div>



  </div>

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
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import TextSlide from './components/slides/TextSlide.vue';
import MediaSlide from './components/slides/MediaSlide.vue';
import QuestionSlide from './components/slides/QuestionSlide.vue';
import SectionIndicator from './components/SectionIndicator.vue';
import LessonSidebar from './components/LessonSidebar.vue';
import LessonPlayer from './components/LessonPlayer.vue';
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
const activeId = props.presentationId || idFromUrl;

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

const currentSection = ref('learn'); // Default to 'learn' section
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

const getSlideComponent = (type) => {
  switch (type) {
    case 'text': return TextSlide;
    case 'question': return QuestionSlide;
    case 'image':
    case 'video':
    case 'audio':
    case 'pdf':
      return MediaSlide;
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

const stripHtml = (html) => {
  const tmp = document.createElement('DIV');
  tmp.innerHTML = html || '';
  return tmp.textContent || tmp.innerText || '';
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
       if (!stripHtml(slide.slide_content?.text).trim()) {
          $q.notify({
            type: 'warning',
            message: `Slide ${sIdx + 1}: Text content cannot be empty`,
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
    if (activeId) {
      // Update existing
      response = await axios.put(route('lesson-presentation.update', { id: activeId }), payload);
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
      if (slide.id) {
        await axios.put(route('lesson-presentation.slides.update', { id: savedPresentation.id, slideId: slide.id }), slide);
      } else {
        await axios.post(route('lesson-presentation.slides.add', { id: savedPresentation.id }), slide);
      }
    }

    $q.notify({
      type: 'positive',
      message: 'Lesson saved successfully!',
      icon: 'check_circle',
      position: 'top',
      timeout: 2000
    });
    
    // Update activeId if it was a new presentation
    if (!activeId && savedPresentation.id) {
      // Update URL without page reload
      const newUrl = new URL(window.location);
      newUrl.searchParams.set('id', savedPresentation.id);
      window.history.pushState({}, '', newUrl);
    }
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
  if (!activeId) return;
  
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
  
  if (activeId) {
    fetchPresentation(activeId);
  } else {
    if (slides.value.length === 0) {
      addSlide();
    }
    // Pre-select first grade if creating new
    if (teacherStore.grades.length > 0) {
      presentation.value.grade_id = teacherStore.grades[0].id;
    }
  }
});
</script>

<style scoped>
/* Add any specific styles here */
</style>
