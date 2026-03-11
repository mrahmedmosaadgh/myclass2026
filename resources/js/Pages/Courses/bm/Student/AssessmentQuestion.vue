<template>
  <Head title="Basic Math - Question" />
  <div class="bm-assessment-question fullscreen flex flex-center column q-pa-md" style="background: linear-gradient(135deg, #FFF8E1 0%, #FFE0B2 100%);">
    <div class="question-container bg-white shadow-6 q-pa-xl text-center" style="max-width: 500px; width: 100%; border-radius: 28px;">
      
      <!-- Top Row: Mascot + Progress + Timer -->
      <div class="row justify-between items-center q-mb-lg">
        <q-avatar size="50px" class="shadow-2">
          <img src="/images/bm/mascot_think.gif" alt="Thinking Mascot"/>
        </q-avatar>

        <div class="col-grow q-mx-md">
          <ProgressBar :current="questionIndex" :total="totalQuestions" />
          <div class="text-caption text-grey-6 q-mt-xs">Question {{ questionIndex }} of {{ totalQuestions }}</div>
        </div>

        <q-chip 
          :color="timerColor" 
          text-color="white" 
          icon="timer" 
          class="text-weight-bold shadow-1"
          style="font-size: 1rem;"
        >
          {{ formattedTime }}
        </q-chip>
      </div>

      <!-- Domain Badge -->
      <q-chip 
        :color="domainColor" 
        text-color="white" 
        class="q-mb-md text-weight-bold"
        icon="category"
        size="md"
      >
        {{ question.domain }}
      </q-chip>

      <!-- Question Text -->
      <div class="question-text text-weight-bolder text-dark q-my-lg" v-html="question.htmlText"></div>

      <!-- Answer Input Box -->
      <div class="q-my-lg">
        <div class="answer-box text-weight-bold q-mb-lg flex flex-center" 
             :class="answerBoxClass">
          <span v-if="currentAnswer" class="answer-text">{{ currentAnswer }}</span>
          <span v-else class="answer-placeholder">?</span>
        </div>
        
        <NumberPad @input="onInput" @backspace="onBackspace" @submit="submitResponseIfValid" numbers-only />
      </div>

      <!-- Submit Button -->
      <q-btn 
        @click="submitResponseIfValid" 
        :disable="!currentAnswer"
        color="primary" 
        size="lg" 
        class="full-width text-weight-bold" 
        style="border-radius: 14px; font-size: 1.1rem; letter-spacing: 1px;"
        label="SUBMIT ANSWER" 
        icon-right="send"
        unelevated 
      />

    </div>

    <!-- Feedback Overlay -->
    <transition name="feedback-fade">
      <div v-if="showFeedback" class="feedback-overlay flex flex-center column" :class="feedbackIsCorrect ? 'feedback--correct' : 'feedback--wrong'">
        <q-icon :name="feedbackIsCorrect ? 'check_circle' : 'cancel'" size="120px" :color="feedbackIsCorrect ? 'white' : 'white'" class="q-mb-md" />
        <div class="text-h3 text-weight-bold text-white q-mb-lg">{{ feedbackIsCorrect ? 'Correct!' : 'Wrong!' }}</div>
        
        <!-- Show previous question to give context -->
        <div class="q-mb-lg q-pa-md bg-white text-dark shadow-3 flex flex-center column" style="border-radius: 16px; min-width: 250px;">
          <div class="text-h4 text-weight-bolder q-mb-sm" v-html="feedbackQuestionHtml"></div>
          
          <div v-if="!feedbackIsCorrect" class="text-h5 text-negative text-strike text-weight-bold">
            {{ feedbackUserAnswer }}
          </div>
        </div>

        <div v-if="!feedbackIsCorrect" class="text-h5 text-white q-mb-sm" style="opacity: 0.9;">
          The correct answer was: <strong>{{ feedbackCorrectAnswer }}</strong>
        </div>
        <div v-if="feedbackIsCorrect" class="text-h5 text-white" style="opacity: 0.9;">
          Great job! Keep going! 🔥
        </div>
        <!-- Progress Bar Indicator -->
        <div class="feedback-progress-container absolute-bottom w-full">
          <div class="feedback-progress-bar" :style="{ animationDuration: feedbackDelayMs + 'ms' }"></div>
        </div>

      </div>
    </transition>

  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import BMLayout from '@/Layouts/BMLayout.vue';
import ProgressBar from '@/Components/Courses/bm/UI/ProgressBar.vue';
import NumberPad from '@/Components/Courses/bm/Math/NumberPad.vue';
import { useBMTimer } from '@/composables/Courses/bm/useBMTimer.js';

defineOptions({ layout: BMLayout });

const props = defineProps({
  assessmentId: Number,
  question: Object,
  questionIndex: Number,
  totalQuestions: Number,
  feedback: {
    type: Object,
    default: null
  }
});

const currentAnswer = ref('');
const showFeedback = ref(false);
const feedbackIsCorrect = ref(false);
const feedbackCorrectAnswer = ref('');
const feedbackUserAnswer = ref('');
const feedbackQuestionHtml = ref('');
const feedbackDelayMs = ref(2000);
const { timeElapsedMs, startTimer, stopTimer } = useBMTimer();

