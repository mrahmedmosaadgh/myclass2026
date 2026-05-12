import { computed } from 'vue';
import { useQuasar } from 'quasar';
import { useGameStore } from '../../../stores/gameStore';

/**
 * Composable for group quiz grading logic.
 * Requires element.questionData with correctId.
 * Returns grading helpers and a grade() method.
 */
export function useQuizGrading(props) {
  const gameStore = useGameStore();
  const $q = useQuasar();

  const qHistory = computed(() => {
    return gameStore.questionHistory[props.element?.id] || { groupAnswers: {}, status: 'locked_in' };
  });

  const isGraded = computed(() => qHistory.value.status === 'graded');

  function executeGrading(answeredGroupIds, ansObj) {
    let anyRight = false;
    let anyWrong = false;

    answeredGroupIds.forEach((gId) => {
      const optId = ansObj[gId];
      if (optId === props.element?.questionData?.correctId) {
        gameStore.updateGroupScore(gId, gameStore.gameSettings.correctPoints);
        anyRight = true;
      } else {
        if (gameStore.gameSettings.allowNegativeScore) {
          gameStore.updateGroupScore(gId, gameStore.gameSettings.wrongPoints || -5);
        }
        anyWrong = true;
      }
    });

    // Mark as graded via store action
    gameStore.questionHistory[props.element.id] = { ...qHistory.value, status: 'graded' };

    return { anyRight, anyWrong };
  }

  function gradeGroups(playSoundFn) {
    if (isGraded.value) return;

    const ansObj = qHistory.value.groupAnswers;
    const answeredGroupIds = Object.keys(ansObj);

    if (answeredGroupIds.length === 0) {
      $q.notify({ type: 'warning', message: 'No groups have answered yet!', position: 'top' });
      return;
    }

    // Check for missing groups
    const missingGroups = gameStore.groups.filter(
      (g) => !answeredGroupIds.includes(g.id.toString())
    );

    if (missingGroups.length > 0) {
      const missingNames = missingGroups.map((g) => g.name).join(', ');
      $q.dialog({
        title: 'Missing Answers',
        message: `Wait! The following groups haven't answered yet: <strong>${missingNames}</strong>.<br><br>Do you want to proceed and grade anyway?`,
        html: true,
        cancel: true,
        persistent: true,
        ok: { label: 'Grade Anyway', color: 'negative', flat: true },
        cancel: { label: 'Wait for them', color: 'primary' },
        style: 'border-radius: 12px'
      }).onOk(() => {
        const result = executeGrading(answeredGroupIds, ansObj);
        if (playSoundFn) {
          if (result.anyRight) playSoundFn('correct');
          else playSoundFn('incorrect');
        }
      });
    } else {
      const result = executeGrading(answeredGroupIds, ansObj);
      if (playSoundFn) {
        if (result.anyRight) playSoundFn('correct');
        else playSoundFn('incorrect');
      }
    }
  }

  return { qHistory, isGraded, gradeGroups };
}
