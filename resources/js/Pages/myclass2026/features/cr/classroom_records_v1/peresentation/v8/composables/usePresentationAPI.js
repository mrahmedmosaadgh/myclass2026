import { ref } from 'vue'

const API_BASE = '/api/v8-presentations'

function getCsrfToken() {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}

export function usePresentationAPI() {
  const loading = ref(false)
  const error = ref(null)

  async function savePresentation(title, description, presentationData) {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`${API_BASE}/save`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-XSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({
          title,
          description,
          presentation_data: presentationData,
        }),
      })

      if (!response.ok) {
        throw new Error('Failed to save presentation')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function listPresentations() {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`${API_BASE}/`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      })

      if (!response.ok) {
        throw new Error('Failed to load presentations')
      }

      const data = await response.json()
      return data.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function loadPresentation(id) {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`${API_BASE}/${id}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      })

      if (!response.ok) {
        throw new Error('Failed to load presentation')
      }

      const data = await response.json()
      return data.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deletePresentation(id) {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`${API_BASE}/${id}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-XSRF-TOKEN': getCsrfToken(),
        },
      })

      if (!response.ok) {
        throw new Error('Failed to delete presentation')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updatePresentation(id, title, description, presentationData) {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`${API_BASE}/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-XSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({
          title,
          description,
          presentation_data: presentationData,
        }),
      })

      if (!response.ok) {
        throw new Error('Failed to update presentation')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function loadSharedPresentation(shareToken) {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`${API_BASE}/shared/${shareToken}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      })

      if (!response.ok) {
        throw new Error('Failed to load shared presentation')
      }

      const data = await response.json()
      return data.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function submitStudentAttempt(shareToken, studentIdentifier, quizAttempts, totalScore, totalQuestions) {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`${API_BASE}/shared/${shareToken}/attempt`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-XSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({
          student_identifier: studentIdentifier,
          quiz_attempts: quizAttempts,
          total_score: totalScore,
          total_questions: totalQuestions,
        }),
      })

      if (!response.ok) {
        throw new Error('Failed to submit attempt')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getStatistics(id) {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`${API_BASE}/${id}/statistics`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      })

      if (!response.ok) {
        throw new Error('Failed to load statistics')
      }

      const data = await response.json()
      return data.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getAttemptHistory(id) {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`${API_BASE}/${id}/attempts`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      })

      if (!response.ok) {
        throw new Error('Failed to load attempt history')
      }

      const data = await response.json()
      return data.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    savePresentation,
    updatePresentation,
    listPresentations,
    loadPresentation,
    deletePresentation,
    loadSharedPresentation,
    submitStudentAttempt,
    getStatistics,
    getAttemptHistory,
  }
}
