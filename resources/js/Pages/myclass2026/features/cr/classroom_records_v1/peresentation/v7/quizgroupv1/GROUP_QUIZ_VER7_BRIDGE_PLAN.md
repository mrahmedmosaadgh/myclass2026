# Group Quiz Ver7 to Reusable Player Bridge Plan

## Objective

Connect the Presentation Ver7 Group Quiz Generator flow to the reusable player so teachers can export generated quizzes into the standalone player session.

## Current State

- Ver7 `GroupQuizGenerator.vue` generates `group-mcq` elements into presentation slides.
- Reusable player currently only shows sample questions.
- Reusable player already has `useGroupQuizPlayerSession` that supports loading from localStorage.

## Strategy

1. Add a `saveGroupQuizSession()` helper in `ver1/composables/useGroupQuizPlayerSession.js`.
2. In `GroupQuizGenerator.vue`, after successful generation, offer an export to player action.
3. Save generated questions and current groups into localStorage.
4. Reusable player reads this automatically on next load.

## Assumptions

- Ver7 presentation state (`gameStore.groups`) should be included with exported quiz.
- Reusable player should keep sample fallback if no exported session exists.
- No breaking changes to existing Ver7 toolbar/generator flow.

## Files to Add/Edit

- `ver1/composables/useGroupQuizPlayerSession.js` — add export/save function.
- `../components/GroupQuizGenerator.vue` — add export action after generation.
- `ver1/GroupQuizPlayerV1.vue` — auto-reload on session change (if needed).

## Verification

- Generate quiz in Ver7.
- Export to player.
- Open reusable player route.
- Confirm real quiz data renders instead of sample data.

## Awaiting Confirmation

No — user said "ccontinue", proceed with implementation.
