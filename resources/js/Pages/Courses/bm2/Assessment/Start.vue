<template>
  <div class="bm2-assessment-start">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-primary mb-4">
          🎯 Basic Math Placement Test
        </h1>
        <p class="text-lg text-gray-600">
          Let's discover your math superpowers! Answer questions to find your perfect learning path.
        </p>
      </div>

      <!-- Error Alert -->
      <div v-if="error" :class="[
        'rounded-lg p-4 mb-6 flex items-start',
        error.type === 'auth' ? 'bg-red-50 border-l-4 border-red-400' : 'bg-yellow-50 border-l-4 border-yellow-400'
      ]">
        <div class="flex-shrink-0">
          <span class="text-2xl">{{ error.type === 'auth' ? '🔒' : '⚠️' }}</span>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium" :class="error.type === 'auth' ? 'text-red-800' : 'text-yellow-800'">
            {{ error.title }}
          </h3>
          <div class="mt-2 text-sm" :class="error.type === 'auth' ? 'text-red-700' : 'text-yellow-700'">
            <p>{{ error.message }}</p>
          </div>
          <div v-if="error.type === 'auth'" class="mt-4">
            <a 
              href="/login" 
              class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors"
            >
              🔐 Go to Login
            </a>
          </div>
        </div>
        <button @click="error = null" class="ml-4 text-gray-400 hover:text-gray-600">
          ✕
        </button>
      </div>

      <!-- Instructions Card -->
      <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-4">📋 What to Expect</h2>
        <ul class="space-y-3">
          <li class="flex items-start">
            <span class="text-green-500 mr-2">✓</span>
            <span>Questions will adapt to your skill level as you answer</span>
          </li>
          <li class="flex items-start">
            <span class="text-green-500 mr-2">✓</span>
            <span>Takes about 15-20 minutes</span>
          </li>
          <li class="flex items-start">
            <span class="text-green-500 mr-2">✓</span>
            <span>You'll get a personalized learning plan at the end</span>
          </li>
          <li class="flex items-start">
            <span class="text-green-500 mr-2">✓</span>
            <span>Earn badges and points along the way!</span>
          </li>
        </ul>
      </div>

      <!-- Game Mode Selection -->
      <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-4">🎮 Game Mode</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div 
            @click="enableGameMode = false; selectedGameMode = null;"
            :class="[
              'border-2 rounded-lg p-4 cursor-pointer transition-all',
              !enableGameMode ? 'border-primary bg-blue-50' : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="text-3xl mb-2">📝</div>
            <h3 class="font-bold text-lg mb-2">Normal Mode</h3>
            <p class="text-sm text-gray-600">Standard assessment format</p>
          </div>

          <div 
            @click="enableGameMode = true; if (!selectedGameMode) selectedGameMode = 'falling';"
            :class="[
              'border-2 rounded-lg p-4 cursor-pointer transition-all',
              enableGameMode ? 'border-primary bg-blue-50' : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="text-3xl mb-2">🎮</div>
            <h3 class="font-bold text-lg mb-2">Game Mode</h3>
            <p class="text-sm text-gray-600">Make it fun and challenging!</p>
          </div>
        </div>
        
        <!-- Game Mode Selector -->
        <div v-if="enableGameMode" class="mt-6">
          <GameModeSelector
            @start="handleGameModeSelection"
            @cancel="enableGameMode = false"
          />
        </div>
        
        <div v-else-if="enableGameMode" class="mt-4 p-4 bg-yellow-50 border-l-4 border-yellow-400">
          <div class="flex">
            <div class="flex-shrink-0">
              <span class="text-2xl">🎯</span>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800">Game Mode Benefits</h3>
              <div class="mt-2 text-sm text-yellow-700">
                <ul class="list-disc list-inside space-y-1">
                  <li>Earn points and build combos</li>
                  <li>Collect power-ups for bonuses</li>
                  <li>Compete with yourself for high scores</li>
                  <li>Earn special game mode badges!</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Assessment Type Selection -->
      <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-4">🎮 Choose Your Challenge</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div 
            @click="selectedType = 'placement'"
            :class="[
              'border-2 rounded-lg p-4 cursor-pointer transition-all',
              selectedType === 'placement' ? 'border-primary bg-blue-50' : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="text-3xl mb-2">🚀</div>
            <h3 class="font-bold text-lg mb-2">Placement Test</h3>
            <p class="text-sm text-gray-600">Find your starting point and get a personalized learning path</p>
          </div>

          <div 
            v-if="hasPreviousAssessment"
            @click="selectedType = 'progress'"
            :class="[
              'border-2 rounded-lg p-4 cursor-pointer transition-all',
              selectedType === 'progress' ? 'border-primary bg-blue-50' : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="text-3xl mb-2">📈</div>
            <h3 class="font-bold text-lg mb-2">Progress Check</h3>
            <p class="text-sm text-gray-600">See how much you've improved</p>
          </div>

          <div 
            @click="selectedType = 'final'"
            :class="[
              'border-2 rounded-lg p-4 cursor-pointer transition-all',
              selectedType === 'final' ? 'border-primary bg-blue-50' : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="text-3xl mb-2">🏆</div>
            <h3 class="font-bold text-lg mb-2">Final Challenge</h3>
            <p class="text-sm text-gray-600">Show what you've mastered!</p>
          </div>
        </div>

        <!-- Grade Level (Optional) -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Your Grade Level (Optional)
          </label>
          <select 
            v-model="selectedGrade"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
          >
            <option value="">Not sure / Skip</option>
            <option value="K">Kindergarten</option>
            <option value="1">Grade 1</option>
            <option value="2">Grade 2</option>
          </select>
        </div>

        <!-- Start Button -->
        <button
          @click="startAssessment"
          :disabled="isLoading || !selectedType"
          class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-4 px-6 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
        >
          <span v-if="isLoading" class="mr-2">⏳</span>
          {{ isLoading ? 'Starting...' : 'Start Assessment!' }}
        </button>
      </div>

      <!-- Tips Section -->
      <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <span class="text-2xl">💡</span>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">Pro Tips!</h3>
            <div class="mt-2 text-sm text-yellow-700">
              <ul class="list-disc list-inside space-y-1">
                <li>Take your time - there's no rush!</li>
                <li>It's okay to make mistakes - that's how we learn</li>
                <li>Use hints if you need them (but they cost points)</li>
                <li>Answer quickly for bonus points!</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import GameModeSelector from '@/Components/Courses/bm2/GameModeSelector.vue';

