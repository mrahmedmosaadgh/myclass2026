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

const page = usePage();

// State
const selectedType = ref('placement');
const selectedGrade = ref('');
const isLoading = ref(false);
const hasPreviousAssessment = ref(false); // Could check from backend

// Computed
const student = computed(() => page.props.auth.user);

// Methods
const startAssessment = async () => {
  if (!selectedType.value) return;

  isLoading.value = true;

  try {
    const response = await axios.post('/api/v2/bm2/assessment/start', {
      type: selectedType.value,
      grade_level: selectedGrade.value || null,
    });

    const { assessment, question } = response.data.data;

    // Navigate to assessment taking page
    router.visit(`/bm2/assessment/${assessment.id}`);
  } catch (error) {
    console.error('Error starting assessment:', error);
    alert('Oops! Something went wrong. Please try again.');
  } finally {
    isLoading.value = false;
  }
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
