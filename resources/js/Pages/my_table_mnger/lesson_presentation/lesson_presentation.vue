<template>
  <div class="flex  bg-gray-50 overflow-hidden">
    <!-- Custom Sidebar (Replaces q-dialog) -->
    <!-- v-model:currentSection="currentSection" -->


    <!-- Main Area: Slide Editor -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Compact Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-3">
        <div class="flex items-center justify-between gap-4">
          <!-- Left: Lesson Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <q-btn
                flat
                dense
                round
                icon="arrow_back"
                color="grey-7"
                size="sm"
                to="/lesson-presentation"
              >
                <q-tooltip>Back to lessons</q-tooltip>
              </q-btn>
              <q-badge 
                v-if="presentation.grade_id" 
                color="primary" 
                :label="currentGradeName"
              />
              <input 
                v-model="presentation.name" 
                class="text-base font-semibold text-gray-800 border-none focus:ring-0 p-0 flex-1 bg-transparent placeholder-gray-400"
                placeholder="Lesson Name"
              />
            </div>
            <div class="flex items-center gap-2 ml-10">
              <q-icon name="description" size="14px" color="grey-5" />
              <input 
                v-model="presentation.description" 
                class="text-sm text-gray-500 border-none focus:ring-0 p-0 flex-1 bg-transparent placeholder-gray-400"
                placeholder="Add description..."
              />
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-3">
            <template-select
              v-model="selectedTemplateId"
              :templates="templates"
              @apply="async (payload) => {
                // payload: { template, overwrite }
                if (!activeId.value) {
                  // Attach template to presentation data and then save
                  presentation.value.lesson_plan_template_id = payload.template.id;
                  await savePresentation();
                } else {
                  try {
                    const response = await axios.post(route('lesson-presentation.apply-template', { id: activeId.value }), { lesson_plan_template_id: payload.template.id, overwrite: payload.overwrite });
                    // The endpoint returns the updated presentation JSON (with slides)
                    const respPresentation = response.data.presentation ?? response.data;
                    if (respPresentation.slides) {
                      slides.value = respPresentation.slides;
                    }
                    if (respPresentation.name) {
                      presentation.value = { ...presentation.value, ...respPresentation };
                    }
                    $q.notify({ type: 'positive', message: 'Template applied successfully' });
                  } catch (err) {
                    console.error('Apply template failed', err);
                    $q.notify({ type: 'negative', message: 'Failed to apply template' });
                  }
                }
              }"
            />

            <!-- Save as Template Button -->
            <q-btn
              dense
              outline
              color="secondary"
              icon="save_alt"
              label="Save as Template"
              size="sm"
              @click="templateDialogOpen = true"
            />

            <!-- Save Template Dialog -->
            <q-dialog v-model="templateDialogOpen" persistent>
              <q-card>
                <q-card-section class="row items-center q-pb-none">
                  <div class="text-h6">Save as Template</div>
                  <q-space />
                  <q-btn icon="close" flat round dense v-close-popup @click="templateDialogOpen = false" />
                </q-card-section>

                <q-card-section>
                  <q-form @submit.prevent="saveAsTemplate">
                    <q-input
                      v-model="newTemplate.name"
                      label="Template Name"
                      outlined
                      :rules="[v => !!v || 'Template name is required']"
                    />

                    <q-input
                      v-model="newTemplate.description"
                      label="Description"
                      type="textarea"
                      outlined
                      class="q-mt-md"
                    />

                    <q-select
                      v-model="newTemplate.scope"
                      :options="[{label: 'Subject', value: 'subject'}, {label: 'Global', value: 'global'}]"
                      label="Scope"
                      outlined
                      class="q-mt-md"
                    />

                    <div class="row items-center justify-end q-mt-md">
                      <q-btn flat label="Cancel" color="grey" v-close-popup @click="templateDialogOpen = false" />
                      <q-btn unelevated color="primary" type="submit" :loading="isSavingTemplate" class="q-ml-sm">Save</q-btn>
                    </div>
                  </q-form>
                </q-card-section>
              </q-card>
            </q-dialog>
            <!-- Quiz Selector -->
            <QuizSelector
              v-model="presentation.quiz_id"
              :school-id="defaultContext.school_id"
              :grade-id="presentation.grade_id"
              :subject-id="defaultContext.subject_id"
            />
            
            <div class="h-6 w-px bg-gray-300"></div>
            
            <q-btn
              v-if="activeId"
              flat
              dense
              round
              icon="content_copy"
              color="grey-7"
              size="sm"
              @click="duplicateLesson"
            >
              <q-tooltip>Duplicate</q-tooltip>
            </q-btn>
            <q-btn
              outline
              dense
              icon="visibility"
              label="Preview"
              color="primary"
              size="sm"
              @click="showPreview = true"
              :disable="!activeId"
            >
              <q-tooltip v-if="!activeId">Save first</q-tooltip>
            </q-btn>
            <q-btn
              unelevated
              dense
              icon="save"
              label="Save"
              color="positive"
              size="sm"
              @click="savePresentation"
              :loading="isSaving"
            />
          </div>
        </div>
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
  <!-- @deleteSlide="deleteSlide" -->
