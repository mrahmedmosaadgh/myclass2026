<template>
  <div class="teacher-view min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Question Response System</h1>
        <p class="text-gray-600">Create and manage real-time question sessions for your students</p>
      </div>

      <!-- Session Setup -->
      <div v-if="!sessionCode" class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Create New Session</h2>
        
        <!-- Session Restoration Notice -->
        <div v-if="isRestoring" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div>
              <p class="text-blue-800 font-medium">Restoring Previous Session...</p>
              <p class="text-blue-700 text-sm">Reconnecting to your active session</p>
            </div>
          </div>
        </div>
        
        <div class="space-y-4">
          <!-- Session Code Display -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Session Code</label>
            <div class="flex items-center space-x-4">
              <div class="flex-1">
                <input
                  v-model="generatedCode"
                  type="text"
                  readonly
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-center text-2xl font-mono font-bold text-blue-600"
                  placeholder="Click generate to create code"
                >
              </div>
              <button
                @click="generateSessionCode"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                Generate Code
              </button>
            </div>
            <p class="text-sm text-gray-500 mt-2">
              Share this code with your students so they can join the session
            </p>
          </div>

          <!-- Start Session Button -->
          <button
            @click="startSession"
            :disabled="!generatedCode || isStarting"
            class="w-full py-3 px-4 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors mb-2"
          >
            <span v-if="isStarting" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Starting Session...
            </span>
            <span v-else>
              Start Session
            </span>
          </button>

          <!-- Clear Saved Session Button -->
          <button
            @click="clearSavedSession"
            class="w-full py-2 px-4 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm"
          >
            Clear Saved Session
          </button>
        </div>
      </div>

      <!-- Active Session -->
      <div v-if="sessionCode" class="space-y-8">
        <!-- Session Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-xl font-semibold text-gray-800">Active Session</h2>
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
                <span class="text-gray-500">{{ responseCount }} responses</span>
                <!-- Restored Session Indicator -->
                <span v-if="wasRestored" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                  Restored
                </span>
              </div>
            </div>
            
            <div class="flex items-center space-x-2">
              <button
                @click="copySessionCode"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Copy Code
              </button>
              
              <button
                @click="endSession"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
              >
                End Session
              </button>
            </div>
          </div>

          <!-- QR Code Placeholder -->
          <div class="bg-gray-50 rounded-lg p-4 text-center">
            <div class="w-32 h-32 bg-gray-200 rounded-lg mx-auto mb-2 flex items-center justify-center">
              <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
              </svg>
            </div>
            <p class="text-sm text-gray-600">QR Code for mobile access</p>
          </div>
        </div>

        <!-- Question Creation -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Create Question</h2>
          
          <div class="space-y-4">
            <!-- Question Type Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Question Type</label>
              <select
                v-model="questionForm.type"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select question type</option>
                <option value="multiple_choice">Multiple Choice (Single)</option>
                <option value="multi_select">Multiple Choice (Multiple)</option>
                <option value="text">Text Answer</option>
                <option value="number">Number Answer</option>
                <option value="date">Date Answer</option>
                <option value="rating">Rating Scale</option>
              </select>
            </div>

            <!-- Question Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Question Title</label>
              <input
                v-model="questionForm.title"
                type="text"
                placeholder="Enter your question..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>

            <!-- Question Description (Optional) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
              <textarea
                v-model="questionForm.description"
                placeholder="Additional context or instructions..."
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
              ></textarea>
            </div>

            <!-- Options for Multiple Choice -->
            <div v-if="questionForm.type === 'multiple_choice' || questionForm.type === 'multi_select'">
              <label class="block text-sm font-medium text-gray-700 mb-2">Answer Options</label>
              <div class="space-y-2">
                <div
                  v-for="(option, index) in questionForm.options"
                  :key="index"
                  class="flex items-center space-x-2"
                >
                  <span class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-sm font-medium">
                    {{ String.fromCharCode(65 + index) }}
                  </span>
                  <input
                    v-model="questionForm.options[index]"
                    type="text"
                    :placeholder="`Option ${index + 1}`"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                  <button
                    @click="removeOption(index)"
                    v-if="questionForm.options.length > 2"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
                <button
                  @click="addOption"
                  v-if="questionForm.options.length < 6"
                  class="w-full py-2 px-4 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                >
                  + Add Option
                </button>
              </div>
            </div>

            <!-- Number Input Settings -->
            <div v-if="questionForm.type === 'number'">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Value</label>
                  <input
                    v-model.number="questionForm.rules.min"
                    type="number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Value</label>
                  <input
                    v-model.number="questionForm.rules.max"
                    type="number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                </div>
              </div>
            </div>

            <!-- Rating Settings -->
            <div v-if="questionForm.type === 'rating'">
              <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Rating</label>
              <select
                v-model.number="questionForm.maxRating"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option :value="5">5 Stars</option>
                <option :value="10">10 Stars</option>
              </select>
            </div>

            <!-- Time Limit -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Time Limit (Optional)</label>
              <div class="flex items-center space-x-4">
                <input
                  v-model.number="questionForm.timeLimit"
                  type="number"
                  min="10"
                  step="10"
                  placeholder="60"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <span class="text-gray-500">seconds</span>
              </div>
            </div>

            <!-- Required Checkbox -->
            <div class="flex items-center">
              <input
                v-model="questionForm.rules.required"
                type="checkbox"
                id="required"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              >
              <label for="required" class="ml-2 text-sm text-gray-700">Required question</label>
            </div>

            <!-- Publish Question Button -->
            <button
              @click="publishQuestion"
              :disabled="!isQuestionValid || isPublishing"
              class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="isPublishing" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Publishing...
              </span>
              <span v-else>
                Publish Question
              </span>
            </button>
          </div>
        </div>

        <!-- Current Question Preview -->
        <div v-if="currentQuestion" class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Current Question</h2>
          <div class="mb-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
              Active
            </span>
            <span class="ml-2 text-gray-500">Published {{ formatTime(currentQuestion.publishedAt) }}</span>
          </div>
          <QuestionRenderer :question-data="currentQuestion" :readonly="true" />
        </div>

        <!-- Response Collector -->
        <div v-if="currentQuestion" class="bg-white rounded-lg shadow-md p-6">
          <ResponseCollector
            :session-code="sessionCode"
            :question-data="currentQuestion"
            :responses="responses"
            @export-responses="handleExportResponses"
            @clear-responses="handleClearResponses"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useQuestionSession } from './composables/useQuestionSession.js'
