<script setup>
import { onMounted, onUnmounted, computed, ref } from 'vue'
import { useQuasar } from 'quasar'
import { usePresentationStore } from './stores/presentationStore.js'
import { useUIStore } from './stores/uiStore.js'
import { usePresentationAPI } from './composables/usePresentationAPI.js'
import SlideCanvasReadonly from './components/SlideCanvasReadonly.vue'
import StudentQuizView from './components/StudentQuizView.vue'

const props = defineProps({
  shareToken: String // Inertia prop from web route
})

const $q = useQuasar()
const presentation = usePresentationStore()
const ui = useUIStore()
const { loadSharedPresentation, submitStudentAttempt } = usePresentationAPI()

const currentIndex = computed(() => presentation.currentSlideIndex)
const totalSlides = computed(() => presentation.totalSlides)

// Student identifier for tracking
const studentIdentifier = ref('')
const showIdentifierDialog = ref(false)
const hasPromptedIdentifier = ref(false)
const showMenu = ref(false)

// Check if current slide has a quiz element (to disable arrow key navigation)
const hasQuizOnCurrentSlide = computed(() => {
  const currentSlide = presentation.currentSlide
  if (!currentSlide || !currentSlide.elements) return false
  return currentSlide.elements.some(el => el.type === 'quiz-v2')
})

// Smart mode: detect if current slide has quiz-v2 element
const quizElement = computed(() => {
  const currentSlide = presentation.currentSlide
  if (!currentSlide || !currentSlide.elements) return null
  return currentSlide.elements.find(el => el.type === 'quiz-v2') || null
})

const slideHasQuiz = computed(() => quizElement.value !== null)

// Progress percentage
const progressPercentage = computed(() => {
  if (totalSlides.value === 0) return 0
  return Math.round(((currentIndex.value + 1) / totalSlides.value) * 100)
})

// Track quiz attempts for submission
const quizAttempts = ref([])
const totalScore = ref(0)
const totalQuestions = ref(0)

// Load presentation from API using share token
async function loadPresentationFromToken() {
  const shareToken = props.shareToken

  if (!shareToken) {
    $q.notify({
      type: 'negative',
      message: 'Invalid presentation link',
      position: 'top',
      timeout: 3000
    })
    return
  }

  try {
    const data = await loadSharedPresentation(shareToken)
    presentation.loadPresentationData(data.presentation_data)
    showIdentifierDialog.value = true
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load presentation',
      position: 'top',
      timeout: 3000
    })
  }
}

function handleIdentifierSubmit() {
  if (!studentIdentifier.value.trim()) {
    $q.notify({
      type: 'warning',
      message: 'Please enter your name or email',
      position: 'top',
      timeout: 3000
    })
    return
  }
  hasPromptedIdentifier.value = true
  showIdentifierDialog.value = false
}

// Collect quiz attempts as students complete them
function collectQuizAttempts() {
  const attempts = []
  presentation.slides.forEach(slide => {
    if (!slide.elements) return
    slide.elements.forEach(el => {
      if (el.type === 'quiz-v2' && el.userAnswers) {
        attempts.push({
          quizId: el.id,
          quizTitle: el.title || 'Quiz',
          userAnswers: el.userAnswers,
          currentQuestionIndex: el.currentQuestionIndex,
          showResults: el.showResults,
        })
      }
    })
  })
  return attempts
}

// Submit all attempts to API
async function submitAttempts() {
  const shareToken = props.shareToken

  const attempts = collectQuizAttempts()
  
  // Calculate total score
  let score = 0
  let questions = 0
  attempts.forEach(attempt => {
    if (attempt.userAnswers) {
      Object.values(attempt.userAnswers).forEach(answer => {
        questions++
        // Simple scoring: 1 point per correct answer
        // This would need to be enhanced based on quiz settings
      })
    }
  })

  try {
    await submitStudentAttempt(
      shareToken,
      studentIdentifier.value,
      attempts,
      score,
      questions
    )
    $q.notify({
      type: 'positive',
      message: 'Your results have been saved',
      position: 'top',
      timeout: 3000
    })
  } catch (err) {
    $q.notify({
      type: 'warning',
      message: 'Failed to save results (you can still continue)',
      position: 'top',
      timeout: 3000
    })
  }
}

// Student mode - always in present mode, no editing
onMounted(() => {
  ui.isEditMode = false
  ui.isPresentMode = true
  loadPresentationFromToken()
})

function goPrev() {
  if (currentIndex.value > 0) {
    presentation.selectSlide(currentIndex.value - 1)
  }
}

