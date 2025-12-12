<template>
  <div class="quiz-preview">
    <!-- Header -->
    <div class="quiz-preview__header">
      <q-btn
        flat
        round
        icon="arrow_back"
        @click="router.visit('/quizzes')"
      />
      <div class="quiz-preview__header-content">
        <h1 class="quiz-preview__title">{{ quiz.name }}</h1>
        <p v-if="quiz.description" class="quiz-preview__description">{{ quiz.description }}</p>
      </div>
      
      <div class="quiz-preview__header-actions">
        <q-btn-dropdown
          unelevated
          color="primary"
          label="View Mode"
          icon="devices"
        >
          <q-list>
            <q-item clickable v-close-popup @click="viewMode = 'desktop'">
              <q-item-section avatar>
                <q-icon name="computer" />
              </q-item-section>
              <q-item-section>Desktop</q-item-section>
              <q-item-section side v-if="viewMode === 'desktop'">
                <q-icon name="check" color="primary" />
              </q-item-section>
            </q-item>
            
            <q-item clickable v-close-popup @click="viewMode = 'tablet'">
              <q-item-section avatar>
                <q-icon name="tablet" />
              </q-item-section>
              <q-item-section>Tablet</q-item-section>
              <q-item-section side v-if="viewMode === 'tablet'">
                <q-icon name="check" color="primary" />
              </q-item-section>
            </q-item>
            
            <q-item clickable v-close-popup @click="viewMode = 'mobile'">
              <q-item-section avatar>
                <q-icon name="smartphone" />
              </q-item-section>
              <q-item-section>Mobile</q-item-section>
              <q-item-section side v-if="viewMode === 'mobile'">
                <q-icon name="check" color="primary" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
        
        <q-btn
          unelevated
          color="positive"
          label="Start Test"
          icon="play_arrow"
          @click="startTest"
        />
      </div>
    </div>

    <!-- Preview Container -->
    <div class="quiz-preview__container">
      <div
        class="quiz-preview__viewport"
        :class="`quiz-preview__viewport--${viewMode}`"
      >
        <!-- Quiz Info Card -->
        <q-card class="quiz-preview__info-card">
          <q-card-section>
            <div class="quiz-preview__info-grid">
              <div class="quiz-preview__info-item">
                <q-icon name="quiz" size="24px" color="primary" />
                <div>
                  <div class="quiz-preview__info-value">{{ quiz.questions?.length || 0 }}</div>
                  <div class="quiz-preview__info-label">Questions</div>
                </div>
              </div>
              
              <div v-if="quiz.time_limit_minutes" class="quiz-preview__info-item">
                <q-icon name="schedule" size="24px" color="warning" />
                <div>
                  <div class="quiz-preview__info-value">{{ quiz.time_limit_minutes }}</div>
                  <div class="quiz-preview__info-label">Minutes</div>
                </div>
              </div>
              
              <div class="quiz-preview__info-item">
                <q-icon name="school" size="24px" color="info" />
                <div>
                  <div class="quiz-preview__info-value">{{ quiz.grade?.name || 'N/A' }}</div>
                  <div class="quiz-preview__info-label">Grade</div>
                </div>
              </div>
              
              <div class="quiz-preview__info-item">
                <q-icon name="book" size="24px" color="positive" />
                <div>
                  <div class="quiz-preview__info-value">{{ quiz.subject?.name || 'N/A' }}</div>
                  <div class="quiz-preview__info-label">Subject</div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Questions List -->
        <div class="quiz-preview__questions">
          <h3 class="quiz-preview__section-title">Questions Preview</h3>
          
          <div
            v-for="(question, index) in quiz.questions"
            :key="question.id"
            class="quiz-preview__question-card"
          >
            <q-card>
              <q-card-section>
                <div class="quiz-preview__question-header">
                  <div class="quiz-preview__question-number">
                    Question {{ index + 1 }}
                  </div>
                  <q-badge
                    :color="getDifficultyColor(question.difficulty)"
                    :label="question.difficulty || 'Medium'"
                  />
                </div>
                
                <div class="quiz-preview__question-text" v-html="question.question_text" />
                
                <!-- Options (if available) -->
                <div v-if="question.options && question.options.length" class="quiz-preview__options">
                  <div
                    v-for="(option, optIndex) in question.options"
                    :key="option.id"
                    class="quiz-preview__option"
                    :class="{ 'quiz-preview__option--correct': showAnswers && option.is_correct }"
                  >
                    <div class="quiz-preview__option-label">
                      {{ String.fromCharCode(65 + optIndex) }}
                    </div>
                    <div class="quiz-preview__option-text">
                      {{ option.option_text }}
                    </div>
                    <q-icon
                      v-if="showAnswers && option.is_correct"
                      name="check_circle"
                      color="positive"
                      size="20px"
                    />
                  </div>
                </div>
                
                <!-- Explanation (if showing answers) -->
                <div v-if="showAnswers && question.explanation" class="quiz-preview__explanation">
                  <div class="quiz-preview__explanation-header">
                    <q-icon name="lightbulb" color="warning" />
                    <strong>Explanation</strong>
                  </div>
                  <p>{{ question.explanation }}</p>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- Settings Info -->
        <q-card class="quiz-preview__settings-card">
          <q-card-section>
            <h4>Quiz Settings</h4>
            
            <div class="quiz-preview__settings-list">
              <div class="quiz-preview__setting-item">
                <q-icon
                  :name="quiz.shuffle_questions ? 'check_circle' : 'cancel'"
                  :color="quiz.shuffle_questions ? 'positive' : 'grey'"
                />
                <span>Shuffle Questions</span>
              </div>
              
              <div class="quiz-preview__setting-item">
                <q-icon
                  :name="quiz.shuffle_options ? 'check_circle' : 'cancel'"
                  :color="quiz.shuffle_options ? 'positive' : 'grey'"
                />
                <span>Shuffle Options</span>
              </div>
              
              <div class="quiz-preview__setting-item">
                <q-icon
                  :name="quiz.allow_review ? 'check_circle' : 'cancel'"
                  :color="quiz.allow_review ? 'positive' : 'grey'"
                />
                <span>Allow Review</span>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Floating Action Bar -->
    <div class="quiz-preview__action-bar">
      <q-toggle
        v-model="showAnswers"
        label="Show Answers"
        color="primary"
      />
      
      <q-space />
      
      <q-btn
        flat
        label="Edit Quiz"
        icon="edit"
        @click="editQuiz"
      />
      
      <q-btn
        unelevated
        color="primary"
        label="Start Test"
        icon="play_arrow"
        @click="startTest"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
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
  description: '',
  questions: [],
  time_limit_minutes: null,
  shuffle_questions: false,
  shuffle_options: false,
  allow_review: true
});

