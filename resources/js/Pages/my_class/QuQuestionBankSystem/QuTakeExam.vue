<template>
  <Head :title="exam.title || 'Take Exam'" />
  <q-layout view="hHh lpR fFf" class="bg-grey-1" v-bind="$attrs">
    <q-header elevated class="bg-white text-dark">
      <q-toolbar>
        <q-toolbar-title>
          <div class="text-h6">{{ exam.title }}</div>
          <div class="text-caption text-grey-7">{{ exam.subject?.name }}</div>
        </q-toolbar-title>
        <QuExamTimer
          :remaining-seconds="currentRemainingSeconds"
          :auto-submit="handleAutoSubmit"
          @time-warning="onTimeWarning"
          @time-expired="onTimeExpired"
          @tick="onTimerTick"
        />
      </q-toolbar>
    </q-header>

    <q-page-container>
      <div class="exam-content">
        <!-- Question Navigation Sidebar -->
        <div class="navigation-sidebar">
          <q-card>
            <q-card-section>
              <div class="q-mb-md">
                <div class="text-subtitle2 q-mb-xs">Questions</div>
                <div class="row q-gutter-xs">
                   <q-badge 
                    v-for="filter in filters" 
                    :key="filter.value"
                    :color="currentFilter === filter.value ? 'primary' : 'grey-3'"
                    :text-color="currentFilter === filter.value ? 'white' : 'grey-8'"
                    class="cursor-pointer"
                    @click="currentFilter = filter.value"
                  >
                    {{ filter.label }}
                  </q-badge>
                </div>
              </div>
              
              <div class="text-caption text-grey-7 q-mb-sm">
                Answered: {{ answeredCount }} / {{ localQuestions.length }}
              </div>
              <div class="question-grid">
                <q-btn
                  v-for="(question, index) in filteredQuestions"
                  :key="question.id"
                  :label="question.originalIndex + 1"
                  :color="getQuestionButtonColor(question.originalIndex)"
                  :outline="currentQuestionIndex !== question.originalIndex"
                  :unelevated="currentQuestionIndex === question.originalIndex"
                  size="sm"
                  @click="navigateToQuestion(question.originalIndex)"
                  class="question-nav-btn"
                >
                  <q-icon
                    v-if="isQuestionAnswered(question.id)"
                    name="check"
                    size="xs"
                    class="absolute-top-right"
                    style="margin: 2px"
                  />
                  <q-icon
                    v-if="isQuestionMarked(question.id)"
                    name="flag"
                    size="xs"
                    color="warning"
                    class="absolute-bottom-right"
                    style="margin: 2px"
                  />
                </q-btn>
                <div v-if="filteredQuestions.length === 0" class="text-caption text-grey-7 q-pa-md text-center col-12">
                  No questions match filter
                </div>
              </div>

            </q-card-section>

            <q-separator />

            <q-card-section>
              <div class="text-caption text-grey-7 q-mb-xs">
                Last saved: {{ lastSavedText }}
              </div>
              <q-linear-progress
                v-if="isSaving"
                indeterminate
                color="primary"
                size="2px"
              />
            </q-card-section>
          </q-card>
        </div>

        <!-- Main Question Area -->
        <div class="question-area">
          <q-card>
            <q-card-section>
              <div class="row items-center justify-between q-mb-sm">
                <div class="text-caption text-grey-7">
                  Question {{ currentQuestionIndex + 1 }} of {{ localQuestions.length }}
                </div>
                <q-btn
                  flat
                  dense
                  :color="isQuestionMarked(currentQuestion?.id) ? 'warning' : 'grey'"
                  :icon="isQuestionMarked(currentQuestion?.id) ? 'flag' : 'outlined_flag'"
                  :label="isQuestionMarked(currentQuestion?.id) ? 'Marked for Review' : 'Mark for Review'"
                  @click="toggleMarkForReview"
                />
              </div>
              
              <QuQuestionDisplay
                :question="currentQuestion"
                :index="currentQuestionIndex + 1"
                v-model="currentAnswer"
                :readonly="false"
                :show-correct-answer="false"
                @update:model-value="onAnswerChange"
              />
            </q-card-section>

            <q-separator />

            <q-card-actions align="between" class="q-pa-md">
              <q-btn
                flat
                color="primary"
                label="Previous"
                icon="chevron_left"
                :disable="currentQuestionIndex === 0"
                @click="previousQuestion"
              />

              <q-btn
                v-if="currentQuestionIndex === localQuestions.length - 1"
                color="negative"
                label="Submit Exam"
                icon="send"
                @click="confirmSubmit"
              />

              <q-btn
                flat
                color="primary"
                label="Next"
                icon-right="chevron_right"
                :disable="currentQuestionIndex === localQuestions.length - 1"
                @click="nextQuestion"
              />
            </q-card-actions>
          </q-card>
        </div>
      </div>
    </q-page-container>

    <!-- Submit Confirmation Dialog -->
    <q-dialog v-model="submitDialog" persistent>
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Submit Exam?</div>
        </q-card-section>

        <q-card-section>
          <p>Are you sure you want to submit your exam?</p>
          <div class="q-mt-md">
            <div class="text-caption text-grey-7">
              Answered: {{ answeredCount }} / {{ localQuestions.length }} questions
            </div>
            <div v-if="answeredCount < localQuestions.length" class="text-caption text-warning q-mt-xs">
              <q-icon name="warning" size="sm" />
              You have {{ localQuestions.length - answeredCount }} unanswered question(s)
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn label="Submit" color="negative" @click="submitExam" :loading="isSubmitting" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-layout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useQuasar } from 'quasar';
import axios from 'axios';
import QuExamTimer from './QuComponents/QuExamTimer.vue';
import QuQuestionDisplay from './QuComponents/QuQuestionDisplay.vue';

