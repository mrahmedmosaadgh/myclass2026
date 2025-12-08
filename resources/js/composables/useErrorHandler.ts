/**
 * Error Handler Composable
 * 
 * Provides centralized error handling utilities for the quiz system.
 * Handles network errors, validation errors, and provides user-friendly messages.
 */

import { ref } from 'vue'

export interface ErrorDetails {
  code: string
  message: string
  details?: any
  timestamp: string
}

export interface ErrorState {
  hasError: boolean
  error: ErrorDetails | null
  retryCount: number
}

/**
 * Maximum number of retry attempts for network failures
 */
const MAX_RETRY_ATTEMPTS = 3

/**
 * Delay between retry attempts in milliseconds
 */
const RETRY_DELAY = 1000

/**
 * User-friendly error messages mapped to error codes
 */
const ERROR_MESSAGES: Record<string, string> = {
  QUIZ_NOT_FOUND: 'The requested quiz could not be found. Please try again.',
  INVALID_ANSWER_FORMAT: 'Your answer could not be submitted. Please check your selection and try again.',
  IMPORT_VALIDATION_FAILED: 'The import file contains errors. Please check the file and try again.',
  UNAUTHORIZED_ACCESS: 'You do not have permission to access this quiz.',
  DATABASE_ERROR: 'A database error occurred. Please try again later.',
  VALIDATION_ERROR: 'Please check your input and try again.',
  QUIZ_FETCH_ERROR: 'Failed to load the quiz. Please refresh the page.',
  ATTEMPT_CREATION_ERROR: 'Failed to start the quiz. Please try again.',
  ANSWER_SUBMISSION_ERROR: 'Failed to submit your answer. Please try again.',
  COMPLETION_ERROR: 'Failed to complete the quiz. Please try again.',
  RESULTS_RETRIEVAL_ERROR: 'Failed to load quiz results. Please try again.',
  NETWORK_ERROR: 'Network connection failed. Please check your internet connection.',
  TIMEOUT_ERROR: 'The request timed out. Please try again.',
  UNKNOWN_ERROR: 'An unexpected error occurred. Please try again.',
  INVALID_QUESTIONS: 'Some questions are not available. Please contact support.',
  ATTEMPT_COMPLETED: 'This quiz has already been completed.',
  INVALID_QUESTION: 'This question is not part of the quiz.',
  NOT_FOUND: 'The requested resource was not found.',
  ALREADY_COMPLETED: 'This quiz attempt has already been completed.',
  ATTEMPT_NOT_COMPLETED: 'This quiz attempt has not been completed yet.',
}

export function useErrorHandler() {
  const errorState = ref<ErrorState>({
    hasError: false,
    error: null,
    retryCount: 0,
  })

  /**
   * Get a user-friendly error message for an error code
   */
  const getUserFriendlyMessage = (code: string): string => {
    return ERROR_MESSAGES[code] || ERROR_MESSAGES.UNKNOWN_ERROR
  }

  /**
   * Handle an error and update the error state
   */
  const handleError = (error: any): void => {
    console.error('Error occurred:', error)

    // Extract error details from different error formats
    let errorDetails: ErrorDetails

    if (error.response?.data?.error) {
      // API error response
      errorDetails = error.response.data.error
    } else if (error.code === 'ECONNABORTED' || error.message?.includes('timeout')) {
      // Timeout error
      errorDetails = {
        code: 'TIMEOUT_ERROR',
        message: getUserFriendlyMessage('TIMEOUT_ERROR'),
        timestamp: new Date().toISOString(),
      }
    } else if (error.code === 'ERR_NETWORK' || !navigator.onLine) {
      // Network error
      errorDetails = {
        code: 'NETWORK_ERROR',
        message: getUserFriendlyMessage('NETWORK_ERROR'),
        timestamp: new Date().toISOString(),
      }
    } else {
      // Unknown error
      errorDetails = {
        code: 'UNKNOWN_ERROR',
        message: error.message || getUserFriendlyMessage('UNKNOWN_ERROR'),
        timestamp: new Date().toISOString(),
      }
    }

    // Update error state
    errorState.value = {
      hasError: true,
      error: errorDetails,
      retryCount: errorState.value.retryCount,
    }

    // Log error for debugging
    logError(errorDetails)
  }

  /**
   * Clear the current error state
   */
  const clearError = (): void => {
    errorState.value = {
      hasError: false,
      error: null,
      retryCount: 0,
    }
  }

  /**
   * Retry a failed operation with exponential backoff
   */
  const retryOperation = async <T>(
    operation: () => Promise<T>,
    maxRetries: number = MAX_RETRY_ATTEMPTS
  ): Promise<T> => {
    let lastError: any

    for (let attempt = 0; attempt <= maxRetries; attempt++) {
      try {
        // Clear error before retry
        if (attempt > 0) {
          clearError()
        }

        // Update retry count
        errorState.value.retryCount = attempt

        // Execute the operation
        const result = await operation()

        // Success - reset retry count
        errorState.value.retryCount = 0

        return result
      } catch (error) {
        lastError = error

        // Don't retry on certain error types
        if (shouldNotRetry(error)) {
          throw error
        }

        // If this was the last attempt, throw the error
        if (attempt === maxRetries) {
          throw error
        }

        // Wait before retrying with exponential backoff
        const delay = RETRY_DELAY * Math.pow(2, attempt)
        await sleep(delay)
      }
    }

    throw lastError
  }

  /**
   * Check if an error should not be retried
   */
  const shouldNotRetry = (error: any): boolean => {
    const nonRetryableCodes = [
      'UNAUTHORIZED_ACCESS',
      'VALIDATION_ERROR',
      'INVALID_QUESTIONS',
      'ATTEMPT_COMPLETED',
      'INVALID_QUESTION',
      'NOT_FOUND',
      'ALREADY_COMPLETED',
      'ATTEMPT_NOT_COMPLETED',
    ]

    const errorCode = error.response?.data?.error?.code

    return nonRetryableCodes.includes(errorCode)
  }

  /**
   * Sleep for a specified duration
   */
  const sleep = (ms: number): Promise<void> => {
    return new Promise(resolve => setTimeout(resolve, ms))
  }

  /**
   * Log error details for debugging
   */
  const logError = (error: ErrorDetails): void => {
    // In production, this could send errors to a logging service
    if (import.meta.env.DEV) {
      console.error('[Error Handler]', {
        code: error.code,
        message: error.message,
        details: error.details,
        timestamp: error.timestamp,
      })
    }
  }

  /**
   * Check if the current error is a network error
   */
  const isNetworkError = (): boolean => {
    return errorState.value.error?.code === 'NETWORK_ERROR'
  }

  /**
   * Check if the current error is retryable
   */
  const isRetryable = (): boolean => {
    if (!errorState.value.error) return false
    return !shouldNotRetry({ response: { data: { error: errorState.value.error } } })
  }

  return {
    errorState,
    handleError,
    clearError,
    retryOperation,
    getUserFriendlyMessage,
    isNetworkError,
    isRetryable,
  }
}
