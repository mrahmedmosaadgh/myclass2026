<template>
  <Head :title="exam.title || 'Take Exam'" />
  <div class="exam-container">
    <!-- Fixed Header -->
    <q-header elevated class="bg-white text-dark">
      <q-toolbar>
        <q-toolbar-title>
          <div class="text-h6">{{ exam.title }}</div>
          <div class="text-caption text-grey-7">{{ exam.subject?.name }}</div>
        </q-toolbar-title>
        <QuExamTimer
          :remaining-seconds="attempt.remaining_seconds"
          :auto-submit="handleAutoSubmit"
          @time-warning="onTimeWarning"
          @time-expired="onTimeExpired"
        />
      </q-toolbar>
    </q-header>

    <div class="exam-content">
      <!-- Question Navigation Sidebar -->
      <div class="navigation-sidebar">
        <q-card>
          <q-card-section>
            <div class="text-subtitle2 q-mb-md">Questions</div>
            <div class="text-caption text-grey-7 q-mb-sm">
              Answered: {{ answeredCount }} / {{ questions.length }}
            </div>
            <div class="question-grid">
              <q-btn
                v-for="(question, index) in questions"
                :key="question.id"
                :label="index + 1"
                :color="getQuestionButtonColor(index)"
                :outline="currentQuestionIndex !== index"
                :unelevated="currentQuestionIndex === index"
                size="sm"
                @click="navigateToQuestion(index)"
                class="question-nav-btn"
              >
                <q-icon
                  v-if="isQuestionAnswered(question.id)"
                  name="check"
                  size="xs"
                  class="absolute-top-right"
                  style="margin: 2px"
                />
              </q-btn>
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
            <div class="text-caption text-grey-7 q-mb-sm">
              Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}
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

            <div class="q-gutter-sm">
              <q-btn
                outline
                color="primary"
                label="Save Progress"
                icon="save"
                @click="saveAnswers(true)"
                :loading="isSaving"
              />
              <q-btn
                color="negative"
                label="Submit Exam"
                icon="send"
                @click="confirmSubmit"
              />
            </div>

            <q-btn
              flat
              color="primary"
              label="Next"
              icon-right="chevron_right"
              :disable="currentQuestionIndex === questions.length - 1"
              @click="nextQuestion"
            />
          </q-card-actions>
        </q-card>
      </div>
    </div>

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
              Answered: {{ answeredCount }} / {{ questions.length }} questions
            </div>
            <div v-if="answeredCount < questions.length" class="text-caption text-warning q-mt-xs">
              <q-icon name="warning" size="sm" />
              You have {{ questions.length - answeredCount }} unanswered question(s)
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn label="Submit" color="negative" @click="submitExam" :loading="isSubmitting" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useQuasar } from 'quasar';
import QuExamTimer from './QuComponents/QuExamTimer.vue';
import QuQuestionDisplay from './QuComponents/QuQuestionDisplay.vue';

const $q = useQuasar();

const props = defineProps({
  exam: Object,
  attempt: Object,
  questions: Array,
  existing_answers: Object
});

const currentQuestionIndex = ref(0);
const answers = ref({ ...props.existing_answers });
const lastSaved = ref(Date.now());
const isSaving = ref(false);
const isSubmitted = ref(false);
const submitDialog = ref(false);
const isSubmitting = ref(false);
let autoSaveInterval = null;

const currentQuestion = computed(() => props.questions[currentQuestionIndex.value]);

const currentAnswer = computed({
  get() {
    return answers.value[currentQuestion.value.id] || {
      selected_options: [],
      answer_text: ''
    };
  },
  set(value) {
    answers.value[currentQuestion.value.id] = value;
  }
});

const answeredCount = computed(() => {
  return Object.keys(answers.value).filter(questionId => {
    const answer = answers.value[questionId];
    return (answer.selected_options && answer.selected_options.length > 0) ||
           (answer.answer_text && answer.answer_text.trim().length > 0);
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
  return (answer.selected_options && answer.selected_options.length > 0) ||
         (answer.answer_text && answer.answer_text.trim().length > 0);
};

const getQuestionButtonColor = (index) => {
  if (currentQuestionIndex.value === index) return 'primary';
  const question = props.questions[index];
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
  if (currentQuestionIndex.value < props.questions.length - 1) {
    currentQuestionIndex.value++;
  }
};

const onAnswerChange = () => {
  // Answer is automatically updated via v-model
};

const saveAnswers = async (showNotification = true) => {
  if (isSubmitted.value) return;
  
  isSaving.value = true;
  
  try {
    await router.post(
      route('qu-student.exams.auto-save', {
        quExam: props.exam.id,
        quAttempt: props.attempt.id
      }),
      { answers: answers.value },
      {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          lastSaved.value = Date.now();
          if (showNotification) {
            $q.notify({
              type: 'positive',
              message: 'Progress saved',
              icon: 'save',
              timeout: 2000
            });
          }
        },
        onError: () => {
          if (showNotification) {
            $q.notify({
              type: 'negative',
              message: 'Failed to save progress',
              icon: 'error'
            });
          }
        }
      }
    );
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
    route('qu-student.exams.submit', {
      quExam: props.exam.id,
      quAttempt: props.attempt.id
    }),
    { answers: answers.value },
    {
      onSuccess: () => {
        isSubmitted.value = true;
        clearInterval(autoSaveInterval);
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

// Navigation guard
const handleBeforeUnload = (e) => {
  if (!isSubmitted.value) {
    e.preventDefault();
    e.returnValue = '';
    return '';
  }
};

onMounted(() => {
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
