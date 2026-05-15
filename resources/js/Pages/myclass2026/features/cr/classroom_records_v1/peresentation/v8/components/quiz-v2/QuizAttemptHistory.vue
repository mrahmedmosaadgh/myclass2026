<script setup>
import { ref, computed } from 'vue'
import { usePresentationStore } from '../../stores/presentationStore.js'
import { useQuasar } from 'quasar'

const props = defineProps({
  quizId: {
    type: String,
    required: true
  },
  quizTitle: {
    type: String,
    default: 'Quiz'
  }
})

const emit = defineEmits(['close'])

const $q = useQuasar()
const presentation = usePresentationStore()

const attempts = computed(() => presentation.getQuizAttempts(props.quizId))
const stats = computed(() => presentation.getQuizStatistics(props.quizId))

const selectedAttempt = ref(null)
const showDetails = ref(false)

function formatDate(isoString) {
  const date = new Date(isoString)
  return date.toLocaleString()
}

function formatDuration(seconds) {
  if (!seconds) return 'N/A'
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}m ${s}s`
}

function viewAttempt(attempt) {
  selectedAttempt.value = attempt
  showDetails.value = true
}

function deleteAttempt(attemptId) {
  $q.dialog({
    title: 'Delete Attempt',
    message: 'Are you sure you want to delete this attempt?',
    ok: {
      label: 'Delete',
      color: 'negative',
      flat: true
    },
    cancel: {
      label: 'Cancel',
      color: 'grey-7',
      flat: true
    },
    style: 'border-radius: 12px'
  }).onOk(() => {
    presentation.deleteQuizAttempt(attemptId)
    $q.notify({
      type: 'positive',
      message: 'Attempt deleted',
      position: 'top',
      timeout: 2000
    })
  })
}

function clearAllAttempts() {
  $q.dialog({
    title: 'Clear All Attempts',
    message: 'Are you sure you want to delete all attempts for this quiz?',
    ok: {
      label: 'Clear All',
      color: 'negative',
      flat: true
    },
    cancel: {
      label: 'Cancel',
      color: 'grey-7',
      flat: true
    },
    style: 'border-radius: 12px'
  }).onOk(() => {
    presentation.clearQuizAttempts(props.quizId)
    $q.notify({
      type: 'positive',
      message: 'All attempts cleared',
      position: 'top',
      timeout: 2000
    })
  })
}

function exportAttempts() {
  const data = {
    quizId: props.quizId,
    quizTitle: props.quizTitle,
    statistics: stats.value,
    attempts: attempts.value
  }
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `quiz-attempts-${props.quizId}-${new Date().toISOString().split('T')[0]}.json`
  a.click()
  URL.revokeObjectURL(url)
  
  $q.notify({
    type: 'positive',
    message: 'Attempts exported',
    position: 'top',
    timeout: 2000
  })
}

function close() {
  emit('close')
}
</script>

<template>
  <div class="attempt-history-overlay" @click.self="close">
    <div class="attempt-history-content">
      <!-- Header -->
      <div class="history-header">
        <h2 class="history-title">📊 Attempt History</h2>
        <button class="history-close" @click="close" title="Close">✕</button>
      </div>

      <!-- Statistics Summary -->
      <div v-if="stats.totalAttempts > 0" class="stats-summary">
        <div class="stat-card">
          <div class="stat-value">{{ stats.totalAttempts }}</div>
          <div class="stat-label">Total Attempts</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ stats.averageScore }}%</div>
          <div class="stat-label">Average Score</div>
        </div>
        <div class="stat-card stat-card-high">
          <div class="stat-value">{{ stats.highestScore }}%</div>
          <div class="stat-label">Highest Score</div>
        </div>
        <div class="stat-card stat-card-low">
          <div class="stat-value">{{ stats.lowestScore }}%</div>
          <div class="stat-label">Lowest Score</div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="attempts.length === 0" class="empty-state">
        <div class="empty-icon">📝</div>
        <div class="empty-text">No attempts yet</div>
        <div class="empty-hint">Complete the quiz to see your attempt history</div>
      </div>

      <!-- Attempts List -->
      <div v-else class="attempts-list">
        <div class="list-header">
          <span class="list-count">{{ attempts.length }} attempts</span>
          <button class="list-action" @click="exportAttempts" title="Export attempts">
            📥 Export
          </button>
        </div>

        <div class="attempts-table">
          <div class="table-header">
            <div class="th th-date">Date</div>
            <div class="th th-score">Score</div>
            <div class="th th-correct">Correct</div>
            <div class="th th-duration">Duration</div>
            <div class="th th-actions">Actions</div>
          </div>

          <div
            v-for="attempt in attempts"
            :key="attempt.id"
            class="table-row"
          >
            <div class="td td-date">{{ formatDate(attempt.timestamp) }}</div>
            <div class="td td-score">
              <span class="score-badge" :class="{
                'score-high': attempt.score >= 80,
                'score-medium': attempt.score >= 60 && attempt.score < 80,
                'score-low': attempt.score < 60
              }">
                {{ attempt.score }}%
              </span>
            </div>
            <div class="td td-correct">
              {{ attempt.correctCount }}/{{ attempt.totalQuestions }}
            </div>
            <div class="td td-duration">{{ formatDuration(attempt.duration) }}</div>
            <div class="td td-actions">
              <button class="action-btn" @click="viewAttempt(attempt)" title="View details">
                👁️
              </button>
              <button class="action-btn" @click="deleteAttempt(attempt.id)" title="Delete">
                🗑️
              </button>
            </div>
          </div>
        </div>

        <button v-if="attempts.length > 0" class="clear-btn" @click="clearAllAttempts">
          🗑️ Clear All Attempts
        </button>
      </div>

      <!-- Attempt Details Modal -->
      <div v-if="showDetails && selectedAttempt" class="details-modal">
        <div class="details-content">
          <div class="details-header">
            <h3 class="details-title">Attempt Details</h3>
            <button class="details-close" @click="showDetails = false">✕</button>
          </div>
          <div class="details-body">
            <div class="detail-row">
              <span class="detail-label">Date:</span>
              <span class="detail-value">{{ formatDate(selectedAttempt.timestamp) }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Score:</span>
              <span class="detail-value">{{ selectedAttempt.score }}%</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Correct:</span>
              <span class="detail-value">{{ selectedAttempt.correctCount }}/{{ selectedAttempt.totalQuestions }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Duration:</span>
              <span class="detail-value">{{ formatDuration(selectedAttempt.duration) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.attempt-history-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.attempt-history-content {
  background: #1e1e1e;
  border-radius: 12px;
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid #333;
}

.history-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.history-title {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #f0f0f0;
}

.history-close {
  background: transparent;
  border: none;
  color: #888;
  font-size: 18px;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.history-close:hover {
  background: #333;
  color: #f0f0f0;
}

.stats-summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.stat-card {
  background: #252525;
  border: 1px solid #383838;
  border-radius: 8px;
  padding: 12px;
  text-align: center;
}

.stat-card-high {
  border-color: #48bb78;
  background: rgba(72, 187, 120, 0.1);
}

.stat-card-low {
  border-color: #f56565;
  background: rgba(245, 101, 101, 0.1);
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
  color: #63b3ed;
  line-height: 1;
}

.stat-card-high .stat-value {
  color: #48bb78;
}

.stat-card-low .stat-value {
  color: #f56565;
}

.stat-label {
  font-size: 12px;
  color: #888;
  margin-top: 4px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  gap: 12px;
}

.empty-icon {
  font-size: 48px;
  opacity: 0.5;
}

.empty-text {
  font-size: 16px;
  color: #888;
}

.empty-hint {
  font-size: 14px;
  color: #666;
}

.attempts-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.list-count {
  font-size: 14px;
  color: #888;
}

.list-action {
  background: transparent;
  border: 1px solid #444;
  color: #ccc;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.list-action:hover {
  background: #333;
  border-color: #505050;
}

.attempts-table {
  flex: 1;
  overflow-y: auto;
  padding: 0;
}

.table-header {
  display: grid;
  grid-template-columns: 1fr 80px 80px 80px 100px;
  gap: 8px;
  padding: 12px 20px;
  background: #252525;
  border-bottom: 1px solid #333;
  position: sticky;
  top: 0;
}

.th {
  font-size: 12px;
  font-weight: 600;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.table-row {
  display: grid;
  grid-template-columns: 1fr 80px 80px 80px 100px;
  gap: 8px;
  padding: 12px 20px;
  border-bottom: 1px solid #2d2d2d;
  transition: background 0.2s;
}

.table-row:hover {
  background: #252525;
}

.td {
  font-size: 14px;
  color: #e0e0e0;
  display: flex;
  align-items: center;
}

.td-date {
  color: #888;
}

.score-badge {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 700;
}

.score-high {
  background: rgba(72, 187, 120, 0.15);
  color: #48bb78;
}

.score-medium {
  background: rgba(251, 191, 36, 0.15);
  color: #fbbf24;
}

.score-low {
  background: rgba(245, 101, 101, 0.15);
  color: #f56565;
}

.td-actions {
  gap: 8px;
}

.action-btn {
  background: transparent;
  border: none;
  font-size: 14px;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #333;
}

.clear-btn {
  background: transparent;
  border: 1px solid #f56565;
  color: #f56565;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  margin: 12px 20px;
}

.clear-btn:hover {
  background: rgba(245, 101, 101, 0.1);
}

.details-modal {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.details-content {
  background: #1e1e1e;
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  border: 1px solid #333;
}

.details-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #333;
}

.details-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #f0f0f0;
}

.details-close {
  background: transparent;
  border: none;
  color: #888;
  font-size: 16px;
  cursor: pointer;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.details-close:hover {
  background: #333;
  color: #f0f0f0;
}

.details-body {
  padding: 16px 20px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #2d2d2d;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-size: 14px;
  color: #888;
}

.detail-value {
  font-size: 14px;
  color: #e0e0e0;
  font-weight: 600;
}

@media (max-width: 640px) {
  .stats-summary {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .table-header,
  .table-row {
    grid-template-columns: 1fr 60px 60px 70px;
  }
  
  .th-actions,
  .td-actions {
    display: none;
  }
}
</style>
