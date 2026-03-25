import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useGameStore = defineStore('presentation-game', () => {
  const gameSettings = ref({
    correctPoints: 10,
    wrongPoints: 0, // Fallback if negative score isn't used
    allowNegativeScore: false // if true, wrongPoints could be mathematically typed or hardcoded to -5
  });

  // Default Hybrid Auto-Generation
  const groups = ref([
    { id: 'g1', name: 'Group A', score: 0, color: '#ef4444' }, // red
    { id: 'g2', name: 'Group B', score: 0, color: '#3b82f6' }, // blue
    { id: 'g3', name: 'Group C', score: 0, color: '#10b981' }, // green
  ]);

  const isLeaderboardOpen = ref(false);
  const isGroupSetupOpen = ref(false);

  // Tracks question metadata for grading/firebase
  // Format: { [elementId]: { groupAnswers: { [groupId]: optionId }, status: 'locked_in' | 'graded' } }
  const questionHistory = ref({});

  // Remote Session State
  const sessionId = ref(null);
  const accessCode = ref(null);
  const sessionStatus = ref('offline'); // offline, waiting, active, completed

  // Group Management Actions
  function setSession(id, code, status = 'waiting') {
    sessionId.value = id;
    accessCode.value = code;
    sessionStatus.value = status;
  }
  function addGroup(name, color) {
    groups.value.push({
      id: 'g' + Date.now() + Math.random().toString(36).substr(2, 5),
      name: name || `Group ${groups.value.length + 1}`,
      score: 0,
      color: color || '#8b5cf6'
    });
  }

  function removeGroup(id) {
    groups.value = groups.value.filter(g => g.id !== id);
  }

  function updateGroupName(id, newName) {
    const group = groups.value.find(g => g.id === id);
    if (group) group.name = newName;
  }

  function updateGroupColor(id, newColor) {
    const group = groups.value.find(g => g.id === id);
    if (group) group.color = newColor;
  }

  function updateGroupScore(id, delta) {
    const group = groups.value.find(g => g.id === id);
    if (group) group.score += delta;
  }

  function resetScores() {
    groups.value.forEach(g => g.score = 0);
    questionHistory.value = {};
  }

  // Answer Logging (Future-proofed for Firebase)
  function logGroupAnswer(elementId, groupId, optionId) {
    if (!questionHistory.value[elementId]) {
      questionHistory.value[elementId] = { groupAnswers: {}, status: 'locked_in' };
    }
    questionHistory.value[elementId].groupAnswers[groupId] = optionId;
  }

  function clearGroupAnswer(elementId, groupId) {
    if (questionHistory.value[elementId] && questionHistory.value[elementId].groupAnswers[groupId]) {
      delete questionHistory.value[elementId].groupAnswers[groupId];
    }
  }

  function getGroupAnswer(elementId, groupId) {
    if (!questionHistory.value[elementId]) return null;
    return questionHistory.value[elementId].groupAnswers[groupId] || null;
  }

  return {
    gameSettings,
    groups,
    isLeaderboardOpen,
    isGroupSetupOpen,
    questionHistory,
    sessionId,
    accessCode,
    sessionStatus,

    setSession,
    addGroup,
    removeGroup,
    updateGroupName,
    updateGroupColor,
    updateGroupScore,
    resetScores,

    logGroupAnswer,
    clearGroupAnswer,
    getGroupAnswer
  };
});
