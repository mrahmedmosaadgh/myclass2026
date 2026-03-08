<template>
  <div class="question-display-container bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-white dark:from-gray-700 dark:to-gray-800">
      <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
        <span class="text-2xl">❓</span>
        <span>{{ questionTitle }}</span>
        <span class="text-xs font-normal px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full">
          {{ answers.length }} Responses
        </span>
      </h3>
    </div>

    <!-- Question Text -->
    <div class="p-6 bg-indigo-50 dark:bg-indigo-900/20">
      <p class="text-lg font-semibold text-gray-800 dark:text-white">
        {{ questionText }}
      </p>
    </div>

    <!-- Answers List -->
    <div class="p-6">
      <div v-if="answers.length === 0" class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl">
        <p class="text-sm">Waiting for responses...</p>
      </div>

      <div v-else class="space-y-3 max-h-96 overflow-y-auto">
        <transition-group name="answer-list">
          <div
            v-for="(answer, index) in sortedAnswers"
            :key="answer.id || index"
            class="answer-card bg-gradient-to-r from-indigo-50 to-white dark:from-gray-700 dark:to-gray-800 border border-indigo-100 dark:border-gray-600 rounded-xl p-4 shadow-sm hover:shadow-md transition-all"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <!-- Avatar -->
                <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                  {{ getInitials(answer.userName || answer.senderName) }}
                </div>
                
                <!-- Sender Info -->
                <div>
                  <p class="font-semibold text-gray-800 dark:text-white">
                    {{ answer.userName || answer.senderName }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatTimestamp(answer.timestamp) }}
                  </p>
                </div>
              </div>

              <!-- Answer Value -->
              <div class="text-right">
                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                  {{ answer.value }}
                </p>
              </div>
            </div>
          </div>
        </transition-group>
      </div>

      <!-- Statistics -->
      <div v-if="answers.length > 0" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
        <div class="grid grid-cols-3 gap-4">
          <div class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Average</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ averageAnswer }}</p>
          </div>
          <div class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Min</p>
            <p class="text-2xl font-bold text-green-600">{{ minAnswer }}</p>
          </div>
          <div class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Max</p>
            <p class="text-2xl font-bold text-red-600">{{ maxAnswer }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  questionTitle: {
    type: String,
    default: 'Live Question'
  },
  questionText: {
    type: String,
    required: true
  },
  answers: {
    type: Array,
    default: () => []
    // Expected format: [{ id: string, senderName: string, value: number, timestamp: number }]
  }
});

// Computed properties
const sortedAnswers = computed(() => {
  return [...props.answers].sort((a, b) => b.timestamp - a.timestamp);
});

const averageAnswer = computed(() => {
  if (props.answers.length === 0) return 0;
  const sum = props.answers.reduce((acc, answer) => acc + Number(answer.value), 0);
  return (sum / props.answers.length).toFixed(2);
});

const minAnswer = computed(() => {
  if (props.answers.length === 0) return 0;
  return Math.min(...props.answers.map(a => Number(a.value)));
});

const maxAnswer = computed(() => {
  if (props.answers.length === 0) return 0;
  return Math.max(...props.answers.map(a => Number(a.value)));
});

// Helper functions
const getInitials = (name) => {
  if (!name) return '?';
  const parts = name.trim().split(' ');
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
};

const formatTimestamp = (timestamp) => {
  if (!timestamp) return 'Just now';
  const date = new Date(timestamp * 1000);
  const now = new Date();
  const diffMs = now - date;
  const diffSecs = Math.floor(diffMs / 1000);
  const diffMins = Math.floor(diffSecs / 60);
  const diffHours = Math.floor(diffMins / 60);

  if (diffSecs < 60) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  return date.toLocaleDateString();
};
</script>

<style scoped>
/* Answer list animations */
.answer-list-enter-active {
  transition: all 0.3s ease-out;
}

.answer-list-leave-active {
  transition: all 0.2s ease-in;
}

.answer-list-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.answer-list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

/* Scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background-color: #f3f4f6;
  border-radius: 9999px;
}

.dark .overflow-y-auto::-webkit-scrollbar-track {
  background-color: #374151;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #a5b4fc;
  border-radius: 9999px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #4f46e5;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: #818cf8;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: #6366f1;
}
</style>