defineOptions({ layout: null });

const $q = useQuasar();

const props = defineProps({
  exam: Object,
  attempt: Object,
  questions: Array,
  existing_answers: Object
});

const localQuestions = ref([...props.questions]);
const currentQuestionIndex = ref(0);
const answers = ref({ ...props.existing_answers });
const markedForReview = ref(new Set());

const currentRemainingSeconds = ref(props.attempt.remaining_seconds);
const lastSaved = ref(Date.now());
const isSaving = ref(false);
const isSubmitted = ref(false);
const submitDialog = ref(false);
const isSubmitting = ref(false);

const currentFilter = ref('all');
let autoSaveInterval = null;

const filters = [
  { label: 'All', value: 'all' },
  { label: 'Solved', value: 'solved' },
  { label: 'Unsolved', value: 'unsolved' },
  { label: 'Marked', value: 'marked' }
];

const currentQuestion = computed(() => localQuestions.value[currentQuestionIndex.value]);

const filteredQuestions = computed(() => {
  return localQuestions.value.map((q, i) => ({ ...q, originalIndex: i }))
    .filter(q => {
      if (currentFilter.value === 'all') return true;
      if (currentFilter.value === 'solved') return isQuestionAnswered(q.id);
      if (currentFilter.value === 'unsolved') return !isQuestionAnswered(q.id);
      if (currentFilter.value === 'marked') return isQuestionMarked(q.id);
      return true;
    });
});

const currentAnswer = computed({
  get() {
    if (!currentQuestion.value) return {
      selected_options: [],
      answer_text: ''
    };
    return answers.value[currentQuestion.value.id] || {
      selected_options: [],
      answer_text: ''
    };
  },
  set(value) {
    if (currentQuestion.value) {
      answers.value[currentQuestion.value.id] = value;
    }
  }
});

const answeredCount = computed(() => {
  return Object.keys(answers.value).filter(questionId => {
    const answer = answers.value[questionId];
    // Check for selected_options (can be string for MCQ or array for multi-select)
    const hasSelectedOption = answer.selected_options && (
      (typeof answer.selected_options === 'string' && answer.selected_options.length > 0) ||
      (Array.isArray(answer.selected_options) && answer.selected_options.length > 0)
    );
    // Check for text answer
    const hasTextAnswer = answer.answer_text && answer.answer_text.trim().length > 0;
    
    return hasSelectedOption || hasTextAnswer;
  }).length;
});

