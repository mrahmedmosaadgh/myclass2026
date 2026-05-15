import { computed, ref, watch } from 'vue'
import { normalizeGroupQuizQuestion } from '../adapters/groupQuizSlideAdapter'
import { sampleGroups, sampleQuestions } from '../data/sampleQuiz'

const SESSION_KEY = 'myclass2026.groupQuizPlayer.session.v1'
const QUESTION_INDEX_KEY = 'myclass2026.groupQuizPlayer.selectedQuestionIndex.v1'

function clone(value) {
  return JSON.parse(JSON.stringify(value))
}

function loadStoredSession() {
  if (typeof window === 'undefined') return null

  try {
    const raw = window.localStorage.getItem(SESSION_KEY)
    if (!raw) return null

    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed?.questions) || parsed.questions.length === 0) return null

    return {
      groups: Array.isArray(parsed.groups) && parsed.groups.length ? parsed.groups : sampleGroups,
      questions: parsed.questions.map(normalizeGroupQuizQuestion)
    }
  } catch {
    return null
  }
}

function loadStoredQuestionIndex(maxIndex) {
  if (typeof window === 'undefined') return 0

  const value = Number(window.localStorage.getItem(QUESTION_INDEX_KEY))
  if (!Number.isInteger(value)) return 0
  return Math.min(Math.max(value, 0), maxIndex)
}

export function saveGroupQuizSession(groups, questions) {
  if (typeof window === 'undefined') return false

  try {
    const payload = {
      groups: Array.isArray(groups) && groups.length ? groups : null,
      questions: Array.isArray(questions) && questions.length ? questions.map(normalizeGroupQuizQuestion) : null
    }

    window.localStorage.setItem(SESSION_KEY, JSON.stringify(payload))
    window.localStorage.removeItem(QUESTION_INDEX_KEY)
    return true
  } catch {
    return false
  }
}

export function clearGroupQuizSession() {
  if (typeof window === 'undefined') return
  window.localStorage.removeItem(SESSION_KEY)
  window.localStorage.removeItem(QUESTION_INDEX_KEY)
}

export function useGroupQuizPlayerSession() {
  const storedSession = loadStoredSession()
  const groups = ref(clone(storedSession?.groups || sampleGroups))
  const questions = ref(clone(storedSession?.questions || sampleQuestions))
  const selectedGroupId = ref(groups.value[0]?.id || null)
  const currentQuestionIndex = ref(loadStoredQuestionIndex(Math.max(questions.value.length - 1, 0)))
  const answersByQuestion = ref({})
  const gradedQuestionIds = ref({})

  const currentQuestion = computed(() => questions.value[currentQuestionIndex.value] || questions.value[0] || null)
  const currentQuestionId = computed(() => currentQuestion.value?.id || null)
  const currentAnswersByGroup = computed(() => {
    if (!currentQuestionId.value) return {}
    return answersByQuestion.value[currentQuestionId.value] || {}
  })
  const isCurrentQuestionGraded = computed(() => Boolean(gradedQuestionIds.value[currentQuestionId.value]))
  const canGradeCurrentQuestion = computed(() => Object.keys(currentAnswersByGroup.value).length > 0)
  const answeredQuestionCount = computed(() => {
    return questions.value.filter((question) => Object.keys(answersByQuestion.value[question.id] || {}).length > 0).length
  })
  const gradedQuestionCount = computed(() => Object.keys(gradedQuestionIds.value).length)

  watch(currentQuestionIndex, (index) => {
    if (typeof window === 'undefined') return
    window.localStorage.setItem(QUESTION_INDEX_KEY, String(index))
  })

  function selectGroup(groupId) {
    selectedGroupId.value = groupId
  }

  function selectQuestion(index) {
    currentQuestionIndex.value = Math.min(Math.max(index, 0), Math.max(questions.value.length - 1, 0))
  }

  function goToPreviousQuestion() {
    selectQuestion(currentQuestionIndex.value - 1)
  }

  function goToNextQuestion() {
    selectQuestion(currentQuestionIndex.value + 1)
  }

  function answerQuestion(optionId) {
    if (!selectedGroupId.value || !currentQuestionId.value || isCurrentQuestionGraded.value) return

    answersByQuestion.value = {
      ...answersByQuestion.value,
      [currentQuestionId.value]: {
        ...(answersByQuestion.value[currentQuestionId.value] || {}),
        [selectedGroupId.value]: optionId
      }
    }
  }

  function gradeCurrentQuestion() {
    if (!currentQuestion.value || !canGradeCurrentQuestion.value || isCurrentQuestionGraded.value) return

    groups.value = groups.value.map((group) => ({
      ...group,
      score: currentAnswersByGroup.value[group.id] === currentQuestion.value.correctOptionId ? group.score + 1 : group.score
    }))

    gradedQuestionIds.value = {
      ...gradedQuestionIds.value,
      [currentQuestionId.value]: true
    }
  }

  function getQuestionStatus(question) {
    if (gradedQuestionIds.value[question.id]) return 'graded'
    if (Object.keys(answersByQuestion.value[question.id] || {}).length > 0) return 'answered'
    return 'pending'
  }

  function resetSession() {
    groups.value = clone(storedSession?.groups || sampleGroups)
    questions.value = clone(storedSession?.questions || sampleQuestions)
    answersByQuestion.value = {}
    gradedQuestionIds.value = {}
    selectedGroupId.value = groups.value[0]?.id || null
    currentQuestionIndex.value = 0
  }

  return {
    groups,
    questions,
    selectedGroupId,
    currentQuestionIndex,
    currentQuestion,
    currentAnswersByGroup,
    isCurrentQuestionGraded,
    canGradeCurrentQuestion,
    answeredQuestionCount,
    gradedQuestionCount,
    selectGroup,
    selectQuestion,
    goToPreviousQuestion,
    goToNextQuestion,
    answerQuestion,
    gradeCurrentQuestion,
    getQuestionStatus,
    resetSession
  }
}
