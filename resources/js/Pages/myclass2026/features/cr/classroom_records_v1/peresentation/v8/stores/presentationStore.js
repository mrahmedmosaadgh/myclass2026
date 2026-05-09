import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'

// Local storage keys
const STORAGE_KEYS = {
  PRESENTATION: 'presentation-v8-data',
  CURRENT_SLIDE: 'presentation-v8-current-slide',
  DESCRIPTION: 'presentation-v8-description',
  SETTINGS: 'presentation-v8-settings'
}

export const usePresentationStore = defineStore('presentation-v8', () => {
  // Load from localStorage on initialization
  function loadFromStorage() {
    try {
      const savedData = localStorage.getItem(STORAGE_KEYS.PRESENTATION)
      if (savedData) {
        const parsed = JSON.parse(savedData)
        return parsed.slides || []
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

  // State
  const slides = ref(loadFromStorage())
  const currentSlideIndex = ref(parseInt(localStorage.getItem(STORAGE_KEYS.CURRENT_SLIDE) || '0'))
  const description = ref(localStorage.getItem(STORAGE_KEYS.DESCRIPTION) || '')
  const showDescriptionInPresentMode = ref(true)

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
        id: 'q' + Date.now(),
        question: 'New Question',
        options: ['Option A', 'Option B', 'Option C', 'Option D'],
        correctAnswer: 0,
        explanation: ''
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
    resetQuiz
  }
})
