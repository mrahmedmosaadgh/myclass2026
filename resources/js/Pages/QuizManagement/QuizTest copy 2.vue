<template>
  <div class="quiz-test">
    <!-- Header -->
    <div class="quiz-test__header">
      <div class="quiz-test__header-content">
        <h1 class="quiz-test__title">{{ quiz.name }}</h1>
        <div class="quiz-test__progress">
          Question {{ currentQuestionIndex + 1 }} of {{ quiz.questions?.length || 0 }}
        </div>
      </div>
      
      <div class="quiz-test__timer" v-if="quiz.time_limit_minutes && !isCompleted">
        <q-icon name="schedule" size="20px" />
        <span>{{ formatTime(timeRemaining) }}</span>
      </div>
    </div>

    <!-- Quiz Content -->
    <div class="quiz-test__container" v-if="!isCompleted">
      <div class="quiz-test__question-container">
        <q-card class="quiz-test__question-card">
          <q-card-section>
            <!-- Question Number & Difficulty -->
            <div class="quiz-test__question-header">
              <div class="quiz-test__question-number">
                Question {{ currentQuestionIndex + 1 }}
              </div>
              <q-badge
                v-if="currentQuestion.difficulty"
                :color="getDifficultyColor(currentQuestion.difficulty)"
                :label="currentQuestion.difficulty"
              />
            </div>
            
            <!-- Question Text -->
            <div class="quiz-test__question-text" v-html="currentQuestion.question_text" />
            
            <!-- Options -->
            <div class="quiz-test__options">
              <div
                v-for="(option, index) in currentQuestion.options"
                :key="option.id"
                class="quiz-test__option"
                :class="{ 'quiz-test__option--selected': selectedAnswer === option.id }"
                @click="selectAnswer(option.id)"
              >
                <div class="quiz-test__option-radio">
                  <q-radio
                    :model-value="selectedAnswer"
                    :val="option.id"
                    color="primary"
                  />
                </div>
                <div class="quiz-test__option-label">
                  {{ String.fromCharCode(65 + index) }}
                </div>
                <div class="quiz-test__option-text">
                  {{ option.option_text }}
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Navigation -->
        <div class="quiz-test__navigation">
          <q-btn
            flat
            label="Previous"
            icon="arrow_back"
            :disable="currentQuestionIndex === 0"
            @click="previousQuestion"
          />
          
          <q-space />
          
          <q-btn
            v-if="currentQuestionIndex < (quiz.questions?.length || 0) - 1"
            unelevated
            color="primary"
            label="Next"
            icon-right="arrow_forward"
            :disable="!selectedAnswer"
            @click="nextQuestion"
          />
          
          <q-btn
            v-else
            unelevated
            color="positive"
            label="Submit Quiz"
            icon-right="check"
            :disable="!selectedAnswer"
            @click="submitQuiz"
          />
        </div>
      </div>

      <!-- Question Navigator Sidebar -->
      <div class="quiz-test__sidebar">
        <q-card>
          <q-card-section>
            <h4>Questions</h4>
            <div class="quiz-test__question-grid">
              <div
                v-for="(question, index) in quiz.questions"
                :key="question.id"
                class="quiz-test__question-nav-item"
                :class="{
                  'quiz-test__question-nav-item--current': index === currentQuestionIndex,
                  'quiz-test__question-nav-item--answered': answers[index] !== undefined
                }"
                @click="goToQuestion(index)"
              >
                {{ index + 1 }}
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Results Screen -->
    <div class="quiz-test__results" v-else>
      <q-card class="quiz-test__results-card">
        <q-card-section class="text-center">
          <q-icon name="check_circle" size="80px" color="positive" />
          <h2>Quiz Completed!</h2>
          <p class="quiz-test__results-message">
            You've completed the quiz. Your answers have been submitted.
          </p>
          
          <div class="quiz-test__results-stats">
            <div class="quiz-test__stat">
              <div class="quiz-test__stat-value">{{ quiz.questions?.length || 0 }}</div>
              <div class="quiz-test__stat-label">Questions Answered</div>
            </div>
            <div class="quiz-test__stat">
              <div class="quiz-test__stat-value">{{ formatTime(timeTaken) }}</div>
              <div class="quiz-test__stat-label">Time Taken</div>
            </div>
          </div>
          
          <div class="quiz-test__results-actions">
            <q-btn
              flat
              label="Back to Dashboard"
              icon="arrow_back"
              @click="router.visit('/quizzes')"
            />
            <q-btn
              unelevated
              color="primary"
              label="View Results"
              icon="assessment"
              @click="viewResults"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();
const props = defineProps({
  quizId: Number
});