const lastSavedText = computed(() => {
  const seconds = Math.floor((Date.now() - lastSaved.value) / 1000);
  if (seconds < 10) return 'Just now';
  if (seconds < 60) return `${seconds}s ago`;
  const minutes = Math.floor(seconds / 60);
  return `${minutes}m ago`;
});

const isQuestionAnswered = (questionId) => {
  const answer = answers.value[questionId];
  if (!answer) return false;
  
  // Check for selected_options (can be string for MCQ or array for multi-select)
  const hasSelectedOption = answer.selected_options && (
    (typeof answer.selected_options === 'string' && answer.selected_options.length > 0) ||
    (Array.isArray(answer.selected_options) && answer.selected_options.length > 0)
  );
  // Check for text answer
  const hasTextAnswer = answer.answer_text && answer.answer_text.trim().length > 0;
  
  return hasSelectedOption || hasTextAnswer;
};

const isQuestionMarked = (questionId) => {
  return markedForReview.value.has(questionId);
};

const toggleMarkForReview = () => {
  if (!currentQuestion.value) return;
  
  const id = currentQuestion.value.id;
  if (markedForReview.value.has(id)) {
    markedForReview.value.delete(id);
  } else {
    markedForReview.value.add(id);
  }
  saveLocalState();
};

const getQuestionButtonColor = (index) => {
  if (currentQuestionIndex.value === index) return 'primary';
  const question = props.questions[index];
  if (!question) return 'grey-5';
  return isQuestionAnswered(question.id) ? 'positive' : 'grey-5';
};

const navigateToQuestion = (index) => {
  currentQuestionIndex.value = index;
};

const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
  }
};

const nextQuestion = () => {
  if (!localQuestions.value || localQuestions.value.length === 0) return;
  if (currentQuestionIndex.value < localQuestions.value.length - 1) {
    currentQuestionIndex.value++;
  }
};

const onAnswerChange = () => {
  // Answer is automatically updated via v-model
  saveLocalState();
};

const storageKey = computed(() => `qu_exam_${props.exam.id}_attempt_${props.attempt.id}`);

const saveLocalState = () => {
  if (isSubmitted.value) return;
  if (!localQuestions.value || localQuestions.value.length === 0) return;
  
  const state = {
    answers: answers.value,
    currentQuestionIndex: currentQuestionIndex.value,
    questionOrder: localQuestions.value.map(q => q.id),
    questionOrder: localQuestions.value.map(q => q.id),
    markedForReview: Array.from(markedForReview.value),
    remainingSeconds: currentRemainingSeconds.value,
    timestamp: Date.now()
  };
  
  localStorage.setItem(storageKey.value, JSON.stringify(state));
};

const loadLocalState = () => {
  if (!props.questions || props.questions.length === 0) return;
  
  const stored = localStorage.getItem(storageKey.value);
  if (stored) {
    try {
      const state = JSON.parse(stored);
      
      // Restore answers
      answers.value = { ...answers.value, ...state.answers };
      
      // Restore index
      if (typeof state.currentQuestionIndex === 'number') {
        currentQuestionIndex.value = Math.min(Math.max(0, state.currentQuestionIndex), props.questions.length - 1);
      }
      
      // Restore Question Order
      if (state.questionOrder && Array.isArray(state.questionOrder) && 
          state.questionOrder.length === props.questions.length && 
          localQuestions.value && localQuestions.value.length > 0) {
         const orderMap = new Map(state.questionOrder.map((id, idx) => [id, idx]));
         localQuestions.value.sort((a, b) => {
           const idxA = orderMap.has(a.id) ? orderMap.get(a.id) : 9999;
           const idxB = orderMap.has(b.id) ? orderMap.get(b.id) : 9999;
           return idxA - idxB;
         });
      }

      // Restore Marked for Review
      if (state.markedForReview && Array.isArray(state.markedForReview)) {
        markedForReview.value = new Set(state.markedForReview);
      }

      // Restore Timer
      if (typeof state.remainingSeconds === 'number') {
        currentRemainingSeconds.value = state.remainingSeconds;
      }
      
    } catch (e) {
      console.error('Failed to restore local state', e);
    }
  } else {
     saveLocalState();
  }
};

