<template>
  <Teleport to="body">
    <Transition name="panel">
      <div v-if="isPanelOpen" class="live-question-panel-overlay" @click.self="closePanel">
        <div class="live-question-panel">
          <div class="panel-header">
            <h2 class="panel-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h2.5a.5.5 0 0 0 .5-.5v-2A2.5 2.5 0 0 1 9.5 3h0A2.5 2.5 0 0 1 12 5.5v2a.5.5 0 0 0 .5.5H15a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2V8.4"></path>
                <path d="M7 15h.01"></path><path d="M11 15h.01"></path><path d="M15 15h.01"></path><path d="M19 15h.01"></path>
              </svg>
              Live Question Session
            </h2>
            <button @click="closePanel" class="close-button" title="Close Panel">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <!-- Session Setup -->
          <div v-if="!isActive" class="panel-content">
            <div class="section">
              <h3 class="section-title">Create Question</h3>

              <div class="form-group">
                <label class="form-label">Question Title *</label>
                <textarea
                  v-model="questionTitle"
                  class="form-textarea"
                  placeholder="Enter your question here..."
                  rows="3"
                  maxlength="500"
                ></textarea>
                <div class="form-hint">{{ questionTitle.length }}/500 characters</div>
              </div>

              <div class="form-group">
                <label class="form-label">Instructions (Optional)</label>
                <textarea
                  v-model="questionInstructions"
                  class="form-textarea"
                  placeholder="Additional instructions for students..."
                  rows="2"
                  maxlength="200"
                ></textarea>
                <div class="form-hint">{{ questionInstructions.length }}/200 characters</div>
              </div>

              <div class="form-group">
                <label class="form-label">Time Limit (Optional)</label>
                <select v-model="timeLimit" class="form-select">
                  <option :value="null">No time limit</option>
                  <option value="60">1 minute</option>
                  <option value="120">2 minutes</option>
                  <option value="300">5 minutes</option>
                  <option value="600">10 minutes</option>
                </select>
              </div>
            </div>

            <div class="section">
              <h3 class="section-title">Session Code</h3>
              <div class="code-display">
                <div class="code-input-group">
                  <input
                    v-model="generatedCode"
                    readonly
                    class="code-input"
                    placeholder="Click generate to create code"
                  >
                  <button @click="generateCode" class="generate-button">
                    Generate
                  </button>
                </div>
                <p class="code-hint">
                  Share this code with students to join the session
                </p>
              </div>
            </div>

            <div class="panel-actions">
              <button
                @click="startSession"
                :disabled="!canStartSession"
                class="primary-button"
              >
                <svg v-if="isStarting" class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isStarting ? 'Starting Session...' : 'Start Live Session' }}
              </button>
            </div>
          </div>

          <!-- Active Session -->
          <div v-else class="panel-content">
            <div class="session-status">
              <div class="status-header">
                <div class="status-info">
                  <div class="status-code">
                    <span class="code-label">Session Code:</span>
                    <span class="code-value">{{ sessionCode }}</span>
                  </div>
                  <div class="status-indicator">
                    <div class="indicator-dot" :class="{ 'connected': isConnected, 'disconnected': !isConnected }"></div>
                    <span class="status-text">{{ isConnected ? 'Connected' : 'Disconnected' }}</span>
                  </div>
                </div>
              </div>

              <div class="response-count">
                <div class="count-number">{{ responseCount }}</div>
                <div class="count-label">Responses</div>
              </div>
            </div>

            <div class="question-display">
              <h4 class="question-title">{{ currentQuestion?.title }}</h4>
              <p v-if="currentQuestion?.instructions" class="question-instructions">
                {{ currentQuestion.instructions }}
              </p>
            </div>

            <div class="session-actions">
              <button @click="toggleResults" class="secondary-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline>
                </svg>
                View Responses
              </button>

              <button @click="exportResponses('json')" :disabled="!hasResponses" class="secondary-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Export JSON
              </button>

              <button @click="exportResponses('csv')" :disabled="!hasResponses" class="secondary-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline>
                </svg>
                Export CSV
              </button>

              <button @click="closeSession" class="danger-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Close Session
              </button>
            </div>
          </div>

          <!-- Results Panel -->
          <LiveQuestionResults
            v-if="isResultsOpen"
            :responses="responses"
            :question="currentQuestion"
            @close="isResultsOpen = false"
          />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useLiveQuestionStore } from '../stores/liveQuestionStore'