const formattedTime = computed(() => {
  const seconds = Math.floor(timeElapsedMs.value / 1000);
  return `${seconds}s`;
});

const timerColor = computed(() => {
  const s = Math.floor(timeElapsedMs.value / 1000);
  if (s < 10) return 'positive';
  if (s < 20) return 'warning';
  return 'negative';
});

const domainColor = computed(() => {
  const map = {
    'Addition': 'blue',
    'Subtraction': 'teal',
    'Multiplication': 'deep-purple',
    'Division': 'orange',
    'Fractions': 'pink'
  };
  return map[props.question?.domain] || 'primary';
});

const answerBoxClass = computed(() => ({
  'answer-box--active': currentAnswer.value !== '',
  'answer-box--empty': currentAnswer.value === ''
}));

const processFeedback = () => {
  if (props.feedback) {
    feedbackIsCorrect.value = props.feedback.isCorrect;
    feedbackCorrectAnswer.value = props.feedback.correctAnswer;
    feedbackUserAnswer.value = props.feedback.userAnswer;
    feedbackQuestionHtml.value = props.feedback.questionHtml || '';
    
    // Auto-dismiss feedback: 2s if correct, 4.5s if wrong
    const delay = props.feedback.isCorrect ? 2000 : 4500;
    feedbackDelayMs.value = delay;
    
    showFeedback.value = true;
    
    setTimeout(() => {
      showFeedback.value = false;
      startTimer();
    }, delay);
  } else {
    startTimer();
  }
};

// --- Flow Protection Logic ---
let removeBeforeListener = null;

const handleBeforeUnload = (e) => {
  e.preventDefault();
  e.returnValue = '';
};

onMounted(() => {
  processFeedback();

  // Protect against native browser refresh/close
  window.addEventListener('beforeunload', handleBeforeUnload);

  // Protect against Inertia single-page navigation
  removeBeforeListener = router.on('before', (event) => {
    // If the navigation is a post to the submit endpoint, we allow it to pass normally
    if (event.detail.visit.method === 'post' && event.detail.visit.url.pathname.includes('/submit')) {
      return true;
    }

    if (!confirm('Are you sure you want to leave? Your assignment progress will be lost.')) {
      event.preventDefault();
    }
  });
});

onUnmounted(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
  if (removeBeforeListener) removeBeforeListener();
});

watch(() => props.questionIndex, () => {
  processFeedback();
});

const onInput = (val) => {
  if (currentAnswer.value.length < 5) currentAnswer.value += val;
};

const onBackspace = () => {
  currentAnswer.value = currentAnswer.value.slice(0, -1);
};

const submitResponseIfValid = () => {
  if (currentAnswer.value) {
    submitResponse();
  }
};

const submitResponse = () => {
  stopTimer();
  router.post(route('bm.assessment.submit'), {
    assessment_id: props.assessmentId,
    question_id: props.question.id,
    answer: currentAnswer.value,
    time_taken_ms: timeElapsedMs.value
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      currentAnswer.value = '';
      startTimer();
    }
  });
};
</script>

<style scoped>
.question-text {
  font-size: 2.8rem;
  line-height: 1.3;
  letter-spacing: 2px;
}

.answer-box {
  height: 80px;
  border-radius: 16px;
  border: 3px solid #e0e0e0;
  background: #f5f5f5;
  transition: all 0.3s ease;
}

.answer-box--active {
  border-color: #1976d2;
  background: #E3F2FD;
  box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.15);
}

.answer-text {
  font-size: 2.5rem;
  color: #1976d2;
  font-weight: 800;
}

.answer-placeholder {
  font-size: 2.5rem;
  color: #bdbdbd;
  font-weight: 300;
}
</style>

<style scoped>
.feedback-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
}

.feedback--correct {
  background: linear-gradient(135deg, #43A047 0%, #66BB6A 100%);
}

.feedback--wrong {
  background: linear-gradient(135deg, #E53935 0%, #EF5350 100%);
}

.feedback-fade-enter-active {
  animation: feedback-pop 0.4s ease;
}

.feedback-fade-leave-active {
  transition: opacity 0.3s ease;
}

.feedback-fade-leave-to {
  opacity: 0;
}

@keyframes feedback-pop {
  0% { opacity: 0; transform: scale(0.8); }
  60% { transform: scale(1.05); }
  100% { opacity: 1; transform: scale(1); }
}

.feedback-progress-container {
  height: 8px;
  background: rgba(0, 0, 0, 0.2);
  width: 100%;
}

.feedback-progress-bar {
  height: 100%;
  background: rgba(255, 255, 255, 0.9);
  width: 100%;
  transform-origin: left;
  animation-name: shrink-bar;
  animation-timing-function: linear;
  animation-fill-mode: forwards;
}

@keyframes shrink-bar {
  from { transform: scaleX(1); }
  to { transform: scaleX(0); }
}
</style>