</div>

      
      <!-- Editor Content -->
      <div   v-if="currentSection"    class="flex-1 overflow-y-auto p-6 bg-gray-50 flex justify-center">
        <div class="w-full max-w-4xl">
          <div v-if="currentSlide" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <!-- Slide Type Selector -->
            <div class="mb-4 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <q-icon name="edit_note" size="24px" color="primary" />
                <h3 class="text-base font-semibold text-gray-700">Slide Editor</h3>
                <q-btn
                  round
                  dense
                  color="negative"
                  icon="delete"
                  size="sm"
                  @click="deleteSlide(currentSlide)"
                >
                  <q-tooltip>Delete Slide</q-tooltip>
                </q-btn>
              </div>
              <select 
                v-model="currentSlide.slide_type"
                class="border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2"
              >
                <option value="text">📝 Text</option>
                <option value="image">🖼️ Image</option>
                <option value="video">🎥 Video</option>
                <option value="audio">🎵 Audio</option>
                <option value="pdf">📄 PDF</option>
                <option value="question">❓ Question</option>
              </select>
            </div>

            <!-- Slide Content -->
            <div class="min-h-[500px]">
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
          
          <!-- Empty State -->
          <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <q-icon name="layers_clear" size="4rem" color="grey-4" />
            <p class="text-gray-500 text-lg mt-4 mb-2">No Slide Selected</p>
            <p class="text-gray-400 text-sm">Choose a section and add a slide to get started</p>
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
import QuizSelector from './components/QuizSelector.vue';
import TemplateSelect from '@/Components/templates/TemplateSelect.vue';
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
const currentSection_data = ref(null);
const slides = ref([]);
const currentSlideIndex = ref(0);
const isSaving = ref(false);
const templates = ref([]);
const selectedTemplateId = ref(null);
const showPreview = ref(false);
const showSectionsDrawerRaw = ref(true); // Closed by default on mobile, show-if-above handles desktop
const showSlideListDialog = ref(true);

// Save-as-template states
const templateDialogOpen = ref(false);
const newTemplate = ref({ name: '', description: '', scope: 'subject' });
const isSavingTemplate = ref(false);

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

    // If user selected a template, include it when creating/updating
    if (selectedTemplateId.value) {
      payload.lesson_plan_template_id = selectedTemplateId.value;
    }

    let response;
    if (activeId.value) {
      // Update existing
      response = await axios.put(route('lesson-presentation.update', { id: activeId.value }), payload);
    } else {
      // Create new
      response = await axios.post(route('lesson-presentation.store'), payload);
    }

    // Save returns presentation (with slides if created via template)
    const savedPresentation = response.data.presentation ?? response.data;
    // If server returned slides, update local slides
    if (response.data.slides) {
      slides.value = response.data.slides;
    } else if (savedPresentation.slides) {
      slides.value = savedPresentation.slides;
    }
    
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
      message: 'Lesson saved successfully!',
      icon: 'check_circle',
      position: 'top',
      timeout: 2000
    });
    
    // Update activeId if it was a new presentation
    if (!activeId.value && savedPresentation.id) {
      // Update the activeId ref so preview button becomes enabled
      activeId.value = savedPresentation.id;
      
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
  
  if (activeId.value) {
    fetchPresentation(activeId.value);
  } else {
    if (slides.value.length === 0) {
      addSlide();
    }
    // Pre-select first grade if creating new
    if (teacherStore.grades.length > 0) {
      presentation.value.grade_id = teacherStore.grades[0].id;
    }
  }

  // Fetch templates for the subject (global + subject-specific)
  try {
    const subjectId = props.defaultContext.subject_id;
    if (subjectId) {
      const resp = await axios.get('/api/lesson-plan-templates', { params: { subject_id: subjectId } });
      templates.value = resp.data.data || resp.data || [];
    }
  } catch (e) {
    templates.value = [];
  }
});

/**
 * Save the current presentation as a lesson plan template
 */
const saveAsTemplate = async () => {
  if (!newTemplate.value.name || !newTemplate.value.name.trim()) {
    $q.notify({ type: 'negative', message: 'Please provide a template name' });
    return;
  }

  isSavingTemplate.value = true;
  try {
    // Build structure from current slides and sections
    const structure = { sections: [] };
    for (const s of sections.value) {
      // Count slides in this section
      const count = slides.value.filter(sl => sl.section === s.id).length;
      const defaultSlideType = slides.value.find(sl => sl.section === s.id)?.slide_type || 'text';
      structure.sections.push({ id: s.id, label: s.title || s.id, slides: count || 1, default_slide_type: defaultSlideType });
    }

    const payload = {
      name: newTemplate.value.name,
      description: newTemplate.value.description || '',
      structure: structure,
      subject_id: newTemplate.value.scope === 'subject' ? props.defaultContext.subject_id : null,
    };

    const response = await axios.post('/api/lesson-plan-templates', payload);

    const created = response.data.data || response.data;
    // Add to local templates and select
    templates.value.push(created);
    selectedTemplateId.value = created.id;

    $q.notify({ type: 'positive', message: 'Template saved successfully' });
    templateDialogOpen.value = false;
    // Clear new template
    newTemplate.value = { name: '', description: '', scope: 'subject' };
  } catch (err) {
    console.error('Save template failed', err);
    $q.notify({ type: 'negative', message: 'Failed to save template' });
  } finally {
    isSavingTemplate.value = false;
  }
};
</script>

<style scoped>
/* Add any specific styles here */
</style>
