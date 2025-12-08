import axios from 'axios'

/**
 * RewardSystemPointAction Service Module
 * Centralized API service for managing student behaviors, points, and rewards
 * Handles all backend communication for the reward system
 */

// Base API configuration
const API_BASE = '/api'
const endpoints = {
  behaviors: `${API_BASE}/behaviors`,
  studentBehaviors: `${API_BASE}/student-behaviors`,
  pointActions: `${API_BASE}/student-behaviors-point-actions`,
  leaderboard: `${API_BASE}/leaderboard`,
  studentSummary: (studentId) => `${API_BASE}/student-behaviors/${studentId}/summary`,
  pointActionStatistics: (studentId) => `${API_BASE}/student-behaviors/${studentId}/statistics`,
  attendance: `${API_BASE}/student-attendance`,
  attendanceBatch: `${API_BASE}/student-attendance/batch`,
}

// ============================================
// POINT ACTIONS - Core point management
// ============================================

/**
 * Add a new point action for a student behavior
 * @param {number} studentBehaviorsId - ID of the student behavior record
 * @param {number} value - Point value (positive or negative)
 * @param {number|null} reasonId - Optional behavior reason ID
 * @param {string} note - Optional note about the action
 * @returns {Promise<Object>} Created point action
 */
export async function addPoint(studentBehaviorsId, value, reasonId = null, note = '') {
  try {
    const payload = {
      student_behaviors_id: studentBehaviorsId,
      value: parseInt(value),
      reason_id: reasonId ? parseInt(reasonId) : null,
      note: note || null,
    }

    const response = await axios.post(endpoints.pointActions, payload)
    return {
      success: true,
      data: response.data,
      message: `Point ${value > 0 ? 'added' : 'deducted'} successfully`,
    }
  } catch (error) {
    console.error('Error adding point:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to add point',
      details: error,
    }
  }
}

/**
 * Cancel an existing point action
 * @param {number} actionId - ID of the point action to cancel
 * @param {string} cancelReason - Reason for cancellation
 * @returns {Promise<Object>} Updated point action
 */
export async function cancelPoint(actionId, cancelReason) {
  try {
    const response = await axios.post(
      `${endpoints.pointActions}/${actionId}/cancel`,
      { cancel_reason: cancelReason }
    )
    return {
      success: true,
      data: response.data,
      message: 'Point action canceled successfully',
    }
  } catch (error) {
    console.error('Error canceling point:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to cancel point',
      details: error,
    }
  }
}

/**
 * Restore (un-cancel) a previously canceled point action
 * @param {number} actionId - ID of the point action to restore
 * @returns {Promise<Object>} Updated point action
 */
export async function restorePoint(actionId) {
  try {
    const response = await axios.post(
      `${endpoints.pointActions}/${actionId}/restore`
    )
    return {
      success: true,
      data: response.data,
      message: 'Point action restored successfully',
    }
  } catch (error) {
    console.error('Error restoring point:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to restore point',
      details: error,
    }
  }
}

/**
 * Delete a point action permanently
 * @param {number} actionId - ID of the point action to delete
 * @returns {Promise<Object>} Deletion result
 */
export async function deletePoint(actionId) {
  try {
    const response = await axios.delete(
      `${endpoints.pointActions}/${actionId}`
    )
    return {
      success: true,
      data: response.data,
      message: 'Point action deleted successfully',
    }
  } catch (error) {
    console.error('Error deleting point:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to delete point',
      details: error,
    }
  }
}

// ============================================
// STUDENT BEHAVIOR RECORDS
// ============================================

/**
 * Create a new student behavior record
 * @param {Object} behaviorData - Student behavior data
 * @returns {Promise<Object>} Created behavior record
 */
export async function createStudentBehavior(behaviorData) {
  try {
    const payload = {
      school_id: behaviorData.schoolId,
      student_behaviors_mains_id: behaviorData.studentBehaviorsMainsId,
      student_id: behaviorData.studentId,
      attend: behaviorData.attend || null,
      points_plus: behaviorData.pointsPlus || 0,
      points_minus: behaviorData.pointsMinus || 0,
      points_details: behaviorData.pointsDetails || '',
      notes: behaviorData.notes || null,
    }

    const response = await axios.post(endpoints.studentBehaviors, payload)
    return {
      success: true,
      data: response.data,
      message: 'Student behavior record created successfully',
    }
  } catch (error) {
    console.error('Error creating student behavior:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to create behavior record',
      details: error,
    }
  }
}

