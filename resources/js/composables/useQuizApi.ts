/**
 * Quiz API Composable
 * 
 * Provides API methods for quiz operations with built-in error handling,
 * retry logic, and loading states.
 */

import { ref } from 'vue'
import axios from 'axios'
import { useErrorHandler } from './useErrorHandler'
import type { QuizQuestion, QuizResult, AnswerRecord } from '@/types/quiz'

export interface QuizFetchParams {
  question_ids?: number[]
  grade_level_id?: number
  subject_id?: number
  topic_id?: number
  difficulty_level?: number
  bloom_level?: number
  status?: string
  limit?: number
  shuffle?: boolean
}

export interface QuizAttemptResponse {
  attempt_id: number
  started_at: string
  total_questions: number
}

export interface AnswerSubmissionResponse {
  answer_id: number
  is_correct: boolean
  answered_at: string
}

export interface QuizCompletionResponse {
  attempt_id: number
  completed_at: string
  total_questions: number
  correct_answers: number
  percentage: number
}

export function useQuizApi() {
  const { handleError, clearError, retryOperation } = useErrorHandler()

  const isLoading = ref(false)
  const isSaving = ref(false)

  /**
   * Fetch quiz questions from the API
   */
  const fetchQuiz = async (params: QuizFetchParams): Promise<QuizQuestion[]> => {
    isLoading.value = true
    clearError()

    try {
      const result = await retryOperation(async () => {
        const response = await axios.get('/api/quiz/fetch', {
          params,
          timeout: 10000, // 10 second timeout
        })

        if (!response.data.success) {
          throw new Error(response.data.error?.message || 'Failed to fetch quiz')
        }

        return response.data.data.questions
      })

      return result
    } catch (error) {
      handleError(error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Start a new quiz attempt
   */
  const startAttempt = async (
    questionIds: number[],
    quizId?: number,
    metadata?: Record<string, any>
  ): Promise<QuizAttemptResponse> => {
    isSaving.value = true
    clearError()

    try {
      const result = await retryOperation(async () => {
        const response = await axios.post('/api/quiz/attempts', {
          question_ids: questionIds,
          quiz_id: quizId,
          metadata,
        }, {
          timeout: 10000,
        })

        if (!response.data.success) {
          throw new Error(response.data.error?.message || 'Failed to start quiz attempt')
        }

        return response.data.data
      })

      return result
    } catch (error) {
      handleError(error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  /**
   * Submit an answer for a question
   */
  const submitAnswer = async (
    attemptId: number,
    questionId: number,
    selectedOptionId?: number,
    selectedText?: string,
    timeSpentSec?: number
  ): Promise<AnswerSubmissionResponse> => {
    isSaving.value = true
    clearError()

    try {
      const result = await retryOperation(async () => {
        const response = await axios.post(
          `/api/quiz/attempts/${attemptId}/answers`,
          {
            question_id: questionId,
            selected_option_id: selectedOptionId,
            selected_text: selectedText,
            time_spent_sec: timeSpentSec,
          },
          {
            timeout: 10000,
          }
        )

        if (!response.data.success) {
          throw new Error(response.data.error?.message || 'Failed to submit answer')
        }

        return response.data.data
      })

      return result
    } catch (error) {
      handleError(error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  /**
   * Complete a quiz attempt
   */
  const completeAttempt = async (attemptId: number): Promise<QuizCompletionResponse> => {
    isSaving.value = true
    clearError()

    try {
      const result = await retryOperation(async () => {
        const response = await axios.put(
          `/api/quiz/attempts/${attemptId}/complete`,
          {},
          {
            timeout: 10000,
          }
        )

        if (!response.data.success) {
          throw new Error(response.data.error?.message || 'Failed to complete quiz')
        }

        return response.data.data
      })

      return result
    } catch (error) {
      handleError(error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  /**
   * Get quiz results
   */
  const getResults = async (attemptId: number): Promise<QuizResult> => {
    isLoading.value = true
    clearError()

    try {
      const result = await retryOperation(async () => {
        const response = await axios.get(`/api/quiz/attempts/${attemptId}/results`, {
          timeout: 10000,
        })

        if (!response.data.success) {
          throw new Error(response.data.error?.message || 'Failed to get results')
        }

        return response.data.data
      })

      return result
    } catch (error) {
      handleError(error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Queue an answer for offline submission
   * Stores the answer locally and will retry when connection is restored
   */
  const queueAnswer = (
    attemptId: number,
    questionId: number,
    answerRecord: AnswerRecord
  ): void => {
    const queue = getAnswerQueue()
    queue.push({
      attemptId,
      questionId,
      answerRecord,
      timestamp: new Date().toISOString(),
    })
    saveAnswerQueue(queue)
  }

  /**
   * Process queued answers when connection is restored
   */
  const processQueuedAnswers = async (): Promise<void> => {
    const queue = getAnswerQueue()

    if (queue.length === 0) return

    for (const item of queue) {
      try {
        await submitAnswer(
          item.attemptId,
          item.questionId,
          item.answerRecord.selectedOptionId as number,
          item.answerRecord.selectedText,
          item.answerRecord.timeSpentSec
        )

        // Remove from queue on success
        removeFromQueue(item)
      } catch (error) {
        // Keep in queue if submission fails
        console.error('Failed to process queued answer:', error)
      }
    }
  }

  /**
   * Get the answer queue from local storage
   */
  const getAnswerQueue = (): any[] => {
    try {
      const queue = localStorage.getItem('quiz_answer_queue')
      return queue ? JSON.parse(queue) : []
    } catch {
      return []
    }
  }

  /**
   * Save the answer queue to local storage
   */
  const saveAnswerQueue = (queue: any[]): void => {
    try {
      localStorage.setItem('quiz_answer_queue', JSON.stringify(queue))
    } catch (error) {
      console.error('Failed to save answer queue:', error)
    }
  }

  /**
   * Remove an item from the answer queue
   */
  const removeFromQueue = (item: any): void => {
    const queue = getAnswerQueue()
    const filtered = queue.filter(
      q => !(q.attemptId === item.attemptId && q.questionId === item.questionId)
    )
    saveAnswerQueue(filtered)
  }

  /**
   * Check if there are queued answers
   */
  const hasQueuedAnswers = (): boolean => {
    return getAnswerQueue().length > 0
  }

  return {
    isLoading,
    isSaving,
    fetchQuiz,
    startAttempt,
    submitAnswer,
    completeAttempt,
    getResults,
    queueAnswer,
    processQueuedAnswers,
    hasQueuedAnswers,
  }
}
