<template>
  <div class="h-screen flex bg-gray-50">
    <!-- Sidebar Navigation -->
    <!-- :sections="sections" -->
    <LessonSidebar
      v-model:currentSection="currentSection"
      v-model:currentSection_data="currentSection_data"
      :slides="slides"
      v-model:showDrawer="showSectionsDrawer"
      :can-edit="false"
      @update:currentSection="handleSectionChange"
      @selectSlide="handleSelectSlide"
    >
      <template #section-status="{ section }">
        <div v-if="section.id === 'learn' && progress?.learn_completed_at">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div v-else-if="section.id === 'practice' && progress?.practice_submitted_at">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div v-else-if="section.id === 'quiz' && progress?.quiz_passed">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div v-else-if="!canAccessSection(section.id)">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        </div>
      </template>
    </LessonSidebar>

    <!-- Main Content Area -->
    <div class="flex-1  flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="bg-white  border-b border-gray-200 px-6 py-4">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 mr-4">
            <div class="text-xl font-bold text-gray-800">{{ presentation.name || 'Loading...' }}</div>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-sm text-gray-500">Status:</span>
              <span
                class="px-2 py-1 rounded-full text-xs font-medium"
                :class="getStatusBadgeClass(progress?.color_status)"
              >
                {{ getStatusLabel(progress?.status) }}
              </span>
            </div>
          </div>
        </div>
        <div class="text-sm text-gray-500">
          {{ presentation.description }}
        </div>
      </header>

      <!-- Main Content Area -->
      <div class="p-8 flex justify-center overflow-y-auto custom-scrollbar">
        <div class="w-full max-w-4xl">

          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center h-64">
            <q-spinner color="primary" size="3em" />
          </div>

          <!-- Locked State -->
          <div v-else-if="!isPreview && progress?.status === 'locked'" class="bg-white rounded-xl shadow-sm p-12 text-center">
            <i class="fas fa-lock text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Lesson Locked</h2>
            <p class="text-gray-600">This lesson has not been opened by your teacher yet.</p>
          </div>

          <!-- Content -->
          <div v-else>
            <!-- Generic Section View -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden min-h-[600px] flex flex-col">
              
              <!-- 1. Slides Player -->
              <div v-if="currentSectionSlides.length > 0 && !showSpecialContent" class="flex-1 flex flex-col">
                <div class="flex-1 p-8 flex flex-col">
                  <div class="mb-4 flex justify-between items-center">
                    <span class="text-sm text-gray-500">Slide {{ currentSlideIndex + 1 }} of {{ currentSectionSlides.length }}</span>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ currentSlide.slide_type }}</span>
                  </div>

                  <!-- Section Header -->
                  <div v-if="currentSection_data" 
                       class="mb-6 p-4 rounded-xl border flex items-center gap-4 transition-colors duration-300"
                       :style="{ 
                         backgroundColor: currentSection_data.bg, 
                         borderColor: currentSection_data.borderColor 
                       }"
                  >
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-white shadow-sm"
                         :style="{ color: currentSection_data.textColor }"
                    >
                      <q-icon :name="currentSection_data.qIcon || currentSection_data.icon" size="24px" />
                    </div>
                    <div>
                      <div class="text-xs font-bold uppercase tracking-wider opacity-70"
                           :style="{ color: currentSection_data.textColor }"
                      >
                        Current Section
                      </div>
                      <div class="text-xl font-bold leading-none mt-1"
                           :style="{ color: currentSection_data.textColor }"
                      >
                        {{ currentSection_data.title }}
                      </div>
                    </div>
                  </div>

                  <!-- Slide Renderers -->
                  <div class="flex-1">
                     <!-- Text Slide -->
                    <div v-if="currentSlide.slide_type === 'text'" class="prose max-w-none " v-html="currentSlide.slide_content?.text"></div>

                    <!-- Media Slide -->
                    <div v-else-if="['image', 'video', 'audio', 'pdf'].includes(currentSlide.slide_type)" class="h-full flex items-center justify-center">
                      <img v-if="currentSlide.slide_type === 'image'" :src="currentSlide.slide_content?.url" class="max-h-[500px] object-contain" />
                      <video v-else-if="currentSlide.slide_type === 'video'" :src="currentSlide.slide_content?.url" controls class="max-h-[500px] w-full"></video>
                      <div v-else class="text-center">
                        <a :href="currentSlide.slide_content?.url" target="_blank" class="text-blue-600 underline text-lg">Open {{ currentSlide.slide_type }}</a>
                      </div>
                    </div>

                    <!-- Question Slide -->
                    <div v-else-if="currentSlide.slide_type === 'question'" class="space-y-8">
                      <!-- Use QuestionSlide component which handles both QuizEngine and legacy questions -->
                      <QuestionSlide
                        :modelValue="currentSlide.slide_content"
                        mode="play"
                        :quizConfig="{
                          allowReviewMode: false,
                          autoAdvance: false,
                          showRationaleOnCorrect: true
                        }"
                        :attemptId="generateAttemptId()"
                        :legacyMode="currentSection"
                        @answer-selected="handleAnswerSelected"
                        @quiz-completed="handleQuizCompleted"
                      />
                    </div>
                  </div>
                </div>

                <!-- Navigation Footer -->
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-between items-center">
                  <button @click="prevSlide" :disabled="currentSlideIndex === 0" class="px-6 py-2 rounded-md font-medium transition-colors flex items-center gap-2" :class="currentSlideIndex === 0 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-200'">
                    <i class="fas fa-arrow-left"></i> Previous
                  </button>

                  <button @click="nextSlide" class="px-6 py-2 rounded-md font-medium transition-colors flex items-center gap-2" :class="!isSlideCompleted ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700'">
                    <span v-if="currentSlideIndex < currentSectionSlides.length - 1">Next</span>
                    <span v-else>
                      {{ currentSection === 'learn' ? 'Complete Learn' : (currentSection === 'practice' || currentSection === 'quiz' ? 'Start ' + currentSection : 'Finish') }}
                    </span>
                    <i class="fas" :class="!isSlideCompleted ? 'fa-lock' : 'fa-arrow-right'"></i>
                  </button>
                </div>
              </div>

              <!-- 2. Special Content (Practice/Quiz) -->
              <div v-else-if="(currentSection === 'practice' && (showSpecialContent || currentSectionSlides.length === 0))" class="flex justify-center p-8">
                <UniversalQuestionPlayer
                  v-if="progress || isPreview"
                  :question="{ type: 'upload_draw', text: 'Practice Submission', points: 0 }"
                  mode="practice"
                  @submitted="onPracticeSubmitted"
                />
              </div>

              <div v-else-if="(currentSection === 'quiz' && (showSpecialContent || currentSectionSlides.length === 0))" class="p-12 text-center max-w-2xl mx-auto">
                <div class="mb-6">
                  <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-check text-4xl text-green-600"></i>
                  </div>
                  <h2 class="text-2xl font-bold text-gray-800 mb-2">Quiz Section</h2>
                  <p class="text-gray-600 mb-6">
                    You have completed the preparation. The quiz is ready for you.
                  </p>
                  
                  <!-- Quiz Information -->
                  <div v-if="presentation.quiz_id && quizInfo" class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
                    <div class="text-sm text-gray-500 mb-2">Quiz</div>
                    <div class="text-2xl font-bold text-gray-800 mb-2">{{ quizInfo.name }}</div>
                    <div v-if="quizInfo.description" class="text-sm text-gray-600 mb-3">{{ quizInfo.description }}</div>
                    <div class="flex items-center justify-center gap-4 text-sm text-gray-600">
                      <div class="flex items-center gap-1">
                        <i class="fas fa-question-circle"></i>
                        <span>{{ quizInfo.questions_count }} Questions</span>
                      </div>
                      <div v-if="quizInfo.time_limit_minutes" class="flex items-center gap-1">
                        <i class="fas fa-clock"></i>
                        <span>{{ quizInfo.time_limit_minutes }} minutes</span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- No Quiz Assigned -->
                  <div v-else-if="!presentation.quiz_id" class="bg-yellow-50 p-6 rounded-lg border border-yellow-200 mb-6">
                    <div class="text-yellow-800 font-medium mb-1">No Quiz Assigned</div>
                    <div class="text-sm text-yellow-700">Please contact your teacher to assign a quiz for this lesson.</div>
                  </div>
                  
                  <!-- Loading Quiz Info -->
                  <div v-else class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
                    <q-spinner color="primary" size="2em" />
                    <div class="text-sm text-gray-500 mt-2">Loading quiz information...</div>
                  </div>
                  
                  <!-- Start Quiz Button -->
                  <div v-if="presentation.quiz_id && quizInfo" class="mt-8">
                    <button 
                      @click="startQuiz"
                      class="px-8 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors shadow-lg shadow-green-200 flex items-center gap-2 mx-auto"
                    >
                      <i class="fas fa-play"></i>
                      <span>Start Quiz</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- 3. Empty State -->
              <div v-else class="flex-1 flex items-center justify-center text-gray-500 p-12">
                <div class="text-center">
                  <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
                  <p>No content available for this section.</p>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';
