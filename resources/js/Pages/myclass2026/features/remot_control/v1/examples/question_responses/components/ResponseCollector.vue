<template>
  <div class="response-collector bg-white rounded-lg shadow-lg p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold text-gray-800">Response Collector</h3>
        <div class="flex items-center space-x-4">
          <!-- Export Button -->
          <button
            @click="handleExport"
            :disabled="responses.length === 0"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export JSON
          </button>
          
          <!-- Clear Button -->
          <button
            @click="handleClear"
            :disabled="responses.length === 0"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Clear All
          </button>
        </div>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
          <div class="flex items-center">
            <div class="bg-blue-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-blue-600 font-medium">Total Responses</p>
              <p class="text-2xl font-bold text-blue-800">{{ responses.length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-green-50 p-4 rounded-lg">
          <div class="flex items-center">
            <div class="bg-green-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-green-600 font-medium">Unique Students</p>
              <p class="text-2xl font-bold text-green-800">{{ uniqueStudentCount }}</p>
            </div>
          </div>
        </div>

        <div class="bg-purple-50 p-4 rounded-lg">
          <div class="flex items-center">
            <div class="bg-purple-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-purple-600 font-medium">Avg Response Time</p>
              <p class="text-2xl font-bold text-purple-800">{{ averageResponseTime }}s</p>
            </div>
          </div>
        </div>

        <div class="bg-orange-50 p-4 rounded-lg">
          <div class="flex items-center">
            <div class="bg-orange-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-orange-600 font-medium">Response Rate</p>
              <p class="text-2xl font-bold text-orange-800">{{ responseRate }}%</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Responses State -->
    <div v-if="responses.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Responses Yet</h3>
      <p class="text-gray-500">Waiting for students to submit their answers...</p>
    </div>

    <!-- Responses List -->
    <div v-else class="space-y-4">
      <!-- Filter and Sort Controls -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by student name..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>

          <!-- Sort -->
          <select
            v-model="sortBy"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="timestamp">Sort by Time</option>
            <option value="name">Sort by Name</option>
            <option value="responseTime">Sort by Response Time</option>
          </select>
        </div>

        <!-- View Toggle -->
        <div class="flex items-center space-x-2">
          <button
            @click="viewMode = 'list'"
            class="px-3 py-1 rounded"
            :class="viewMode === 'list' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
          >
            List
          </button>
          <button
            @click="viewMode = 'grid'"
            class="px-3 py-1 rounded"
            :class="viewMode === 'grid' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
          >
            Grid
          </button>
        </div>
      </div>

      <!-- List View -->
      <div v-if="viewMode === 'list'" class="space-y-3">
        <div
          v-for="response in filteredResponses"
          :key="response.studentId + response.timestamp"
          class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                {{ getInitials(response.studentName) }}
              </div>
              <div>
                <h4 class="font-medium text-gray-900">{{ response.studentName }}</h4>
                <div class="flex items-center space-x-3 text-sm text-gray-500">
                  <span>{{ formatTime(response.timestamp) }}</span>
                  <span v-if="response.responseTime">{{ response.responseTime }}s</span>
                  <span v-if="response.isAuthenticated" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Authenticated
                  </span>
                  <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Guest
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center space-x-2">
              <button
                @click="toggleExpand(response.studentId + response.timestamp)"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Answer Preview -->
          <div v-if="expandedResponses.includes(response.studentId + response.timestamp)" class="mt-4">
            <div class="bg-gray-50 rounded-lg p-3">
              <h5 class="text-sm font-medium text-gray-700 mb-2">Answer:</h5>
              <AnswerPreview :question-data="questionData" :answer-data="response.answer" />
            </div>
          </div>
        </div>
      </div>

      <!-- Grid View -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="response in filteredResponses"
          :key="response.studentId + response.timestamp"
          class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
        >
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold mr-2">
              {{ getInitials(response.studentName) }}
            </div>
            <div class="flex-1">
              <h4 class="font-medium text-gray-900 text-sm">{{ response.studentName }}</h4>
              <div class="text-xs text-gray-500">{{ formatTime(response.timestamp) }}</div>
            </div>
          </div>
          
          <!-- Answer Preview -->
          <div class="bg-gray-50 rounded p-2">
            <AnswerPreview :question-data="questionData" :answer-data="response.answer" />
          </div>
        </div>
      </div>
    </div>

    <!-- Answer Distribution Chart (for MCQ) -->
    <div v-if="questionData.type === 'multiple_choice' && responses.length > 0" class="mt-8">
      <h4 class="text-lg font-semibold text-gray-800 mb-4">Answer Distribution</h4>
      <div class="space-y-3">
        <div
          v-for="(option, index) in questionData.options"
          :key="index"
          class="flex items-center"
        >
          <div class="w-32 text-sm text-gray-700 mr-4">{{ option }}</div>
          <div class="flex-1 bg-gray-200 rounded-full h-8 relative">
            <div
              class="bg-blue-500 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium"
              :style="{ width: `${getAnswerPercentage(index)}%` }"
            >
              {{ getAnswerCount(index) }} ({{ getAnswerPercentage(index) }}%)
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import AnswerPreview from './AnswerPreview.vue'

export default {
  name: 'ResponseCollector',
  components: {
    AnswerPreview
  },
  props: {
    sessionCode: {
      type: String,
      required: true
    },
    questionData: {
      type: Object,
      required: true
    },
    responses: {
      type: Array,
      default: () => []
    }
  },
  emits: ['export-responses', 'clear-responses'],
  setup(props, { emit }) {
    // State
    const searchQuery = ref('')
    const sortBy = ref('timestamp')
    const viewMode = ref('list')
    const expandedResponses = ref([])

    // Computed
    const uniqueStudentCount = computed(() => {
      const uniqueIds = new Set(props.responses.map(r => r.studentId))
      return uniqueIds.size
    })

    const averageResponseTime = computed(() => {
      if (props.responses.length === 0) return 0
      
      const validTimes = props.responses
        .filter(r => r.responseTime && r.responseTime > 0)
        .map(r => r.responseTime)
      
      if (validTimes.length === 0) return 0
      
      const sum = validTimes.reduce((acc, time) => acc + time, 0)
      return Math.round(sum / validTimes.length)
    })

    const responseRate = computed(() => {
      // This would need total expected students from somewhere
      // For now, just return 100% if we have any responses
      return props.responses.length > 0 ? 100 : 0
    })

    const filteredResponses = computed(() => {
      let filtered = [...props.responses]

      // Apply search filter
      if (searchQuery.value) {
        filtered = filtered.filter(response =>
          response.studentName.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
      }

      // Apply sorting
      filtered.sort((a, b) => {
        switch (sortBy.value) {
          case 'name':
            return a.studentName.localeCompare(b.studentName)
          case 'responseTime':
            return (a.responseTime || 0) - (b.responseTime || 0)
          case 'timestamp':
          default:
            return new Date(b.timestamp) - new Date(a.timestamp)
        }
      })

      return filtered
    })

    // Methods
    const getInitials = (name) => {
      return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2)
    }

    const formatTime = (timestamp) => {
      try {
        const date = new Date(timestamp)
        return date.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        })
      } catch (error) {
        return timestamp
      }
    }

    const toggleExpand = (responseId) => {
      const index = expandedResponses.value.indexOf(responseId)
      if (index > -1) {
        expandedResponses.value.splice(index, 1)
      } else {
        expandedResponses.value.push(responseId)
      }
    }

    const handleExport = () => {
      emit('export-responses', {
        sessionCode: props.sessionCode,
        question: props.questionData,
        responses: props.responses,
        statistics: {
          totalResponses: props.responses.length,
          uniqueStudents: uniqueStudentCount.value,
          averageResponseTime: averageResponseTime.value,
          responseRate: responseRate.value
        },
        exportedAt: new Date().toISOString()
      })
    }

    const handleClear = () => {
      if (confirm('Are you sure you want to clear all responses? This action cannot be undone.')) {
        emit('clear-responses')
        expandedResponses.value = []
      }
    }

    // Answer distribution methods (for MCQ)
    const getAnswerCount = (optionIndex) => {
      return props.responses.filter(r => r.answer?.selectedIndex === optionIndex).length
    }

    const getAnswerPercentage = (optionIndex) => {
      if (props.responses.length === 0) return 0
      const count = getAnswerCount(optionIndex)
      return Math.round((count / props.responses.length) * 100)
    }

    return {
      // State
      searchQuery,
      sortBy,
      viewMode,
      expandedResponses,

      // Computed
      uniqueStudentCount,
      averageResponseTime,
      responseRate,
      filteredResponses,

      // Methods
      getInitials,
      formatTime,
      toggleExpand,
      handleExport,
      handleClear,
      getAnswerCount,
      getAnswerPercentage
    }
  }
}
</script>

<style scoped>
.response-collector {
  max-width: 100%;
}
</style>
