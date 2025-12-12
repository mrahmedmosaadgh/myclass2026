<template>
  <div class="quiz-results">
    <!-- Header -->
    <div class="quiz-results__header">
      <q-btn
        flat
        round
        icon="arrow_back"
        @click="router.visit('/quizzes')"
      />
      <div class="quiz-results__header-content">
        <h1 class="quiz-results__title">{{ results.quizName }}</h1>
        <p class="quiz-results__subtitle">Quiz Results</p>
      </div>
    </div>

    <!-- Score Summary -->
    <div class="quiz-results__summary">
      <q-card class="quiz-results__score-card">
        <q-card-section class="text-center">
          <div class="quiz-results__score-circle">
            <q-circular-progress
              :value="results.score"
              size="180px"
              :thickness="0.12"
              :color="getScoreColor(results.score)"
              track-color="grey-3"
              show-value
              class="quiz-results__progress"
            >
              <div class="quiz-results__score-value">
                {{ results.score }}%
              </div>
            </q-circular-progress>
          </div>
          
          <h2 class="quiz-results__score-label">{{ getScoreLabel(results.score) }}</h2>
          
          <div class="quiz-results__stats-grid">
            <div class="quiz-results__stat">
              <q-icon name="check_circle" size="32px" color="positive" />
              <div class="quiz-results__stat-value">{{ results.correctAnswers }}</div>
              <div class="quiz-results__stat-label">Correct</div>
            </div>
            
            <div class="quiz-results__stat">
              <q-icon name="cancel" size="32px" color="negative" />
              <div class="quiz-results__stat-value">{{ results.totalQuestions - results.correctAnswers }}</div>
              <div class="quiz-results__stat-label">Incorrect</div>
            </div>
            
            <div class="quiz-results__stat">
              <q-icon name="schedule" size="32px" color="info" />
              <div class="quiz-results__stat-value">{{ formatTime(results.timeTaken) }}</div>
              <div class="quiz-results__stat-label">Time Taken</div>
            </div>
          </div>
          
          <div class="quiz-results__actions">
            <q-btn
              flat
              label="Back to Dashboard"
              icon="arrow_back"
              @click="router.visit('/quizzes')"
            />
            <q-btn
              unelevated
              color="primary"
              label="Retake Quiz"
              icon="refresh"
              @click="retakeQuiz"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Question Review -->
    <div class="quiz-results__review">
      <div class="quiz-results__review-header">
        <h3>Question Review</h3>
        <q-btn-toggle
          v-model="filterMode"
          :options="[
            { label: 'All', value: 'all' },
            { label: 'Correct', value: 'correct' },
            { label: 'Incorrect', value: 'incorrect' }
          ]"
          unelevated
          dense
        />
      </div>
      
      <div class="quiz-results__questions">
        <div
          v-for="(question, index) in filteredQuestions"
          :key="question.id"
          class="quiz-results__question-card"
        >
          <q-card :class="getQuestionCardClass(question, index)">
            <q-card-section>
              <!-- Question Header -->
              <div class="quiz-results__question-header">
                <div class="quiz-results__question-number">
                  Question {{ index + 1 }}
                </div>
                <q-badge
                  :color="isCorrect(question, index) ? 'positive' : 'negative'"
                  :icon="isCorrect(question, index) ? 'check' : 'close'"
                  :label="isCorrect(question, index) ? 'Correct' : 'Incorrect'"
                />
              </div>
              
              <!-- Question Text -->
              <div class="quiz-results__question-text" v-html="question.question_text" />
              
              <!-- Options -->
              <div class="quiz-results__options">
                <div
                  v-for="(option, optIndex) in question.options"
                  :key="option.id"
                  class="quiz-results__option"
                  :class="getOptionClass(option, question, index)"
                >
                  <div class="quiz-results__option-label">
                    {{ String.fromCharCode(65 + optIndex) }}
                  </div>
                  <div class="quiz-results__option-text">
                    {{ option.option_text }}
                  </div>
                  <div class="quiz-results__option-icon">
                    <q-icon
                      v-if="option.is_correct"
                      name="check_circle"
                      color="positive"
                      size="24px"
                    />
                    <q-icon
                      v-else-if="results.answers[index] === option.id"
                      name="cancel"
                      color="negative"
                      size="24px"
                    />
                  </div>
                </div>
              </div>
              
              <!-- Explanation -->
              <div v-if="question.explanation" class="quiz-results__explanation">
                <div class="quiz-results__explanation-header">
                  <q-icon name="lightbulb" color="warning" />
                  <strong>Explanation</strong>
                </div>
                <p>{{ question.explanation }}</p>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const props = defineProps({
  quizId: Number
});

// State
const results = ref({
  quizId: null,
  quizName: '',
  answers: {},
  questions: [],
  score: 0,
  correctAnswers: 0,
  totalQuestions: 0,
  timeTaken: 0
});

const filterMode = ref('all');

// Computed
const filteredQuestions = computed(() => {
  if (filterMode.value === 'all') {
    return results.value.questions;
  }
  
  return results.value.questions.filter((question, index) => {
    const correct = isCorrect(question, index);
    return filterMode.value === 'correct' ? correct : !correct;
  });
});