const viewMode = ref('desktop'); // desktop, tablet, mobile
const showAnswers = ref(false);

// Methods
const loadQuiz = async () => {
  try {
    const response = await axios.get(`/api/quizzes/${props.quizId}`);
    quiz.value = response.data;
  } catch (error) {
    console.error('Failed to load quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load quiz',
      icon: 'error'
    });
  }
};

const getDifficultyColor = (difficulty) => {
  const colors = {
    'Easy': 'positive',
    'Medium': 'warning',
    'Hard': 'negative'
  };
  return colors[difficulty] || 'info';
};

const editQuiz = () => {
  router.visit(`/quizzes/${props.quizId}/edit`);
};

const startTest = () => {
  // Navigate to quiz engine or start test mode
  router.visit(`/quizzes/${props.quizId}/test`);
};

// Lifecycle
onMounted(() => {
  loadQuiz();
});
</script>

<style scoped>
.quiz-preview {
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
  
  &__description {
    font-size: 0.875rem;
    color: #718096;
    margin: 4px 0 0 0;
  }
  
  &__header-actions {
    display: flex;
    gap: 12px;
  }
  
  &__container {
    display: flex;
    justify-content: center;
    padding: 40px 24px 100px;
  }
  
  &__viewport {
    width: 100%;
    max-width: 1200px;
    transition: all 0.3s ease;
    
    &--desktop {
      max-width: 1200px;
    }
    
    &--tablet {
      max-width: 768px;
    }
    
    &--mobile {
      max-width: 375px;
    }
  }
  
  &__info-card {
    margin-bottom: 24px;
    border-radius: 16px;
  }
  
  &__info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 24px;
  }
  
  &__info-item {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  &__info-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a202c;
    line-height: 1;
  }
  
  &__info-label {
    font-size: 0.75rem;
    color: #718096;
    margin-top: 4px;
  }
  
  &__questions {
    margin-bottom: 24px;
  }
  
  &__section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1a202c;
    margin: 0 0 16px 0;
  }
  
  &__question-card {
    margin-bottom: 16px;
    
    .q-card {
      border-radius: 12px;
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
    font-size: 1rem;
    color: #1a202c;
    margin-bottom: 16px;
    line-height: 1.6;
  }
  
  &__options {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  
  &__option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.2s ease;
    
    &--correct {
      background: rgba(34, 197, 94, 0.05);
      border-color: #22c55e;
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
  
  &__explanation {
    margin-top: 16px;
    padding: 16px;
    background: rgba(251, 191, 36, 0.05);
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
  
  &__settings-card {
    border-radius: 16px;
    
    h4 {
      margin: 0 0 16px 0;
      font-size: 1.125rem;
      font-weight: 600;
    }
  }
  
  &__settings-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  
  &__setting-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.875rem;
    color: #1a202c;
  }
  
  &__action-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid #e2e8f0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 10;
  }
}

 
@media (max-width: 960px) {
  .quiz-preview {
    &__header {
      flex-wrap: wrap;
    }
    
    &__header-actions {
      width: 100%;
      justify-content: flex-end;
    }
    
    &__info-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
}

@media (max-width: 600px) {
  .quiz-preview {
    &__container {
      padding: 20px 16px 100px;
    }
    
    &__info-grid {
      grid-template-columns: 1fr;
    }
    
    &__action-bar {
      flex-wrap: wrap;
      
      .q-space {
        display: none;
      }
    }
  }
}
</style>