/**
 * Get student behavior summary (total points, positive, negative)
 * @param {number} studentId - Student ID
 * @returns {Promise<Object>} Behavior summary
 */
export async function getStudentSummary(studentId) {
  try {
    const response = await axios.get(endpoints.studentSummary(studentId))
    return {
      success: true,
      data: response.data,
    }
  } catch (error) {
    console.error('Error fetching student summary:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to fetch student summary',
      details: error,
    }
  }
}

/**
 * Get all point actions for a specific student behavior
 * @param {number} studentBehaviorId - Student behavior record ID
 * @returns {Promise<Object>} Array of point actions
 */
export async function getPointActions(studentBehaviorId) {
  try {
    const response = await axios.get(endpoints.pointActions, {
      params: { student_behaviors_id: studentBehaviorId },
    })
    return {
      success: true,
      data: response.data,
    }
  } catch (error) {
    console.error('Error fetching point actions:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to fetch point actions',
      details: error,
    }
  }
}

/**
 * Get statistics for a student behavior
 * @param {number} studentBehaviorId - Student behavior record ID
 * @returns {Promise<Object>} Statistics with positive/negative/total counts
 */
export async function getPointStatistics(studentBehaviorId) {
  try {
    const response = await axios.get(
      `${endpoints.pointActions}/statistics/${studentBehaviorId}`
    )
    return {
      success: true,
      data: response.data,
    }
  } catch (error) {
    console.error('Error fetching point statistics:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to fetch statistics',
      details: error,
    }
  }
}

// ============================================
// BEHAVIORS & ACTIONS
// ============================================

/**
 * Fetch all available behaviors
 * @returns {Promise<Object>} Array of behaviors with types (positive/negative)
 */
export async function fetchBehaviors() {
  try {
    const response = await axios.get(endpoints.behaviors)
    return {
      success: true,
      data: Array.isArray(response.data) ? response.data : [],
    }
  } catch (error) {
    console.error('Error fetching behaviors:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to fetch behaviors',
      data: [],
      details: error,
    }
  }
}

/**
 * Apply a behavior action to multiple students
 * @param {Array<number>} studentIds - Array of student IDs
 * @param {number} behaviorId - ID of the behavior to apply
 * @param {Object} options - Additional options (notes, date, etc.)
 * @returns {Promise<Object>} Result of applying behavior to all students
 */
export async function applyBehaviorToStudents(studentIds, behaviorId, options = {}) {
  try {
    const results = []
    const errors = []

    // Safety: extract ID if behaviorId is an object
    const actualBehaviorId = typeof behaviorId === 'object' ? behaviorId.id : behaviorId

    console.log('🎯 applyBehaviorToStudents called with:', {
      studentCount: studentIds.length,
      behaviorIdInput: behaviorId,
      actualBehaviorId: actualBehaviorId,
      behaviorIdType: typeof behaviorId,
    })

    if (!actualBehaviorId || isNaN(actualBehaviorId)) {
      console.error('❌ Invalid behavior ID:', behaviorId)
      return {
        success: false,
        error: 'Invalid behavior ID provided',
      }
    }

    for (const studentId of studentIds) {
      const payload = {
        student_id: studentId,
        behavior_id: actualBehaviorId,
        date: options.date || new Date().toISOString().split('T')[0],
        period_code: options.periodCode || null,
        notes: options.notes || null,
      }

      console.log('═══════════════════════════════════════════════════════════')
      console.log('📤 Sending behavior application request')
      console.log('   Student ID:', studentId)
      console.log('   Behavior ID:', actualBehaviorId)
      console.log('   Date:', payload.date)
      console.log('   Period Code:', payload.period_code)
      console.log('   Payload:', JSON.stringify(payload, null, 2))
      console.log('═══════════════════════════════════════════════════════════')

      try {
        const response = await axios.post(
          `${API_BASE}/student-behaviors/quick-create`,
          payload
        )
        console.log('✅ SUCCESS - Behavior applied for student:', studentId)
        console.log('   Response Status:', response.status)
        console.log('   Response Data:', response.data)
        results.push({
          studentId,
          success: true,
          data: response.data,
        })
      } catch (err) {
        console.error('═══════════════════════════════════════════════════════════')
        console.error('❌ FAILED - Error applying behavior to student:', studentId)
        console.error('   HTTP Status:', err.response?.status, err.response?.statusText)
        console.error('   Error Message:', err.response?.data?.message)
        console.error('   Error Details:', err.response?.data?.error)
        console.error('   Validation Errors:', err.response?.data?.errors)
        console.error('   Full Response:', err.response?.data)
        console.error('   Sent Payload:', payload)
        console.error('═══════════════════════════════════════════════════════════')
        
        errors.push({
          studentId,
          success: false,
          error: err.response?.data?.message || 'Failed to apply behavior',
          fullError: err.response?.data,
        })
      }
    }

    console.log('🎯 BATCH RESULT:', {
      totalRequests: studentIds.length,
      successful: results.length,
      failed: errors.length,
      successRate: `${Math.round((results.length / studentIds.length) * 100)}%`,
    })

    return {
      success: errors.length === 0,
      results,
      errors,
      message: `Applied to ${results.length}/${studentIds.length} students`,
    }
  } catch (error) {
    console.error('❌ OUTER ERROR - Exception in applyBehaviorToStudents:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to apply behavior',
      details: error,
    }
  }
}

