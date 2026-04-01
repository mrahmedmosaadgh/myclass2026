<template>
  <Transition name="overlay">
    <div v-if="isActive && !isEditMode" class="live-question-overlay">
      <div class="overlay-content">
        <div class="question-header">
          <div class="question-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h2.5a.5.5 0 0 0 .5-.5v-2A2.5 2.5 0 0 1 9.5 3h0A2.5 2.5 0 0 1 12 5.5v2a.5.5 0 0 0 .5.5H15a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2V8.4"></path>
              <path d="M7 15h.01"></path><path d="M11 15h.01"></path><path d="M15 15h.01"></path><path d="M19 15h.01"></path>
            </svg>
          </div>
          <div class="question-info">
            <div class="session-code-display">
              <span class="code-label">Code:</span>
              <span class="code-value">{{ sessionCode }}</span>
            </div>
            <div class="response-count">
              <span class="count-number">{{ responseCount }}</span>
              <span class="count-label">responses</span>
            </div>
          </div>
        </div>

        <div class="question-content">
          <h3 class="question-title">{{ currentQuestion?.title }}</h3>
          <p v-if="currentQuestion?.instructions" class="question-instructions">
            {{ currentQuestion.instructions }}
          </p>
        </div>

        <div v-if="timeRemaining > 0" class="timer-section">
          <div class="timer-display">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle><polyline points="12,6 12,12 16,14"></polyline>
            </svg>
            <span class="timer-text">{{ formattedTime }}</span>
          </div>
        </div>

        <div class="overlay-actions">
          <button @click="minimize" class="minimize-btn" title="Minimize">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="18,15 12,9 6,15"></polyline>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useLiveQuestionStore } from '../stores/liveQuestionStore'
import { useUIStore } from '../stores/uiStore'

const store = useLiveQuestionStore()
const ui = useUIStore()

// Component state
const isMinimized = ref(false)
const timeRemaining = ref(0)
const timerInterval = ref(null)

// Computed
const isActive = computed(() => store.isActive)
const isEditMode = computed(() => ui.isEditMode)
const sessionCode = computed(() => store.sessionCode)
const responseCount = computed(() => store.responseCount)
const currentQuestion = computed(() => store.currentQuestion)

const formattedTime = computed(() => {
  const minutes = Math.floor(timeRemaining.value / 60)
  const seconds = timeRemaining.value % 60
  return `${minutes}:${seconds.toString().padStart(2, '0')}`
})

// Methods
function minimize() {
  isMinimized.value = !isMinimized.value
}

// Timer management
function startTimer(duration) {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
  }

  timeRemaining.value = duration
  timerInterval.value = setInterval(() => {
    timeRemaining.value--
    if (timeRemaining.value <= 0) {
      clearInterval(timerInterval.value)
      timerInterval.value = null
    }
  }, 1000)
}

function stopTimer() {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
    timerInterval.value = null
  }
  timeRemaining.value = 0
}

// Watchers
watch(isActive, (active) => {
  if (active && currentQuestion.value?.timeLimit) {
    startTimer(currentQuestion.value.timeLimit)
  } else {
    stopTimer()
  }
})

watch(currentQuestion, (question) => {
  if (question?.timeLimit && isActive.value) {
    startTimer(question.timeLimit)
  }
})

// Lifecycle
onMounted(() => {
  // Initialize timer if question is already active
  if (isActive.value && currentQuestion.value?.timeLimit) {
    startTimer(currentQuestion.value.timeLimit)
  }
})

onUnmounted(() => {
  stopTimer()
})
</script>

<style scoped>
.live-question-overlay {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  max-width: 400px;
  width: 100%;
}

.overlay-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  border: 2px solid #10b981;
  overflow: hidden;
}

.question-header {
  background: #f0fdf4;
  padding: 16px;
  border-bottom: 1px solid #dcfce7;
  display: flex;
  align-items: center;
  gap: 12px;
}

.question-icon {
  flex-shrink: 0;
}

.question-info {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.session-code-display {
  display: flex;
  align-items: center;
  gap: 6px;
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
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid #10b981;
}

.response-count {
  display: flex;
  align-items: center;
  gap: 4px;
}

.count-number {
  font-size: 18px;
  font-weight: 700;
  color: #10b981;
}

.count-label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.question-content {
  padding: 16px;
}

.question-title {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.question-instructions {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
  line-height: 1.4;
}

.timer-section {
  padding: 12px 16px;
  background: #fef3c7;
  border-top: 1px solid #fde68a;
}

.timer-display {
  display: flex;
  align-items: center;
  gap: 6px;
  justify-content: center;
}

.timer-display svg {
  color: #f59e0b;
}

.timer-text {
  font-size: 16px;
  font-weight: 600;
  font-family: monospace;
  color: #92400e;
}

.overlay-actions {
  padding: 8px 16px;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
}

.minimize-btn {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.minimize-btn:hover {
  background: #e5e7eb;
  color: #374151;
}

/* Overlay animations */
.overlay-enter-active,
.overlay-leave-active {
  transition: all 0.3s ease;
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.overlay-enter-to,
.overlay-leave-from {
  opacity: 1;
  transform: translateX(0);
}

/* Responsive */
@media (max-width: 640px) {
  .live-question-overlay {
    top: 10px;
    right: 10px;
    left: 10px;
    max-width: none;
  }

  .question-header {
    padding: 12px;
  }

  .question-content {
    padding: 12px;
  }

  .question-info {
    flex-direction: column;
    gap: 8px;
    align-items: flex-start;
  }

  .response-count {
    align-self: flex-end;
  }
}
</style>
