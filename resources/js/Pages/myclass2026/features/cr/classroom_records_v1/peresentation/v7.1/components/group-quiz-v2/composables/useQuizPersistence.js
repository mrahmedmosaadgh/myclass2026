import { watch } from 'vue';
import { useGameStore } from '../../../stores/gameStore';

const STORAGE_KEY = 'presentation-v7.1-group-quiz-session';

/**
 * Composable to persist/restore group quiz state to localStorage.
 * Call once at app init to restore, then watch auto-saves.
 */
export function useQuizPersistence() {
  const gameStore = useGameStore();

  function saveSession() {
    try {
      const payload = {
        groups: gameStore.groups,
        gameSettings: gameStore.gameSettings,
        questionHistory: gameStore.questionHistory,
        sessionId: gameStore.sessionId,
        accessCode: gameStore.accessCode,
        sessionStatus: gameStore.sessionStatus,
        participants: gameStore.participants,
        timestamp: Date.now()
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    } catch {
      // localStorage quota exceeded or private mode
    }
  }

  function restoreSession() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return false;
      const data = JSON.parse(raw);
      if (!data) return false;

      if (Array.isArray(data.groups)) {
        gameStore.groups = data.groups;
      }
      if (data.gameSettings) {
        Object.assign(gameStore.gameSettings, data.gameSettings);
      }
      if (data.questionHistory) {
        gameStore.questionHistory = data.questionHistory;
      }
      if (data.sessionId) gameStore.sessionId = data.sessionId;
      if (data.accessCode) gameStore.accessCode = data.accessCode;
      if (data.sessionStatus) gameStore.sessionStatus = data.sessionStatus;
      if (Array.isArray(data.participants)) gameStore.participants = data.participants;

      return true;
    } catch {
      return false;
    }
  }

  function clearSession() {
    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch {
      // ignore
    }
  }

  // Auto-save whenever relevant state changes
  watch(
    () => [
      gameStore.groups.map((g) => ({ id: g.id, score: g.score, name: g.name })),
      gameStore.questionHistory,
      gameStore.gameSettings.allowNegativeScore
    ],
    () => saveSession(),
    { deep: true }
  );

  return { saveSession, restoreSession, clearSession };
}
