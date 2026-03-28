<template>
  <div class="student-view min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Question Response</h1>
        <p class="text-gray-600">Join a session and answer questions in real-time</p>
      </div>

      <!-- Join Session Form -->
      <div v-if="!sessionCode" class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Join Session</h2>
        
        <!-- Session Restoration Notice -->
        <div v-if="isRestoring" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div>
              <p class="text-blue-800 font-medium">Reconnecting to Session...</p>
              <p class="text-blue-700 text-sm">Restoring your session data</p>
            </div>
          </div>
        </div>
        
        <!-- Previous Session Found -->
        <div v-if="hasPreviousSession && !isRestoring" class="mb-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-800 font-medium">Previous Session Found</p>
              <p class="text-purple-700 text-sm">Session: {{ previousSessionCode }} | Name: {{ previousStudentName }}</p>
            </div>
            <div class="flex space-x-2">
              <button
                @click="rejoinPreviousSession"
                :disabled="isRejoining"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors text-sm"
              >
                <span v-if="isRejoining" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Rejoining...
                </span>
                <span v-else>Rejoin</span>
              </button>
              <button
                @click="clearPreviousSession"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm"
              >
                Clear
              </button>
            </div>
          </div>
        </div>
        
        <div class="space-y-4">
          <!-- Session Code Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Session Code</label>
            <input
              v-model="inputCode"
              type="text"
              placeholder="Enter 6-character code"
              maxlength="6"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg text-center text-xl font-mono font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase"
              @keyup.enter="joinSession"
            >
            <p class="text-sm text-gray-500 mt-2">
              Enter the code provided by your teacher
            </p>
          </div>

          <!-- Student Name Input (for guests) -->
          <div v-if="!isAuthenticated">
            <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
            <input
              v-model="studentName"
              type="text"
              placeholder="Enter your name"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              @keyup.enter="joinSession"
            >
            <p class="text-sm text-gray-500 mt-2">
              Required for guest users
            </p>
          </div>

          <!-- Join Button -->
          <button
            @click="joinSession"
            :disabled="!canJoin || isJoining"
            class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="isJoining" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Joining Session...
            </span>
            <span v-else>
              Join Session
            </span>
          </button>
        </div>
      </div>

      <!-- Active Session -->
      <div v-if="sessionCode" class="space-y-8">
        <!-- Session Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-xl font-semibold text-gray-800">Session Active</h2>
              <div class="flex items-center space-x-4 mt-2">
                <span class="text-lg font-mono font-bold text-blue-600">{{ sessionCode }}</span>
                <div class="flex items-center">
                  <div 
                    class="w-3 h-3 rounded-full mr-2"
                    :class="isConnected ? 'bg-green-500' : 'bg-red-500'"
                  ></div>
                  <span :class="isConnected ? 'text-green-600' : 'text-red-600'">
                    {{ isConnected ? 'Connected' : 'Disconnected' }}
                  </span>
                </div>
                <span class="text-gray-500">{{ session?.studentInfo?.name }}</span>
                <!-- Restored Session Indicator -->
                <span v-if="wasRestored" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                  Restored
                </span>
              </div>
            </div>
            
            <button
              @click="leaveSession"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              Leave Session
            </button>
          </div>
        </div>

        <!-- Waiting State -->
        <div v-if="!hasActiveQuestion" class="bg-white rounded-lg shadow-md p-8 text-center">
          <div class="max-w-md mx-auto">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Waiting for Question</h3>
            <p class="text-gray-500 mb-4">
              Your teacher will publish a question soon. Stay connected!
            </p>
            
            <!-- Connection Status -->
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center justify-center space-x-2">
                <div 
                  class="w-2 h-2 rounded-full"
                  :class="isConnected ? 'bg-green-500' : 'bg-red-500'"
                ></div>
                <span class="text-sm text-gray-600">
                  {{ isConnected ? 'Connected to session' : 'Reconnecting...' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Question Display -->
        <div v-if="hasActiveQuestion" class="bg-white rounded-lg shadow-md p-6">
          <div class="mb-4">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-800">Question</h2>
              <div class="flex items-center space-x-3">
                <span v-if="currentQuestion?.timeLimit" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  Time Limit: {{ currentQuestion.timeLimit }}s
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                  {{ getQuestionTypeLabel(currentQuestion?.type) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Answer Input Component -->
          <AnswerInput
            :question-data="currentQuestion"
            :allow-edit="true"
            @answer-submitted="handleAnswerSubmitted"
            @answer-changed="handleAnswerChanged"
          />
        </div>

        <!-- Answer Submitted Confirmation -->
        <div v-if="answerSubmitted" class="bg-white rounded-lg shadow-md p-6">
          <div class="text-center">
            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Answer Submitted!</h3>
            <p class="text-gray-500 mb-4">
              Your response has been recorded. Thank you for participating.
            </p>
            
            <!-- Waiting for next question -->
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-600">
                Waiting for the next question or session end...
              </p>
            </div>
          </div>
        </div>

        <!-- Session Ended -->
        <div v-if="sessionStatus === 'closed'" class="bg-white rounded-lg shadow-md p-8 text-center">
          <div class="max-w-md mx-auto">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Session Ended</h3>
            <p class="text-gray-500 mb-4">
              The teacher has ended this session. Thank you for participating!
            </p>
            
            <button
              @click="resetSession"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Join Another Session
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useQuestionSession } from './composables/useQuestionSession.js'
import AnswerInput from './components/AnswerInput.vue'

export default {
  name: 'StudentView',
  components: {
    AnswerInput
  },
  setup() {
    // Page props (for auth user)
    const { auth } = usePage().props || {}
    
    // State
    const sessionCode = ref('')
    const inputCode = ref('')
    const studentName = ref('')
    const isJoining = ref(false)
    const answerSubmitted = ref(false)
    const isRestoring = ref(false)
    const isRejoining = ref(false)
    const wasRestored = ref(false)
    const hasPreviousSession = ref(false)
    const previousSessionCode = ref('')
    const previousStudentName = ref('')
    
    // Session instance
    let session = null

    // Computed
    const isAuthenticated = computed(() => !!auth?.user)
    
    const canJoin = computed(() => {
      if (!inputCode.value || inputCode.value.length !== 6) return false
      if (!isAuthenticated.value && !studentName.value.trim()) return false
      return true
    })

    // Methods
    const joinSession = async () => {
      if (!canJoin.value) return
      
      isJoining.value = true
      
      try {
        sessionCode.value = inputCode.value.toUpperCase()
        session = useQuestionSession(sessionCode.value, 'student')
        
        // Wait for connection
        await new Promise(resolve => {
          const checkConnection = () => {
            if (session.isConnected.value) {
              resolve()
            } else {
              setTimeout(checkConnection, 100)
            }
          }
          checkConnection()
        })
        
        // Explicitly join the session
        session.joinSession()
        
        answerSubmitted.value = false
        
      } catch (error) {
        console.error('Failed to join session:', error)
        alert('Failed to join session. Please check the code and try again.')
        sessionCode.value = ''
      } finally {
        isJoining.value = false
      }
    }

    const leaveSession = () => {
      if (confirm('Are you sure you want to leave this session?')) {
        clearStudentSession()
        stopAutoSave()
        resetSession()
      }
    }

    const resetSession = () => {
      sessionCode.value = ''
      inputCode.value = ''
      studentName.value = ''
      answerSubmitted.value = false
      wasRestored.value = false
      hasPreviousSession.value = false
      session = null
    }

    const handleAnswerSubmitted = (submissionData) => {
      if (session) {
        const success = session.submitAnswer(submissionData.answer)
        if (success) {
          answerSubmitted.value = true
        } else {
          alert('Failed to submit answer. Please try again.')
        }
      }
    }

    const handleAnswerChanged = (answerData) => {
      // Optional: Handle real-time answer changes
      console.log('Answer changed:', answerData)
    }

    const getQuestionTypeLabel = (type) => {
      const labels = {
        'multiple_choice': 'Multiple Choice',
        'multi_select': 'Multiple Selection',
        'text': 'Text Answer',
        'number': 'Number Answer',
        'date': 'Date Answer',
        'rating': 'Rating Scale',
        'custom': 'Custom Type'
      }
      return labels[type] || 'Unknown Type'
    }

    // Session persistence methods
    const saveStudentSession = () => {
      if (sessionCode.value && session?.studentInfo?.value) {
        const sessionData = {
          sessionCode: sessionCode.value,
          studentName: session.studentInfo.value.name,
          isAuthenticated: session.studentInfo.value.isAuthenticated,
          studentId: session.studentInfo.value.id,
          answerSubmitted: answerSubmitted.value,
          savedAt: new Date().toISOString()
        }
        localStorage.setItem('question_student_session', JSON.stringify(sessionData))
      }
    }

    const loadStudentSession = () => {
      const saved = localStorage.getItem('question_student_session')
      if (saved) {
        try {
          const sessionData = JSON.parse(saved)
          
          // Check if session is recent (within 24 hours)
          const savedTime = new Date(sessionData.savedAt)
          const now = new Date()
          const hoursDiff = (now - savedTime) / (1000 * 60 * 60)
          
          if (hoursDiff < 24) {
            previousSessionCode.value = sessionData.sessionCode
            previousStudentName.value = sessionData.studentName
            hasPreviousSession.value = true
            return true
          } else {
            // Session too old, clear it
            localStorage.removeItem('question_student_session')
          }
        } catch (error) {
          console.error('Failed to load student session:', error)
          localStorage.removeItem('question_student_session')
        }
      }
      return false
    }

    const rejoinPreviousSession = async () => {
      if (!previousSessionCode.value) return
      
      isRejoining.value = true
      isRestoring.value = true
      
      try {
        inputCode.value = previousSessionCode.value
        if (!isAuthenticated.value) {
          studentName.value = previousStudentName.value
        }
        
        await joinSession()
        wasRestored.value = true
        hasPreviousSession.value = false
        
      } catch (error) {
        console.error('Failed to rejoin session:', error)
        alert('Failed to rejoin session. Please try again.')
      } finally {
        isRejoining.value = false
        isRestoring.value = false
      }
    }

    const clearPreviousSession = () => {
      localStorage.removeItem('question_student_session')
      hasPreviousSession.value = false
      previousSessionCode.value = ''
      previousStudentName.value = ''
    }

    const clearStudentSession = () => {
      localStorage.removeItem('question_student_session')
    }

    // Auto-save session state periodically
    let saveInterval = null
    const startAutoSave = () => {
      if (saveInterval) clearInterval(saveInterval)
      saveInterval = setInterval(saveStudentSession, 5000) // Save every 5 seconds
    }

    const stopAutoSave = () => {
      if (saveInterval) {
        clearInterval(saveInterval)
        saveInterval = null
      }
    }

    // Watch for session changes and save
    watch([sessionCode, answerSubmitted], () => {
      saveStudentSession()
    })

    // Update joinSession to set wasRestored flag
    const joinSessionWithRestore = async () => {
      const result = await joinSession()
      if (result && !wasRestored.value) {
        wasRestored.value = false // Reset for new session
      }
      return result
    }

    // Initialize student name from localStorage if guest
    onMounted(() => {
      // Try to load previous session first
      loadStudentSession()
      
      if (!isAuthenticated.value) {
        const savedName = localStorage.getItem('question_session_guest_name')
        if (savedName) {
          studentName.value = savedName
        }
      }
      
      // Check for URL parameter (direct join)
      const urlParams = new URLSearchParams(window.location.search)
      const codeParam = urlParams.get('code')
      if (codeParam && codeParam.length === 6) {
        inputCode.value = codeParam.toUpperCase()
      }
      
      // Start auto-save
      startAutoSave()
    })

    // Cleanup on unmount
    onUnmounted(() => {
      stopAutoSave()
      saveStudentSession() // Final save
    })

    return {
      // State
      sessionCode,
      inputCode,
      studentName,
      isJoining,
      answerSubmitted,
      isRestoring,
      isRejoining,
      wasRestored,
      hasPreviousSession,
      previousSessionCode,
      previousStudentName,
      
      // Computed
      isAuthenticated,
      canJoin,
      
      // Session computed (from useQuestionSession)
      isConnected: computed(() => session?.isConnected.value || false),
      sessionStatus: computed(() => session?.sessionStatus.value || 'waiting'),
      currentQuestion: computed(() => session?.currentQuestion.value || null),
      studentInfo: computed(() => session?.studentInfo.value || null),
      hasActiveQuestion: computed(() => session?.hasActiveQuestion.value || false),
      
      // Methods
      joinSession,
      leaveSession,
      resetSession,
      handleAnswerSubmitted,
      handleAnswerChanged,
      getQuestionTypeLabel,
      rejoinPreviousSession,
      clearPreviousSession
    }
  }
}
</script>

<style scoped>
.student-view {
  max-width: 100%;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