import UniversalQuestionPlayer from '@/Components/QuestionSystem/UniversalQuestionPlayer.vue';
import LessonSidebar from './LessonSidebar.vue';
import QuestionSlide from './slides/QuestionSlide.vue';
import { useLessonPresentationStore } from '@/Stores/lessonPresentationStore';
const store = useLessonPresentationStore();

const $q = useQuasar();

const props = defineProps({
  presentation: {
    type: Object,
    default: () => ({})
  },
  // sections: {
  //   type: Array,
  //   default: () => []
  // },
  slides: {
    type: Array,
    default: () => []
  },
  progress: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  isPreview: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['complete-learn', 'submit-practice']);

const currentSection = ref('learn');
const currentSection_data = ref(null);
const currentSlideIndex = ref(0);
const showSectionsDrawer = ref(true);
const showSpecialContent = ref(false);

// Question State
const answers = ref({});
const questionAttempts = ref({});
const questionSolved = ref({});
const submittedQuestions = ref({});

// Quiz State
const quizInfo = ref(null);
const showQuizEngine = ref(false);

// Computed
const currentSectionSlides = computed(() => {
  return props.slides.filter(s => s.section === currentSection.value);
});

const currentSlide = computed(() => currentSectionSlides.value[currentSlideIndex.value] || {});

const isSlideCompleted = computed(() => {
  if (props.isPreview) return true; // Always complete in preview
  if (currentSlide.value?.slide_type !== 'question') return true;
  const questions = currentSlide.value.slide_content?.questions || [];
  return questions.every(q => questionSolved.value[q.id]);
});

// Access Control
const canAccessPractice = computed(() => {
  if (props.isPreview) return true;
  if (!props.progress) return false;
  return props.progress.learn_completed_at || props.progress.status !== 'opened';
});

const canAccessQuiz = computed(() => {
  if (props.isPreview) return true;
  if (!props.progress) return false;
  return props.progress.status === 'quiz_unlocked' || props.progress.status === 'completed' || props.progress.status === 'failed';
});

const getSectionConfig = (sectionId) => {
  return store.value.sections?.find(s => s.id === sectionId) || null;
};

const evaluateUnlockRule = (rule) => {
  // isPreview bypasses all rules
  if (props.isPreview) return true;

  if (!rule) return true; // default to accessible

  // Accept short-hand strings for common cases
  if (typeof rule === 'string') {
    const lower = rule.toLowerCase();
    if (lower === 'always') return true;
    if (lower.startsWith('requires:')) {
      const parts = lower.split(':')[1].split(',').map(p => p.trim());
      return parts.every(p => canAccessSection(p));
    }
    if (lower.startsWith('status_in:')) {
      const statuses = lower.split(':')[1].split(',').map(s => s.trim());
      return !!props.progress && statuses.includes(props.progress.status);
    }
    // Fallback for named sections using previous logic
    if (lower === 'practice') return canAccessPractice.value;
    if (lower === 'quiz') return canAccessQuiz.value;

    // Unknown rule - default to true
    return true;
  }

  // If rule is an object, support a few shapes
  if (typeof rule === 'object') {
    if (rule.type === 'requires' && Array.isArray(rule.requires)) {
      return rule.requires.every(req => canAccessSection(req));
    }
    if (rule.type === 'status_in' && Array.isArray(rule.statuses)) {
      return !!props.progress && rule.statuses.includes(props.progress.status);
    }
    if (rule.type === 'always') return true;
    if (rule.type === 'practice') return canAccessPractice.value;
    if (rule.type === 'quiz') return canAccessQuiz.value;
    // Default allow
    return true;
  }

  return true;
};

const canAccessSection = (sectionId) => {
  if (props.isPreview) return true;

  const cfg = getSectionConfig(sectionId);
  // If no config, fallback to legacy behavior
  if (!cfg) {
    if (sectionId === 'learn') return true;
    if (sectionId === 'objectives') return true;
    if (sectionId === 'warmup') return true;
    if (sectionId === 'homework') return true;
    if (sectionId === 'practice') return canAccessPractice.value;
    if (sectionId === 'quiz') return canAccessQuiz.value;
    return true;
  }

  // If the section has an explicit `visible` flag
  if (cfg.visible === false) return false;

  // If section defines an unlock rule, evaluate it (support snake_case and camelCase)
  const unlockRule = cfg.unlock_rule || cfg.unlockRule || null;
  if (unlockRule) return evaluateUnlockRule(unlockRule);

  // If section defines a `type` fallback
  if (cfg.type === 'practice') return canAccessPractice.value;
  if (cfg.type === 'quiz') return canAccessQuiz.value;

  // Default
  return true;
};

// Methods
const getStatusBadgeClass = (color) => {
  const colorMap = {
    'gray': 'bg-gray-100 text-gray-800',
    'light_blue': 'bg-blue-100 text-blue-800',
    'blue': 'bg-blue-500 text-white',
    'purple': 'bg-purple-500 text-white',
    'green': 'bg-green-500 text-white',
    'yellow': 'bg-yellow-500 text-white',
    'red': 'bg-red-500 text-white'
  };
  return colorMap[color] || 'bg-gray-500 text-white';
};

const getStatusLabel = (status) => {
  if (props.isPreview) return 'PREVIEW';
  return status ? status.replace(/_/g, ' ').toUpperCase() : 'UNKNOWN';
};

const handleSectionChange = (sectionId) => {
  if (canAccessSection(sectionId)) {
    currentSection.value = sectionId;
  } else {
    let message = 'You cannot access this section yet.';

    const cfg = getSectionConfig(sectionId);
    if (cfg && (cfg.unlock_message || cfg.unlockMessage)) {
      message = cfg.unlock_message || cfg.unlockMessage;
    } else if (sectionId === 'practice') {
      message = 'Please complete the Learn section first.';
    } else if (sectionId === 'quiz') {
      message = 'Please complete the Practice section first.';
    }

    $q.notify({
      type: 'warning',
      message: message,
      position: 'top'
    });
  }
};

const handleSelectSlide = (slide) => {
  // Ensure we are in the correct section
  if (currentSection.value !== slide.section) {
      if(canAccessSection(slide.section)){
          currentSection.value = slide.section;
      } else {
          handleSectionChange(slide.section); // Show error
          return;
      }
  }
  currentSlideIndex.value = currentSectionSlides.value.indexOf(slide);
};

const nextSlide = () => {
  if (!isSlideCompleted.value) {
    $q.notify({ type: 'warning', message: 'Please complete all questions before proceeding.' });
    return;
  }
  
  if (currentSlideIndex.value < currentSectionSlides.value.length - 1) {
    currentSlideIndex.value++;
  } else {
    handleSectionCompletion();
  }
};

const prevSlide = () => {
  if (currentSlideIndex.value > 0) {
    currentSlideIndex.value--;
  }
};

const handleSectionCompletion = () => {
  if (currentSection.value === 'learn') {
    if (props.isPreview) {
      $q.notify({ type: 'positive', message: '[Preview] Learn section completed' });
      currentSection.value = 'practice';
    } else {
      emit('complete-learn');
    }
  } else if (currentSection.value === 'practice') {
    showSpecialContent.value = true;
  } else if (currentSection.value === 'quiz') {
    showSpecialContent.value = true;
  } else {
    $q.notify({ type: 'positive', message: 'Section completed!' });
  }
};

const onPracticeSubmitted = () => {
  if (props.isPreview) {
    $q.notify({ type: 'positive', message: '[Preview] Practice submitted' });
  } else {
    emit('submit-practice');
  }
};

/**
 * Generate a unique attempt ID for quiz tracking
 */
const generateAttemptId = () => {
  return `attempt_${props.presentation.id}_${currentSlide.value.id || currentSlideIndex.value}_${Date.now()}`;
};

/**
 * Handle answer selection from QuizEngine
 * Save individual answers to the database
 */
const handleAnswerSelected = async (record) => {
  if (props.isPreview) {
    console.log('Preview mode - Answer selected:', record);
    return;
  }

  try {
    // Save answer to database via API
    await axios.post('/api/quiz-attempts/answers', {
      presentation_id: props.presentation.id,
      slide_id: currentSlide.value.id,
      question_id: record.questionId,
      selected_option_id: record.selectedOptionId,
      selected_text: record.selectedText,
      is_correct: record.correct,
      time_spent_sec: record.timeSpentSec,
      answered_at: record.answeredAt
    });
  } catch (error) {
    console.error('Failed to save answer:', error);
    // Don't show error to user - continue with quiz
  }
};

/**
 * Handle quiz completion from QuizEngine
 * Save quiz attempt results to the database
 */
const handleQuizCompleted = async (result) => {
  if (props.isPreview) {
    $q.notify({
      type: 'positive',
      message: `[Preview] Quiz completed! Score: ${result.correct}/${result.total} (${result.percentage}%)`,
      timeout: 3000
    });
    return;
  }

  try {
    // Save quiz attempt to database
    const response = await axios.post('/api/quiz-attempts', {
      presentation_id: props.presentation.id,
      slide_id: currentSlide.value.id,
      attempt_id: result.attemptId,
      total_questions: result.total,
      correct_answers: result.correct,
      percentage: result.percentage,
      completed_at: result.completedAt,
      answers: result.answers,
      metadata: result.metadata
    });

    // Show success notification
    $q.notify({
      type: 'positive',
      message: `Quiz completed! Score: ${result.correct}/${result.total} (${result.percentage}%)`,
      icon: 'check_circle',
      position: 'top',
      timeout: 3000
    });

    // Update question solved status for navigation
    const questions = currentSlide.value.slide_content?.questions || [];
    questions.forEach(q => {
      questionSolved.value[q.id] = true;
    });

  } catch (error) {
    console.error('Failed to save quiz attempt:', error);
    $q.notify({
      type: 'warning',
      message: 'Quiz completed but failed to save results. Please contact your teacher.',
      icon: 'warning',
      position: 'top',
      timeout: 5000
    });
  }
};

// Watchers
watch(currentSection, () => {
  currentSlideIndex.value = 0;
  showSpecialContent.value = false;
});

/**
 * Fetch quiz information
 */
const fetchQuizInfo = async () => {
  if (!props.presentation.quiz_id) {
    quizInfo.value = null;
    return;
  }

  try {
    const response = await axios.get(`/api/quizzes/${props.presentation.quiz_id}`);
    quizInfo.value = response.data;
  } catch (error) {
    console.error('Failed to fetch quiz info:', error);
    $q.notify({
      type: 'warning',
      message: 'Failed to load quiz information',
      icon: 'warning',
      position: 'top'
    });
  }
};

/**
 * Start the quiz
 */
const startQuiz = () => {
  if (!props.presentation.quiz_id || !quizInfo.value) {
    $q.notify({
      type: 'warning',
      message: 'Quiz information not available',
      icon: 'warning',
      position: 'top'
    });
    return;
  }

  // TODO: Launch QuizEngine component with the quiz
  // For now, show a notification
  $q.notify({
    type: 'info',
    message: 'Quiz engine integration coming soon!',
    icon: 'info',
    position: 'top'
  });
  
  // In the future, this will:
  // 1. Load the quiz questions from the API
  // 2. Launch the QuizEngine component
  // 3. Track the quiz attempt
  // showQuizEngine.value = true;
};

// Initialize
onMounted(() => {
    // If progress exists, set initial section
    if (props.progress) {
        if (props.progress.status === 'locked') {
            // Stay on learn (locked view)
        } else if (canAccessQuiz.value) {
            currentSection.value = 'quiz';
        } else if (canAccessPractice.value && !props.progress.practice_submitted_at) {
            currentSection.value = 'practice';
        }
    }
    
    // Fetch quiz info if quiz_id is set
    if (props.presentation.quiz_id) {
        fetchQuizInfo();
    }
});

// Watch for quiz_id changes
watch(() => props.presentation.quiz_id, (newQuizId) => {
  if (newQuizId) {
    fetchQuizInfo();
  } else {
    quizInfo.value = null;
  }
});

</script>
