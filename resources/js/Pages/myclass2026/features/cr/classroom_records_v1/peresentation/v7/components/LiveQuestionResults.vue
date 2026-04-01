<template>
  <div class="results-modal-overlay" @click.self="$emit('close')">
    <div class="results-modal">
      <div class="modal-header">
        <h3 class="modal-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline>
          </svg>
          Question Responses
        </h3>
        <button @click="$emit('close')" class="close-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="modal-content">
        <!-- Question Summary -->
        <div class="question-summary">
          <h4 class="question-text">{{ question?.title }}</h4>
          <div class="summary-stats">
            <div class="stat-item">
              <span class="stat-number">{{ responses.length }}</span>
              <span class="stat-label">Total Responses</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">{{ authenticatedCount }}</span>
              <span class="stat-label">Logged In</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">{{ guestCount }}</span>
              <span class="stat-label">Guests</span>
            </div>
          </div>
        </div>

        <!-- Export Actions -->
        <div class="export-section">
          <div class="export-buttons">
            <button @click="exportResponses('json')" :disabled="!hasResponses" class="export-btn json-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Export JSON
            </button>
            <button @click="exportResponses('csv')" :disabled="!hasResponses" class="export-btn csv-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline>
              </svg>
              Export CSV
            </button>
          </div>
        </div>

        <!-- Scoring Mode Toggle -->
        <div v-if="hasResponses" class="scoring-toggle">
          <button @click="toggleScoringMode" :class="['toggle-btn', { active: isScoringMode }]">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            {{ isScoringMode ? 'Done Scoring' : 'Score Responses' }}
          </button>
        </div>

        <!-- Responses List -->
        <div class="responses-section">
          <div v-if="!hasResponses" class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14,2 14,8 20,8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10,9 9,9 8,9"></polyline>
            </svg>
            <p class="empty-text">No responses yet</p>
            <p class="empty-subtext">Students will see their responses here as they submit them</p>
          </div>

          <div v-else class="responses-list">
            <div
              v-for="(response, index) in rankedResponses"
              :key="response.studentId + index"
              :class="['response-item', { 'scoring-mode': isScoringMode, 'ranked': response.rank }]"
            >
              <!-- Rank Badge -->
              <div v-if="response.rank" class="rank-badge" :class="`rank-${response.rank}`">
                <span class="rank-number">{{ response.rank }}</span>
                <span v-if="response.rank === 1" class="rank-trophy">🏆</span>
                <span v-else-if="response.rank === 2" class="rank-trophy">🥈</span>
                <span v-else-if="response.rank === 3" class="rank-trophy">🥉</span>
              </div>

              <div class="response-header">
                <div class="student-info">
                  <div class="student-name">
                    {{ response.studentName }}
                    <span v-if="response.isAuthenticated" class="auth-badge" title="Logged in user">✓</span>
                    <span v-else class="guest-badge" title="Guest user">○</span>
                  </div>
                  <div class="response-time">
                    {{ formatTime(response.timestamp) }}
                  </div>
                </div>

                <!-- Score Input -->
                <div v-if="isScoringMode" class="score-input">
                  <input
                    type="number"
                    :value="response.score"
                    @input="updateScore(response.studentId, $event.target.value)"
                    min="0"
                    max="100"
                    class="score-field"
                    placeholder="Score"
                  />
                  <span class="score-label">/100</span>
                </div>
                
                <!-- Score Display -->
                <div v-else-if="response.score > 0" class="score-display">
                  <span class="score-value">{{ response.score }}</span>
                  <span class="score-label">pts</span>
                </div>
              </div>
              <div class="response-content">
                <div class="response-text">{{ response.answer?.text || 'No response' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useLiveQuestionStore } from '../stores/liveQuestionStore'

const store = useLiveQuestionStore()
const isScoringMode = ref(false)

const props = defineProps({
  responses: {
    type: Array,
    default: () => []
  },
  question: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

// Computed
const hasResponses = computed(() => props.responses.length > 0)

const authenticatedCount = computed(() => {
  return props.responses.filter(r => r.isAuthenticated).length
})

const guestCount = computed(() => {
  return props.responses.filter(r => !r.isAuthenticated).length
})

const rankedResponses = computed(() => {
  return store.getRankedResponses()
})

// Methods
function toggleScoringMode() {
  isScoringMode.value = !isScoringMode.value
}

function updateScore(studentId, score) {
  const numericScore = parseInt(score) || 0
  store.updateResponseScore(studentId, numericScore)
}

// Methods
function formatTime(timestamp) {
  const date = new Date(timestamp)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function exportResponses(format) {
  if (!hasResponses.value) return

  const data = {
    sessionCode: 'LIVE_SESSION', // This would come from store
    question: props.question,
    responses: props.responses,
    statistics: {
      totalResponses: props.responses.length,
      authenticatedUsers: authenticatedCount.value,
      guestUsers: guestCount.value,
      createdAt: props.question?.createdAt ? new Date(props.question.createdAt).toISOString() : null,
      exportedAt: new Date().toISOString()
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
  a.download = `question_responses_${Date.now()}.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

function exportAsCSV(data) {
  const headers = ['Student Name', 'Student ID', 'User Type', 'Response', 'Submitted At']
  const rows = data.responses.map(response => [
    response.studentName,
    response.studentId,
    response.isAuthenticated ? 'Logged In' : 'Guest',
    response.answer?.text || '',
    response.submittedAt || new Date(response.timestamp).toISOString()
  ])

  const csvContent = [
    headers.join(','),
    ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
  ].join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)

  const a = document.createElement('a')
  a.href = url
  a.download = `question_responses_${Date.now()}.csv`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}
</script>

<style scoped>
.results-modal-overlay {
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

.results-modal {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  max-width: 700px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 18px;
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

.modal-content {
  flex: 1;
  overflow-y: auto;
  padding: 0;
}

.question-summary {
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.question-text {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 16px 0;
  line-height: 1.4;
}

.summary-stats {
  display: flex;
  gap: 24px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #10b981;
  line-height: 1;
}

.stat-label {
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
  margin-top: 4px;
}

.export-section {
  padding: 16px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.export-buttons {
  display: flex;
  gap: 12px;
}

.export-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.json-btn {
  background: #10b981;
  color: white;
}

.json-btn:hover:not(:disabled) {
  background: #059669;
}

.csv-btn {
  background: #3b82f6;
  color: white;
}

.csv-btn:hover:not(:disabled) {
  background: #2563eb;
}

.export-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.responses-section {
  padding: 20px 24px;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
}

.empty-state svg {
  margin-bottom: 16px;
}

.empty-text {
  font-size: 16px;
  font-weight: 500;
  color: #374151;
  margin: 0 0 8px 0;
}

.empty-subtext {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.responses-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.response-item {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
}

.response-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.student-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.student-name {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  display: flex;
  align-items: center;
  gap: 6px;
}

.auth-badge {
  color: #10b981;
  font-size: 12px;
}

.guest-badge {
  color: #6b7280;
  font-size: 12px;
}

.response-time {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
}

.response-content {
  background: white;
  border-radius: 6px;
  padding: 12px;
  border: 1px solid #e5e7eb;
}

.response-text {
  font-size: 14px;
  color: #374151;
  line-height: 1.5;
  margin: 0;
  white-space: pre-wrap;
}

/* Scoring Styles */
.scoring-toggle {
  padding: 16px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.toggle-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: #f3f4f6;
  border: 2px solid transparent;
  border-radius: 8px;
  color: #374151;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.toggle-btn:hover {
  background: #e5e7eb;
}

.toggle-btn.active {
  background: #10b981;
  color: white;
  border-color: #059669;
}

.rank-badge {
  position: absolute;
  top: -8px;
  left: -8px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 14px;
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.rank-1 {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
}

.rank-2 {
  background: linear-gradient(135deg, #d1d5db, #9ca3af);
}

.rank-3 {
  background: linear-gradient(135deg, #f87171, #dc2626);
}

.rank-number {
  font-size: 16px;
}

.rank-trophy {
  font-size: 12px;
  margin-left: 2px;
}

.score-input {
  display: flex;
  align-items: center;
  gap: 4px;
}

.score-field {
  width: 60px;
  padding: 6px 8px;
  border: 2px solid #e5e7eb;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  transition: border-color 0.2s;
}

.score-field:focus {
  outline: none;
  border-color: #10b981;
}

.score-display {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  background: #f0fdf4;
  border: 2px solid #10b981;
  border-radius: 6px;
}

.score-value {
  font-weight: 600;
  color: #059669;
  font-size: 14px;
}

.score-label {
  font-size: 12px;
  color: #6b7280;
}

.response-item.scoring-mode {
  padding-left: 50px;
}

.response-item.ranked {
  border-left: 4px solid #10b981;
  background: #f0fdf4;
}

@media (max-width: 640px) {
  .results-modal-overlay {
    padding: 10px;
  }

  .summary-stats {
    flex-direction: column;
    gap: 16px;
  }

  .export-buttons {
    flex-direction: column;
  }

  .response-header {
    flex-direction: column;
    gap: 8px;
  }

  .student-info {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}
</style>
