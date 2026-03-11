<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  assessmentId: {
    type: Number,
    required: true,
  },
});

// State
const loading = ref(true);
const results = ref(null);
const showCelebration = ref(false);

// Computed
const overallScore = computed(() => {
  return results.value?.overall_score || 0;
});

const performanceLevel = computed(() => {
  return results.value?.performance_level || 'developing';
});

const gradeLevelEquivalent = computed(() => {
  return results.value?.grade_level_equivalent || 'K';
});

const totalQuestions = computed(() => {
  return results.value?.questions_answered || 0;
});

const correctAnswers = computed(() => {
  return results.value?.correct_answers || 0;
});

const accuracyPercentage = computed(() => {
  if (totalQuestions.value === 0) return 0;
  return Math.round((correctAnswers.value / totalQuestions.value) * 100);
});

const durationMinutes = computed(() => {
  return results.value?.duration_minutes || 0;
});

const skillBreakdown = computed(() => {
  return results.value?.skill_breakdown || {};
});

const learningPath = computed(() => {
  return results.value?.learning_path || null;
});

const questionDetails = computed(() => {
  return results.value?.question_details || [];
});

// Performance level display
const performanceDisplay = computed(() => {
  const map = {
    advanced: { label: 'Advanced', color: 'text-green-600', bg: 'bg-green-100', icon: '🌟' },
    proficient: { label: 'Proficient', color: 'text-blue-600', bg: 'bg-blue-100', icon: '⭐' },
    developing: { label: 'Developing', color: 'text-yellow-600', bg: 'bg-yellow-100', icon: '📚' },
    emerging: { label: 'Emerging', color: 'text-orange-600', bg: 'bg-orange-100', icon: '🌱' },
  };
  return map[performanceLevel.value] || map.developing;
});

// Score color
const scoreColor = computed(() => {
  if (overallScore.value >= 90) return 'text-green-500';
  if (overallScore.value >= 70) return 'text-blue-500';
  if (overallScore.value >= 40) return 'text-yellow-500';
  return 'text-orange-500';
});

// Methods
const fetchResults = async () => {
  try {
    const response = await axios.get(`/api/v2/bm2/assessment/${props.assessmentId}/results`);
    results.value = response.data.data;
    
    // Show celebration if score is good
    if (overallScore.value >= 70) {
      showCelebration.value = true;
      setTimeout(() => {
        showCelebration.value = false;
      }, 3000);
    }
  } catch (error) {
    console.error('Error fetching results:', error);
  } finally {
    loading.value = false;
  }
};

const getSkillColor = (percentage) => {
  if (percentage >= 90) return 'bg-green-500';
  if (percentage >= 70) return 'bg-blue-500';
  if (percentage >= 40) return 'bg-yellow-500';
  return 'bg-orange-500';
};

const goToDashboard = () => {
  router.visit('/bm2/dashboard');
};

const retakeAssessment = () => {
  router.visit('/bm2/assessment/start');
};

const viewLearningPath = () => {
  if (learningPath.value) {
    router.visit('/bm2/learning-paths');
  }
};

// Lifecycle
onMounted(() => {
  fetchResults();
});
</script>