// ============================================
// LEADERBOARD
// ============================================

/**
 * Fetch leaderboard data for a classroom within date range
 * @param {Object} params - Query parameters
 * @param {number} params.classroomId - Classroom ID
 * @param {string} params.startDate - Start date (YYYY-MM-DD)
 * @param {string} params.endDate - End date (YYYY-MM-DD)
 * @param {number} params.limit - Max number of students (default: 10)
 * @returns {Promise<Object>} Leaderboard data
 */
export async function fetchLeaderboard({ classroomId, startDate, endDate, limit = 10 }) {
  try {
    const response = await axios.get(endpoints.leaderboard, {
      params: {
        classroom_id: classroomId,
        start_date: startDate,
        end_date: endDate,
        limit: Math.min(limit, 50), // Cap at 50
      },
    })

    return {
      success: true,
      data: Array.isArray(response.data) ? response.data : [],
    }
  } catch (error) {
    console.error('Error fetching leaderboard:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to fetch leaderboard',
      data: [],
      details: error,
    }
  }
}

/**
 * Get top students by period (week/month/all)
 * @param {Object} params - Query parameters
 * @param {number} params.classroomId - Classroom ID
 * @param {string} params.period - Period type: 'week', 'month', 'all'
 * @param {number} params.limit - Max number of results
 * @returns {Promise<Object>} Top students data
 */
export async function getTopStudentsByPeriod({ classroomId, period = 'week', limit = 5 }) {
  try {
    const now = new Date()
    let startDate

    switch (period) {
      case 'week':
        startDate = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
        break
      case 'month':
        startDate = new Date(now.getFullYear(), now.getMonth(), 1)
        break
      case 'all':
        startDate = new Date('2020-01-01')
        break
      default:
        startDate = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
    }

    const formatDate = (d) => d.toISOString().split('T')[0]

    return fetchLeaderboard({
      classroomId,
      startDate: formatDate(startDate),
      endDate: formatDate(now),
      limit,
    })
  } catch (error) {
    console.error('Error getting top students:', error)
    return {
      success: false,
      error: 'Failed to get top students',
      data: [],
      details: error,
    }
  }
}

// ============================================
// ATTENDANCE
// ============================================

/**
 * Update attendance for a student
 * @param {number} studentId - Student ID
 * @param {boolean} isPresent - Attendance status
 * @param {Object} options - Additional options
 * @returns {Promise<Object>} Updated attendance record
 */
