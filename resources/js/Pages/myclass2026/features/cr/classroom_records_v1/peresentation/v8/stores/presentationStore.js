import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import { normalize } from '../domains/questions/index.js'

// Local storage keys
const STORAGE_KEYS = {
  PRESENTATION: 'presentation-v8-data',
  CURRENT_SLIDE: 'presentation-v8-current-slide',
  DESCRIPTION: 'presentation-v8-description',
  SETTINGS: 'presentation-v8-settings',
  QUIZ_ATTEMPTS: 'presentation-v8-quiz-attempts'
}

export const usePresentationStore = defineStore('presentation-v8', () => {
  // Load from localStorage on initialization
  function loadFromStorage() {
    try {
      const savedData = localStorage.getItem(STORAGE_KEYS.PRESENTATION)
      if (savedData) {
        const parsed = JSON.parse(savedData)
        const slides = parsed.slides || []
        // Auto-normalize old-format quiz questions to v8
        migrateQuizQuestions(slides)
        return slides
      }
    } catch (error) {
      console.warn('Failed to load presentation from localStorage:', error)
    }

    // Default data if nothing in storage
    return [
      {
        id: 'slide-1',
        elements: [
          {
            id: 'el-1',
            type: 'text',
            content: 'Welcome to Builder V8',
            x: 100,
            y: 100,
            width: 400,
            height: 60,
            fontSize: 32,
            color: '#000000',
            zIndex: 1,
            visibilityOption: 'shown-clickable',
            isVisible: true,
            hiddenOpacity: 0.05
          }
        ]
      }
    ]
  }

  /**
   * Auto-migrate quiz questions from legacy format to v8 canonical format.
   * Scans all slides and elements, normalizing any quiz/group-mcq questions.
   */
  function migrateQuizQuestions(slides) {
    let migratedCount = 0

    slides.forEach(slide => {
      if (!slide.elements) return
      slide.elements.forEach(el => {
        // Migrate quiz-v2 elements with legacy questions
        if (el.type === 'quiz-v2' && Array.isArray(el.questions)) {
          el.questions = el.questions.map(q => {
            if (q.schema_version === 1) return q // Already v8
            try {
              const normalized = normalize(q)
              if (normalized) {
                migratedCount++
                return normalized
              }
            } catch (e) {
              console.warn('Failed to normalize quiz question:', e, q)
            }
            return q // Keep original if normalization fails
          })
        }

        // Migrate group-mcq elements with legacy questionData
        if (el.type === 'group-mcq' && el.questionData) {
          const q = el.questionData
          if (q.schema_version !== 1) {
            try {
              const normalized = normalize(q)
              if (normalized) {
                el.questionData = normalized
                migratedCount++
              }
            } catch (e) {
              console.warn('Failed to normalize group-mcq questionData:', e, q)
            }
          }
        }
      })
    })

    if (migratedCount > 0) {
      console.log(`[migrateQuizQuestions] Migrated ${migratedCount} question(s) to v8 format`)
    }
  }

  // Load quiz attempts from localStorage
  function loadQuizAttempts() {
    try {
      const savedData = localStorage.getItem(STORAGE_KEYS.QUIZ_ATTEMPTS)
      if (savedData) {
        return JSON.parse(savedData)
      }
    } catch (error) {
      console.warn('Failed to load quiz attempts from localStorage:', error)
    }
    return []
  }

  // Save quiz attempts to localStorage
  function saveQuizAttempts() {
    try {
      localStorage.setItem(STORAGE_KEYS.QUIZ_ATTEMPTS, JSON.stringify(quizAttempts.value))
    } catch (error) {
      console.warn('Failed to save quiz attempts to localStorage:', error)
    }
  }

  // State
  const slides = ref(loadFromStorage())
  const currentSlideIndex = ref(parseInt(localStorage.getItem(STORAGE_KEYS.CURRENT_SLIDE) || '0'))
  const description = ref(localStorage.getItem(STORAGE_KEYS.DESCRIPTION) || '')
  const showDescriptionInPresentMode = ref(true)
  const quizAttempts = ref(loadQuizAttempts())

  // Getters
  const currentSlide = computed(() => {
    return slides.value[currentSlideIndex.value] || { elements: [] }
  })

  const totalSlides = computed(() => slides.value.length)

  // Actions
  function addSlide() {
    const newSlide = {
      id: `slide-${Date.now()}`,
      elements: []
    }
    slides.value.push(newSlide)
    selectSlide(slides.value.length - 1)
  }

  function deleteSlide(index) {
    if (slides.value.length <= 1) return
    slides.value.splice(index, 1)
    if (currentSlideIndex.value >= slides.value.length) {
      selectSlide(slides.value.length - 1)
    }
  }

  function selectSlide(index) {
    if (index >= 0 && index < slides.value.length) {
      currentSlideIndex.value = index
    }
  }

  function addElement(elementData) {
    const newElement = {
      id: `el-${Date.now()}`,
      type: elementData.type || 'text',
      content: elementData.content || '',
      x: elementData.x || 150,
      y: elementData.y || 150,
      width: elementData.width || 200,
      height: elementData.height || 50,
      fontSize: elementData.fontSize || 24,
      color: elementData.color || '#000000',
      zIndex: elementData.zIndex || 1,
      visibilityOption: elementData.visibilityOption || 'shown-clickable',
      isVisible: elementData.isVisible !== false,
      hiddenOpacity: elementData.hiddenOpacity || 0.05,
      ...elementData
    }
    
    currentSlide.value.elements.push(newElement)
    return newElement
  }

  function updateElement({ id, changes }) {
    const element = currentSlide.value.elements.find(el => el.id === id)
    if (!element) return
    
    Object.assign(element, changes)
  }

  function deleteElement(id) {
    const index = currentSlide.value.elements.findIndex(el => el.id === id)
    if (index > -1) {
      currentSlide.value.elements.splice(index, 1)
    }
  }

  function duplicateElement(id) {
    const element = currentSlide.value.elements.find(el => el.id === id)
    if (!element) return
    
    const duplicated = {
      ...element,
      id: `el-${Date.now()}`,
      x: element.x + 20,
      y: element.y + 20,
      zIndex: element.zIndex + 1
    }
    
    currentSlide.value.elements.push(duplicated)
    return duplicated
  }

  function bringToFront(id) {
    const element = currentSlide.value.elements.find(el => el.id === id)
    if (!element) return
    
    const maxZ = Math.max(...currentSlide.value.elements.map(el => el.zIndex))
    element.zIndex = maxZ + 1
  }

  function sendToBack(id) {
    const element = currentSlide.value.elements.find(el => el.id === id)
    if (!element) return
    
    const minZ = Math.min(...currentSlide.value.elements.map(el => el.zIndex))
    element.zIndex = minZ - 1
  }

  // Quiz-specific actions
  function addQuiz(quizData) {
    const quiz = {
      id: 'quiz-' + Date.now(),
      type: 'quiz',
      x: 100,
      y: 100,
      width: 600,
      height: 400,
      backgroundColor: '#6366f1',
      borderRadius: '8px',
      title: quizData.title || 'New Quiz',
      questions: quizData.questions || [],
      currentQuestionIndex: 0,
      showResults: false,
      userAnswers: {},
      isInteractive: false,
      zIndex: Math.max(...currentSlide.value.elements.map(el => el.zIndex || 0), 0) + 1
    }
    
    addElement(quiz)
    return quiz
  }

  function updateQuizAnswer(quizId, questionIndex, answerIndex) {
    const quiz = currentSlide.value.elements.find(el => el.id === quizId)
    if (quiz) {
      if (!quiz.userAnswers) {
        quiz.userAnswers = {}
      }
      quiz.userAnswers[questionIndex] = answerIndex
    }
  }

  function updateQuizProperty(quizId, property, value) {
    const quiz = currentSlide.value.elements.find(el => el.id === quizId)
    if (quiz) {
      quiz[property] = value
    }
  }

  function addQuizQuestion(quizId) {
    const quiz = currentSlide.value.elements.find(el => el.id === quizId)
    if (quiz) {
      const newQuestion = {
        schema_version: 1,
        id: 'q_' + Date.now(),
        type: 'multiple_choice',
        marks: 1,
        content: {
          prompt: 'New Question',
          options: [
            { id: 'a', text: 'Option A', is_correct: true },
            { id: 'b', text: 'Option B', is_correct: false },
            { id: 'c', text: 'Option C', is_correct: false },
            { id: 'd', text: 'Option D', is_correct: false },
          ],
          explanation: '',
        },
        meta: {
          difficulty: 2,
          bloom_level: 1,
          estimated_time_sec: 60,
          source: 'teacher',
          tags: [],
          cognitive_demand: 'recall',
          assessment_mode: 'traditional',
        },
        evaluation: { mode: 'auto' },
      }
      quiz.questions.push(newQuestion)
    }
  }

  function removeQuizQuestion(quizId, questionIndex) {
    const quiz = currentSlide.value.elements.find(el => el.id === quizId)
    if (quiz && quiz.questions.length > 1) {
      quiz.questions.splice(questionIndex, 1)
    }
  }

  function toggleQuizInteractive(quizId) {
    const quiz = currentSlide.value.elements.find(el => el.id === quizId)
    if (quiz) {
      quiz.isInteractive = !quiz.isInteractive
      if (!quiz.isInteractive) {
        // Reset interactive state when turned off
        quiz.currentQuestionIndex = 0
        quiz.showResults = false
      }
    }
  }

  function resetQuiz(quizId) {
    const quiz = currentSlide.value.elements.find(el => el.id === quizId)
    if (quiz) {
      quiz.currentQuestionIndex = 0
      quiz.showResults = false
      quiz.userAnswers = {}
      quiz.isInteractive = false
    }
  }

  // Quiz attempt tracking actions
  function saveQuizAttempt(attemptData) {
    const attempt = {
      id: 'attempt-' + Date.now(),
      timestamp: new Date().toISOString(),
      ...attemptData
    }
    quizAttempts.value.push(attempt)
    saveQuizAttempts()
    return attempt
  }

  function getQuizAttempts(quizId) {
    return quizAttempts.value.filter(attempt => attempt.quizId === quizId)
  }

  function getQuizStatistics(quizId) {
    const attempts = getQuizAttempts(quizId)
    if (attempts.length === 0) {
      return {
        totalAttempts: 0,
        averageScore: 0,
        highestScore: 0,
        lowestScore: 0,
        scores: []
      }
    }

    const scores = attempts.map(a => a.score)
    const averageScore = scores.reduce((sum, score) => sum + score, 0) / scores.length
    const highestScore = Math.max(...scores)
    const lowestScore = Math.min(...scores)

    return {
      totalAttempts: attempts.length,
      averageScore: Math.round(averageScore * 10) / 10,
      highestScore,
      lowestScore,
      scores
    }
  }

  function deleteQuizAttempt(attemptId) {
    const index = quizAttempts.value.findIndex(a => a.id === attemptId)
    if (index > -1) {
      quizAttempts.value.splice(index, 1)
      saveQuizAttempts()
    }
  }

  function clearQuizAttempts(quizId) {
    quizAttempts.value = quizAttempts.value.filter(attempt => attempt.quizId !== quizId)
    saveQuizAttempts()
  }

  function setDescription(value) {
    description.value = value
    saveToStorage()
  }

  function toggleDescriptionInPresentMode() {
    showDescriptionInPresentMode.value = !showDescriptionInPresentMode.value
    saveToStorage()
  }

  function clearDescription() {
    description.value = ''
    saveToStorage()
  }

  // Save to localStorage
  function saveToStorage() {
    try {
      localStorage.setItem(STORAGE_KEYS.PRESENTATION, JSON.stringify({ 
        slides: slides.value,
        version: 'v8',
        savedAt: new Date().toISOString()
      }))
      localStorage.setItem(STORAGE_KEYS.CURRENT_SLIDE, currentSlideIndex.value.toString())
      localStorage.setItem(STORAGE_KEYS.DESCRIPTION, description.value)
      localStorage.setItem(STORAGE_KEYS.SETTINGS, JSON.stringify({
        showDescriptionInPresentMode: showDescriptionInPresentMode.value
      }))
    } catch (error) {
      console.warn('Failed to save presentation to localStorage:', error)
    }
  }

  // Auto-save watchers
  watch(slides, saveToStorage, { deep: true })
  watch(currentSlideIndex, saveToStorage)
  watch(description, saveToStorage)
  watch(showDescriptionInPresentMode, saveToStorage)

  function exportPresentation() {
    const data = {
      slides: slides.value,
      currentSlideIndex: currentSlideIndex.value,
      description: description.value,
      showDescriptionInPresentMode: showDescriptionInPresentMode.value,
      version: 'v8',
      exportedAt: new Date().toISOString()
    }
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `presentation-v8-${new Date().toISOString().split('T')[0]}.json`
    a.click()
    URL.revokeObjectURL(url)
  }

  function importPresentation(jsonData) {
    try {
      const data = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData
      
      if (!data.slides || !Array.isArray(data.slides)) {
        throw new Error('Invalid presentation format')
      }
      
      slides.value = data.slides
      currentSlideIndex.value = data.currentSlideIndex || 0
      description.value = data.description || ''
      showDescriptionInPresentMode.value = data.showDescriptionInPresentMode !== false
      
      return true
    } catch (error) {
      console.error('Failed to import presentation:', error)
      return false
    }
  }

  return {
    // State
    slides,
    currentSlideIndex,
    description,
    showDescriptionInPresentMode,
    quizAttempts,

    // Getters
    currentSlide,
    totalSlides,

    // Actions
    addSlide,
    deleteSlide,
    selectSlide,
    addElement,
    updateElement,
    deleteElement,
    duplicateElement,
    bringToFront,
    sendToBack,
    setDescription,
    toggleDescriptionInPresentMode,
    clearDescription,
    exportPresentation,
    importPresentation,
    // Quiz actions
    addQuiz,
    updateQuizAnswer,
    updateQuizProperty,
    addQuizQuestion,
    removeQuizQuestion,
    toggleQuizInteractive,
    resetQuiz,
    // Quiz attempt tracking actions
    saveQuizAttempt,
    getQuizAttempts,
    getQuizStatistics,
    deleteQuizAttempt,
    clearQuizAttempts
  }
})
