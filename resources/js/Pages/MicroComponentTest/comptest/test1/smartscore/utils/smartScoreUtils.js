/**
 * Utility functions for Smart Score visualization
 */

/**
 * Get color based on score percentage
 * @param {number} score - Current score (0-100)
 * @returns {string} CSS color string
 */
export function getScoreColor(score) {
  if (score >= 85) return '#4CAF50'; // Green - Master
  if (score >= 70) return '#FF9800'; // Orange - Advanced
  if (score >= 50) return '#FFC107'; // Yellow - Intermediate
  return '#F44336'; // Red - Beginner
}

/**
 * Get mastery level based on score
 * @param {number} score - Current score (0-100)
 * @returns {string} Mastery level name
 */
export function getMasteryLevel(score) {
  if (score >= 85) return 'Master';
  if (score >= 70) return 'Advanced';
  if (score >= 50) return 'Intermediate';
  return 'Beginner';
}

/**
 * Format score for display
 * @param {number} score - Score value
 * @param {boolean} showPercentage - Whether to show percentage symbol
 * @returns {string} Formatted score string
 */
export function formatScore(score, showPercentage = true) {
  if (showPercentage) {
    return `${Math.round(score)}%`;
  }
  return Math.round(score).toString();
}

/**
 * Calculate improvement from previous score
 * @param {number} currentScore - Current score
 * @param {number} previousScore - Previous score
 * @returns {string} Improvement string (e.g., "+12", "-5")
 */
export function calculateImprovement(currentScore, previousScore) {
  if (previousScore === undefined || previousScore === null) {
    return '';
  }
  
  const diff = currentScore - previousScore;
  return diff >= 0 ? `+${diff}` : `${diff}`;
}

/**
 * Get badge color based on mastery level
 * @param {string} masteryLevel - Mastery level name
 * @returns {string} Badge color class
 */
export function getBadgeColor(masteryLevel) {
  switch (masteryLevel) {
    case 'Master': return 'bg-green-500 text-white';
    case 'Advanced': return 'bg-orange-500 text-white';
    case 'Intermediate': return 'bg-yellow-500 text-gray-900';
    case 'Beginner': return 'bg-red-500 text-white';
    default: return 'bg-gray-500 text-white';
  }
}

/**
 * Normalize smart score data
 * @param {Object} data - Raw smart score data
 * @returns {Object} Normalized data
 */
export function normalizeSmartScoreData(data) {
  return {
    currentScore: data.currentScore || 0,
    maxScore: data.maxScore || 100,
    masteryLevel: data.masteryLevel || getMasteryLevel(data.currentScore || 0),
    previousScore: data.previousScore,
    improvement: data.improvement || calculateImprovement(data.currentScore || 0, data.previousScore),
    visual: {
      showPercentage: data.visual?.showPercentage ?? true,
      showMasteryBadge: data.visual?.showMasteryBadge ?? true,
      showImprovement: data.visual?.showImprovement ?? true,
      colorScheme: data.visual?.colorScheme ?? 'default'
    }
  };
}