<script setup>
import { onMounted, onUnmounted, computed, ref } from 'vue'
import { useQuasar } from 'quasar'
import { usePresentationStore } from './stores/presentationStore.js'
import { useUIStore } from './stores/uiStore.js'
import { usePresentationAPI } from './composables/usePresentationAPI.js'
import SlideCanvasReadonly from './components/SlideCanvasReadonly.vue'

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

// Check if current slide has a quiz element (to disable arrow key navigation)
const hasQuizOnCurrentSlide = computed(() => {
  const currentSlide = presentation.currentSlide
  if (!currentSlide || !currentSlide.elements) return false
  return currentSlide.elements.some(el => el.type === 'quiz-v2')
})

// Track quiz attempts for submission
const quizAttempts = ref([])
const totalScore = ref(0)
const totalQuestions = ref(0)

// Load presentation from API using share token
async function loadPresentationFromToken() {
  const pathParts = window.location.pathname.split('/')
  const shareToken = pathParts[pathParts.length - 1]

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
  const pathParts = window.location.pathname.split('/')
  const shareToken = pathParts[pathParts.length - 1]

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
      <div class="sp-header-left">
        <h1 class="sp-title">Student Presentation</h1>
        <span class="sp-slide-count">
          Slide {{ currentIndex + 1 }} of {{ totalSlides }}
        </span>
      </div>
      <div class="sp-header-right">
        <button class="sp-btn" @click="submitAttempts" title="Submit results">
          ✓ Submit Results
        </button>
        <button class="sp-btn" @click="sharePresentation" title="Share presentation">
          🔗 Share
        </button>
        <button class="sp-btn" @click="toggleFullscreen" title="Toggle fullscreen">
          ⛶ Fullscreen
        </button>
      </div>
    </div>

    <!-- Student Identifier Dialog -->
    <q-dialog
      v-model="showIdentifierDialog"
      persistent
    >
      <q-card style="min-width: 350px;">
        <q-card-section>
          <div class="text-h6">Enter Your Name</div>
          <div class="text-caption text-grey-7">
            Please enter your name or email so your teacher can track your progress
          </div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input
            v-model="studentIdentifier"
            label="Name or Email"
            outlined
            dense
            autofocus
            @keyup.enter="handleIdentifierSubmit"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn
            flat
            label="Start"
            color="primary"
            @click="handleIdentifierSubmit"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Main Content -->
    <div class="sp-main">
      <!-- Slide Canvas -->
      <div class="sp-canvas-container">
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
  background: #1a1a1a;
  color: #f0f0f0;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
}

.sp-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  background: #252525;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.sp-header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.sp-title {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #f0f0f0;
}

.sp-slide-count {
  font-size: 14px;
  color: #888;
}

.sp-header-right {
  display: flex;
  gap: 8px;
}

.sp-btn {
  background: #2a2a2a;
  border: 1px solid #444;
  color: #ccc;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.sp-btn:hover {
  background: #333;
  border-color: #505050;
  color: #e0e0e0;
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
  background: #1a1a1a;
}

.sp-bottom-nav {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background: #252525;
  border-top: 1px solid #333;
}

.sp-nav-btn {
  background: #2a2a2a;
  border: 1px solid #444;
  color: #ccc;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 120px;
}

.sp-nav-btn:hover:not(:disabled) {
  background: #333;
  border-color: #505050;
  color: #e0e0e0;
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
  color: #f0f0f0;
  background: #1a1a1a;
  padding: 12px 24px;
  border-radius: 8px;
  border: 1px solid #383838;
}

.sp-current {
  color: #63b3ed;
}

.sp-divider {
  color: #666;
}

.sp-total {
  color: #888;
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
</style>