// State
const quiz = ref({
  name: '',
  questions: [],
  time_limit_minutes: null
});

const currentQuestionIndex = ref(0);
const answers = ref({});
const selectedAnswer = ref(null);
const isCompleted = ref(false);
const timeRemaining = ref(0);
const timeTaken = ref(0);
const startTime = ref(null);
let timerInterval = null;

// Computed
const currentQuestion = computed(() => {
  return quiz.value.questions?.[currentQuestionIndex.value] || {};
});

// Methods
const loadQuiz = async () => {
  try {
    const response = await axios.get(`/api/quizzes/${props.quizId}`);
    quiz.value = response.data;
    
    // Initialize timer
    if (quiz.value.time_limit_minutes) {
      timeRemaining.value = quiz.value.time_limit_minutes * 60;
      startTimer();
    }
    
    startTime.value = Date.now();
    
    // Load saved answer for current question
    loadCurrentAnswer();
  } catch (error) {
    console.error('Failed to load quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load quiz',
      icon: 'error'
    });
  }
};

const startTimer = () => {
  timerInterval = setInterval(() => {
    timeRemaining.value--;
    if (timeRemaining.value <= 0) {
      submitQuiz();
    }
  }, 1000);
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const getDifficultyColor = (difficulty) => {
  const colors = {
    'Easy': 'positive',
    'Medium': 'warning',
    'Hard': 'negative'
  };
  return colors[difficulty] || 'info';
};

const selectAnswer = (optionId) => {
  selectedAnswer.value = optionId;
};

const saveCurrentAnswer = () => {
  if (selectedAnswer.value) {
    answers.value[currentQuestionIndex.value] = selectedAnswer.value;
  }
};

const loadCurrentAnswer = () => {
  selectedAnswer.value = answers.value[currentQuestionIndex.value] || null;
};

const nextQuestion = () => {
  saveCurrentAnswer();
  if (currentQuestionIndex.value < (quiz.value.questions?.length || 0) - 1) {
    currentQuestionIndex.value++;
    loadCurrentAnswer();
  }
};

const previousQuestion = () => {
  saveCurrentAnswer();
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
    loadCurrentAnswer();
  }
};

const goToQuestion = (index) => {
  saveCurrentAnswer();
  currentQuestionIndex.value = index;
  loadCurrentAnswer();
};

const submitQuiz = async () => {
  saveCurrentAnswer();
  
  // Calculate time taken
  timeTaken.value = Math.floor((Date.now() - startTime.value) / 1000);
  
  // Stop timer
  if (timerInterval) {
    clearInterval(timerInterval);
  }
  
  // Calculate score
  let correctAnswers = 0;
  quiz.value.questions.forEach((question, index) => {
    const userAnswer = answers.value[index];
    const correctOption = question.options?.find(opt => opt.is_correct);
    if (userAnswer === correctOption?.id) {
      correctAnswers++;
    }
  });
  
  const score = Math.round((correctAnswers / quiz.value.questions.length) * 100);
  
  // Store results in sessionStorage for the results page
  sessionStorage.setItem('quizResults', JSON.stringify({
    quizId: props.quizId,
    quizName: quiz.value.name,
    answers: answers.value,
    questions: quiz.value.questions,
    score: score,
    correctAnswers: correctAnswers,
    totalQuestions: quiz.value.questions.length,
    timeTaken: timeTaken.value
  }));
  
  try {
    // Here you would submit answers to the backend
    // await axios.post(`/api/quiz/attempts`, {
    //   quiz_id: props.quizId,
    //   answers: answers.value,
    //   score: score,
    //   time_taken: timeTaken.value
    // });
    
    isCompleted.value = true;
    
    $q.notify({
      type: 'positive',
      message: 'Quiz submitted successfully!',
      icon: 'check'
    });
  } catch (error) {
    console.error('Failed to submit quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to submit quiz',
      icon: 'error'
    });
  }
};

const viewResults = () => {
  router.visit(`/quizzes/${props.quizId}/results`);
};

// Lifecycle
onMounted(() => {
  loadQuiz();
});

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval);
  }
});
</script>