import { useQuestionSession } from '../../../../../remot_control/v1/examples/question_responses/composables/useQuestionSession'
import LiveQuestionResults from './LiveQuestionResults.vue'

const store = useLiveQuestionStore()
let activeSession = null

// Form data
const questionTitle = ref('')
const questionInstructions = ref('')
const timeLimit = ref(null)
const generatedCode = ref('')
const isStarting = ref(false)

// Panel state
const isPanelOpen = computed(() => store.isPanelOpen)
const isResultsOpen = ref(false)

// Session state
const isActive = computed(() => store.isActive)
const sessionCode = computed(() => store.sessionCode)
const isConnected = computed(() => store.isConnected)
const responseCount = computed(() => store.responseCount)
const responses = computed(() => store.responses)
const hasResponses = computed(() => store.hasResponses)
const currentQuestion = computed(() => store.currentQuestion)

// Computed
const canStartSession = computed(() => {
  return questionTitle.value.trim().length > 0 && generatedCode.value.length > 0
})

// Methods
function closePanel() {
  store.closePanel()
  resetForm()
}

function resetForm() {
  questionTitle.value = ''
  questionInstructions.value = ''
  timeLimit.value = null
  generatedCode.value = ''
  isStarting.value = false
}

function generateCode() {
  const chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'
  let code = ''
  for (let i = 0; i < 6; i++) {
    code += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  generatedCode.value = code
}

async function startSession() {
  if (!canStartSession.value) return

  isStarting.value = true

  try {
    // Set question data in store
    store.setQuestion({
      title: questionTitle.value.trim(),
      instructions: questionInstructions.value.trim(),
      timeLimit: timeLimit.value,
      minLength: 1,
      maxLength: 1000
    })

    store.setSessionCode(generatedCode.value)

    // Initialize the remote control question session
    activeSession = useQuestionSession(generatedCode.value, 'teacher')

    // Publish the question to Firebase
    const published = activeSession.publishQuestion(store.questionData)
    
    if (!published) {
      throw new Error('Failed to publish question')
    }

    // Mark as active in store
    store.startSession()

    // Listen for responses from students via the channel
    activeSession.channel.onCommand((command) => {
      if (command.type === 'submit_answer') {
        console.log('Received answer:', command.payload)
        store.addResponse({
          studentId: command.payload.studentId,
          studentName: command.payload.studentName,
          isAuthenticated: command.payload.isAuthenticated || false,
          answer: command.payload.answer,
          timestamp: command.payload.timestamp,
          submittedAt: new Date().toISOString()
        })
      }
    })

    // Update connection status based on channel connection
    const checkConnection = () => {
      store.setConnectionStatus(activeSession.isConnected.value)
    }
    checkConnection()
    setInterval(checkConnection, 2000)

    console.log('Session started successfully:', generatedCode.value)

  } catch (error) {
    console.error('Failed to start session:', error)
    alert('Failed to start session. Please try again.')
  } finally {
    isStarting.value = false
  }
}

function closeSession() {
  if (confirm('Are you sure you want to close this session? All responses will be kept but no new responses can be submitted.')) {
    // Close the remote control session
    if (activeSession) {
      activeSession.closeSession()
      activeSession = null
    }
    
    store.endSession()
    resetForm()
  }
}

function toggleResults() {
  isResultsOpen.value = !isResultsOpen.value
}

function exportResponses(format) {
  if (!hasResponses.value) return

  // This would integrate with the composable's export functionality
  // For now, we'll create a basic export
  const data = {
    sessionCode: sessionCode.value,
    question: currentQuestion.value,
    responses: responses.value,
    statistics: {
      totalResponses: responseCount.value,
      createdAt: currentQuestion.value?.createdAt ? new Date(currentQuestion.value.createdAt).toISOString() : null,
      closedAt: new Date().toISOString()
    }
  }

  if (format === 'csv') {
    exportAsCSV(data)
  } else {
    exportAsJSON(data)
  }
}

function exportAsJSON(data) {
  const jsonString = JSON.stringify(data, null, 2)
  const blob = new Blob([jsonString], { type: 'application/json' })
  const url = URL.createObjectURL(blob)

  const a = document.createElement('a')
  a.href = url
  a.download = `live_question_responses_${sessionCode.value}_${Date.now()}.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

function exportAsCSV(data) {
  const headers = ['Student Name', 'Student ID', 'Authenticated', 'Response', 'Submitted At']
  const rows = data.responses.map(response => [
    response.studentName,
    response.studentId,
    response.isAuthenticated ? 'Yes' : 'No',
    response.answer?.text || '',
    response.submittedAt
  ])

  const csvContent = [
    headers.join(','),
    ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
  ].join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)

  const a = document.createElement('a')
  a.href = url
  a.download = `live_question_responses_${sessionCode.value}_${Date.now()}.csv`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}
</script>

<style scoped>
.live-question-panel-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.live-question-panel {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 24px 0 24px;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 24px;
}

.panel-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 20px;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.close-button {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.close-button:hover {
  background: #f3f4f6;
  color: #374151;
}

.panel-content {
  padding: 0 24px 24px 24px;
}

.section {
  margin-bottom: 24px;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 16px 0;
}

.form-group {
  margin-bottom: 16px;
}

.form-label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}

.form-textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  resize: vertical;
  transition: border-color 0.2s;
}

.form-textarea:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-select {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  background: white;
}

.form-hint {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
  text-align: right;
}

.code-display {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
}

.code-input-group {
  display: flex;
  gap: 12px;
  margin-bottom: 8px;
}

.code-input {
  flex: 1;
  padding: 12px;
  font-size: 18px;
  font-weight: 600;
  text-align: center;
  background: white;
  border: 2px solid #10b981;
  border-radius: 8px;
  color: #10b981;
  font-family: monospace;
}

.generate-button {
  padding: 12px 16px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.generate-button:hover {
  background: #059669;
}

.code-hint {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.panel-actions {
  display: flex;
  justify-content: center;
  padding-top: 8px;
}

.primary-button {
  background: #10b981;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
  min-width: 160px;
}

.primary-button:hover:not(:disabled) {
  background: #059669;
}

.primary-button:disabled {
  background: #d1d5db;
  cursor: not-allowed;
}

.session-status {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
}

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.status-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.status-code {
  display: flex;
  align-items: center;
  gap: 8px;
}

.code-label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.code-value {
  font-size: 16px;
  font-weight: 600;
  font-family: monospace;
  color: #10b981;
  background: white;
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid #10b981;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 6px;
}

.indicator-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.indicator-dot.connected {
  background: #10b981;
}

.indicator-dot.disconnected {
  background: #ef4444;
}

.status-text {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.response-count {
  text-align: center;
  padding: 16px;
  background: white;
  border-radius: 8px;
  border: 2px solid #10b981;
}

.count-number {
  font-size: 48px;
  font-weight: 700;
  color: #10b981;
  line-height: 1;
}

.count-label {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin-top: 4px;
}

.question-display {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 20px;
}

.question-title {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 8px 0;
}

.question-instructions {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.session-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.secondary-button {
  display: flex;
  align-items: center;
  gap: 6px;
  background: white;
  color: #374151;
  border: 1px solid #d1d5db;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.secondary-button:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.secondary-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.danger-button {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #ef4444;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.danger-button:hover {
  background: #dc2626;
}

/* Panel animations */
.panel-enter-active,
.panel-leave-active {
  transition: all 0.3s ease;
}

.panel-enter-from,
.panel-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.panel-enter-to,
.panel-leave-from {
  opacity: 1;
  transform: scale(1);
}

@media (max-width: 640px) {
  .live-question-panel-overlay {
    padding: 10px;
  }

  .panel-header {
    padding: 16px 16px 0 16px;
  }

  .panel-content {
    padding: 0 16px 16px 16px;
  }

  .status-header {
    flex-direction: column;
    gap: 12px;
  }

  .session-actions {
    flex-direction: column;
  }

  .code-input-group {
    flex-direction: column;
  }
}
</style>
