import { ref } from 'vue';

export function useBMScore() {
  const calculateLevel = (score) => {
    if (score >= 90) return 'Expert';
    if (score >= 75) return 'Advanced';
    if (score >= 50) return 'Proficient';
    if (score >= 25) return 'Developing';
    return 'Beginner';
  };

  const getDomainColor = (domainScore) => {
    if (domainScore >= 80) return 'positive';
    if (domainScore >= 50) return 'warning';
    return 'negative';
  };

  return {
    calculateLevel,
    getDomainColor
  };
}
