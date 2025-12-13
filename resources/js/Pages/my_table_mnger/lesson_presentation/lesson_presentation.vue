<template>
  <Head :title="pageTitle" />
  <div class="flex  bg-gray-50 overflow-hidden">
    <!-- Custom Sidebar -->
    <!-- Props removed as Sidebar now uses store directly -->
    store.sections:{{ store.sections }} <br>
    selectedTemplate:{{ store.selectedTemplate.structure }} <br>
    <div class="flex w-full flex-col max-w-[280px] border-r border-gray-200 bg-white">
      <LessonSidebar
        v-model:showDrawer="showSectionsDrawer"
        :can-edit="true"
      />
    </div>

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
                @click="navigateBack"
              >
                <q-tooltip>Back to lessons</q-tooltip>
              </q-btn>
              <q-badge 
                v-if="store.presentation.grade_id" 
                color="primary" 
                :label="currentGradeName"
              />
              <q-badge 
                v-if="store.presentation.subject_id" 
                color="secondary" 
                :label="currentSubjectName"
              />
              <input 
                v-model="store.presentation.name" 
                class="text-base font-semibold text-gray-800 border-none focus:ring-0 p-0 flex-1 bg-transparent placeholder-gray-400"
                placeholder="Lesson Name"
              />
            </div>
            <div class="flex items-center gap-2 ml-10">
              <q-icon name="description" size="14px" color="grey-5" />
              <input 
                v-model="store.presentation.description" 
                class="text-sm text-gray-500 border-none focus:ring-0 p-0 flex-1 bg-transparent placeholder-gray-400"
                placeholder="Add description..."
              />
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-3">
            <div class="h-6 w-px bg-gray-300"></div>
            
            <q-btn
              v-if="store.presentation.id"
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
              :disable="!store.presentation.id"
            >
              <q-tooltip v-if="!store.presentation.id">Save first</q-tooltip>
            </q-btn>
            <q-btn
              unelevated
              dense
              icon="save"
              label="Save"
              color="positive"
              size="sm"
              @click="handleSave"
              :loading="store.isSaving"
            />
          </div>
        </div>
      </div>


      <!-- Editor Content -->
      <div v-if="store.currentSectionId" class="flex-1 overflow-y-auto p-6 bg-gray-50 flex justify-center">
        <div class="w-full max-w-4xl">
          <div v-if="store.activeSlide" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
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
                  @click="store.deleteSlide(store.activeSlide)"
                >
                  <q-tooltip>Delete Slide</q-tooltip>
                </q-btn>
              </div>
              <select 
                v-model="store.activeSlide.slide_type"
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
              <component 
                :is="getSlideComponent(store.activeSlide.slide_type)" 
                v-model="store.activeSlide.slide_content"
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
          :presentation="{ ...store.presentation, name: 'Preview: ' + store.presentation.name }"
          :slides="store.slides"
          :is-preview="true"
          />
          <!-- :sections="store.sections" -->
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import TextSlide from './components/slides/TextSlide.vue';
import MediaSlide from './components/slides/MediaSlide.vue';
import QuestionSlide from './components/slides/QuestionSlide.vue';
import LessonSidebar from './components/LessonSidebar.vue';
import LessonPlayer from './components/LessonPlayer.vue';
import { useTeacherStore } from '@/Stores/teacherStore';
import { useLessonPresentationStore } from '@/Stores/lessonPresentationStore';

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
  // sections: {
  //   type: Array,
  //   default: () => []
  // }
});

const teacherStore = useTeacherStore();
const store = useLessonPresentationStore();
const $q = useQuasar();

const showPreview = ref(false);
const showSectionsDrawerRaw = ref(true);

// Computed property to make dialog always hidden on small screens
const showSectionsDrawer = computed({
  get() {
    return $q.screen.lt.sm ? false : showSectionsDrawerRaw.value;
  },
  set(value) {
    showSectionsDrawerRaw.value = value;
  }
});

const currentGradeName = computed(() => {
  if (!store.presentation.grade_id) return 'No Grade';
  const grade = teacherStore.grades.find(g => g.id === store.presentation.grade_id);
  return grade ? grade.name : 'Unknown Grade';
});

const currentSubjectName = computed(() => {
  if (!store.presentation.subject_id) return 'No Subject';
  // Try to find subject from the teacher store grades
  for (const grade of teacherStore.grades) {
    if (grade.subjects) {
      const subject = grade.subjects.find(s => s.id === store.presentation.subject_id);
      if (subject) return subject.name;
    }
  }
  return 'Unknown Subject';
});