const clearLocalState = () => {
  localStorage.removeItem(storageKey.value);
};

const saveAnswers = async (showNotification = true) => {
  if (isSubmitted.value) return;
  
  isSaving.value = true;
  
  try {
    await axios.post(
      route('qu.student.exams.auto-save', {
        quExam: props.exam.id,
        quAttempt: props.attempt.id
      }),
      { answers: answers.value }
    );
    
    lastSaved.value = Date.now();
    saveLocalState(); // Also save locally on successful server save
    if (showNotification) {
      $q.notify({
        type: 'positive',
        message: 'Progress saved',
        icon: 'save',
        timeout: 2000
      });
    }
  } catch (error) {
    if (showNotification) {
      $q.notify({
        type: 'negative',
        message: 'Failed to save progress',
        icon: 'error'
      });
    }
    console.error('Auto-save error:', error);
  } finally {
    isSaving.value = false;
  }
};

const confirmSubmit = () => {
  submitDialog.value = true;
};

const submitExam = () => {
  isSubmitting.value = true;
  
  router.post(
    route('qu.student.exams.submit', {
      quExam: props.exam.id,
      quAttempt: props.attempt.id
    }),
    { answers: answers.value },
    {
      onSuccess: () => {
        isSubmitted.value = true;
        clearInterval(autoSaveInterval);
        clearLocalState();
        $q.notify({
          type: 'positive',
          message: 'Exam submitted successfully!',
          icon: 'check_circle'
        });
      },
      onError: (errors) => {
        isSubmitting.value = false;
        $q.notify({
          type: 'negative',
          message: errors.message || 'Failed to submit exam',
          icon: 'error'
        });
      }
    }
  );
};

const handleAutoSubmit = () => {
  isSubmitted.value = true;
  clearInterval(autoSaveInterval);
  submitExam();
};

const onTimeWarning = (minutes) => {
  // Handled by timer component
};

const onTimeExpired = () => {
  // Handled by timer component
};

const onTimerTick = (seconds) => {
  currentRemainingSeconds.value = seconds;
  // We don't save every tick to avoid thrashing storage, 
  // but we save on question change and auto-save interval.
  // If user closes tab, they might lose up to 30s of timer progress which is acceptable.
  // Or we can throttle save here if strict accuracy is needed.
};

// Navigation guard
const handleBeforeUnload = (e) => {
  if (!isSubmitted.value) {
    e.preventDefault();
    e.returnValue = '';
    return '';
  }
};

onMounted(() => {
  loadLocalState();
  
  // Watch for index changes to save state
  watch(currentQuestionIndex, () => {
    saveLocalState();
  });

  // Auto-save every 30 seconds
  autoSaveInterval = setInterval(() => {
    saveAnswers(false);
  }, 30000);

  // Prevent accidental browser close
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  if (autoSaveInterval) {
    clearInterval(autoSaveInterval);
  }
  window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>

<style scoped>
.exam-container {
  min-height: 100vh;
  background-color: #f5f5f5;
}

.exam-content {
  display: flex;
  gap: 16px;
  padding: 16px;
  padding-top: 80px; /* Account for fixed header */
}

.navigation-sidebar {
  width: 250px;
  flex-shrink: 0;
}

.question-area {
  flex: 1;
  min-width: 0;
}

.question-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 8px;
}

.question-nav-btn {
  position: relative;
  min-width: 40px;
  min-height: 40px;
}

@media (max-width: 768px) {
  .exam-content {
    flex-direction: column;
  }

  .navigation-sidebar {
    width: 100%;
  }

  .question-grid {
    grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
  }
}
</style>