import QuestionRenderer from './components/QuestionRenderer.vue'
import ResponseCollector from './components/ResponseCollector.vue'

export default {
  name: 'TeacherView',
  components: {
    QuestionRenderer,
    ResponseCollector
  },
  setup() {
    // Session state
    const sessionCode = ref('')
    const generatedCode = ref('')
    const isStarting = ref(false)
    const isPublishing = ref(false)
    const isRestoring = ref(false)
    const wasRestored = ref(false)

    // Question form state
    const questionForm = ref({
      type: '',
      title: '',
      description: '',
      options: ['', ''],
      rules: {
        required: true,
        minSelection: 1,
        maxSelection: 1,
        minLength: 1,
        maxLength: 500,
        min: null,
        max: null
      },
      timeLimit: null,
      maxRating: 5,
      metadata: {
        subject: '',
        difficulty: '',
        points: 1
      }
    })

    // Initialize session
    let session = null

    // Computed
    const isQuestionValid = computed(() => {
      const form = questionForm.value
      
      if (!form.type || !form.title.trim()) return false
      
      switch (form.type) {
        case 'multiple_choice':
        case 'multi_select':
          return form.options.filter(opt => opt.trim()).length >= 2
        case 'number':
          return form.rules.min !== null && form.rules.max !== null && form.rules.min < form.rules.max
        default:
          return true
      }
    })

    // Methods
    const generateSessionCode = () => {
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
      let code = ''
      for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length))
      }
      generatedCode.value = code
    }

    const startSession = async () => {
      if (!generatedCode.value) return
      
      isStarting.value = true
      wasRestored.value = false // Reset for new session
      
      try {
        sessionCode.value = generatedCode.value
        session = useQuestionSession(sessionCode.value, 'teacher')
        
        // Wait for connection with timeout
        await new Promise((resolve, reject) => {
          const timeout = setTimeout(() => {
            reject(new Error('Connection timeout'))
          }, 10000) // 10 second timeout
          
          const checkConnection = () => {
            if (session.isConnected.value) {
              clearTimeout(timeout)
              resolve()
            } else if (session.lastError?.value) {
              clearTimeout(timeout)
              reject(session.lastError.value instanceof Error ? session.lastError.value : new Error(String(session.lastError.value)))
            } else {
              setTimeout(checkConnection, 100)
            }
          }
          checkConnection()
        })
        
        console.log('Teacher session started successfully:', sessionCode.value)
        
      } catch (error) {
        console.error('Failed to start session:', error)
        alert('Failed to start session: ' + error.message + '. Please check your internet connection and try again.')
        sessionCode.value = ''
        session = null
      } finally {
        isStarting.value = false
      }
    }

    const endSession = () => {
      if (session && confirm('Are you sure you want to end this session?')) {
        session.closeSession()
        sessionCode.value = ''
        generatedCode.value = ''
        session = null
        resetQuestionForm()
        
        // Clear persisted session state
        clearSessionState()
        stopAutoSave()
      }
    }

    const copySessionCode = () => {
      navigator.clipboard.writeText(sessionCode.value)
        .then(() => {
          alert('Session code copied to clipboard!')
        })
        .catch(() => {
          alert('Failed to copy session code')
        })
    }

    const addOption = () => {
      if (questionForm.value.options.length < 6) {
        questionForm.value.options.push('')
      }
    }

    const removeOption = (index) => {
      if (questionForm.value.options.length > 2) {
        questionForm.value.options.splice(index, 1)
      }
    }

    const resetQuestionForm = () => {
      questionForm.value = {
        type: '',
        title: '',
        description: '',
        options: ['', ''],
        rules: {
          required: true,
          minSelection: 1,
          maxSelection: 1,
          minLength: 1,
          maxLength: 500,
          min: null,
          max: null
        },
        timeLimit: null,
        maxRating: 5,
        metadata: {
          subject: '',
          difficulty: '',
          points: 1
        }
      }
    }

    const publishQuestion = () => {
      if (!session || !isQuestionValid.value) return
      
      isPublishing.value = true
      
      try {
        const questionData = {
          ...questionForm.value,
          id: crypto.randomUUID(),
          options: questionForm.value.options.filter(opt => opt.trim())
        }
        
        session.publishQuestion(questionData)
        resetQuestionForm()
        
      } catch (error) {
        console.error('Failed to publish question:', error)
        alert('Failed to publish question. Please try again.')
      } finally {
        isPublishing.value = false
      }
    }

    const formatTime = (timestamp) => {
      try {
        const date = new Date(timestamp)
        return date.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (error) {
        return timestamp
      }
    }

    const handleExportResponses = (exportData) => {
      // Create and download JSON file
      const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `question_responses_${sessionCode.value}_${Date.now()}.json`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      URL.revokeObjectURL(url)
    }

    const handleClearResponses = () => {
      if (session) {
        session.responses.value = []
      }
    }

    // Session persistence methods
    const saveSessionState = () => {
      if (sessionCode.value) {
        const sessionState = {
          sessionCode: sessionCode.value,
          generatedCode: generatedCode.value,
          currentQuestion: session?.currentQuestion.value,
          responses: session?.responses.value || [],
          sessionStatus: session?.sessionStatus.value || 'active',
          savedAt: new Date().toISOString()
        }
        localStorage.setItem('question_teacher_session', JSON.stringify(sessionState))
      }
    }

    const loadSessionState = () => {
      const saved = localStorage.getItem('question_teacher_session')
      if (saved) {
        try {
          const sessionState = JSON.parse(saved)
          
          // Check if session is recent (within 24 hours)
          const savedTime = new Date(sessionState.savedAt)
          const now = new Date()
          const hoursDiff = (now - savedTime) / (1000 * 60 * 60)
          
          if (hoursDiff < 24) {
            sessionCode.value = sessionState.sessionCode
            generatedCode.value = sessionState.generatedCode
            
            // Auto-reconnect to session
            reconnectToSession(sessionState)
            
            return true
          } else {
            // Session too old, clear it
            localStorage.removeItem('question_teacher_session')
          }
        } catch (error) {
          console.error('Failed to load session state:', error)
          localStorage.removeItem('question_teacher_session')
        }
      }
      return false
    }

    const reconnectToSession = async (sessionState) => {
      isStarting.value = true
      isRestoring.value = true
      wasRestored.value = true
      
      try {
        session = useQuestionSession(sessionCode.value, 'teacher')
        
        await new Promise((resolve, reject) => {
          const timeout = setTimeout(() => {
            reject(new Error('Connection timeout'))
          }, 10000)

          const checkConnection = () => {
            if (session.isConnected.value) {
              clearTimeout(timeout)
              resolve()
            } else if (session.lastError?.value) {
              clearTimeout(timeout)
              reject(session.lastError.value instanceof Error ? session.lastError.value : new Error(String(session.lastError.value)))
            } else {
              setTimeout(checkConnection, 100)
            }
          }
          checkConnection()
        })
        
        // Restore session data if available
        if (sessionState.currentQuestion) {
          // The session will automatically sync the current question from Firebase
          // We just need to wait a bit for it to load
          setTimeout(() => {
            if (!session.currentQuestion.value && sessionState.currentQuestion) {
              // If no question in Firebase, restore the saved one
              session.publishQuestion(sessionState.currentQuestion)
            }
          }, 1000)
        }
        
      } catch (error) {
        console.error('Failed to reconnect to session:', error)
        // Clear invalid session
        localStorage.removeItem('question_teacher_session')
        sessionCode.value = ''
        generatedCode.value = ''
      } finally {
        isStarting.value = false
        isRestoring.value = false
      }
    }

    const clearSessionState = () => {
      localStorage.removeItem('question_teacher_session')
    }

    const clearSavedSession = () => {
      if (confirm('Are you sure you want to clear the saved session? This will remove any saved session data.')) {
        clearSessionState()
        sessionCode.value = ''
        generatedCode.value = ''
        session = null
        stopAutoSave()
        generateSessionCode()
      }
    }

    // Auto-save session state periodically
    let saveInterval = null
    const startAutoSave = () => {
      if (saveInterval) clearInterval(saveInterval)
      saveInterval = setInterval(saveSessionState, 5000) // Save every 5 seconds
    }

    const stopAutoSave = () => {
      if (saveInterval) {
        clearInterval(saveInterval)
        saveInterval = null
      }
    }

    // Watch for session changes and save
    watch([sessionCode, () => session?.currentQuestion.value, () => session?.responses.value], () => {
      saveSessionState()
    }, { deep: true })

    // Initialize
    onMounted(() => {
      // Try to load existing session first
      const hasExistingSession = loadSessionState()
      
      if (!hasExistingSession) {
        generateSessionCode()
      }
      
      // Start auto-save
      startAutoSave()
    })

    // Cleanup on unmount
    onUnmounted(() => {
      stopAutoSave()
      saveSessionState() // Final save
    })

    return {
      // Session
      sessionCode,
      generatedCode,
      isStarting,
      isPublishing,
      isRestoring,
      wasRestored,
      
      // Question form
      questionForm,
      isQuestionValid,
      
      // Session computed (from useQuestionSession)
      isConnected: computed(() => session?.isConnected.value || false),
      currentQuestion: computed(() => session?.currentQuestion.value || null),
      responses: computed(() => session?.responses.value || []),
      responseCount: computed(() => session?.responseCount.value || 0),
      lastError: computed(() => session?.channel?.lastError?.value || null),
      
      // Methods
      generateSessionCode,
      startSession,
      endSession,
      copySessionCode,
      clearSavedSession,
      addOption,
      removeOption,
      publishQuestion,
      formatTime,
      handleExportResponses,
      handleClearResponses
    }
  }
}
</script>

<style scoped>
.teacher-view {
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