const pageTitle = computed(() => {
  if (store.presentation.name && store.presentation.name !== 'New Lesson') {
    return `Edit: ${store.presentation.name}`;
  }
  return 'Create New Lesson';
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

const stripHtml = (html) => {
  const tmp = document.createElement('DIV');
  tmp.innerHTML = html || '';
  return tmp.textContent || tmp.innerText || '';
};

// Validation Logic (Keep in component for now as it's UI-heavy)
const validatePresentation = () => {
  if (!store.presentation.name.trim()) {
    $q.notify({ type: 'warning', message: 'Please enter a lesson name', position: 'top' });
    return false;
  }
  
  if (!store.presentation.grade_id) {
    $q.notify({ type: 'warning', message: 'Please select a grade', position: 'top' });
    return false;
  }

  // Helper to find index in global slides
  const slides = store.slides;
  
  for (let sIdx = 0; sIdx < slides.length; sIdx++) {
    const slide = slides[sIdx];
    // We can focus the slide if invalid
    
    if (slide.slide_type === 'question') {
      const questions = slide.slide_content?.questions || [];
      if (questions.length === 0) {
        $q.notify({ type: 'warning', message: `Slide ${sIdx + 1}: Please add at least one question`, position: 'top' });
        store.activeSlideId = slide.id || slide._tempId;
        return false;
      }

      for (let qIdx = 0; qIdx < questions.length; qIdx++) {
        const q = questions[qIdx];
        if (!stripHtml(q.text).trim()) {
          $q.notify({ type: 'warning', message: `Slide ${sIdx + 1}, Q${qIdx + 1}: Question text empty`, position: 'top' });
          store.activeSlideId = slide.id || slide._tempId;
          return false;
        }

        if (['short_answer', 'number'].includes(q.type) && !q.correct_answer) {
          $q.notify({ type: 'warning', message: `Slide ${sIdx + 1}, Q${qIdx + 1}: Missing correct answer`, position: 'top' });
          store.activeSlideId = slide.id || slide._tempId;
          return false;
        }

        if (q.type === 'single_choice' && !q.correct_answer) {
           $q.notify({ type: 'warning', message: `Slide ${sIdx + 1}, Q${qIdx + 1}: Select correct option`, position: 'top' });
           store.activeSlideId = slide.id || slide._tempId;
           return false;
        }

        if (['single_choice', 'multiple_choice'].includes(q.type)) {
          if (!q.options || q.options.length < 2) {
            $q.notify({ type: 'warning', message: `Slide ${sIdx + 1}, Q${qIdx + 1}: Needs 2+ options`, position: 'top' });
            store.activeSlideId = slide.id || slide._tempId;
            return false;
          }

          for (let oIdx = 0; oIdx < q.options.length; oIdx++) {
            if (!stripHtml(q.options[oIdx].text).trim()) {
              $q.notify({ type: 'warning', message: `Slide ${sIdx + 1}, Q${qIdx + 1}, Op${oIdx + 1}: Text empty`, position: 'top' });
              store.activeSlideId = slide.id || slide._tempId;
              return false;
            }
          }
        }
      }
    } else if (slide.slide_type === 'text') {
       if (slide.slide_content?.text && !stripHtml(slide.slide_content.text).trim()) {
          $q.notify({ type: 'warning', message: `Slide ${sIdx + 1}: Text content empty`, position: 'top' });
          store.activeSlideId = slide.id || slide._tempId;
          return false;
       }
    }
  }
  return true;
};

const handleSave = async () => {
  if (!validatePresentation()) return;
  
  try {
      await store.save();
      $q.notify({ type: 'positive', message: 'Lesson saved!', position: 'top' });
  } catch (e) {
      $q.notify({ type: 'negative', message: 'Failed to save', position: 'top' });
  }
};

const duplicateLesson = async () => {
  if (!store.presentation.id) return;
  
  try {
    $q.notify({ type: 'info', message: 'Duplicating...', timeout: 1000 });

    const duplicateData = {
      ...store.presentation,
      name: `${store.presentation.name} (Copy)`,
      slides: store.slides
    };

    const response = await axios.post(route('lesson-presentation.store'), duplicateData);
    const newId = response.data.id;

    $q.notify({ type: 'positive', message: 'Duplicated!', position: 'top' });
    
    setTimeout(() => {
      window.location.href = `/lesson-presentation/edit?id=${newId}`;
    }, 500);
  } catch (error) {
    console.error('Duplicate failed:', error);
    $q.notify({ type: 'negative', message: 'Failed to duplicate', position: 'top' });
  }
};

const navigateBack = () => {
  const gradeId = store.presentation.grade_id || props.defaultContext.grade_id;
  const subjectId = props.defaultContext.subject_id;
  
  if (gradeId && subjectId) {
    router.visit(route('lesson-presentation.index'), {
      data: { grade_id: gradeId, subject_id: subjectId }
    });
  } else {
    router.visit(route('lesson-presentation.index'));
  }
};

onMounted(async () => {
  await teacherStore.fetchTeacherData();
  
  // Get ID from URL or Props (store init will handle fetching if ID provided)
  const urlParams = new URLSearchParams(window.location.search);
  const idFromUrl = urlParams.get('id');
  const initialId = props.presentationId || (idFromUrl ? parseInt(idFromUrl) : null);
  
  // Initialize store with context and potentially existing presentation ID
  store.init({
      sections: store.selectedTemplate.structure || [],
      defaultContext: props.defaultContext,
      presentation: initialId ? { id: initialId } : null
  });
});
</script>
