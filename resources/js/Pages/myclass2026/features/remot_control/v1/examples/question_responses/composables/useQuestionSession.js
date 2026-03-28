/**
 * Question Session Management Composable
 * Manages real-time question-answer sessions using Remote Control v1
 */

import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useRealtimeChannel } from '../../../core/composables/useRealtimeChannel.js'

export function useQuestionSession(sessionCode, userRole = 'student') {
  // Firebase channel for real-time communication
  const channel = useRealtimeChannel(sessionCode, {
    firebasePath: 'question_sessions',
    persistence: true,
    logEvents: false, // Changed to false to reduce console noise
    debounce: 100,
    rateLimitMaxCalls: 50, // Increased for better performance
    rateLimitWindowMs: 1000
  })

  // Reactive state
  const sessionStatus = ref('waiting') // waiting | active | closed
  const currentQuestion = ref(null)
  const responses = ref([])
  const studentInfo = ref(null)
  const sessionMetadata = ref(null)

  // Get auth user if available
  const { auth } = usePage().props || {}

  // Computed properties
  const isTeacher = computed(() => userRole === 'teacher')
  const isStudent = computed(() => userRole === 'student')
  const isConnected = computed(() => channel.isConnected.value === 'connected')
  const responseCount = computed(() => responses.value.length)
  const hasActiveQuestion = computed(() => currentQuestion.value && sessionStatus.value === 'active')

  // Initialize student info for student role
  const initializeStudentInfo = () => {
    if (isStudent.value && !studentInfo.value) {
      // Check if user is authenticated
      if (auth?.user) {
        studentInfo.value = {
          id: auth.user.id,
          name: auth.user.name,
          email: auth.user.email,
          isAuthenticated: true,
          joinedAt: new Date().toISOString()
        }
      } else {
        // Handle guest user
        let guestId = localStorage.getItem('question_session_guest_id')
        let guestName = localStorage.getItem('question_session_guest_name')
        
        if (!guestId) {
          guestId = crypto.randomUUID()
          localStorage.setItem('question_session_guest_id', guestId)
        }
        
        if (!guestName) {
          guestName = prompt("Enter your name:") || 'Anonymous'
          localStorage.setItem('question_session_guest_name', guestName)
        }
        
        studentInfo.value = {
          id: guestId,
          name: guestName,
          isAuthenticated: false,
          joinedAt: new Date().toISOString()
        }
      }
    }
  }

  // Teacher Methods
  const publishQuestion = (questionData) => {
    if (!isTeacher.value) {
      console.error('Only teachers can publish questions')
      return false
    }

    const question = {
      ...questionData,
      id: questionData.id || crypto.randomUUID(),
      publishedAt: new Date().toISOString(),
      publishedBy: auth?.user?.name || 'Anonymous Teacher'
    }

    channel.sendCommand('publish_question', question, {
      priority: 'high',
      requiresAck: true
    })

    channel.updateState({
      question,
      status: 'active',
      metadata: {
        createdAt: sessionMetadata.value?.createdAt || new Date().toISOString(),
        publishedAt: question.publishedAt,
        publishedBy: question.publishedBy
      }
    })

    currentQuestion.value = question
    sessionStatus.value = 'active'
    
    return true
  }

  const closeSession = () => {
    if (!isTeacher.value) {
      console.error('Only teachers can close sessions')
      return false
    }

    const closedAt = new Date().toISOString()

    channel.sendCommand('close_session', {
      closedAt,
      closedBy: auth?.user?.name || 'Anonymous Teacher'
    })

    channel.updateState({
      question: currentQuestion.value,
      status: 'closed',
      metadata: {
        ...sessionMetadata.value,
        closedAt
      }
    })

    sessionStatus.value = 'closed'
    return true
  }

  const reopenSession = () => {
    if (!isTeacher.value) {
      console.error('Only teachers can reopen sessions')
      return false
    }

    const reopenedAt = new Date().toISOString()

    channel.sendCommand('reopen_session', {
      reopenedAt,
      reopenedBy: auth?.user?.name || 'Anonymous Teacher'
    })

    channel.updateState({
      question: currentQuestion.value,
      status: 'active',
      metadata: {
        ...sessionMetadata.value,
        reopenedAt
      }
    })

    sessionStatus.value = 'active'
    return true
  }

  const getResponses = () => {
    return responses.value
  }

  const exportResponses = () => {
    const exportData = {
      sessionCode,
      question: currentQuestion.value,
      responses: responses.value,
      statistics: {
        totalStudents: responses.value.length,
        totalResponses: responses.value.length,
        responseRate: responses.value.length > 0 ? 1 : 0,
        averageResponseTime: calculateAverageResponseTime()
      },
      createdAt: sessionMetadata.value?.createdAt,
      closedAt: sessionStatus.value === 'closed' ? new Date().toISOString() : null,
      exportedAt: new Date().toISOString()
    }

    // Create and download JSON file
    const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `question_responses_${sessionCode}_${Date.now()}.json`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)

    return exportData
  }

  const calculateAverageResponseTime = () => {
    if (responses.value.length === 0) return 0
    
    const responseTimes = responses.value
      .filter(r => r.responseTime)
      .map(r => r.responseTime)
    
    if (responseTimes.length === 0) return 0
    
    return responseTimes.reduce((sum, time) => sum + time, 0) / responseTimes.length
  }

  // Student Methods
  const joinSession = () => {
    if (!isStudent.value) {
      console.error('Only students can join sessions')
      return false
    }

    initializeStudentInfo()

    channel.sendCommand('join_session', studentInfo.value)

    return true
  }

  const submitAnswer = (answerData) => {
    if (!isStudent.value || !studentInfo.value) {
      console.error('Student info not initialized')
      return false
    }

    if (!hasActiveQuestion.value) {
      console.error('No active question to answer')
      return false
    }

    const response = {
      questionId: currentQuestion.value.id,
      studentId: studentInfo.value.id,
      studentName: studentInfo.value.name,
      isAuthenticated: studentInfo.value.isAuthenticated,
      answer: answerData,
      timestamp: new Date().toISOString(),
      responseTime: calculateResponseTime()
    }

    channel.sendCommand('submit_answer', response, {
      priority: 'high',
      requiresAck: true
    })

    return true
  }

  const getCurrentQuestion = () => {
    return currentQuestion.value
  }

  const calculateResponseTime = () => {
    if (!currentQuestion.value?.publishedAt) return 0
    
    const publishedTime = new Date(currentQuestion.value.publishedAt).getTime()
    const currentTime = new Date().getTime()
    
    return Math.round((currentTime - publishedTime) / 1000) // seconds
  }

  // Event Handlers
  const handleChannelState = (newState) => {
    const stateData = newState?.data || newState

    if (stateData?.question) {
      currentQuestion.value = stateData.question
      sessionStatus.value = 'active'
    }
    
    if (stateData?.status) {
      sessionStatus.value = stateData.status
    }
    
    if (stateData?.metadata) {
      sessionMetadata.value = stateData.metadata
    }
  }

  const handleChannelCommand = (command) => {
    switch (command.type) {
      case 'submit_answer':
        if (isTeacher.value) {
          responses.value.push(command.payload)
        }
        break
        
      case 'join_session':
        if (isTeacher.value) {
          console.log('Student joined:', command.payload)
        }
        break
        
      case 'close_session':
        sessionStatus.value = 'closed'
        break
        
      case 'reopen_session':
        sessionStatus.value = 'active'
        break
        
      case 'publish_question':
        if (isStudent.value) {
          currentQuestion.value = command.payload
          sessionStatus.value = 'active'
        }
        break
    }
  }

  channel.onStateChange(handleChannelState)
  channel.onCommand(handleChannelCommand)

  return {
    // Teacher API
    publishQuestion,
    closeSession,
    reopenSession,
    getResponses,
    exportResponses,
    
    // Student API
    joinSession,
    submitAnswer,
    getCurrentQuestion,
    
    // Shared State
    sessionStatus,
    currentQuestion,
    responses,
    studentInfo,
    sessionMetadata,
    
    // Computed
    isTeacher,
    isStudent,
    isConnected,
    responseCount,
    hasActiveQuestion,
    lastError: channel.lastError,
    channel
  }
}
