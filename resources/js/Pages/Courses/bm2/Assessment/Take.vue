<template>
  <Bm2GameWrapper
    v-if="allQuestions.length > 0"
    :assessmentId="Number(id)"
    :questions="allQuestions"
    @answer="handleGameAnswer"
    @game-complete="handleGameComplete"
    ref="gameWrapperRef"
    @click="handleGameClick"
  >
    <!-- Normal Mode Content -->
    <div class="bm2-assessment-take">
      <!-- Progress Bar -->
      <div class="fixed top-0 left-0 w-full bg-gray-200 h-2">
        <div 
          class="h-full bg-gradient-to-r from-green-400 to-blue-500 transition-all duration-500"
          :style="{ width: `${progressPercentage}%` }"
        ></div>
      </div>

      <!-- Celebration Feedback Component -->
      <FeedbackCelebration
        v-bind="celebrationState"
        @continue="onContinue"
        @close="onClose"
        @hidden="onHidden"
      />

      <div class="max-w-4xl mx-auto px-4 py-8 mt-4">
      <!-- Header Stats -->
      <div class="flex justify-between items-center mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex items-center space-x-4">
          <div class="text-2xl font-bold text-primary">
            Question {{ currentQuestionNumber }}
          </div>
          <div class="text-sm text-gray-500">
            📊 Score: <span class="font-bold">{{ currentScore }}</span> pts
          </div>
        </div>
        
        <div class="flex items-center space-x-4">
          <div class="text-2xl font-mono">
            ⏱️ {{ formattedTime }}
          </div>
          <button
            @click="showHint = !showHint"
            class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-full text-sm font-medium transition-colors"
          >
            💡 Hint
          </button>
        </div>
      </div>

      <!-- Question Card -->
      <div v-if="question" class="bg-white rounded-xl shadow-xl p-8 mb-6">
        <!-- Difficulty Badge -->
        <div class="mb-4">
          <span 
            :class="[
              'inline-block px-3 py-1 rounded-full text-sm font-semibold',
              difficultyClass(question.difficulty)
            ]"
          >
            {{ question.difficulty.toUpperCase() }}
          </span>
          <span class="ml-2 text-sm text-gray-500">
            Topic: {{ question.topic.replace('_', ' ') }}
          </span>
        </div>

        <!-- Question Text -->
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">
          {{ question.question_text }}
        </h2>

        <!-- Context Description (for word problems) -->
        <p v-if="question.context_description" class="mb-4 text-gray-600 italic">
          {{ question.context_description }}
        </p>

        <!-- Image if available -->
        <img 
          v-if="question.image_url" 
          :src="question.image_url" 
          alt="Question visual aid"
          class="max-w-full h-auto rounded-lg mb-4"
        />

        <!-- Answer Options -->
        <div class="space-y-3">
          <!-- Multiple Choice -->
          <div v-if="question.question_format === 'multiple_choice'">
            <div 
              v-for="(option, index) in question.options" 
              :key="index"
              @click="selectAnswer(option)"
              :class="[
                'p-4 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md',
                selectedAnswer === option ? 'border-primary bg-blue-50' : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <span class="font-bold mr-2">{{ String.fromCharCode(65 + index) }}.</span>
              {{ option }}
            </div>
          </div>

          <!-- True/False -->
          <div v-else-if="question.question_format === 'true_false'" class="flex space-x-4">
            <button
              @click="selectAnswer(true)"
              :class="[
                'flex-1 py-4 rounded-lg font-bold transition-all',
                selectedAnswer === true ? 'bg-green-500 text-white' : 'bg-gray-200 hover:bg-gray-300'
              ]"
            >
              ✓ True
            </button>
            <button
              @click="selectAnswer(false)"
              :class="[
                'flex-1 py-4 rounded-lg font-bold transition-all',
                selectedAnswer === false ? 'bg-red-500 text-white' : 'bg-gray-200 hover:bg-gray-300'
              ]"
            >
              ✗ False
            </button>
          </div>

          <!-- Fill in Blank / Short Answer -->
          <div v-else-if="['fill_in_blank', 'short_answer'].includes(question.question_format)">
            <input
              type="text"
              v-model="selectedAnswer"
              placeholder="Type your answer here..."
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @keyup.enter="submitAnswer"
            />
          </div>
        </div>

        <!-- Hint Section -->
        <div v-if="showHint && question.hints && question.hints.length > 0" class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <span class="text-2xl">💡</span>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800">Hint {{ hintsUsed + 1 }}</h3>
              <p class="text-sm text-yellow-700 mt-1">
                {{ question.hints[hintsUsed] }}
              </p>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8">
          <button
            @click="submitAnswer"
            :disabled="!selectedAnswer || isSubmitting"
            class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-4 px-6 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed text-lg"
          >
            {{ isSubmitting ? 'Submitting...' : 'Submit Answer' }} →
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-else class="bg-white rounded-xl shadow-xl p-12 text-center">
        <div class="animate-spin text-6xl mb-4">⏳</div>
        <p class="text-xl text-gray-600">Loading next question...</p>
      </div>

      <!-- Navigation -->
      <div class="flex justify-between mt-6">
        <button
          @click="confirmExit"
          class="px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-lg transition-colors"
        >
          ← Exit Assessment
        </button>
        
        <div class="text-sm text-gray-500 flex items-center">
          🎯 Keep going! You're doing great!
        </div>
      </div>
      </div> <!-- Closes max-w-4xl -->
    </div> <!-- Closes bm2-assessment-take -->
  </Bm2GameWrapper>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { useBm2FirebaseSync } from '@/composables/useBm2FirebaseSync';