export async function updateAttendance(studentId, isPresent, options = {}) {
  try {
    // Attempt to persist attendance to backend. If the endpoint does not
    // exist or fails, fall back to client-only response so the UI remains
    // responsive.
    const payload = {
      student_id: parseInt(studentId),
      attend: Boolean(isPresent),
      date: options.date || new Date().toISOString().split('T')[0],
      period_code: options.periodCode || null,
      classroom_id: options.classroomId || null,
    }

    try {
      const response = await axios.post(endpoints.attendance, payload)
      return {
        success: true,
        data: response.data,
        message: response.data?.message || `Student marked as ${isPresent ? 'present' : 'absent'}`,
      }
    } catch (err) {
      // If the server responds with 404 or the route isn't implemented,
      // gracefully fall back to a client-side success while returning
      // details so the caller can notify the user.
      console.warn('Attendance API call failed, falling back to client-only update:', err?.response?.status)
      return {
        success: true,
        data: {
          studentId: parseInt(studentId),
          isPresent: Boolean(isPresent),
          updatedAt: new Date().toISOString(),
        },
        message: `Student marked as ${isPresent ? 'present' : 'absent'} (local)`,
        warning: err.response?.data || err.message,
      }
    }
  } catch (error) {
    console.error('Error updating attendance:', error)
    return {
      success: false,
      error: 'Failed to update attendance',
      details: error,
    }
  }
}

/**
 * Batch update attendance for multiple students
 * @param {Object} attendanceData - Object with studentId as key, boolean as value
 * @returns {Promise<Object>} Batch update result
 */
export async function batchUpdateAttendance(attendanceData, options = {}) {
  try {
    // Validate input
    if (!attendanceData || typeof attendanceData !== 'object') {
      return {
        success: false,
        error: 'Invalid attendance data',
      }
    }

    const updates = Object.entries(attendanceData).map(([studentId, isPresent]) => ({
      student_id: parseInt(studentId),
      attend: Boolean(isPresent),
      // Include date/period_code/classroom_id so backend store() validation (which requires date)
      date: options.date || new Date().toISOString().split('T')[0],
      period_code: options.periodCode || null,
      classroom_id: options.classroomId || null,
      updated_at: new Date().toISOString(),
    }))

    // Try to persist batch update to backend if route exists
    try {
      const response = await axios.post(endpoints.attendanceBatch, { attendance: updates })
      return {
        success: true,
        data: response.data,
        message: response.data?.message || `Updated attendance for ${updates.length} students`,
      }
    } catch (err) {
      console.warn('Batch attendance API call failed, falling back to local update:', err?.response?.status)
      return {
        success: true,
        data: updates,
        message: `Updated attendance for ${updates.length} students (local)`,
        warning: err.response?.data || err.message,
      }
    }
  } catch (error) {
    console.error('Error batch updating attendance:', error)
    return {
      success: false,
      error: 'Failed to batch update attendance',
      details: error,
    }
  }
}

// ============================================
// HISTORY & UNDO
// ============================================

/**
 * Get recent point actions (history)
 * @param {Object} params - Query parameters
 * @returns {Promise<Object>} Recent actions
 */
export async function getRecentActions({ classroomId, date, limit = 10 }) {
  try {
    const response = await axios.get(`${API_BASE}/student-behaviors/recent-actions`, {
      params: {
        classroom_id: classroomId,
        date: date,
        limit: limit
      }
    })

    return {
      success: true,
      data: Array.isArray(response.data.data) ? response.data.data : []
    }
  } catch (error) {
    console.error('Error fetching recent actions:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to fetch recent actions',
      data: []
    }
  }
}

/**
 * Cancel (undo) a point action
 * @param {number} actionId - Action ID to cancel
 * @param {string} reason - Cancellation reason
 * @returns {Promise<Object>} Cancellation result
 */
