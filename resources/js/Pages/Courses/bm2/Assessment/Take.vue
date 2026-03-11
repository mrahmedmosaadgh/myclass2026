<template>
  <div class="bm2-assessment-take">
    <!-- Progress Bar -->
    <div class="fixed top-0 left-0 w-full bg-gray-200 h-2">
      <div 
        class="h-full bg-gradient-to-r from-green-400 to-blue-500 transition-all duration-500"
        :style="{ width: `${progressPercentage}%` }"
      ></div>
    </div>

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
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useBm2FirebaseSync } from '@/composables/useBm2FirebaseSync';

const router = useRouter();
const props = defineProps({
  id: {
    type: [Number, String],
    required: true
  }
});

// State
const question = ref(null);
const selectedAnswer = ref(null);
const isSubmitting = ref(false);
const showHint = ref(false);
const hintsUsed = ref(0);
const startTime = ref(Date.now());
const elapsedTime = ref(0);
const currentScore = ref(0);
const currentQuestionNumber = ref(1);
let timerInterval = null;

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

const submitAnswer = async () => {
  if (!selectedAnswer.value || isSubmitting.value) return;

  isSubmitting.value = true;

  try {
    const timeTaken = Math.floor((Date.now() - startTime.value) / 1000);

    const response = await axios.post(`/api/v2/bm2/assessment/${props.id}/submit`, {
      question_id: question.value.id,
      student_answer: selectedAnswer.value,
      time_taken_seconds: timeTaken,
      hints_used: hintsUsed.value,
    });

    const { current_question, points_earned, is_correct, explanation, next_question } = response.data.data;

    // Update score
    currentScore.value += points_earned;

    // Sync to Firebase
    const { syncAssessmentProgress } = useBm2FirebaseSync();
    await syncAssessmentProgress(props.id, {
      currentQuestion: currentQuestionNumber.value,
      score: currentScore.value,
      totalQuestions: 20,
      lastAnswer: selectedAnswer.value,
      timeElapsed: elapsedTime.value,
    });

    // Show feedback (could add a modal here)
    if (is_correct) {
      alert(`🎉 Correct! +${points_earned} points!`);
    } else {
      alert(`❌ Not quite. The correct answer was: ${explanation || current_question.correct_answer}`);
    }

    // Load next question or complete
    if (next_question) {
      question.value = next_question;
      currentQuestionNumber.value++;
      resetQuestionState();
    } else {
      // No more questions - complete assessment
      await completeAssessment();
    }
  } catch (error) {
    console.error('Error submitting answer:', error);
    alert('Oops! Something went wrong. Please try again.');
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
    const response = await axios.post(`/api/v2/bm2/assessment/${props.id}/complete`);
    
    // Navigate to results page
    router.push(`/bm2/assessment/${props.id}/results`);
  } catch (error) {
    console.error('Error completing assessment:', error);
  }
};

const confirmExit = () => {
  if (confirm('Are you sure you want to exit? Your progress will be lost.')) {
    router.push('/bm2/dashboard');
  }
};

// Lifecycle
onMounted(() => {
  // Load first question
  axios.get(`/api/v2/bm2/assessment/${props.id}/next`)
    .then(response => {
      question.value = response.data.data.question;
    })
    .catch(error => {
      console.error('Error loading question:', error);
      alert('Error loading question. Please refresh the page.');
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
