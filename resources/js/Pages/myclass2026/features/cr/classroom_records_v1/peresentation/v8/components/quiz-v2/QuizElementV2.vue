<script setup>
import { ref, computed, watch } from 'vue'
import { usePresentationStore } from '../../stores/presentationStore.js'
import { useMathRenderer } from '../../composables/useMathRenderer.js'

const props = defineProps({
  element: { type: Object, required: true },
  isPresentMode: { type: Boolean, default: false }
})

const presentation = usePresentationStore()
const { renderMath } = useMathRenderer()

// ── Reactive element data ──────────────────────────────
const quiz = computed(() => props.element)
const questions = computed(() => quiz.value.questions || [])
const settings = computed(() => quiz.value.settings || {})
const currentIndex = computed({
  get: () => quiz.value.currentQuestionIndex || 0,
  set: (val) => updateQuiz({ currentQuestionIndex: val })
})
const userAnswers = computed(() => quiz.value.userAnswers || {})
const showResults = computed({
  get: () => quiz.value.showResults || false,
  set: (val) => updateQuiz({ showResults: val })
})

// ── Local state ─────────────────────────────────────────
const selectedOption = ref(null)
const showExplanation = ref(false)
const timerRemaining = ref(0)
const timerInterval = ref(null)

// ── Computed ────────────────────────────────────────────
const currentQuestion = computed(() => questions.value[currentIndex.value] || null)
const totalQuestions = computed(() => questions.value.length)
const isFirstQuestion = computed(() => currentIndex.value === 0)
const isLastQuestion = computed(() => currentIndex.value === totalQuestions.value - 1)

// v8 compatibility: normalize old format questions on-the-fly
const currentQuestionData = computed(() => {
  const q = currentQuestion.value
  if (!q) return null
  // v8 format
  if (q.content) {
    return {
      id: q.id,
      prompt: q.content.prompt || '',
      options: q.content.options || [],
      explanation: q.content.explanation || '',
      // Find correct option id
      correctId: (q.content.options || []).find(opt => opt.is_correct)?.id || null,
    }
  }
  // Legacy format fallback
  return {
    id: q.id,
    prompt: q.question || '',
    options: q.options || [],
    explanation: q.explanation || '',
    correctId: q.correctId || null,
  }
})

const currentAnswer = computed(() => {
  if (!currentQuestion.value) return null
  return userAnswers.value[currentQuestion.value.id]
})

const progressPercent = computed(() => {
  if (totalQuestions.value === 0) return 0
  return ((currentIndex.value + (currentAnswer.value ? 1 : 0)) / totalQuestions.value) * 100
})

const score = computed(() => {
  let points = 0
  const correctPts = settings.value.pointsPerCorrect || 10
  const penalty = settings.value.penaltyPerWrong || 0
  questions.value.forEach(q => {
    const ans = userAnswers.value[q.id]
    // v8: find correct option by is_correct flag
    const correctId = q.content
      ? (q.content.options || []).find(opt => opt.is_correct)?.id
      : q.correctId
    if (ans === correctId) {
      points += correctPts
    } else if (ans && penalty) {
      points -= penalty
    }
  })
  return Math.max(0, points)
})

function getQuestionCorrectId(q) {
  if (!q) return null
  return q.content
    ? (q.content.options || []).find(opt => opt.is_correct)?.id
    : q.correctId
}

const correctCount = computed(() => {
  return questions.value.filter(q => userAnswers.value[q.id] === getQuestionCorrectId(q)).length
})

const wrongCount = computed(() => {
  return questions.value.filter(q => {
    const ans = userAnswers.value[q.id]
    return ans && ans !== getQuestionCorrectId(q)
  }).length
})

const hasAnsweredCurrent = computed(() => {
  return !!currentAnswer.value
})

const allAnswered = computed(() => {
  return questions.value.every(q => !!userAnswers.value[q.id])
})

const isInteractive = computed(() => props.isPresentMode)

