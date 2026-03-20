import { ref, computed, onUnmounted } from 'vue'
import { useFgSessionsStore } from '../stores/fg-sessions.store'

export function useFgSession(taskId) {
  const sessionsStore = useFgSessionsStore()
  
  const timerInterval = ref(null)
  const elapsedSeconds = ref(0)
  
  const activeSession = computed(() => {
    return sessionsStore.activeSession
  })
  
  const isRunning = computed(() => {
    return activeSession.value && activeSession.value.task_id === taskId
  })
  
  const formattedTime = computed(() => {
    const mins = Math.floor(elapsedSeconds.value / 60)
    const secs = elapsedSeconds.value % 60
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  })
  
  const startTimer = () => {
    if (timerInterval.value) clearInterval(timerInterval.value)
    
    // Calculate initial elapsed if resuming an existing active session
    if (activeSession.value) {
       const start = new Date(activeSession.value.started_at).getTime()
       const now = new Date().getTime()
       elapsedSeconds.value = Math.floor((now - start) / 1000)
    } else {
       elapsedSeconds.value = 0
    }
    
    timerInterval.value = setInterval(() => {
      elapsedSeconds.value++
    }, 1000)
  }
  
  const stopTimer = () => {
    if (timerInterval.value) {
      clearInterval(timerInterval.value)
      timerInterval.value = null
    }
  }
  
  const startSession = async (intention = null, energy_level = null) => {
    await sessionsStore.createSession({
      task_id: taskId,
      intention,
      energy_level
    })
    startTimer()
  }
  
  const completeSession = async (check_in_answer = null) => {
    if (activeSession.value) {
      await sessionsStore.updateSession(activeSession.value.id, {
        status: 'completed',
        check_in_answer
      })
      stopTimer()
    }
  }
  
  onUnmounted(() => {
    stopTimer()
  })
  
  // If there's an active session when composable is mounted, start the tick
  if (isRunning.value) {
     startTimer()
  }

  return {
    isRunning,
    elapsedSeconds,
    formattedTime,
    startSession,
    completeSession
  }
}