<style scoped>
.quiz-test {
  min-height: 100vh;
  background: #f7fafc;
  
  &__header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 10;
  }
  
  &__header-content {
    flex: 1;
  }
  
  &__title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 4px 0;
  }
  
  &__progress {
    font-size: 0.875rem;
    color: #718096;
  }
  
  &__timer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #fef3c7;
    border-radius: 8px;
    font-weight: 600;
    color: #92400e;
  }
  
  &__container {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 24px;
    padding: 24px;
    max-width: 1400px;
    margin: 0 auto;
  }
  
  &__question-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }
  
  &__question-card {
    border-radius: 16px;
  }
  
  &__question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }
  
  &__question-number {
    font-size: 1rem;
    font-weight: 600;
    color: #667eea;
  }
  
  &__question-text {
    font-size: 1.125rem;
    color: #1a202c;
    margin-bottom: 32px;
    line-height: 1.6;
  }
  
  &__options {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  
  &__option {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    
    &:hover {
      border-color: #667eea;
      background: rgba(102, 126, 234, 0.05);
    }
    
    &--selected {
      border-color: #667eea;
      background: rgba(102, 126, 234, 0.1);
    }
  }
  
  &__option-radio {
    flex-shrink: 0;
  }
  
  &__option-label {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f7fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: #667eea;
    flex-shrink: 0;
  }
  
  &__option-text {
    flex: 1;
    color: #1a202c;
    font-size: 1rem;
  }
  
  &__navigation {
    display: flex;
    gap: 16px;
  }
  
  &__sidebar {
    position: sticky;
    top: 88px;
    height: fit-content;
    
    .q-card {
      border-radius: 16px;
    }
    
    h4 {
      margin: 0 0 16px 0;
      font-size: 1rem;
      font-weight: 600;
    }
  }
  
  &__question-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
  }
  
  &__question-nav-item {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    color: #718096;
    transition: all 0.2s ease;
    
    &:hover {
      border-color: #667eea;
      color: #667eea;
    }
    
    &--current {
      border-color: #667eea;
      background: #667eea;
      color: white;
    }
    
    &--answered {
      border-color: #22c55e;
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
    }
  }
  
  &__results {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 80px);
    padding: 24px;
  }
  
  &__results-card {
    max-width: 600px;
    width: 100%;
    border-radius: 16px;
    
    h2 {
      margin: 16px 0 8px 0;
      font-size: 2rem;
      font-weight: 700;
    }
  }
  
  &__results-message {
    color: #718096;
    margin-bottom: 32px;
  }
  
  &__results-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 32px;
  }
  
  &__stat {
    padding: 16px;
    background: #f7fafc;
    border-radius: 12px;
  }
  
  &__stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1a202c;
  }
  
  &__stat-label {
    font-size: 0.875rem;
    color: #718096;
    margin-top: 4px;
  }
  
  &__results-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
  }
}

@media (max-width: 1024px) {
  .quiz-test {
    &__container {
      grid-template-columns: 1fr;
    }
    
    &__sidebar {
      position: static;
    }
  }
}












.quiz-test__question-wrapper {
  max-width: 900px;
  margin: 0 auto;
  padding: 24px;
}

.quiz-test__question-text {
  font-size: 1.5rem;
  line-height: 1.6;
  margin-bottom: 2rem;
  color: #1f2937;
}
@media (min-width: 768px) {
  .quiz-test__question-text { font-size: 1.75rem; }
}

.quiz-test__options-grid {
  display: grid;
  gap: 16px;
}
@media (min-width: 1024px) {
  .quiz-test__options-grid { grid-template-columns: repeat(2, 1fr); }
}

.quiz-test__option-card {
  position: relative;
  background: white;
  border: 2px solid transparent;
  border-radius: 16px;
  padding: 20px 20px 20px 80px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 16px;
  overflow: hidden;
}
.quiz-test__option-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.12);
  border-color: #3b82f6;
}

.quiz-test__option-card.selected {
  background: #eff6ff;
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59,130,246,0.3);
}

.quiz-test__option-card.correct {
  background: #f0fdf4;
  border-color: #22c55e;
}

.quiz-test__option-card.incorrect {
  background: #fef2f2;
  border-color: #ef4444;
}

.quiz-test__option-prefix {
  position: absolute;
  left: 20px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  gap: 12px;
}

.quiz-test__option-letter {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  font-weight: bold;
  font-size: 1.25rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(59,130,246,0.4);
}

.quiz-test__custom-radio {
  width: 28px;
  height: 28px;
  border: 3px solid #d1d5db;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.quiz-test__option-card.selected .quiz-test__custom-radio {
  border-color: #3b82f6;
}

.quiz-test__custom-radio-inner {
  width: 0;
  height: 0;
  background: #3b82f6;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.quiz-test__option-card.selected .quiz-test__custom-radio-inner {
  width: 12px;
  height: 12px;
}

.quiz-test__option-text {
  font-size: 1.125rem;
  color: #374151;
  font-weight: 500;
  padding-right: 48px;
}

.quiz-test__feedback-icon {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 2rem;
}

.icon-success { color: #22c55e; }
.icon-error   { color: #ef4444; }

/* Simple fade + scale animation */
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.3s ease;
}
.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: translateY(-50%) scale(0.3);
}
</style>