<template>
  <Head title="Assessment Results" />

  <div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-green-50 py-8">
    <!-- Celebration Animation -->
    <div v-if="showCelebration" class="fixed inset-0 pointer-events-none z-50">
      <div class="absolute inset-0 overflow-hidden">
        <div class="confetti-animation">
          <span class="text-6xl absolute animate-bounce" style="left: 10%; animation-delay: 0s;">🎉</span>
          <span class="text-6xl absolute animate-bounce" style="left: 30%; animation-delay: 0.2s;">⭐</span>
          <span class="text-6xl absolute animate-bounce" style="left: 50%; animation-delay: 0.4s;">🎊</span>
          <span class="text-6xl absolute animate-bounce" style="left: 70%; animation-delay: 0.6s;">✨</span>
          <span class="text-6xl absolute animate-bounce" style="left: 90%; animation-delay: 0.8s;">🌟</span>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-4">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">
          🎯 Assessment Complete!
        </h1>
        <p class="text-gray-600">Here's how you performed</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-primary"></div>
      </div>

      <!-- Results Content -->
      <div v-else class="space-y-6">
        <!-- Overall Score Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Overall Score -->
            <div class="text-center">
              <div class="mb-4">
                <span class="text-6xl font-bold" :class="scoreColor">
                  {{ overallScore.toFixed(1) }}%
                </span>
              </div>
              <div class="text-sm text-gray-500 uppercase tracking-wide">Overall Score</div>
              <div class="mt-2 inline-block px-4 py-2 rounded-full" :class="performanceDisplay.bg">
                <span class="font-semibold" :class="performanceDisplay.color">
                  {{ performanceDisplay.icon }} {{ performanceDisplay.label }}
                </span>
              </div>
            </div>

            <!-- Stats -->
            <div class="space-y-4">
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Questions Answered</span>
                <span class="font-bold text-lg">{{ totalQuestions }}</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Correct Answers</span>
                <span class="font-bold text-lg text-green-600">{{ correctAnswers }}</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Accuracy</span>
                <span class="font-bold text-lg" :class="scoreColor">{{ accuracyPercentage }}%</span>
              </div>
            </div>

            <!-- Additional Info -->
            <div class="space-y-4">
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Time Taken</span>
                <span class="font-bold text-lg">{{ durationMinutes }} min</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Grade Level</span>
                <span class="font-bold text-lg">Grade {{ gradeLevelEquivalent }}</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Performance</span>
                <span class="font-bold text-lg capitalize">{{ performanceLevel }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Skill Breakdown -->
        <div v-if="Object.keys(skillBreakdown).length > 0" class="bg-white rounded-2xl shadow-xl p-8">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">📊 Skill Breakdown</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="(skill, skillName) in skillBreakdown" 
              :key="skillName"
              class="p-4 border-2 rounded-lg"
            >
              <div class="flex justify-between items-center mb-2">
                <span class="font-semibold text-gray-700 capitalize">
                  {{ skillName.replace('_', ' ') }}
                </span>
                <span class="text-sm font-medium" :class="getSkillColor(skill.percentage).replace('bg-', 'text-')">
                  {{ skill.percentage }}% accuracy
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="h-3 rounded-full transition-all duration-500"
                  :class="getSkillColor(skill.percentage)"
                  :style="{ width: `${skill.percentage}%` }"
                ></div>
              </div>
              <div class="mt-2 text-xs text-gray-500">
                {{ skill.correct }} / {{ skill.total }} correct
              </div>
            </div>
          </div>
        </div>

        <!-- Learning Path Recommendation -->
        <div v-if="learningPath" class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl shadow-xl p-8">
          <h2 class="text-2xl font-bold text-gray-800 mb-4">🎓 Your Personalized Learning Path</h2>
          <p class="text-gray-600 mb-6">
            Based on your performance, here are the areas we recommend focusing on:
          </p>
          
          <div class="space-y-3">
            <div 
              v-for="(module, index) in learningPath.recommended_modules || []" 
              :key="index"
              class="bg-white rounded-lg p-4 flex items-center justify-between"
            >
              <div class="flex items-center space-x-3">
                <span class="flex-shrink-0 w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold">
                  {{ index + 1 }}
                </span>
                <div>
                  <h3 class="font-semibold text-gray-800">{{ module.topic || module.name }}</h3>
                  <p class="text-sm text-gray-500">Priority: {{ module.priority || 'Medium' }}</p>
                </div>
              </div>
              <button 
                @click="viewLearningPath"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors text-sm font-medium"
              >
                Start Practice
              </button>
            </div>
          </div>
        </div>

        <!-- Question Review -->
        <div v-if="questionDetails.length > 0" class="bg-white rounded-2xl shadow-xl p-8">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">📝 Question Review</h2>
          <div class="space-y-3">
            <div 
              v-for="(q, index) in questionDetails" 
              :key="index"
              class="p-4 border-l-4 rounded-lg"
              :class="q.is_correct ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                  <p class="font-medium text-gray-800 mb-2">{{ q.question_text }}</p>
                  <div class="text-sm space-y-1">
                    <p class="text-gray-600">
                      <span class="font-medium">Your answer:</span> 
                      <span :class="q.is_correct ? 'text-green-600' : 'text-red-600'">
                        {{ q.student_answer }}
                      </span>
                    </p>
                    <p v-if="!q.is_correct" class="text-gray-600">
                      <span class="font-medium">Correct answer:</span> 
                      <span class="text-green-600">{{ q.correct_answer }}</span>
                    </p>
                  </div>
                </div>
                <div class="ml-4 flex-shrink-0 text-right">
                  <div class="text-2xl mb-1">
                    {{ q.is_correct ? '✅' : '❌' }}
                  </div>
                  <div class="text-xs text-gray-500">
                    ⏱️ {{ q.time_taken }}s
                  </div>
                  <div class="text-sm font-semibold text-primary">
                    +{{ q.points_earned }} pts
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button
            @click="goToDashboard"
            class="px-8 py-4 bg-gray-700 hover:bg-gray-800 text-white font-bold rounded-lg transition-all shadow-lg"
          >
            📊 Go to Dashboard
          </button>
          <button
            @click="retakeAssessment"
            class="px-8 py-4 bg-primary hover:bg-primary-dark text-white font-bold rounded-lg transition-all shadow-lg"
          >
            🔄 Take Another Assessment
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.confetti-animation {
  animation: confetti-fall 3s linear infinite;
}

@keyframes confetti-fall {
  0% {
    transform: translateY(-100vh);
  }
  100% {
    transform: translateY(100vh);
  }
}
</style>