import { useCelebrationFeedback } from '@/composables/useCelebrationFeedback';
import FeedbackCelebration from '@/Components/Courses/bm2/FeedbackCelebration.vue';
import Bm2GameWrapper from '@/Components/Courses/bm2/Bm2GameWrapper.vue';

// Configure axios with credentials for Sanctum
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const props = defineProps({
  id: {
    type: [Number, String],
    required: true
  }
});

// State
const question = ref(null);
const allQuestions = ref([]); // Store all questions loaded at once
const selectedAnswer = ref(null);
const isSubmitting = ref(false);
const showHint = ref(false);
const hintsUsed = ref(0);
const startTime = ref(Date.now());
const elapsedTime = ref(0);
const currentScore = ref(0);
const currentQuestionNumber = ref(1);
let timerInterval = null;
const gameWrapperRef = ref(null);

// Store all answers locally during assessment
const studentAnswers = ref([]);

// Initialize celebration feedback
const { 
  celebrationState, 
  showSuccess, 
  showEncouragement, 
  showAchievement, 
  showCombo, 
  showPerfect,
  onContinue,
  onClose,
  onHidden 
} = useCelebrationFeedback();

// Debug: Log props on mount
console.log('Take.vue - Props received:', props);
console.log('Take.vue - Assessment ID:', props.id);

// Computed
const progressPercentage = computed(() => {
  // Assuming ~20 questions per assessment
  return Math.min((currentQuestionNumber.value / 20) * 100, 100);
});

const formattedTime = computed(() => {
  const seconds = Math.floor(elapsedTime.value / 1000);
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
});

// Methods
const handleGameClick = (event) => {
  if (gameWrapperRef.value && gameWrapperRef.value.handleClick) {
    gameWrapperRef.value.handleClick(event);
  }
};

const handleGameComplete = (gameStats) => {
  console.log('Game mode completed:', gameStats);
  // Navigate to results with game stats
  router.visit(`/bm2/assessment/${id}/results`, {
    data: { gameStats }
  });
};

