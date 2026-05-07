/**
 * useAttendanceService - Centralized service for all attendance and points operations
 * 
 * This composable provides a single source of truth for all attendance and score updates
 * across all views. It handles business logic, confirmations, and state synchronization.
 */

import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import { useClassroomRecordsStore } from '@/stores/classroomRecords'

export function useAttendanceService() {
  const $q = useQuasar()
  const store = useClassroomRecordsStore()
  
  // Local state for pending operations
  const pendingOperations = ref(new Map())
  
  /**
   * Update attendance for a student with automatic confirmation logic
   */
  const updateAttendance = async (studentPeriodId, attendanceStatus, attendanceScore = null) => {
    // Find the student
    const student = store.sessionData?.students.find(s => s.student_period_id === studentPeriodId)
    if (!student) {
      console.error('Student not found:', studentPeriodId)
      return false
    }
    
    // Calculate attendance score if not provided
    if (attendanceScore === null) {
      attendanceScore = attendanceStatus === 'absent' ? 0 : (attendanceStatus === 'late' ? 3 : 5)
    }
    
    // Check if confirmation is needed (marking absent with points)
    if (attendanceStatus === 'absent' && student.period?.total_score > 0) {
      const confirmed = await showAbsenceConfirmation(student)
      if (!confirmed) return false
    }
    
    // Perform the update
    return await performAttendanceUpdate(studentPeriodId, attendanceStatus, attendanceScore)
  }
  
  /**
   * Update score for a specific category
   */
  const updateScore = async (studentPeriodId, mappingId, numericValue) => {
    // Find the student and score record
    const student = store.sessionData?.students.find(s => s.student_period_id === studentPeriodId)
    if (!student) {
      console.error('Student not found:', studentPeriodId)
      return false
    }
    
    const scoreRecord = student.scores?.find(s => s.mapping_id === mappingId)
    if (!scoreRecord) {
      console.error('Score record not found:', mappingId)
      return false
    }
    
    // Validate and clamp the value
    const maxValue = scoreRecord.max_value ?? 5
    const clampedValue = Math.max(0, Math.min(maxValue, Number(numericValue) || 0))
    
    // Perform the update
    return await performScoreUpdate(studentPeriodId, mappingId, clampedValue)
  }
  
  /**
   * Bulk update attendance for multiple students
   */
  const bulkUpdateAttendance = async (studentPeriodIds, attendanceStatus, attendanceScore = null) => {
    // Calculate attendance score if not provided
    if (attendanceScore === null) {
      attendanceScore = attendanceStatus === 'absent' ? 0 : (attendanceStatus === 'late' ? 3 : 5)
    }
    
    // Check if confirmation is needed for bulk absent
    if (attendanceStatus === 'absent') {
      const studentsWithPoints = studentPeriodIds.map(id => 
        store.sessionData?.students.find(s => s.student_period_id === id)
      ).filter(student => student && student.period?.total_score > 0)
      
      if (studentsWithPoints.length > 0) {
        const confirmed = await showBulkAbsenceConfirmation(studentsWithPoints)
        if (!confirmed) return false
      }
    }
    
    // Perform bulk updates
    const results = await Promise.all(
      studentPeriodIds.map(id => performAttendanceUpdate(id, attendanceStatus, attendanceScore))
    )
    
    return results.every(result => result)
  }
  
  /**
   * Bulk update scores for multiple students in the same category
   */
  const bulkUpdateScores = async (studentPeriodIds, mappingId, numericValue) => {
    // Validate and clamp the value
    const sampleStudent = store.sessionData?.students.find(s => s.scores?.find(score => score.mapping_id === mappingId))
    if (!sampleStudent) {
      console.error('No student found with mapping_id:', mappingId)
      return false
    }
    
    const scoreRecord = sampleStudent.scores.find(s => s.mapping_id === mappingId)
    const maxValue = scoreRecord?.max_value ?? 5
    const clampedValue = Math.max(0, Math.min(maxValue, Number(numericValue) || 0))
    
    // Perform bulk updates
    const results = await Promise.all(
      studentPeriodIds.map(id => performScoreUpdate(id, mappingId, clampedValue))
    )
    
    return results.every(result => result)
  }
  
  /**
   * Show confirmation dialog for marking individual student absent
   */
  const showAbsenceConfirmation = (student) => {
    return new Promise((resolve) => {
      const points = student.period?.total_score || 0
      const attendanceScore = student.period?.attendance_score || 0
      const otherPoints = points - attendanceScore
      
      // Build detailed message
      let message = `Student currently has ${points} points`
      
      if (points > 0) {
        message += `\n\n📊 Current Points:`
        message += `\n• Attendance: ${attendanceScore} points`
        if (otherPoints > 0) {
          message += `\n• Other categories: ${otherPoints} points`
        }
        message += `\n\n⚠️ Marking absent will:`
        message += `\n• Reset attendance to 0 points`
        message += `\n• Keep other category points`
        message += `\n• Total will become: ${otherPoints} points`
      }
      
      $q.dialog({
        title: '⚠️ Mark Student Absent?',
        message: message,
        html: true,
        ok: {
          label: '🗑️ Mark Absent',
          color: 'negative'
        },
        cancel: {
          label: '❌ Cancel',
          color: 'grey-7'
        },
        persistent: false,
        style: 'border-radius: 12px',
        'class': 'text-center'
      }).onOk(() => {
        resolve(true)
      }).onCancel(() => {
        resolve(false)
      })
    })
  }
  
  /**
   * Show confirmation dialog for bulk absent operation
   */
  const showBulkAbsenceConfirmation = (studentsWithPoints) => {
    return new Promise((resolve) => {
      // Set up bulk confirmation with detailed breakdown
      let message = `${studentsWithPoints.length} students have points that will be affected:\n\n`
      
      // Add breakdown for each student with points
      studentsWithPoints.forEach(student => {
        const attendanceScore = student.period?.attendance_score || 0
        const totalScore = student.period?.total_score || 0
        const otherPoints = totalScore - attendanceScore
        
        message += `👤 ${student.name}:\n`
        message += `  • Current: ${totalScore} points (Attendance: ${attendanceScore}, Other: ${otherPoints})\n`
        message += `  • After absent: ${otherPoints} points\n\n`
      })
      
      message += `⚠️ Total points lost: ${studentsWithPoints.reduce((sum, student) => sum + (student.period?.attendance_score || 0), 0)} attendance points`
      
      $q.dialog({
        title: '🗑️ Mark All Absent?',
        message: message,
        html: true,
        ok: {
          label: '🗑️ Mark All Absent',
          color: 'negative'
        },
        cancel: {
          label: '❌ Cancel',
          color: 'grey-7'
        },
        persistent: false,
        style: 'border-radius: 12px',
        'class': 'text-center'
      }).onOk(() => {
        resolve(true)
      }).onCancel(() => {
        resolve(false)
      })
    })
  }
  
  /**
   * Perform the actual attendance update
   */
  const performAttendanceUpdate = async (studentPeriodId, attendanceStatus, attendanceScore) => {
    try {
      // Use the store's existing method for optimistic updates
      store.updateStudentAttendance(studentPeriodId, attendanceStatus)
      
      // Update attendance score if provided
      if (attendanceScore !== null && attendanceStatus !== 'absent') {
        const student = store.sessionData?.students.find(s => s.student_period_id === studentPeriodId)
        if (student) {
          student.period.attendance_score = attendanceScore
          // Recalculate total
          const categoryScoresSum = student.scores.reduce(
            (sum, score) => sum + (score.numeric_value || 0),
            0
          )
          student.period.total_score = attendanceScore + categoryScoresSum
        }
      }
      
      // Mark as dirty for background save (this will be handled by the parent component)
      // The service focuses on business logic and state management
      
      return true
    } catch (error) {
      console.error('Failed to update attendance:', error)
      $q.notify({
        type: 'negative',
        message: 'Failed to update attendance',
        position: 'top'
      })
      return false
    }
  }
  
  /**
   * Perform the actual score update
   */
  const performScoreUpdate = async (studentPeriodId, mappingId, numericValue) => {
    try {
      // Use the store's existing method for optimistic updates
      store.updateStudentScore(studentPeriodId, mappingId, numericValue)
      
      // Mark as dirty for background save (this will be handled by the parent component)
      // The service focuses on business logic and state management
      
      return true
    } catch (error) {
      console.error('Failed to update score:', error)
      $q.notify({
        type: 'negative',
        message: 'Failed to update score',
        position: 'top'
      })
      return false
    }
  }
  
  /**
   * Get attendance score for a given status
   */
  const getAttendanceScore = (status) => {
    switch (status) {
      case 'absent': return 0
      case 'late': return 3
      case 'present': return 5
      default: return 5
    }
  }
  
  /**
   * Get attendance status from score
   */
  const getAttendanceStatus = (score) => {
    if (score === 0) return 'absent'
    if (score === 3) return 'late'
    return 'present'
  }
  
  // Computed properties for convenience
  const isUpdating = computed(() => pendingOperations.value.size > 0)
  
  return {
    // Core methods
    updateAttendance,
    updateScore,
    bulkUpdateAttendance,
    bulkUpdateScores,
    
    // Utility methods
    getAttendanceScore,
    getAttendanceStatus,
    
    // State
    isUpdating,
    pendingOperations
  }
}
