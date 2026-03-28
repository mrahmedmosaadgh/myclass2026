<template>
  <div class="live-question-element" :class="{ 'presentation-mode': !isEditMode, 'active': element.data?.isActive }">
    <!-- Edit Mode -->
    <div v-if="isEditMode" class="edit-mode">
      <div class="element-header">
        <h3 class="element-title">Live Question Element</h3>
        <div class="element-controls">
          <button 
            @click="startSession" 
            :disabled="element.data?.isActive || !element.data?.questionTitle"
            class="control-btn start-btn"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="5 3 19 12 5 21 5 3"></polygon>
            </svg>
            Start
          </button>
          <button 
            @click="stopSession" 
            :disabled="!element.data?.isActive"
            class="control-btn stop-btn"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="6" y="4" width="4" height="16"></rect>
              <rect x="14" y="4" width="4" height="16"></rect>
            </svg>
            Stop
          </button>
          <button @click="openResults" class="control-btn results-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14,2 14,8 20,8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10,9 9,9 8,9"></polyline>
            </svg>
            Results
          </button>
        </div>
      </div>
      
      <div class="question-editor">
        <div class="form-group">
          <label>Question Title</label>
          <input 
            v-model="element.data.questionTitle" 
            type="text" 
            placeholder="Enter your question..."
            class="form-input"
          />
        </div>
        <div class="form-group">
          <label>Instructions</label>
          <textarea 
            v-model="element.data.questionInstructions" 
            placeholder="Provide instructions for students..."
            class="form-textarea"
            rows="2"
          ></textarea>
        </div>
        <div class="form-group">
          <label>Time Limit (seconds)</label>
          <input 
            v-model="element.data.timeLimit" 
            type="number" 
            placeholder="Optional time limit"
            class="form-input"
            min="10"
            max="300"
          />
        </div>
      </div>

      <div v-if="element.data?.isActive" class="session-status">
        <div class="status-indicator active"></div>
        <span class="status-text">Session Active</span>
        <div class="session-info">
          <span class="session-code">{{ element.data.sessionCode }}</span>
          <span class="response-count">{{ element.data.responses?.length || 0 }} responses</span>
        </div>
      </div>
    </div>

    <!-- Presentation Mode -->
    <div v-else class="presentation-mode">
      <div v-if="!element.data?.isActive" class="inactive-state">
        <div class="placeholder-content">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>
          </svg>
          <h3>{{ element.data?.questionTitle || 'Live Question' }}</h3>
          <p>Start the session to enable student responses</p>
        </div>
      </div>

      <div v-else class="active-state">
        <div class="question-display">
          <h2 class="question-title">{{ element.data?.questionTitle }}</h2>
          <p class="question-instructions">{{ element.data?.questionInstructions }}</p>
        </div>

        <div class="session-info">
          <div class="code-display">
            <span class="code-label">Session Code:</span>
            <span class="code-value">{{ element.data?.sessionCode }}</span>
          </div>
          <div class="responses-display">
            <span class="responses-count">{{ element.data?.responses?.length || 0 }}</span>
            <span class="responses-label">Responses</span>
          </div>
          <div v-if="element.data?.timeLimit" class="timer-display">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span class="timer-value">{{ formatTime(remainingTime) }}</span>
          </div>
        </div>

        <!-- Response Preview -->
        <div v-if="element.data?.responses?.length > 0" class="response-preview">
          <h4>Recent Responses</h4>
          <div class="response-list">
            <div 
              v-for="response in recentResponses" 
              :key="response.studentId"
              class="response-item"
            >
              <span class="student-name">{{ response.studentName }}</span>
              <span class="response-text">{{ truncateText(response.answer?.text || response.answer, 50) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Results Modal -->
    <LiveQuestionResults 
      v-if="showResults"
      :responses="element.data?.responses || []"
      :question="element.data"
      @close="showResults = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useLiveQuestionStore } from '../stores/liveQuestionStore'
import { useGameStore } from '../stores/gameStore'
import { useQuestionSession } from '../../../../../remot_control/v1/examples/question_responses/composables/useQuestionSession'
import LiveQuestionResults from './LiveQuestionResults.vue'

const props = defineProps({
  element: {
    type: Object,
    required: true
  },
  isEditMode: {
    type: Boolean,
    default: true
  }
})

const liveQuestionStore = useLiveQuestionStore()
const gameStore = useGameStore()

const showResults = ref(false)
const activeSession = ref(null)
const timerInterval = ref(null)
const remainingTime = ref(0)

const recentResponses = computed(() => {
  const responses = props.element.data?.responses || []
  return responses.slice(-3).reverse() // Show last 3 responses
})

function generateSessionCode() {
  const chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'
  let code = ''
  for (let i = 0; i < 6; i++) {
    code += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  return code
}

async function startSession() {
  if (!props.element.data?.questionTitle) {
    alert('Please enter a question title first')
    return
  }

  try {
    const sessionCode = generateSessionCode()
    
    // Update element data
    props.element.data.sessionCode = sessionCode
    props.element.data.isActive = true
    props.element.data.responses = []

    // Initialize remote control session
    activeSession.value = useQuestionSession(sessionCode, 'teacher')

    // Publish question
    const questionData = {
      id: props.element.id,
      title: props.element.data.questionTitle,
      instructions: props.element.data.questionInstructions,
      timeLimit: props.element.data.timeLimit,
      minLength: 1,
      maxLength: 1000
    }

    const published = activeSession.value.publishQuestion(questionData)
    if (!published) {
      throw new Error('Failed to publish question')
    }

    // Listen for responses
    activeSession.value.channel.onCommand((command) => {
      if (command.type === 'submit_answer') {
        handleStudentResponse(command.payload)
      }
    })

    // Start timer if time limit is set
    if (props.element.data.timeLimit) {
      remainingTime.value = props.element.data.timeLimit
      startTimer()
    }

    console.log('Live question session started:', sessionCode)

  } catch (error) {
    console.error('Failed to start session:', error)
    alert('Failed to start session. Please try again.')
  }
}

function stopSession() {
  if (activeSession.value) {
    activeSession.value.closeSession()
    activeSession.value = null
  }

  if (timerInterval.value) {
    clearInterval(timerInterval.value)
    timerInterval.value = null
  }

  props.element.data.isActive = false
  remainingTime.value = 0

  // Award points to groups based on responses
  awardGroupPoints()
}

function handleStudentResponse(response) {
  if (!props.element.data.responses) {
    props.element.data.responses = []
  }

  // Add response if not already exists
  const existingIndex = props.element.data.responses.findIndex(r => r.studentId === response.studentId)
  if (existingIndex === -1) {
    props.element.data.responses.push({
      ...response,
      score: 0,
      rank: null
    })
  }
}

function startTimer() {
  timerInterval.value = setInterval(() => {
    remainingTime.value--
    if (remainingTime.value <= 0) {
      stopSession()
    }
  }, 1000)
}

function formatTime(seconds) {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

function truncateText(text, maxLength) {
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

function openResults() {
  showResults.value = true
}

function awardGroupPoints() {
  const responses = props.element.data.responses || []
  if (responses.length === 0) return

  // Calculate rankings
  const rankedResponses = [...responses]
    .sort((a, b) => b.score - a.score)
    .map((response, index) => ({
      ...response,
      rank: index + 1
    }))

  // Award points to groups based on rankings
  rankedResponses.forEach((response) => {
    if (response.score > 0) {
      const points = Math.round(response.score / 10) // Convert score to points (1-10 points)
      
      // Find student's group and award points
      // Check if student is in participants list first
      const participant = gameStore.participants.find(p => p.id === response.studentId)
      if (participant && participant.group) {
        // Find group by name from participant's group assignment
        const group = gameStore.groups.find(g => g.name === participant.group)
        if (group) {
          gameStore.updateGroupScore(group.id, points)
          console.log(`Awarded ${points} points to group ${group.name} for ${response.studentName}'s response`)
        }
      }
    }
  })

  // Log the question in history for grading purposes
  gameStore.questionHistory[props.element.id] = {
    type: 'live-question',
    status: 'graded',
    responses: rankedResponses,
    totalPoints: rankedResponses.reduce((sum, r) => sum + (r.score > 0 ? Math.round(r.score / 10) : 0), 0),
    gradedAt: new Date().toISOString()
  }

  console.log('Points awarded to groups based on live question responses')
}

onUnmounted(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
  }
  if (activeSession.value) {
    activeSession.value.closeSession()
  }
})
</script>

<style scoped>
.live-question-element {
  width: 100%;
  height: 100%;
  background: white;
  border-radius: 8px;
  border: 2px solid #e5e7eb;
  overflow: hidden;
  position: relative;
}

.live-question-element.active {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Edit Mode Styles */
.edit-mode {
  padding: 20px;
  height: 100%;
  overflow-y: auto;
}

.element-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e5e7eb;
}

.element-title {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.element-controls {
  display: flex;
  gap: 8px;
}

.control-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #374151;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.control-btn:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.control-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.start-btn:not(:disabled) {
  background: #10b981;
  color: white;
  border-color: #10b981;
}

.stop-btn:not(:disabled) {
  background: #ef4444;
  color: white;
  border-color: #ef4444;
}

.question-editor {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 12px;
  font-weight: 500;
  color: #374151;
}

.form-input, .form-textarea {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-input:focus, .form-textarea:focus {
  outline: none;
  border-color: #10b981;
}

.session-status {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 20px;
  padding: 12px;
  background: #f0fdf4;
  border: 1px solid #10b981;
  border-radius: 6px;
}

.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  animation: pulse 2s infinite;
}

.status-text {
  font-weight: 500;
  color: #059669;
}

.session-info {
  margin-left: auto;
  display: flex;
  gap: 16px;
  font-size: 12px;
}

.session-code {
  font-weight: 600;
  color: #059669;
}

.response-count {
  color: #6b7280;
}

/* Presentation Mode Styles */
.presentation-mode {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.inactive-state {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9fafb;
}

.placeholder-content {
  text-align: center;
  color: #6b7280;
}

.placeholder-content h3 {
  margin: 16px 0 8px;
  font-size: 18px;
  color: #374151;
}

.placeholder-content p {
  margin: 0;
  font-size: 14px;
}

.active-state {
  height: 100%;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.question-display {
  text-align: center;
}

.question-title {
  font-size: 24px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 12px;
}

.question-instructions {
  font-size: 16px;
  color: #6b7280;
  margin: 0;
}

.session-info {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 32px;
  padding: 16px;
  background: #f3f4f6;
  border-radius: 8px;
}

.code-display, .responses-display, .timer-display {
  display: flex;
  align-items: center;
  gap: 8px;
}

.code-label, .responses-label {
  font-size: 14px;
  color: #6b7280;
}

.code-value {
  font-size: 18px;
  font-weight: 600;
  color: #10b981;
}

.responses-count {
  font-size: 24px;
  font-weight: 600;
  color: #10b981;
}

.timer-display {
  color: #f59e0b;
}

.timer-value {
  font-weight: 600;
  margin-left: 4px;
}

.response-preview {
  flex: 1;
  overflow-y: auto;
}

.response-preview h4 {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 12px;
}

.response-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.response-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.student-name {
  font-weight: 500;
  color: #374151;
  font-size: 12px;
}

.response-text {
  color: #6b7280;
  font-size: 12px;
  max-width: 60%;
  text-align: right;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