const handleGameAnswer = (answerData) => {
  console.log('Game answer received:', answerData);
  
  // Store answer locally (will submit all at end)
  const answerRecord = {
    question_id: answerData.questionId,
    student_answer: answerData.selectedAnswer,
    time_taken_seconds: Math.floor(elapsedTime.value / 1000),
    hints_used: 0,
    question_number: currentQuestionNumber.value,
  };
  
  studentAnswers.value.push(answerRecord);
  console.log('Answer stored locally:', answerRecord);
  console.log('Total answers stored:', studentAnswers.value.length);
  
  // Update score display
  if (answerData.isCorrect && answerData.points) {
    currentScore.value += answerData.points;
  }
};
const difficultyClass = (difficulty) => {
  const classes = {
    easy: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    hard: 'bg-red-100 text-red-800'
  };
  return classes[difficulty] || classes.medium;
};

const selectAnswer = (answer) => {
  selectedAnswer.value = answer;
};

/**
 * Check answer locally for immediate feedback (optimistic UI)
 * Note: This is for UI purposes only, real scoring happens on backend
 */
const checkAnswerLocally = async (questionData, studentAnswer) => {
  if (!questionData || !questionData.correct_answer) return false;
  
  // Case-insensitive string comparison
  const correct = String(questionData.correct_answer).toLowerCase().trim();
  const student = String(studentAnswer).toLowerCase().trim();
  
  return correct === student;
};

/**
 * Load next question from local array (all questions loaded at once)
 */
const loadNextQuestion = () => {
  if (currentQuestionNumber.value < allQuestions.value.length) {
    question.value = allQuestions.value[currentQuestionNumber.value];
    currentQuestionNumber.value++;
    console.log('Take.vue - Next question from local array:', question.value?.id);
  } else {
    // No more questions - complete assessment
    console.log('Take.vue - All questions answered - completing assessment');
    completeAssessment();
  }
};

const submitAnswer = async () => {
  if (!selectedAnswer.value || isSubmitting.value) {
    console.warn('No answer selected or already submitting');
    return;
  }

  isSubmitting.value = true;

  try {
    const timeTaken = Math.floor((Date.now() - startTime.value) / 1000);
    
    // Convert answer to appropriate string format
    let answerString;
    if (typeof selectedAnswer.value === 'boolean') {
      answerString = selectedAnswer.value ? 'True' : 'False';
    } else if (typeof selectedAnswer.value === 'number') {
      answerString = String(selectedAnswer.value);
    } else if (typeof selectedAnswer.value === 'object') {
      // If it's an object, stringify it
      answerString = JSON.stringify(selectedAnswer.value);
    } else {
      // Already a string or convert to string
      answerString = String(selectedAnswer.value ?? '');
    }
    
    // Store answer locally (will submit all at end)
    const answerRecord = {
      question_id: question.value.id,
      student_answer: answerString,
      time_taken_seconds: timeTaken,
      hints_used: hintsUsed.value,
      question_number: currentQuestionNumber.value,
    };
    
    studentAnswers.value.push(answerRecord);
    console.log('Answer stored locally:', answerRecord);
    console.log('Total answers stored:', studentAnswers.value.length);

    // Calculate temporary score for display (optimistic UI update)
    // Real score will be calculated by backend
    const isCorrectTemp = await checkAnswerLocally(question.value, answerString);
    const pointsTemp = isCorrectTemp ? question.value.points_default : 0;
    currentScore.value += pointsTemp;

    // Show celebration feedback based on performance
    if (isCorrectTemp) {
      // Check if it's a perfect answer (fast response)
      const timeTaken = elapsedTime.value / 1000;
      if (timeTaken < 5 && pointsTemp >= 20) {
        showPerfect(pointsTemp);
      } else {
        showSuccess(pointsTemp);
      }
    } else {
      showEncouragement(question.value.correct_answer);
    }

    // Move to next question
    currentQuestionNumber.value++;
    resetQuestionState();
    
    // Load next question from backend (adaptive)
    await loadNextQuestion();
    
  } catch (error) {
    console.error('Error processing answer:', error);
    alert('Error processing answer. Please try again.');
  } finally {
    isSubmitting.value = false;
  }
};