function goNext() {
  if (currentIndex.value < totalSlides.value - 1) {
    presentation.selectSlide(currentIndex.value + 1)
  }
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {})
  } else {
    document.exitFullscreen().catch(() => {})
  }
}

function sharePresentation() {
  const url = window.location.href
  navigator.clipboard.writeText(url).then(() => {
    $q.notify({
      type: 'positive',
      message: 'Presentation URL copied to clipboard',
      position: 'top',
      timeout: 3000
    })
  }).catch(() => {
    $q.notify({
      type: 'warning',
      message: 'Failed to copy URL',
      position: 'top',
      timeout: 3000
    })
  })
}

// Keyboard shortcuts for navigation only
function handleKeydown(e) {
  if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return

  const key = e.key.toLowerCase()

  // If current slide has a quiz, don't use arrow keys for slide navigation
  // (they're used for quiz option navigation)
  const hasQuiz = hasQuizOnCurrentSlide.value

  // Navigation shortcuts
  if (key === 'arrowright' || key === ' ') {
    // Space is used by quiz for "next" after answering, only use it for slide nav if no quiz
    if (key !== ' ' || !hasQuiz) {
      goNext()
      e.preventDefault()
    }
  } else if (key === 'arrowdown') {
    // Arrow down is used by quiz for option navigation, only use it for slide nav if no quiz
    if (!hasQuiz) {
      goNext()
      e.preventDefault()
    }
  } else if (key === 'arrowleft') {
    // Arrow left is used by quiz for option navigation, only use it for slide nav if no quiz
    if (!hasQuiz) {
      goPrev()
      e.preventDefault()
    }
  } else if (key === 'arrowup') {
    // Arrow up is used by quiz for option navigation, only use it for slide nav if no quiz
    if (!hasQuiz) {
      goPrev()
      e.preventDefault()
    }
  } else if (key === 'home') {
    presentation.selectSlide(0)
    e.preventDefault()
  } else if (key === 'end') {
    presentation.selectSlide(totalSlides.value - 1)
    e.preventDefault()
  } else if (key === 'f') {
    toggleFullscreen()
    e.preventDefault()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div class="student-presentation">
    <!-- Header -->
    <div class="sp-header">
      <div class="sp-header-top">
        <div class="sp-header-left">
          <h1 class="sp-title">Student Presentation</h1>
          <span class="sp-slide-count">
            Slide {{ currentIndex + 1 }} of {{ totalSlides }}
          </span>
        </div>
        <div class="sp-header-right">
          <button class="sp-menu-btn" @click="showMenu = !showMenu" title="Menu">
            <span v-if="!showMenu">⋮</span>
            <span v-else>✕</span>
          </button>
        </div>
      </div>
      <!-- Progress Bar -->
      <div class="sp-progress-bar">
        <div class="sp-progress-fill" :style="{ width: progressPercentage + '%' }" />
        <span class="sp-progress-text">{{ progressPercentage }}%</span>
      </div>
    </div>

    <!-- Overflow Menu -->
    <q-menu v-model="showMenu" anchor="top right" self="top right">
      <q-list style="min-width: 200px">
        <q-item clickable @click="submitAttempts" v-close-popup>
          <q-item-section avatar>
            <q-icon name="check" />
          </q-item-section>
          <q-item-section>Submit Results</q-item-section>
        </q-item>
        <q-item clickable @click="sharePresentation" v-close-popup>
          <q-item-section avatar>
            <q-icon name="share" />
          </q-item-section>
          <q-item-section>Share</q-item-section>
        </q-item>
        <q-item clickable @click="toggleFullscreen" v-close-popup>
          <q-item-section avatar>
            <q-icon name="fullscreen" />
          </q-item-section>
          <q-item-section>Fullscreen</q-item-section>
        </q-item>
      </q-list>
    </q-menu>

    <!-- Student Identifier Dialog -->
    <q-dialog
      v-model="showIdentifierDialog"
      persistent
      maximized
      transition-show="slide-up"
      transition-hide="slide-down"
    >
      <q-card class="identifier-dialog">
        <q-card-section class="dialog-content">
          <div class="dialog-icon">🎓</div>
          <h2 class="dialog-title">Welcome!</h2>
          <p class="dialog-subtitle">What's your name so your teacher can track your progress?</p>

          <q-input
            v-model="studentIdentifier"
            label="Your name"
            outlined
            size="lg"
            class="dialog-input"
            autofocus
            @keyup.enter="handleIdentifierSubmit"
          >
            <template v-slot:prepend>
              <q-icon name="person" />
            </template>
          </q-input>

          <q-btn
            unelevated
            label="Let's Start →"
            color="primary"
            size="lg"
            class="dialog-button"
            @click="handleIdentifierSubmit"
          />
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Main Content -->
    <div class="sp-main">
      <!-- Quiz Mode: Render quiz natively at full width -->
      <div v-if="slideHasQuiz" class="sp-quiz-container">
        <StudentQuizView :element="quizElement" />
      </div>

      <!-- Canvas Mode: Render slide canvas (for non-quiz slides) -->
      <div v-else class="sp-canvas-container">
        <SlideCanvasReadonly
          :slide="presentation.currentSlide"
          :is-present-mode="true"
          :zoom-level="ui.zoomLevel"
        />
      </div>

      <!-- Bottom Navigation -->
      <div class="sp-bottom-nav">
        <button
          class="sp-nav-btn sp-nav-prev"
          :disabled="currentIndex === 0"
          @click="goPrev"
          title="Previous slide"
        >
          ← Previous
        </button>

        <div class="sp-slide-indicator">
          <span class="sp-current">{{ currentIndex + 1 }}</span>
          <span class="sp-divider">/</span>
          <span class="sp-total">{{ totalSlides }}</span>
        </div>

        <button
          class="sp-nav-btn sp-nav-next"
          :disabled="currentIndex === totalSlides - 1"
          @click="goNext"
          title="Next slide"
        >
          Next →
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.student-presentation {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #f8fafc;
  color: #1e293b;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
}

.sp-header {
  flex-shrink: 0;
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.sp-header-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
}

.sp-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sp-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
}

.sp-slide-count {
  font-size: 13px;
  color: #64748b;
}

.sp-header-right {
  display: flex;
  gap: 8px;
}

.sp-menu-btn {
  background: transparent;
  border: none;
  color: #64748b;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 20px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.sp-menu-btn:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.sp-progress-bar {
  position: relative;
  height: 4px;
  background: #e2e8f0;
}

.sp-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
  transition: width 0.3s ease;
}

