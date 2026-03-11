/**
 * useBm2FirebaseSync - Firebase Realtime Database Sync for BM2 Assessments
 * 
 * Handles real-time synchronization of assessment progress, leaderboards,
 * and instant feedback using Firebase Realtime Database.
 */

import { ref, set, update, onValue, remove } from 'firebase/database';
import { realtimeDb } from '@/firebase/bm2-config';

export function useBm2FirebaseSync() {
  /**
   * Sync assessment progress to Firebase in real-time
   * @param {string} sessionId - Unique assessment session ID
   * @param {object} data - Assessment progress data
   */
  const syncAssessmentProgress = (sessionId, data) => {
    try {
      const updates = {
        currentQuestion: data.currentQuestion,
        score: data.score,
        totalQuestions: data.totalQuestions,
        lastAnswer: data.lastAnswer,
        timeElapsed: data.timeElapsed,
        lastUpdate: Date.now(),
        isActive: true
      };

      return set(ref(realtimeDb, `bm2_live_assessments/${sessionId}`), updates);
    } catch (error) {
      console.error('Error syncing assessment progress:', error);
      throw error;
    }
  };

  /**
   * Subscribe to instant feedback for a student
   * @param {string} studentId - Student ID
   * @param {function} callback - Function to call when feedback received
   */
  const subscribeToFeedback = (studentId, callback) => {
    try {
      const feedbackRef = ref(realtimeDb, `bm2_instant_feedback/${studentId}`);
      
      return onValue(feedbackRef, (snapshot) => {
        const data = snapshot.val();
        if (data) {
          callback(data);
          
          // Clear feedback after reading (one-time notifications)
          if (data.type === 'celebration') {
            setTimeout(() => {
              remove(ref(realtimeDb, `bm2_instant_feedback/${studentId}`));
            }, 1000);
          }
        }
      });
    } catch (error) {
      console.error('Error subscribing to feedback:', error);
      throw error;
    }
  };

  /**
   * Update leaderboard in real-time
   * @param {string} classId - Class/Group ID
   * @param {string} studentId - Student ID
   * @param {object} studentData - Student performance data
   */
  const updateLeaderboard = (classId, studentId, studentData) => {
    try {
      const updates = {
        name: studentData.name,
        score: studentData.score,
        badges: studentData.badges || 0,
        avatar: studentData.avatar || 'default',
        lastUpdated: Date.now()
      };

      return update(ref(realtimeDb, `bm2_leaderboards/${classId}/${studentId}`), updates);
    } catch (error) {
      console.error('Error updating leaderboard:', error);
      throw error;
    }
  };

  /**
   * Trigger celebration animation for a student
   * @param {string} studentId - Student ID
   * @param {string} type - Celebration type (confetti, stars, etc.)
   * @param {string} message - Encouragement message
   */
  const triggerCelebration = (studentId, type = 'confetti', message = 'Great job!') => {
    try {
      return set(ref(realtimeDb, `bm2_instant_feedback/${studentId}`), {
        type: 'celebration',
        celebration: type,
        message: message,
        timestamp: Date.now()
      });
    } catch (error) {
      console.error('Error triggering celebration:', error);
      throw error;
    }
  };

  /**
   * Listen to live assessments (for teacher dashboard)
   * @param {string} classId - Class ID to monitor
   * @param {function} callback - Function to call with updates
   */
  const subscribeToLiveAssessments = (classId, callback) => {
    try {
      const assessmentsRef = ref(realtimeDb, 'bm2_live_assessments');
      
      return onValue(assessmentsRef, (snapshot) => {
        const data = snapshot.val();
        if (data) {
          // Filter active assessments for this class
          const activeAssessments = Object.entries(data)
            .filter(([sessionId, assessment]) => {
              return assessment.isActive && 
                     assessment.lastUpdate > (Date.now() - 300000); // Last 5 minutes
            })
            .map(([sessionId, assessment]) => ({
              sessionId,
              ...assessment
            }));
          
          callback(activeAssessments);
        } else {
          callback([]);
        }
      });
    } catch (error) {
      console.error('Error subscribing to live assessments:', error);
      throw error;
    }
  };

  /**
   * Mark assessment as complete
   * @param {string} sessionId - Assessment session ID
   */
  const completeAssessment = (sessionId) => {
    try {
      return update(ref(realtimeDb, `bm2_live_assessments/${sessionId}`), {
        isActive: false,
        completedAt: Date.now()
      });
    } catch (error) {
      console.error('Error completing assessment:', error);
      throw error;
    }
  };

  return {
    syncAssessmentProgress,
    subscribeToFeedback,
    updateLeaderboard,
    triggerCelebration,
    subscribeToLiveAssessments,
    completeAssessment
  };
}
