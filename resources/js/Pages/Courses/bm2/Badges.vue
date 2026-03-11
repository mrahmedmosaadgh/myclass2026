<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

// State
const loading = ref(true);
const badges = ref([]);
const selectedCategory = ref('all');

// Categories for filtering
const categories = [
  { id: 'all', label: 'All Badges', icon: '🎯' },
  { id: 'achievement', label: 'Achievement', icon: '⭐' },
  { id: 'milestone', label: 'Milestone', icon: '🏆' },
  { id: 'skill_mastery', label: 'Skill Mastery', icon: '💪' },
  { id: 'speed', label: 'Speed', icon: '⚡' },
  { id: 'consistency', label: 'Consistency', icon: '🔥' },
];

// Computed
const filteredBadges = computed(() => {
  if (selectedCategory.value === 'all') {
    return badges.value;
  }
  return badges.value.filter(badge => badge.category === selectedCategory.value);
});

const earnedCount = computed(() => {
  return badges.value.filter(badge => badge.earned).length;
});

const totalCount = computed(() => {
  return badges.value.length;
});

// Methods
const fetchBadges = async () => {
  try {
    const response = await axios.get('/api/v2/bm2/student/badges');
    badges.value = response.data.data.badges || [];
  } catch (error) {
    console.error('Error fetching badges:', error);
    // Load mock data for development
    loadMockBadges();
  } finally {
    loading.value = false;
  }
};

const loadMockBadges = () => {
  // Mock data for testing
  badges.value = [
    {
      id: 1,
      name: 'First Steps',
      description: 'Complete your first assessment',
      category: 'achievement',
      icon: '🎯',
      points_value: 10,
      rarity: 'common',
      earned: true,
      earned_at: new Date().toISOString(),
    },
    {
      id: 2,
      name: 'Math Wizard',
      description: 'Score 100% on any assessment',
      category: 'achievement',
      icon: '🧙',
      points_value: 50,
      rarity: 'epic',
      earned: false,
      earned_at: null,
    },
    {
      id: 3,
      name: 'Dedicated Learner',
      description: 'Complete 5 assessments',
      category: 'milestone',
      icon: '📚',
      points_value: 25,
      rarity: 'uncommon',
      earned: true,
      earned_at: new Date(Date.now() - 86400000).toISOString(),
    },
    {
      id: 4,
      name: 'Century Club',
      description: 'Complete 100 assessments',
      category: 'milestone',
      icon: '💯',
      points_value: 100,
      rarity: 'legendary',
      earned: false,
      earned_at: null,
    },
    {
      id: 5,
      name: 'Addition Ace',
      description: 'Master addition with 90%+ accuracy',
      category: 'skill_mastery',
      icon: '➕',
      points_value: 30,
      rarity: 'rare',
      earned: true,
      earned_at: new Date().toISOString(),
    },
    {
      id: 6,
      name: 'Subtraction Star',
      description: 'Master subtraction with 90%+ accuracy',
      category: 'skill_mastery',
      icon: '➖',
      points_value: 30,
      rarity: 'rare',
      earned: false,
      earned_at: null,
    },
    {
      id: 7,
      name: 'Number Sense Ninja',
      description: 'Master number sense with 90%+ accuracy',
      category: 'skill_mastery',
      icon: '🔢',
      points_value: 30,
      rarity: 'rare',
      earned: false,
      earned_at: null,
    },
    {
      id: 8,
      name: 'Speed Demon',
      description: 'Complete an assessment in under 10 minutes with 85%+ accuracy',
      category: 'speed',
      icon: '⚡',
      points_value: 40,
      rarity: 'epic',
      earned: false,
      earned_at: null,
    },
    {
      id: 9,
      name: 'On Fire!',
      description: 'Practice for 7 days in a row',
      category: 'consistency',
      icon: '🔥',
      points_value: 35,
      rarity: 'rare',
      earned: true,
      earned_at: new Date().toISOString(),
    },
    {
      id: 10,
      name: 'Unstoppable',
      description: 'Practice for 30 days in a row',
      category: 'consistency',
      icon: '💪',
      points_value: 75,
      rarity: 'legendary',
      earned: false,
      earned_at: null,
    },
  ];
};

const getRarityColor = (rarity) => {
  const colors = {
    common: 'from-gray-200 to-gray-300 border-gray-400',
    uncommon: 'from-green-100 to-green-200 border-green-400',
    rare: 'from-blue-100 to-blue-200 border-blue-400',
    epic: 'from-purple-100 to-purple-200 border-purple-400',
    legendary: 'from-yellow-100 to-yellow-200 border-yellow-400',
  };
  return colors[rarity] || colors.common;
};

