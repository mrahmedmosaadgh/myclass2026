/**
 * Classroom Records Pinia Store
 * 
 * Manages state for the entire Classroom Records system:
 * - Session context (classroom, subject, teacher, date, period)
 * - Student data and scores
 * - Loading and error states
 * - Auto-save state management
 */

import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';

export const useClassroomRecordsStore = defineStore('classroomRecords', () => {
  // Session Context
  const sessionContext = reactive({
    classroom_id: null,
    subject_id: null,
    teacher_id: null,
    date: new Date().toISOString().split('T')[0],
    day_number: 1,
    period_number: 1,
    period_code: '',
  });

  // Session Data
  const sessionData = ref(null);
  
  // State
  const loading = ref(false);
  const error = ref(null);
  const contextReady = ref(false);
  
  // Available Options (for standalone mode)
  const options = reactive({
    classrooms: [],
    subjects: [],
  });

  // Computed Properties
  const isContextComplete = () => {
    return !!(
      sessionContext.classroom_id &&
      sessionContext.subject_id &&
      sessionContext.teacher_id &&
      sessionContext.period_code
    );
  };

  // Actions
  const setSessionContext = (context) => {
    Object.assign(sessionContext, context);
  };

  const updateContextField = (field, value) => {
    sessionContext[field] = value;
  };

  const setSessionData = (data) => {
    sessionData.value = data;
    contextReady.value = true;
  };

  const setLoading = (isLoading) => {
    loading.value = isLoading;
  };

  const setError = (errorMsg) => {
    error.value = errorMsg;
    if (errorMsg) {
      contextReady.value = false;
    }
  };

  const setOptions = (classrooms, subjects) => {
    options.classrooms = classrooms;
    options.subjects = subjects;
  };

  const clearSession = () => {
    sessionContext.classroom_id = null;
    sessionContext.subject_id = null;
    sessionContext.period_code = '';
    sessionData.value = null;
    contextReady.value = false;
    error.value = null;
  };

  // Update student score (optimistic update)
  const updateStudentScore = (studentPeriodId, mappingId, numericValue) => {
    if (!sessionData.value?.students) return;

    const student = sessionData.value.students.find(
      s => s.student_period_id === studentPeriodId
    );

    if (student) {
      const score = student.scores.find(
        s => s.mapping_id === mappingId
      );

      if (score) {
        score.numeric_value = numericValue;
        // Recalculate total
        const attendanceScore = student.period.attendance_score || 0;
        const categoryScoresSum = student.scores.reduce(
          (sum, score) => sum + (score.numeric_value || 0),
          0
        );
        student.period.total_score = attendanceScore + categoryScoresSum;
      }
    }
  };

  // Update student attendance (optimistic update)
  const updateStudentAttendance = (studentPeriodId, attendanceStatus) => {
    if (!sessionData.value?.students) return;

    const student = sessionData.value.students.find(
      s => s.student_period_id === studentPeriodId
    );

    if (student) {
      student.period.attendance_status = attendanceStatus;
      
      // If absent, zero all scores
      if (attendanceStatus === 'absent') {
        student.scores.forEach(score => {
          score.numeric_value = 0;
        });
        student.period.total_score = 0;
      }
    }
  };

  return {
    // State
    sessionContext,
    sessionData,
    loading,
    error,
    contextReady,
    options,
    
    // Computed
    isContextComplete,
    
    // Actions
    setSessionContext,
    updateContextField,
    setSessionData,
    setLoading,
    setError,
    setOptions,
    clearSession,
    updateStudentScore,
    updateStudentAttendance,
  };
});
