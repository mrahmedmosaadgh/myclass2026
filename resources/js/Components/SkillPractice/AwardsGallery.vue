<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Your Awards</h3>
    
    <div v-if="awards.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
      <div 
        v-for="award in awards" 
        :key="award.id"
        class="border rounded-lg p-4 text-center bg-gradient-to-br from-indigo-50 to-blue-50"
      >
        <div class="flex justify-center mb-2">
          <div class="bg-yellow-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <h4 class="font-medium text-gray-800 text-sm">{{ award.award_type.replace('_', ' ') }}</h4>
        <p class="text-xs text-gray-500 mt-1">{{ formatDate(award.created_at) }}</p>
        <div v-if="award.metadata" class="mt-2">
          <p class="text-xs text-gray-600">{{ getAwardDescription(award.award_type) }}</p>
        </div>
      </div>
    </div>
    
    <div v-else class="text-center py-8">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No awards yet</h3>
      <p class="mt-1 text-sm text-gray-500">Keep practicing to earn your first award!</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AwardsGallery',
  props: {
    awards: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    },
    getAwardDescription(awardType) {
      const descriptions = {
        'streak_5': 'Achieved a 5+ question streak',
        'first_mastery': 'Reached master level in a skill',
        'rapid_responder': 'Answered questions quickly',
        'accuracy_master': 'Maintained high accuracy rate',
        'fast_finisher': 'Completed sessions quickly'
      };
      
      return descriptions[awardType] || 'Achievement unlocked!';
    }
  }
};
</script>