// ── Watchers ────────────────────────────────────────────
watch(currentIndex, () => {
  selectedOption.value = null
  showExplanation.value = false
  if (timerInterval.value) clearInterval(timerInterval.value)
  startTimerIfNeeded()
}, { immediate: true })

watch(() => currentAnswer.value, (ans) => {
  if (ans) selectedOption.value = ans
}, { immediate: true })

// ── Methods ─────────────────────────────────────────────
function updateQuiz(changes) {
  presentation.updateElement({
    id: props.element.id,
    changes
  })
}

function selectOption(optId) {
  if (!isInteractive.value) return
  if (!currentQuestion.value) return

  selectedOption.value = optId
  showExplanation.value = true

  updateQuiz({
    userAnswers: { ...userAnswers.value, [currentQuestion.value.id]: optId }
  })

  // Auto-advance after delay (configurable)
  if (settings.value.autoAdvance !== false) {
    setTimeout(() => {
      if (!isLastQuestion.value) {
        nextQuestion()
      } else {
        showResults.value = true
      }
    }, settings.value.autoAdvanceDelay || 1200)
  }
}

function nextQuestion() {
  if (!isLastQuestion.value) {
    currentIndex.value = currentIndex.value + 1
  }
}

function prevQuestion() {
  if (!isFirstQuestion.value) {
    currentIndex.value = currentIndex.value - 1
  }
}

function goToQuestion(idx) {
  currentIndex.value = idx
}

function restartQuiz() {
  updateQuiz({
    currentQuestionIndex: 0,
    userAnswers: {},
    showResults: false
  })
  selectedOption.value = null
  showExplanation.value = false
}

function startTimerIfNeeded() {
  if (!settings.value.timerEnabled || !settings.value.timerSeconds) return
  timerRemaining.value = settings.value.timerSeconds
  timerInterval.value = setInterval(() => {
    timerRemaining.value--
    if (timerRemaining.value <= 0) {
      clearInterval(timerInterval.value)
      if (!currentAnswer.value) {
        // Time up: mark as unanswered
        selectOption('__timeout__')
      }
    }
  }, 1000)
}

function getOptionClass(optId) {
  if (!isInteractive.value) return ''
  const q = currentQuestionData.value
  if (!q) return ''
  const ans = userAnswers.value[currentQuestion.value?.id]

  if (ans) {
    if (optId === q.correctId) return 'opt-correct'
    if (optId === ans) return 'opt-wrong'
    return 'opt-dimmed'
  }
  if (selectedOption.value === optId) return 'opt-selected'
  return ''
}

function getQuestionStatusClass(idx) {
  const q = questions.value[idx]
  if (!q) return ''
  const ans = userAnswers.value[q.id]
  const correctId = getQuestionCorrectId(q)
  if (ans === correctId) return 'status-correct'
  if (ans && ans !== correctId) return 'status-wrong'
  if (idx === currentIndex.value) return 'status-current'
  return ''
}

