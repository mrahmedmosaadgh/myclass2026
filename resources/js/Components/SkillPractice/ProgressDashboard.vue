<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Your Progress</h3>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-indigo-50 p-4 rounded-lg text-center">
        <div class="text-sm text-indigo-600">Sessions</div>
        <div class="text-xl font-bold text-gray-800">{{ analytics.total_sessions }}</div>
      </div>
      <div class="bg-green-50 p-4 rounded-lg text-center">
        <div class="text-sm text-green-600">Accuracy</div>
        <div class="text-xl font-bold text-gray-800">{{ analytics.average_accuracy }}%</div>
      </div>
      <div class="bg-blue-50 p-4 rounded-lg text-center">
        <div class="text-sm text-blue-600">Questions</div>
        <div class="text-xl font-bold text-gray-800">{{ analytics.total_questions_answered }}</div>
      </div>
      <div class="bg-purple-50 p-4 rounded-lg text-center">
        <div class="text-sm text-purple-600">Hours</div>
        <div class="text-xl font-bold text-gray-800">{{ analytics.hours_practiced }}</div>
      </div>
    </div>
    
    <div class="mb-6">
      <h4 class="font-medium text-gray-700 mb-2">SmartScore Over Time</h4>
      <div class="h-48">
        <div v-if="analytics.progress_over_time && analytics.progress_over_time.length > 0" class="h-full flex items-end space-x-1">
          <div 
            v-for="(point, index) in analytics.progress_over_time" 
            :key="index"
            class="flex flex-col items-center flex-1"
          >
            <div 
              class="w-full bg-indigo-500 rounded-t"
              :style="{ height: `${Math.min(100, point.smart_score || 0)}%` }"
              :title="`${point.date}: ${point.smart_score}`"
            ></div>
            <div class="text-xs text-gray-500 mt-1">{{ formatDate(point.date) }}</div>
          </div>
        </div>
        <div v-else class="h-full flex items-center justify-center text-gray-500">
          No progress data available yet
        </div>
      </div>
    </div>
    
    <div class="space-y-4">
      <div>
        <h4 class="font-medium text-gray-700 mb-2">Current Streak</h4>
        <div class="flex items-center">
          <div class="mr-2 text-2xl">🔥</div>
          <div class="text-lg font-bold text-gray-800">{{ currentStreak }}</div>
        </div>
      </div>
      
      <div>
        <h4 class="font-medium text-gray-700 mb-2">Best Streak</h4>
        <div class="text-lg font-bold text-gray-800">{{ bestStreak }}</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProgressDashboard',
  props: {
    analytics: {
      type: Object,
      default: () => ({})
    },
    currentStreak: {
      type: Number,
      default: 0
    },
    bestStreak: {
      type: Number,
      default: 0
    }
  },
  methods: {
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
  }
};
</script>