// Methods
const loadResults = () => {
  const storedResults = sessionStorage.getItem('quizResults');
  
  if (storedResults) {
    results.value = JSON.parse(storedResults);
  } else {
    $q.notify({
      type: 'warning',
      message: 'No quiz results found. Please take the quiz first.',
      icon: 'warning'
    });
    router.visit(`/quizzes/${props.quizId}/test`);
  }
};

const isCorrect = (question, index) => {
  const userAnswer = results.value.answers[index];
  const correctOption = question.options?.find(opt => opt.is_correct);
  return userAnswer === correctOption?.id;
};

const getOptionClass = (option, question, questionIndex) => {
  const userAnswer = results.value.answers[questionIndex];
  const isUserAnswer = userAnswer === option.id;
  const isCorrectAnswer = option.is_correct;
  
  return {
    'quiz-results__option--correct': isCorrectAnswer,
    'quiz-results__option--incorrect': isUserAnswer && !isCorrectAnswer,
    'quiz-results__option--selected': isUserAnswer
  };
};

const getQuestionCardClass = (question, index) => {
  return {
    'quiz-results__question-card--correct': isCorrect(question, index),
    'quiz-results__question-card--incorrect': !isCorrect(question, index)
  };
};

const getScoreColor = (score) => {
  if (score >= 80) return 'positive';
  if (score >= 60) return 'warning';
  return 'negative';
};

const getScoreLabel = (score) => {
  if (score >= 90) return 'Excellent!';
  if (score >= 80) return 'Great Job!';
  if (score >= 70) return 'Good Work!';
  if (score >= 60) return 'Not Bad!';
  return 'Keep Practicing!';
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${String(secs).padStart(2, '0')}`;
};

const retakeQuiz = () => {
  sessionStorage.removeItem('quizResults');
  router.visit(`/quizzes/${props.quizId}/test`);
};

// Lifecycle
onMounted(() => {
  loadResults();
});
</script>

<style scoped>
.quiz-results {
  min-height: 100vh;
  background: #f7fafc;
  
  &__header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
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
    margin: 0;
  }
  
  &__subtitle {
    font-size: 0.875rem;
    color: #718096;
    margin: 4px 0 0 0;
  }
  
  &__summary {
    padding: 40px 24px;
    display: flex;
    justify-content: center;
  }
  
  &__score-card {
    max-width: 600px;
    width: 100%;
    border-radius: 16px;
  }
  
  &__score-circle {
    margin-bottom: 24px;
  }
  
  &__progress {
    font-size: 2.5rem;
    font-weight: 700;
  }
  
  &__score-value {
    font-size: 2.5rem;
    font-weight: 700;
  }
  
  &__score-label {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 32px 0;
  }
  
  &__stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 32px;
  }
  
  &__stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
  }
  
  &__stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a202c;
  }
  
  &__stat-label {
    font-size: 0.875rem;
    color: #718096;
  }
  
  &__actions {
    display: flex;
    gap: 16px;
    justify-content: center;
  }
  
  &__review {
    padding: 0 24px 40px;
    max-width: 1200px;
    margin: 0 auto;
  }
  
  &__review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    
    h3 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 700;
      color: #1a202c;
    }
  }
  
  &__questions {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }
  
  &__question-card {
    .q-card {
      border-radius: 16px;
      border-left: 4px solid transparent;
    }
    
    &--correct .q-card {
      border-left-color: #22c55e;
      background: rgba(34, 197, 94, 0.02);
    }
    
    &--incorrect .q-card {
      border-left-color: #ef4444;
      background: rgba(239, 68, 68, 0.02);
    }
  }
  
  &__question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }
  
  &__question-number {
    font-size: 1rem;
    font-weight: 600;
    color: #667eea;
  }
  
  &__question-text {
    font-size: 1.125rem;
    color: #1a202c;
    margin-bottom: 24px;
    line-height: 1.6;
  }
  
  &__options {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
  }
  
  &__option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.2s ease;
    
    &--correct {
      border-color: #22c55e;
      background: rgba(34, 197, 94, 0.1);
    }
    
    &--incorrect {
      border-color: #ef4444;
      background: rgba(239, 68, 68, 0.1);
    }
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
  }
  
  &__option-icon {
    flex-shrink: 0;
    width: 24px;
  }
  
  &__explanation {
    margin-top: 16px;
    padding: 16px;
    background: rgba(251, 191, 36, 0.1);
    border-left: 4px solid #fbbf24;
    border-radius: 8px;
    
    p {
      margin: 8px 0 0 0;
      color: #1a202c;
      line-height: 1.6;
    }
  }
  
  &__explanation-header {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #d97706;
    font-weight: 600;
  }
}

@media (max-width: 768px) {
  .quiz-results {
    &__stats-grid {
      grid-template-columns: 1fr;
    }
    
    &__actions {
      flex-direction: column;
      width: 100%;
      
      .q-btn {
        width: 100%;
      }
    }
    
    &__review-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 16px;
    }
  }
}
</style>