function formatTime(seconds) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${s.toString().padStart(2, '0')}`
}

function getResultClass(q) {
  const ans = userAnswers.value[q.id]
  const correctId = getQuestionCorrectId(q)
  if (ans === correctId) return 'result-correct'
  return 'result-wrong'
}
</script>

<template>
  <div class="quiz-v2" :class="{ 'present-mode': isPresentMode }">
    <!-- HEADER: Title + Score Badges -->
    <div class="qv2-header">
      <div class="qv2-header-left">
        <span class="qv2-icon">📝</span>
        <span class="qv2-title">{{ quiz.title || 'Untitled Quiz' }}</span>
      </div>
      <div v-if="isPresentMode" class="qv2-header-right">
        <span class="qv2-badge qv2-badge-wrong">
          <span class="qv2-badge-icon">✗</span>
          {{ wrongCount }}
        </span>
        <span class="qv2-badge qv2-badge-correct">
          <span class="qv2-badge-icon">✓</span>
          {{ correctCount }}
        </span>
      </div>
    </div>

    <!-- PROGRESS BAR -->
    <div class="qv2-progress-wrap">
      <div class="qv2-progress-bar">
        <div
          class="qv2-progress-fill"
          :style="{ width: progressPercent + '%' }"
        />
      </div>
      <span class="qv2-progress-text">
        {{ currentIndex + 1 }}/{{ totalQuestions }}
      </span>
    </div>

    <!-- TIMER (if enabled) -->
    <div v-if="settings.timerEnabled && isPresentMode" class="qv2-timer">
      ⏱️ {{ formatTime(timerRemaining) }}
    </div>

    <!-- RESULTS VIEW -->
    <div v-if="showResults && isPresentMode" class="qv2-results">
      <div class="qv2-result-summary">
        <div class="qv2-result-score">{{ correctCount }}/{{ totalQuestions }}</div>
        <div class="qv2-result-label">
          {{ score }} points
          <span v-if="settings.pointsPerCorrect"> ({{ correctCount }} × {{ settings.pointsPerCorrect }}pts)</span>
        </div>
        <div class="qv2-result-percent">{{ Math.round((correctCount / totalQuestions) * 100) }}%</div>
      </div>

      <div class="qv2-result-breakdown">
        <div
          v-for="(q, idx) in questions"
          :key="q.id"
          class="qv2-result-item"
          :class="getResultClass(q)"
        >
          <div class="qv2-result-num">{{ idx + 1 }}</div>
          <div class="qv2-result-text" v-html="renderMath(q.content?.prompt || q.question || '')" />
          <div class="qv2-result-answer">
            <span v-if="userAnswers[q.id] === getQuestionCorrectId(q)" class="qv2-result-check">✓</span>
            <span v-else class="qv2-result-x">✗</span>
          </div>
        </div>
      </div>

      <button class="qv2-restart-btn" @click="restartQuiz">
        ↻ Restart Quiz
      </button>
    </div>

    <!-- QUESTION VIEW -->
    <div v-else class="qv2-body">
      <!-- Question Palette (present mode) -->
      <div v-if="isPresentMode" class="qv2-palette">
        <button
          v-for="(_, idx) in questions"
          :key="idx"
          class="qv2-palette-dot"
          :class="getQuestionStatusClass(idx)"
          @click="goToQuestion(idx)"
        >
          {{ idx + 1 }}
        </button>
      </div>

      <!-- Question Text -->
      <div class="qv2-question-wrap">
        <span class="qv2-question-num">{{ currentIndex + 1 }}.</span>
        <span
          class="qv2-question-text"
          v-html="renderMath(currentQuestionData?.prompt || 'Question...')"
        />
      </div>

      <!-- Options -->
      <div class="qv2-options">
        <button
          v-for="opt in currentQuestionData?.options || []"
          :key="opt.id"
          class="qv2-option"
          :class="getOptionClass(opt.id)"
          :disabled="!isInteractive || !!currentAnswer"
          @click="selectOption(opt.id)"
        >
          <span class="qv2-opt-label">{{ String(opt.id).toUpperCase() }}</span>
          <span class="qv2-opt-text" v-html="renderMath(opt.text)" />
          <span v-if="currentAnswer && opt.id === currentQuestionData?.correctId" class="qv2-opt-check">✓</span>
        </button>
      </div>

      <!-- Explanation -->
      <div
        v-if="showExplanation && currentQuestionData?.explanation && settings.showExplanation"
        class="qv2-explanation"
      >
        💡 {{ currentQuestionData.explanation }}
      </div>

      <!-- Navigation -->
      <div class="qv2-nav">
        <button
          v-if="!isFirstQuestion"
          class="qv2-nav-btn qv2-nav-prev"
          @click="prevQuestion"
        >
          ← Prev
        </button>
        <button
          v-if="isLastQuestion && hasAnsweredCurrent"
          class="qv2-nav-btn qv2-nav-next qv2-nav-finish"
          @click="showResults = true"
        >
          Finish →
        </button>
        <button
          v-else-if="!isLastQuestion && hasAnsweredCurrent"
          class="qv2-nav-btn qv2-nav-next"
          @click="nextQuestion"
        >
          Next →
        </button>
        <span v-else class="qv2-nav-placeholder" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.quiz-v2 {
  width: 100%;
  height: 100%;
  background: #1a1a1a;
  color: #ffffff;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
  box-sizing: border-box;
}

/* ── Header ─────────────────────────────────────────── */
.qv2-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  border-bottom: 1px solid #2d2d2d;
  flex-shrink: 0;
}

.qv2-header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 600;
  color: #e0e0e0;
}

.qv2-icon {
  font-size: 16px;
}

.qv2-header-right {
  display: flex;
  gap: 8px;
}

.qv2-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
}

.qv2-badge-correct {
  background: rgba(72, 187, 120, 0.15);
  color: #48bb78;
}

.qv2-badge-wrong {
  background: rgba(245, 101, 101, 0.15);
  color: #f56565;
}

.qv2-badge-icon {
  font-size: 11px;
}

/* ── Progress Bar ───────────────────────────────────── */
.qv2-progress-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 20px;
  flex-shrink: 0;
}

.qv2-progress-bar {
  flex: 1;
  height: 4px;
  background: #2d2d2d;
  border-radius: 2px;
  overflow: hidden;
}

.qv2-progress-fill {
  height: 100%;
  background: #63b3ed;
  border-radius: 2px;
  transition: width 0.4s ease;
}

.qv2-progress-text {
  font-size: 13px;
  color: #888;
  font-weight: 500;
  white-space: nowrap;
}

/* ── Timer ───────────────────────────────────────────── */
.qv2-timer {
  text-align: center;
  font-size: 14px;
  color: #fbbf24;
  padding: 4px 0;
  flex-shrink: 0;
}

/* ── Body ───────────────────────────────────────────── */
.qv2-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 16px 24px 20px;
  overflow-y: auto;
}

/* Question Palette */
.qv2-palette {
  display: flex;
  gap: 6px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.qv2-palette-dot {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid #404040;
  background: #2d2d2d;
  color: #888;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.qv2-palette-dot:hover {
  background: #3a3a3a;
}

.qv2-palette-dot.status-current {
  border-color: #63b3ed;
  color: #63b3ed;
  background: rgba(99, 179, 237, 0.1);
}

.qv2-palette-dot.status-correct {
  background: rgba(72, 187, 120, 0.2);
  border-color: #48bb78;
  color: #48bb78;
}

.qv2-palette-dot.status-wrong {
  background: rgba(245, 101, 101, 0.2);
  border-color: #f56565;
  color: #f56565;
}

/* Question */
.qv2-question-wrap {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 20px;
}

.qv2-question-num {
  font-size: 22px;
  font-weight: 700;
  color: #63b3ed;
  line-height: 1.3;
  flex-shrink: 0;
}

.qv2-question-text {
  font-size: 18px;
  line-height: 1.5;
  color: #f0f0f0;
  word-break: break-word;
}

.qv2-question-text :deep(sup) {
  font-size: 0.75em;
  vertical-align: super;
}

.qv2-question-text :deep(sub) {
  font-size: 0.75em;
  vertical-align: sub;
}

/* Options */
.qv2-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
}

.qv2-option {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  background: #2d2d2d;
  border: 1px solid #404040;
  border-radius: 10px;
  color: #e0e0e0;
  font-size: 15px;
  text-align: left;
  cursor: pointer;
  transition: all 0.15s;
  position: relative;
}

.qv2-option:hover:not(:disabled) {
  background: #3a3a3a;
  border-color: #505050;
}

.qv2-option:disabled {
  cursor: default;
}

.qv2-option.opt-selected {
  border-color: #63b3ed;
  background: rgba(99, 179, 237, 0.1);
}

.qv2-option.opt-correct {
  border-color: #48bb78;
  background: rgba(72, 187, 120, 0.15);
}

.qv2-option.opt-wrong {
  border-color: #f56565;
  background: rgba(245, 101, 101, 0.15);
}

.qv2-option.opt-dimmed {
  opacity: 0.4;
}

.qv2-opt-label {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #404040;
  border-radius: 50%;
  font-size: 13px;
  font-weight: 700;
  color: #b0b0b0;
}

.qv2-option.opt-correct .qv2-opt-label,
.qv2-option.opt-wrong .qv2-opt-label {
  background: transparent;
}

.qv2-opt-text {
  flex: 1;
  line-height: 1.4;
}

.qv2-opt-text :deep(sup) {
  font-size: 0.75em;
}

.qv2-opt-text :deep(sub) {
  font-size: 0.75em;
}

.qv2-opt-check {
  font-size: 18px;
  color: #48bb78;
  font-weight: 700;
}

/* Explanation */
.qv2-explanation {
  padding: 12px 16px;
  background: rgba(251, 191, 36, 0.1);
  border: 1px solid rgba(251, 191, 36, 0.3);
  border-radius: 8px;
  color: #fbbf24;
  font-size: 14px;
  margin-bottom: 16px;
}

/* Navigation */
.qv2-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 8px;
}

.qv2-nav-btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.qv2-nav-prev {
  background: #2d2d2d;
  color: #b0b0b0;
  border: 1px solid #404040;
}

.qv2-nav-prev:hover {
  background: #3a3a3a;
  color: #e0e0e0;
}

.qv2-nav-next {
  background: #63b3ed;
  color: #1a1a1a;
}

.qv2-nav-next:hover {
  background: #4fa3e0;
}

.qv2-nav-next:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.qv2-nav-finish {
  background: #48bb78;
}

.qv2-nav-finish:hover {
  background: #38a169;
}

.qv2-nav-placeholder {
  visibility: hidden;
}

/* ── Results View ────────────────────────────────────── */
.qv2-results {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24px;
  overflow-y: auto;
}

.qv2-result-summary {
  text-align: center;
  margin-bottom: 24px;
}

.qv2-result-score {
  font-size: 48px;
  font-weight: 800;
  color: #63b3ed;
  line-height: 1;
}

.qv2-result-label {
  font-size: 14px;
  color: #888;
  margin-top: 8px;
}

.qv2-result-percent {
  font-size: 20px;
  font-weight: 700;
  color: #f0f0f0;
  margin-top: 4px;
}

.qv2-result-breakdown {
  width: 100%;
  max-width: 480px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.qv2-result-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 8px;
  background: #2d2d2d;
  border: 1px solid #404040;
}

.qv2-result-item.result-correct {
  border-color: #48bb78;
  background: rgba(72, 187, 120, 0.1);
}

.qv2-result-item.result-wrong {
  border-color: #f56565;
  background: rgba(245, 101, 101, 0.1);
}

.qv2-result-num {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #404040;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
  color: #b0b0b0;
  flex-shrink: 0;
}

.qv2-result-text {
  flex: 1;
  font-size: 14px;
  color: #e0e0e0;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.qv2-result-answer {
  flex-shrink: 0;
  font-size: 16px;
}

.qv2-result-check {
  color: #48bb78;
}

.qv2-result-x {
  color: #f56565;
}

.qv2-restart-btn {
  margin-top: 20px;
  padding: 12px 28px;
  border-radius: 8px;
  background: #63b3ed;
  color: #1a1a1a;
  font-size: 14px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}

.qv2-restart-btn:hover {
  background: #4fa3e0;
}

/* Edit mode preview */
.quiz-v2:not(.present-mode) .qv2-option {
  cursor: default;
  opacity: 0.7;
}

.quiz-v2:not(.present-mode) .qv2-palette-dot {
  cursor: default;
}

.quiz-v2:not(.present-mode) .qv2-nav-btn {
  opacity: 0.5;
  cursor: default;
}

/* Math rendering */
.qv2-question-text :deep(sup),
.qv2-opt-text :deep(sup) {
  font-size: 0.7em;
  vertical-align: super;
  line-height: 0;
}

.qv2-question-text :deep(sub),
.qv2-opt-text :deep(sub) {
  font-size: 0.7em;
  vertical-align: sub;
  line-height: 0;
}

.qv2-result-text :deep(sup),
.qv2-result-text :deep(sub) {
  font-size: 0.7em;
}
</style>
