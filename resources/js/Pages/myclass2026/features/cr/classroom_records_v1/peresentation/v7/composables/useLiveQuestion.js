import { computed } from 'vue'
import { useLiveQuestionStore } from '../stores/liveQuestionStore'
import { useQuestionSession } from '../../../remot_control/v1/examples/question_responses/composables/useQuestionSession'

export function useLiveQuestion() {
  const store = useLiveQuestionStore()

  // Generate unique session code
  function generateSessionCode() {
    const chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789' // No I, O, 0, 1 to avoid confusion
    let code = ''
    for (let i = 0; i < 6; i++) {
      code += chars.charAt(Math.floor(Math.random() * chars.length))
    }
    return code
  }

  // Create and publish a question
  async function createQuestion(questionText, instructions = '', timeLimit = null) {
    if (!questionText?.trim()) {
      throw new Error('Question text is required')
    }

    // Set question data in store
    store.setQuestion({
      title: questionText.trim(),
      instructions: instructions.trim() || '',
      timeLimit: timeLimit ? parseInt(timeLimit) : null,
      minLength: 1,
      maxLength: 1000
    })

    // Generate session code
    const code = generateSessionCode()
    store.setSessionCode(code)

    return code
  }

  // Start the session
  async function startSession() {
    if (!store.sessionCode || !store.questionData) {
      throw new Error('Question must be created before starting session')
    }

    try {
      // Initialize the question session using remote control
      const session = useQuestionSession(store.sessionCode)

      // Publish the question
      session.publishQuestion(store.questionData)

      // Mark as active
      store.startSession()

      // Listen for responses
      session.onEvent((event) => {
        if (event.type === 'answer_submitted') {
          store.addResponse({
            studentId: event.data.studentId,
            studentName: event.data.studentName,
            isAuthenticated: event.data.isAuthenticated || false,
            answer: event.data.answer,
            timestamp: event.data.timestamp,
            submittedAt: new Date().toISOString()
          })
        }
      })

      // Update connection status
      store.setConnectionStatus(session.isConnected)

      return session
    } catch (error) {
      console.error('Failed to start session:', error)
      throw error
    }
  }

  // Close the session
  function closeSession() {
    store.endSession()
    store.clearResponses()
  }

  // Export responses as JSON
  function exportResponses(format = 'json') {
    const data = {
      sessionCode: store.sessionCode,
      question: store.questionData,
      responses: store.responses,
      statistics: {
        totalResponses: store.responseCount,
        createdAt: store.questionData?.createdAt ? new Date(store.questionData.createdAt).toISOString() : null,
        closedAt: new Date().toISOString()
      }
    }

    if (format === 'csv') {
      return exportAsCSV(data)
    }

    return exportAsJSON(data)
  }

  // Export as JSON
  function exportAsJSON(data) {
    const jsonString = JSON.stringify(data, null, 2)
    const blob = new Blob([jsonString], { type: 'application/json' })
    const url = URL.createObjectURL(blob)

    const a = document.createElement('a')
    a.href = url
    a.download = `live_question_responses_${store.sessionCode}_${Date.now()}.json`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  }

  // Export as CSV
  function exportAsCSV(data) {
    const headers = ['Student Name', 'Student ID', 'Authenticated', 'Response', 'Submitted At']
    const rows = data.responses.map(response => [
      response.studentName,
      response.studentId,
      response.isAuthenticated ? 'Yes' : 'No',
      response.answer.text || '',
      response.submittedAt
    ])

    const csvContent = [
      headers.join(','),
      ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n')

    const blob = new Blob([csvContent], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)

    const a = document.createElement('a')
    a.href = url
    a.download = `live_question_responses_${store.sessionCode}_${Date.now()}.csv`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  }

  // Computed properties
  const isActive = computed(() => store.isActive)
  const sessionCode = computed(() => store.sessionCode)
  const responses = computed(() => store.responses)
  const responseCount = computed(() => store.responseCount)
  const currentQuestion = computed(() => store.currentQuestion)
  const isConnected = computed(() => store.isConnected)

  return {
    // Methods
    createQuestion,
    startSession,
    closeSession,
    exportResponses,

    // Computed
    isActive,
    sessionCode,
    responses,
    responseCount,
    currentQuestion,
    isConnected,

    // Store actions
    openPanel: store.openPanel,
    closePanel: store.closePanel,
    clearSession: store.clearSession,
    toggleResults: store.toggleResults
  }
}