const getRarityGlow = (rarity) => {
  const glows = {
    common: '',
    uncommon: 'shadow-green-200',
    rare: 'shadow-blue-300',
    epic: 'shadow-purple-300',
    legendary: 'shadow-yellow-300 animate-pulse',
  };
  return glows[rarity] || '';
};

const getCategoryIcon = (category) => {
  const icons = {
    achievement: '⭐',
    milestone: '🏆',
    skill_mastery: '💪',
    speed: '⚡',
    consistency: '🔥',
  };
  return icons[category] || '🎯';
};

const selectCategory = (categoryId) => {
  selectedCategory.value = categoryId;
};

const goBack = () => {
  router.visit('/bm2/dashboard');
};

// Lifecycle
onMounted(() => {
  fetchBadges();
});
</script>

<template>
  <Head title="My Badges" />

  <div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-green-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-bold text-gray-800 mb-2">
            🏆 My Badge Collection
          </h1>
          <p class="text-gray-600">
            Earned {{ earnedCount }} of {{ totalCount }} badges
          </p>
        </div>
        <button
          @click="goBack"
          class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors"
        >
          ← Back to Dashboard
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-primary"></div>
      </div>

      <!-- Content -->
      <div v-else>
        <!-- Progress Bar -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
          <div class="mb-2">
            <div class="flex justify-between text-sm mb-1">
              <span class="font-medium text-gray-700">Collection Progress</span>
              <span class="font-bold text-primary">{{ Math.round((earnedCount / totalCount) * 100) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
              <div 
                class="bg-gradient-to-r from-yellow-400 to-orange-500 h-4 rounded-full transition-all duration-500"
                :style="{ width: `${(earnedCount / totalCount) * 100}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Category Filter -->
        <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="category in categories"
              :key="category.id"
              @click="selectCategory(category.id)"
              :class="[
                'px-4 py-2 rounded-lg font-medium transition-all',
                selectedCategory === category.id
                  ? 'bg-primary text-white shadow-lg'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              <span class="mr-1">{{ category.icon }}</span>
              {{ category.label }}
            </button>
          </div>
        </div>

        <!-- Badges Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="badge in filteredBadges"
            :key="badge.id"
            :class="[
              'relative rounded-xl p-6 cursor-pointer transition-all duration-300 transform hover:scale-105',
              'bg-gradient-to-br border-2 shadow-lg hover:shadow-xl',
              getRarityColor(badge.rarity),
              getRarityGlow(badge.rarity)
            ]"
          >
            <!-- Earned Badge -->
            <div v-if="badge.earned" class="text-center">
              <div class="text-6xl mb-3 animate-bounce">{{ badge.icon }}</div>
              <h3 class="text-xl font-bold text-gray-800 mb-2">{{ badge.name }}</h3>
              <p class="text-sm text-gray-600 mb-3">{{ badge.description }}</p>
              <div class="flex justify-between items-center text-xs">
                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">
                  ✓ Earned
                </span>
                <span class="text-gray-500">{{ badge.points_value }} pts</span>
              </div>
              <div class="mt-2 text-xs text-gray-500">
                {{ new Date(badge.earned_at).toLocaleDateString() }}
              </div>
            </div>

            <!-- Locked Badge -->
            <div v-else class="text-center opacity-60">
              <div class="text-6xl mb-3 grayscale">{{ badge.icon }}</div>
              <h3 class="text-xl font-bold text-gray-800 mb-2">{{ badge.name }}</h3>
              <p class="text-sm text-gray-600 mb-3">{{ badge.description }}</p>
              <div class="flex justify-between items-center text-xs">
                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">
                  🔒 Locked
                </span>
                <span class="text-gray-500">{{ badge.points_value }} pts</span>
              </div>
            </div>

            <!-- Rarity Badge -->
            <div class="absolute top-2 right-2">
              <span 
                :class="[
                  'text-xs px-2 py-1 rounded-full font-medium capitalize',
                  badge.rarity === 'legendary' ? 'bg-yellow-200 text-yellow-800' :
                  badge.rarity === 'epic' ? 'bg-purple-200 text-purple-800' :
                  badge.rarity === 'rare' ? 'bg-blue-200 text-blue-800' :
                  badge.rarity === 'uncommon' ? 'bg-green-200 text-green-800' :
                  'bg-gray-200 text-gray-800'
                ]"
              >
                {{ badge.rarity }}
              </span>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredBadges.length === 0" class="text-center py-20">
          <div class="text-6xl mb-4">🎯</div>
          <h3 class="text-2xl font-bold text-gray-700 mb-2">No badges in this category</h3>
          <p class="text-gray-500">Keep practicing to earn more badges!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
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