const page = usePage();

// Configure axios with credentials for Sanctum
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// State
const selectedType = ref('placement');
const selectedGrade = ref('');
const isLoading = ref(false);
const hasPreviousAssessment = ref(false);
const error = ref(null);
const enableGameMode = ref(false);
const selectedGameMode = ref(null);

// Computed
const student = computed(() => page.props.auth.user);

// Methods
const handleGameModeSelection = (settings) => {
  selectedGameMode.value = settings.mode;
  // Store settings for later use
  localStorage.setItem('bm2_game_settings', JSON.stringify(settings));
  // Hide game mode selector
  enableGameMode.value = false;
  // Auto-start the assessment after game mode selection
  startAssessment();
};

const startAssessment = async () => {
  if (!selectedType.value) return;

  isLoading.value = true;
  error.value = null; // Clear previous errors

  try {
    // Get saved game settings if game mode is enabled
    const gameSettings = enableGameMode.value && selectedGameMode.value 
      ? JSON.parse(localStorage.getItem('bm2_game_settings') || '{}') 
      : null;
    
    const response = await axios.post('/api/v2/bm2/assessment/start', {
      type: selectedType.value,
      grade_level: selectedGrade.value || null,
      game_mode: enableGameMode.value ? (gameSettings?.mode || 'falling') : 'normal',
      game_settings: gameSettings,
    });

    const { assessment, questions } = response.data.data;

    // Navigate to assessment taking page with questions
    router.visit(`/bm2/assessment/${assessment.id}`, {
      preserveState: true,
      preserveScroll: true,
    });
  } catch (error) {
    console.error('Error starting assessment:', error);
    
    // Handle different error types
    if (error.response) {
      const status = error.response.status;
      const data = error.response.data;
      
      if (status === 401 || status === 403) {
        // Authentication error
        setError({
          type: 'auth',
          title: 'Login Required',
          message: 'You need to be logged in to start an assessment. Please login to continue.',
        });
      } else if (status === 400) {
        // Bad request
        setError({
          type: 'warning',
          title: 'Invalid Request',
          message: data.message || 'Please check your selection and try again.',
        });
      } else if (status === 500) {
        // Server error
        setError({
          type: 'warning',
          title: 'Server Error',
          message: 'Oops! Something went wrong on our end. Please try again in a few moments.',
        });
      } else {
        // Generic error
        setError({
          type: 'warning',
          title: 'Oops!',
          message: data.message || 'Something went wrong. Please try again.',
        });
      }
    } else if (error.request) {
      // Network error
      setError({
        type: 'warning',
        title: 'Network Error',
        message: 'Unable to connect to the server. Please check your internet connection.',
      });
    } else {
      // Other errors
      setError({
        type: 'warning',
        title: 'Error',
        message: error.message || 'An unexpected error occurred.',
      });
    }
  } finally {
    isLoading.value = false;
  }
};

const setError = (errorData) => {
  error.value = errorData;
  // Scroll to top to show error
  window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<style scoped>
.bm2-assessment-start {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;
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