.sp-progress-text {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 11px;
  font-weight: 600;
  color: #6366f1;
  background: white;
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid #e2e8f0;
}

.sp-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.sp-canvas-container {
  flex: 1;
  width: 100%;
  height: 100%;
  overflow: hidden;
  background: #f8fafc;
}

.sp-quiz-container {
  flex: 1;
  width: 100%;
  height: 100%;
  overflow-y: auto;
  background: #f8fafc;
}

.sp-bottom-nav {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: white;
  border-top: 1px solid #e2e8f0;
  min-height: 64px;
}

.sp-nav-btn {
  background: white;
  border: 1px solid #cbd5e1;
  color: #475569;
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 100px;
  min-height: 48px;
}

.sp-nav-btn:hover:not(:disabled) {
  background: #f1f5f9;
  border-color: #94a3b8;
  color: #1e293b;
}

.sp-nav-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.sp-slide-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  background: #f1f5f9;
  padding: 10px 20px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.sp-current {
  color: #6366f1;
}

.sp-divider {
  color: #94a3b8;
}

/* Mobile responsive adjustments */
@media (max-width: 640px) {
  .sp-title {
    font-size: 14px;
  }

  .sp-slide-count {
    font-size: 12px;
  }

  .sp-nav-btn {
    padding: 10px 16px;
    font-size: 13px;
    min-width: 80px;
  }

  .sp-slide-indicator {
    padding: 8px 16px;
    font-size: 14px;
  }
}

@media (max-width: 768px) {
  .sp-header {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }

  .sp-header-right {
    width: 100%;
    justify-content: flex-end;
  }

  .sp-bottom-nav {
    padding: 12px 16px;
  }

  .sp-nav-btn {
    padding: 10px 16px;
    min-width: 90px;
    font-size: 13px;
  }

  .sp-slide-indicator {
    padding: 10px 16px;
    font-size: 14px;
  }
}

/* Identifier Dialog Styles */
.identifier-dialog {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.dialog-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  padding: 24px;
  text-align: center;
}

.dialog-icon {
  font-size: 64px;
  margin-bottom: 24px;
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.dialog-title {
  margin: 0 0 12px 0;
  font-size: 32px;
  font-weight: 700;
  color: white;
}

.dialog-subtitle {
  margin: 0 0 32px 0;
  font-size: 16px;
  color: rgba(255, 255, 255, 0.9);
  max-width: 400px;
  line-height: 1.5;
}

.dialog-input {
  width: 100%;
  max-width: 400px;
  margin-bottom: 24px;
}

.dialog-input :deep(.q-field__control) {
  background: white;
  border-radius: 12px;
}

.dialog-button {
  width: 100%;
  max-width: 400px;
  height: 56px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 12px;
}
</style>
