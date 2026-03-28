/**
 * Question Session Management Composable
 * Manages real-time question-answer sessions using Remote Control v1
 */

import { ref, watch, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useRealtimeChannel } from '../../../core/composables/useRealtimeChannel.js'

export function useQuestionSession(sessionCode, userRole = 'student') {
  // Firebase channel for real-time communication
  const channel = useRealtimeChannel(sessionCode, {
    firebasePath: 'question_sessions',
    persistence: true,
    logEvents: true,
    debounce: 100
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
  const isConnected = computed(() => channel.isConnected.value)
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

    channel.sendCommand({
      type: 'publish_question',
      data: question
    }, {
      priority: 'high',
      requiresAck: true
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

    channel.sendCommand({
      type: 'close_session',
      data: {
        closedAt: new Date().toISOString(),
        closedBy: auth?.user?.name || 'Anonymous Teacher'
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

    channel.sendCommand({
      type: 'reopen_session',
      data: {
        reopenedAt: new Date().toISOString(),
        reopenedBy: auth?.user?.name || 'Anonymous Teacher'
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

    channel.sendCommand({
      type: 'join_session',
      data: studentInfo.value
    })

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

    channel.sendCommand({
      type: 'submit_answer',
      data: response
    }, {
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
    if (newState?.question) {
      currentQuestion.value = newState.question
      sessionStatus.value = 'active'
    }
    
    if (newState?.status) {
      sessionStatus.value = newState.status
    }
    
    if (newState?.metadata) {
      sessionMetadata.value = newState.metadata
    }
  }

  const handleChannelEvent = (event) => {
    switch (event.type) {
      case 'submit_answer':
        if (isTeacher.value) {
          responses.value.push(event.data)
        }
        break
        
      case 'join_session':
        if (isTeacher.value) {
          // Handle student joining (optional: show in teacher view)
          console.log('Student joined:', event.data)
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
          currentQuestion.value = event.data
          sessionStatus.value = 'active'
        }
        break
    }
  }

  // Watchers
  watch(channel.state, handleChannelState, { deep: true })
  watch(channel.events, (events) => {
    events.forEach(handleChannelEvent)
  }, { deep: true })

  // Auto-join for students
  onMounted(() => {
    if (isStudent.value) {
      initializeStudentInfo()
      joinSession()
    }
  })

  // Cleanup
  onUnmounted(() => {
    // Channel cleanup is handled by useRealtimeChannel
  })

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
    
    // Channel access (for advanced usage)
    channel
  }
}
