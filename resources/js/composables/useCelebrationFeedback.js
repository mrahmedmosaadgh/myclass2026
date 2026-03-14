/**
 * useCelebrationFeedback - Reusable Celebration Feedback Hook
 * 
 * Provides easy-to-use methods for showing joyful, motivational feedback
 * to students during assessments and learning activities.
 */

import { ref } from 'vue';

export function useCelebrationFeedback() {
  const celebrationState = ref({
    isVisible: false,
    variant: 'default',
    title: '',
    message: '',
    icon: '',
    pointsEarned: 0,
    showScore: true,
    showStars: true,
    showConfetti: true,
    showButtons: false,
    autoHide: true,
    autoHideDelay: 2500,
    playSound: true,
    soundType: 'success',
    confettiIntensity: 'normal'
  });

  const callbacks = {
    onContinue: null,
    onClose: null,
    onHidden: null
  };

  /**
   * Show a celebration feedback
   * @param {Object} options - Celebration options
   */
  const showCelebration = (options = {}) => {
    celebrationState.value = {
      ...celebrationState.value,
      isVisible: true,
      ...options
    };
  };

  /**
   * Hide the celebration
   */
  const hideCelebration = () => {
    celebrationState.value.isVisible = false;
  };

  /**
   * Show success feedback (correct answer)
   * @param {number} points - Points earned
   * @param {string} customMessage - Custom message
   */
  const showSuccess = (points = 0, customMessage = '') => {
    showCelebration({
      variant: 'success',
      title: 'Correct! 🎉',
      message: customMessage || 'Excellent work!',
      icon: '✅',
      pointsEarned: points,
      showScore: points > 0,
      soundType: 'success',
      confettiIntensity: 'normal',
      autoHide: true,
      autoHideDelay: 2000
    });
  };

  /**
   * Show encouragement feedback (incorrect answer but motivational)
   * @param {string} correctAnswer - The correct answer
   */
  const showEncouragement = (correctAnswer = '') => {
    showCelebration({
      variant: 'default',
      title: 'Keep Going! 💪',
      message: correctAnswer ? `The correct answer was: ${correctAnswer}` : 'You\'ll get it next time!',
      icon: '🌟',
      showScore: false,
      soundType: 'success',
      confettiIntensity: 'low',
      autoHide: true,
      autoHideDelay: 3000
    });
  };

  /**
   * Show achievement unlocked
   * @param {string} achievementName - Name of achievement
   * @param {string} description - Achievement description
   */
  const showAchievement = (achievementName, description = '') => {
    showCelebration({
      variant: 'achievement',
      title: 'Achievement Unlocked! 🏆',
      message: `${achievementName}${description ? ' - ' + description : ''}`,
      icon: '🏆',
      showScore: false,
      showStars: true,
      stars: 5,
      soundType: 'achievement',
      confettiIntensity: 'high',
      autoHide: false,
      showButtons: true,
      showContinueButton: true,
      continueButtonText: 'Awesome! →'
    });
  };

  /**
   * Show combo/streak feedback
   * @param {number} streak - Current streak count
   * @param {number} bonusPoints - Bonus points earned
   */
  const showCombo = (streak = 0, bonusPoints = 0) => {
    showCelebration({
      variant: 'combo',
      title: `${streak}x Combo! 🔥`,
      message: bonusPoints > 0 ? `+${bonusPoints} bonus points!` : 'Keep it up!',
      icon: '🔥',
      showScore: bonusPoints > 0,
      pointsEarned: bonusPoints,
      showProgress: true,
      progressValue: Math.min(streak * 10, 100),
      progressLabel: `${streak} in a row!`,
      soundType: 'combo',
      confettiIntensity: 'high',
      autoHide: true,
      autoHideDelay: 2500
    });
  };

  /**
   * Show perfect answer feedback
   * @param {number} points - Points earned
   */
  const showPerfect = (points = 0) => {
    showCelebration({
      variant: 'perfect',
      title: 'Perfect! 💎',
      message: 'Absolutely flawless!',
      icon: '💎',
      pointsEarned: points,
      showScore: true,
      showStars: true,
      stars: 5,
      soundType: 'perfect',
      confettiIntensity: 'extreme',
      autoHide: true,
      autoHideDelay: 3000
    });
  };

  /**
   * Show milestone celebration
   * @param {string} milestone - Milestone description
   * @param {number} points - Bonus points
   */
  const showMilestone = (milestone, points = 0) => {
    showCelebration({
      variant: 'achievement',
      title: 'Milestone Reached! 🎯',
      message: milestone,
      icon: '🎯',
      pointsEarned: points,
      showScore: points > 0,
      soundType: 'achievement',
      confettiIntensity: 'high',
      autoHide: false,
      showButtons: true
    });
  };

  /**
   * Set event callbacks
   * @param {Object} handlers - Event handlers
   */
  const setCallbacks = (handlers) => {
    callbacks.onContinue = handlers.onContinue || null;
    callbacks.onClose = handlers.onClose || null;
    callbacks.onHidden = handlers.onHidden || null;
  };

  /**
   * Handle continue button click
   */
  const onContinue = () => {
    hideCelebration();
    if (callbacks.onContinue) {
      callbacks.onContinue();
    }
  };

  /**
   * Handle close button click
   */
  const onClose = () => {
    hideCelebration();
    if (callbacks.onClose) {
      callbacks.onClose();
    }
  };

  /**
   * Handle hidden event
   */
  const onHidden = () => {
    if (callbacks.onHidden) {
      callbacks.onHidden();
    }
  };

  return {
    // State
    celebrationState,
    
    // Methods
    showCelebration,
    hideCelebration,
    showSuccess,
    showEncouragement,
    showAchievement,
    showCombo,
    showPerfect,
    showMilestone,
    setCallbacks,
    onContinue,
    onClose,
    onHidden
  };
}
