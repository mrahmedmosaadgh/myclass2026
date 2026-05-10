import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'

const STORAGE_KEY = 'presentation-v8-game-state'

export const useGameStore = defineStore('presentation-v8-game', () => {
  // Load from localStorage
  function loadFromStorage() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY)
      if (raw) {
        const parsed = JSON.parse(raw)
        const age = Date.now() - (parsed.timestamp || 0)
        if (age > 24 * 60 * 60 * 1000) {
          localStorage.removeItem(STORAGE_KEY)
          return null
        }
        return parsed
      }
    } catch (err) {
      console.warn('Failed to load game state:', err)
    }
    return null
  }

  const saved = loadFromStorage()

  // ── State ──────────────────────────────────────────────
  const gameSettings = ref(saved?.gameSettings || {
    correctPoints: 10,
    wrongPoints: -5,
    allowNegativeScore: false
  })

  const groups = ref(saved?.groups || [
    { id: 'g1', name: 'Group A', score: 0, color: '#ef4444' },
    { id: 'g2', name: 'Group B', score: 0, color: '#3b82f6' },
    { id: 'g3', name: 'Group C', score: 0, color: '#10b981' }
  ])

  const questionHistory = ref(saved?.questionHistory || {})

  // Remote session state (future: student digital submission)
  const sessionId = ref(saved?.sessionId || null)
  const accessCode = ref(saved?.accessCode || null)
  const sessionStatus = ref(saved?.sessionStatus || 'offline') // offline, waiting, active, completed
  const participants = ref(saved?.participants || [])

  // ── Getters ────────────────────────────────────────────
  const onlineCount = computed(() =>
    participants.value.filter(p => p.status === 'online').length
  )

  const rankedGroups = computed(() => {
    const sorted = [...groups.value].sort((a, b) => b.score - a.score)
    let rank = 1
    let prevScore = sorted[0]?.score ?? 0
    return sorted.map(g => {
      if (g.score < prevScore) { rank++; prevScore = g.score }
      return { ...g, computedRank: rank }
    })
  })

  // ── Actions ────────────────────────────────────────────
  // Group management
  function addGroup(name, color) {
    const id = 'g' + Date.now() + Math.random().toString(36).slice(2, 5)
    groups.value.push({
      id,
      name: name?.trim() || `Group ${groups.value.length + 1}`,
      score: 0,
      color: color || '#8b5cf6'
    })
  }

  function removeGroup(id) {
    groups.value = groups.value.filter(g => g.id !== id)
  }

  function updateGroupName(id, newName) {
    const g = groups.value.find(g => g.id === id)
    if (g) g.name = newName?.trim() || g.name
  }

  function updateGroupColor(id, newColor) {
    const g = groups.value.find(g => g.id === id)
    if (g) g.color = newColor
  }

  function setGroupScore(id, newScore) {
    const g = groups.value.find(g => g.id === id)
    if (g) g.score = Number(newScore) || 0
  }

  function updateGroupScore(id, delta) {
    const g = groups.value.find(g => g.id === id)
    if (g) g.score += delta
  }

  function resetScores() {
    groups.value.forEach(g => { g.score = 0 })
    questionHistory.value = {}
  }

  function resetGroups() {
    groups.value = [
      { id: 'g1', name: 'Group A', score: 0, color: '#ef4444' },
      { id: 'g2', name: 'Group B', score: 0, color: '#3b82f6' },
      { id: 'g3', name: 'Group C', score: 0, color: '#10b981' }
    ]
    questionHistory.value = {}
  }

  // Question / answer tracking
  function logGroupAnswer(elementId, groupId, optionId) {
    if (!questionHistory.value[elementId]) {
      questionHistory.value[elementId] = { groupAnswers: {}, status: 'locked_in' }
    }
    questionHistory.value[elementId].groupAnswers[groupId] = optionId
  }

  function clearGroupAnswer(elementId, groupId) {
    const q = questionHistory.value[elementId]
    if (q?.groupAnswers?.[groupId]) {
      delete q.groupAnswers[groupId]
    }
  }

  function clearElementHistory(elementId) {
    delete questionHistory.value[elementId]
  }

  function getGroupAnswer(elementId, groupId) {
    return questionHistory.value[elementId]?.groupAnswers?.[groupId] || null
  }

  function markGraded(elementId) {
    if (questionHistory.value[elementId]) {
      questionHistory.value[elementId].status = 'graded'
    }
  }

  function markUngraded(elementId) {
    if (questionHistory.value[elementId]) {
      questionHistory.value[elementId].status = 'locked_in'
    }
  }

  function isGraded(elementId) {
    return questionHistory.value[elementId]?.status === 'graded'
  }

  // Session management (future: remote control)
  function setSession(id, code, status = 'waiting') {
    sessionId.value = id
    accessCode.value = code
    sessionStatus.value = status
  }

  function handleStudentSignal(signal) {
    if (signal.event === 'STUDENT_JOINED') {
      const student = signal.context
      const existing = participants.value.find(p => p.id === student.student_id)
      if (existing) {
        existing.status = 'online'
      } else {
        participants.value.push({
          id: student.student_id,
          name: student.name,
          group: student.group || 'Joined',
          status: 'online'
        })
      }
    } else if (signal.event === 'STUDENT_LEFT') {
      const p = participants.value.find(p => p.id === signal.context.student_id)
      if (p) p.status = 'offline'
    }
  }

  function endSession() {
    sessionStatus.value = 'completed'
    participants.value = []
  }

  function resetSession() {
    sessionId.value = null
    accessCode.value = null
    sessionStatus.value = 'offline'
    participants.value = []
  }

  // ── Persistence ────────────────────────────────────────
  function saveToStorage() {
    try {
      const state = {
        gameSettings: gameSettings.value,
        groups: groups.value,
        questionHistory: questionHistory.value,
        sessionId: sessionId.value,
        accessCode: accessCode.value,
        sessionStatus: sessionStatus.value,
        participants: participants.value,
        timestamp: Date.now()
      }
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
    } catch (err) {
      console.warn('Failed to save game state:', err)
    }
  }

  // Auto-save (debounced via watcher batching)
  watch(groups, saveToStorage, { deep: true })
  watch(questionHistory, saveToStorage, { deep: true })
  watch(gameSettings, saveToStorage, { deep: true })
  watch(sessionStatus, saveToStorage)

  return {
    // State
    gameSettings,
    groups,
    questionHistory,
    sessionId,
    accessCode,
    sessionStatus,
    participants,

    // Getters
    onlineCount,
    rankedGroups,

    // Actions
    addGroup,
    removeGroup,
    updateGroupName,
    updateGroupColor,
    setGroupScore,
    updateGroupScore,
    resetScores,
    resetGroups,

    logGroupAnswer,
    clearGroupAnswer,
    clearElementHistory,
    getGroupAnswer,
    markGraded,
    markUngraded,
    isGraded,

    setSession,
    handleStudentSignal,
    endSession,
    resetSession
  }
})
