import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useLiveQuestionStore = defineStore('liveQuestion', () => {
  // State
  const isActive = ref(false)
  const sessionCode = ref(null)
  const questionData = ref(null)
  const responses = ref([])
  const isPanelOpen = ref(false)
  const isResultsOpen = ref(false)
  const isConnected = ref(false)

  // Computed
  const responseCount = computed(() => responses.value.length)
  const hasResponses = computed(() => responses.value.length > 0)
  const currentQuestion = computed(() => questionData.value)

  // Actions
  function openPanel() {
    isPanelOpen.value = true
  }

  function closePanel() {
    isPanelOpen.value = false
  }

  function setQuestion(data) {
    questionData.value = {
      id: crypto.randomUUID(),
      type: 'text',
      title: data.title || '',
      instructions: data.instructions || '',
      rules: {
        required: true,
        minLength: data.minLength || 1,
        maxLength: data.maxLength || 1000
      },
      timeLimit: data.timeLimit || null,
      createdAt: Date.now()
    }
  }

  function setSessionCode(code) {
    sessionCode.value = code
  }

  function addResponse(response) {
    const existingIndex = responses.value.findIndex(r => r.studentId === response.studentId)
    if (existingIndex >= 0) {
      // Update existing response
      responses.value[existingIndex] = response
    } else {
      // Add new response
      responses.value.push(response)
    }
  }

  function clearResponses() {
    responses.value = []
  }

  function startSession() {
    isActive.value = true
    isConnected.value = true
  }

  function endSession() {
    isActive.value = false
    isConnected.value = false
  }

  function clearSession() {
    isActive.value = false
    sessionCode.value = null
    questionData.value = null
    responses.value = []
    isPanelOpen.value = false
    isResultsOpen.value = false
    isConnected.value = false
  }

  function toggleResults() {
    isResultsOpen.value = !isResultsOpen.value
  }

  function setConnectionStatus(connected) {
    isConnected.value = connected
  }

  return {
    // State
    isActive,
    sessionCode,
    questionData,
    responses,
    isPanelOpen,
    isResultsOpen,
    isConnected,

    // Computed
    responseCount,
    hasResponses,
    currentQuestion,

    // Actions
    openPanel,
    closePanel,
    setQuestion,
    setSessionCode,
    addResponse,
    clearResponses,
    startSession,
    endSession,
    clearSession,
    toggleResults,
    setConnectionStatus
  }
})