const resetQuestionState = () => {
  selectedAnswer.value = null;
  showHint.value = false;
  hintsUsed.value = 0;
  startTime.value = Date.now();
};

const completeAssessment = async () => {
  try {
    console.log('Completing assessment with', studentAnswers.value.length, 'answers');
    console.log('Submitting all answers at once:', studentAnswers.value);
    
    // Submit all answers at once to backend
    const response = await axios.post(`/api/v2/bm2/assessment/${props.id}/submit-all`, {
      answers: studentAnswers.value,
      total_time_seconds: Math.floor(elapsedTime.value / 1000),
    });
    
    const { final_score, performance_level, awarded_badges, total_points } = response.data.data;
    
    console.log('Assessment completed!', {
      final_score,
      performance_level,
      awarded_badges,
      total_points
    });
    
    // Sync final results to Firebase
    const { syncAssessmentProgress } = useBm2FirebaseSync();
    syncAssessmentProgress(props.id, {
      completed: true,
      finalScore: final_score,
      performanceLevel: performance_level,
      badges: awarded_badges,
      totalPoints: total_points,
      totalAnswers: studentAnswers.value.length,
      timeElapsed: elapsedTime.value,
    }).catch(error => {
      console.warn('Firebase sync failed (assessment completed):', error.message);
    });
    
    // Navigate to results page using Inertia
    router.visit(`/bm2/assessment/${props.id}/results`);
  } catch (error) {
    console.error('Error completing assessment:', error);
    alert('Error finalizing assessment. Please try again.');
  }
};

const confirmExit = () => {
  if (confirm('Are you sure you want to exit? Your progress will be lost.')) {
    router.visit('/bm2/dashboard');
  }
};

// Lifecycle
onMounted(() => {
  // Validate that we have an assessment ID
  if (!props.id) {
    console.error('Take.vue - No assessment ID provided!');
    alert('Invalid assessment. Please start a new assessment.');
    router.visit('/bm2/dashboard');
    return;
  }
  
  console.log('Take.vue - Loading all questions for assessment ID:', props.id);
  
  // Load all questions at once using the new endpoint
  axios.get(`/api/v2/bm2/assessment/${props.id}/questions`)
    .then(response => {
      const { questions } = response.data.data;
      allQuestions.value = questions || [];
      
      if (allQuestions.value.length > 0) {
        question.value = allQuestions.value[0];
        console.log('Take.vue - All questions loaded successfully:', allQuestions.value.length, 'questions');
        console.log('Take.vue - First question:', question.value?.id);
      } else {
        console.warn('Take.vue - No questions available');
        alert('No questions available for this assessment.');
        router.visit('/bm2/dashboard');
      }
    })
    .catch(error => {
      console.error('Take.vue - Error loading questions:', error);
      
      // Provide more specific error message for other errors
      let errorMessage = 'Error loading questions. ';
      if (error.response) {
        if (error.response.status === 401 || error.response.status === 403) {
          errorMessage += 'Please login to continue.';
        } else if (error.response.status === 404) {
          errorMessage += 'Assessment not found.';
        } else if (error.response.status === 500) {
          errorMessage += 'Server error. Please try again.';
        } else {
          errorMessage += 'Please refresh the page.';
        }
      } else if (error.request) {
        errorMessage += 'Network error. Please check your connection.';
      } else {
        errorMessage += error.message || 'An unexpected error occurred.';
      }
      
      alert(errorMessage);
      
      // Redirect to dashboard if there's an error
      setTimeout(() => {
        router.visit('/bm2/dashboard');
      }, 2000);
    });

  // Start timer
  timerInterval = setInterval(() => {
    elapsedTime.value = Date.now() - startTime.value;
  }, 1000);
});

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval);
  }
});
</script>

<style scoped>
.bm2-assessment-take {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding-bottom: 2rem;
}

.text-primary {
  color: #667eea;
}

.bg-primary {
  background-color: #667eea;
}

.bg-primary:hover {
  background-color: #5568d3;
}
</style>