export async function undoAction(actionId, reason = 'Undone by teacher') {
  try {
    const response = await axios.post(
      `${API_BASE}/student-behaviors/actions/${actionId}/cancel`,
      { cancel_reason: reason }
    )

    return {
      success: true,
      data: response.data.data,
      message: response.data.message || 'Action undone successfully'
    }
  } catch (error) {
    console.error('Error undoing action:', error)
    return {
      success: false,
      error: error.response?.data?.message || 'Failed to undo action'
    }
  }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Format point value for display
 * @param {number} value - Point value
 * @returns {string} Formatted string with sign and icon
 */
export function formatPointValue(value) {
  if (value > 0) {
    return `+${value} ⭐`
  } else if (value < 0) {
    return `${value} ⚠️`
  } else {
    return '0'
  }
}

/**
 * Get action type label
 * @param {string} actionType - Action type code
 * @returns {string} User-friendly label
 */
export function getActionTypeLabel(actionType) {
  const labels = {
    plus: 'Added',
    minus: 'Deducted',
    cancel: 'Canceled',
    revoke: 'Revoked',
  }
  return labels[actionType] || actionType
}

/**
 * Calculate total points from point actions array
 * @param {Array} actions - Array of point actions
 * @param {boolean} includeCancel - Include canceled actions (default: false)
 * @returns {number} Total points
 */
export function calculateTotalPoints(actions, includeCancel = false) {
  if (!Array.isArray(actions)) return 0

  return actions
    .filter((action) => includeCancel || !action.canceled)
    .reduce((total, action) => total + (action.value || 0), 0)
}

/**
 * Separate points into positive and negative
 * @param {Array} actions - Array of point actions
 * @returns {Object} { positive: number, negative: number }
 */
export function separatePoints(actions) {
  if (!Array.isArray(actions)) {
    return { positive: 0, negative: 0 }
  }

  const active = actions.filter((a) => !a.canceled)
  const positive = active
    .filter((a) => a.value > 0)
    .reduce((sum, a) => sum + a.value, 0)
  const negative = Math.abs(
    active
      .filter((a) => a.value < 0)
      .reduce((sum, a) => sum + a.value, 0)
  )

  return { positive, negative }
}

/**
 * Check if an action is still editable (not too old)
 * @param {Date|string} createdAt - Creation timestamp
 * @param {number} hoursLimit - Hours limit for editability (default: 24)
 * @returns {boolean} Whether action can be edited
 */
export function isActionEditable(createdAt, hoursLimit = 24) {
  try {
    const created = new Date(createdAt)
    const now = new Date()
    const hours = (now - created) / (1000 * 60 * 60)
    return hours < hoursLimit
  } catch {
    return false
  }
}

// ============================================
// ERROR HANDLING UTILITIES
// ============================================

/**
 * Get user-friendly error message
 * @param {Error} error - Error object
 * @returns {string} User-friendly message
 */
export function getUserFriendlyErrorMessage(error) {
  if (error.response?.status === 401) {
    return 'You are not authenticated. Please log in.'
  } else if (error.response?.status === 403) {
    return 'You do not have permission to perform this action.'
  } else if (error.response?.status === 404) {
    return 'The requested resource was not found.'
  } else if (error.response?.status === 422) {
    return 'Invalid data provided. Please check your input.'
  } else if (error.response?.status === 500) {
    return 'Server error. Please try again later.'
  } else if (error.message === 'Network Error') {
    return 'Network error. Please check your connection.'
  }
  return error.response?.data?.message || 'An unexpected error occurred.'
}

/**
 * Retry failed API calls with exponential backoff
 * @param {Function} fn - Async function to retry
 * @param {number} maxRetries - Maximum retry attempts (default: 3)
 * @param {number} delayMs - Initial delay in milliseconds (default: 1000)
 * @returns {Promise} Result from function
 */
export async function retryWithBackoff(fn, maxRetries = 3, delayMs = 1000) {
  let lastError

  for (let i = 0; i < maxRetries; i++) {
    try {
      return await fn()
    } catch (error) {
      lastError = error
      if (i < maxRetries - 1) {
        const delay = delayMs * Math.pow(2, i)
        await new Promise((resolve) => setTimeout(resolve, delay))
      }
    }
  }

  throw lastError
}

export default {
  // Point Actions
  addPoint,
  cancelPoint,
  restorePoint,
  deletePoint,

  // Student Behaviors
  createStudentBehavior,
  getStudentSummary,
  getPointActions,
  getPointStatistics,

  // Behaviors
  fetchBehaviors,
  applyBehaviorToStudents,

  // Leaderboard
  fetchLeaderboard,
  getTopStudentsByPeriod,

  // Attendance
  updateAttendance,
  batchUpdateAttendance,

  // History & Undo
  getRecentActions,
  undoAction,

  // Utilities
  formatPointValue,
  getActionTypeLabel,
  calculateTotalPoints,
  separatePoints,
  isActionEditable,
  getUserFriendlyErrorMessage,
  retryWithBackoff,
}
