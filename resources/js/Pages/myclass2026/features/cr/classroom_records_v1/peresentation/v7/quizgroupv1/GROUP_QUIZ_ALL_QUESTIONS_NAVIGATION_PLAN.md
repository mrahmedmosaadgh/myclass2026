# Group Quiz All Questions Navigation Plan

## Objective

Update the reusable Group Quiz player under `quizgroupv1/ver1` so it can show a full Group Quiz session in one player view, with all questions available through clear navigation.

This plan is planning-only until user confirms coding.

## Source Flow

In Presentation Ver7:

- `Interactive > Groups` opens `GroupSetupModal.vue`.
- `Interactive > Group Quiz` opens `GroupQuizGenerator.vue`.
- Group Quiz generation calls `generateQuestionElements(parsedQuestions, 'new', 'v3')` from `useAIPaste.js`.
- Each generated question becomes a `group-mcq` slide element with `questionData`.
- Current slide rendering uses `ElementNode.vue` and `InteractiveGroupMCQ.vue`.

## Current Data Shape

Generated `group-mcq` elements use:

```js
{
  id: 'el-...',
  type: 'group-mcq',
  questionData: {
    question: '...',
    options: [
      { id: 'A', text: '...' }
    ],
    correctId: 'A'
  }
}
```

Group state uses:

```js
{
  id: 'g1',
  name: 'Group A',
  score: 0,
  color: '#ef4444'
}
```

## Assumptions

- The current Ver7 presentation builder flow must not be broken.
- The reusable player page remains separate from the main Ver7 builder for now.
- Version selection remains saved with `myclass2026.groupQuizPlayer.selectedVersion`.
- Ver1 should support multi-question navigation before full QR/scanner/audio parity.
- The reusable player should fallback to sample questions if no real session data exists.

## Implementation Strategy

### Phase 1: Normalize Quiz Data

Add an adapter to normalize Ver7 slide data into reusable player data.

Suggested file:

```txt
ver1/adapters/groupQuizSlideAdapter.js
```

Responsibilities:

- Accept slides or raw question objects.
- Extract `group-mcq` elements.
- Normalize `correctId` into a consistent internal field.
- Normalize option IDs/text.
- Return clean player questions.

### Phase 2: Player Session Composable

Add a session composable.

Suggested file:

```txt
ver1/composables/useGroupQuizPlayerSession.js
```

Responsibilities:

- Load quiz session from localStorage.
- Save current question index.
- Track answers per question and group.
- Track grading status per question.
- Reset session state.
- Fallback to sample data.

Suggested localStorage keys:

```txt
myclass2026.groupQuizPlayer.session.v1
myclass2026.groupQuizPlayer.selectedQuestionIndex.v1
```

### Phase 3: Navigation Components

Add focused UI components.

Suggested files:

```txt
ver1/components/QuestionNavigator.vue
ver1/components/QuestionOverview.vue
ver1/components/QuestionProgressBar.vue
```

Responsibilities:

- Previous/Next navigation.
- Question chips/cards.
- Answered/graded/unanswered status.
- Clear visual progress.

### Phase 4: Update Player Layout

Update:

```txt
ver1/GroupQuizPlayerV1.vue
```

New behavior:

- Load `questions` array instead of one sample question.
- Display the active question.
- Show all question navigation in one view.
- Keep group selector, answer options, score panel, and controls.
- Grade current question safely.
- Move between questions without losing answers.

### Phase 5: Optional Ver7 Export Bridge

After multi-question player works, optionally update:

```txt
v7/components/GroupQuizGenerator.vue
```

Possible behavior:

- Save generated quiz and groups into localStorage.
- Notify teacher using Quasar notify.
- Optionally provide/open reusable player route.

Reusable player route:

```txt
/classroom-records/presentation/builder-v7/group-quiz-player
```

## Proposed UI

Main view:

- Header: quiz/session title and question count.
- Main column: active question, options, controls.
- Side column: scores and all-question overview.
- Navigation: Previous, Next, question chips, progress bar.

Question statuses:

- `Not answered`
- `Answered`
- `Graded`

## Files To Add

```txt
ver1/adapters/groupQuizSlideAdapter.js
ver1/composables/useGroupQuizPlayerSession.js
ver1/components/QuestionNavigator.vue
ver1/components/QuestionOverview.vue
ver1/components/QuestionProgressBar.vue
```

## Files To Edit

```txt
ver1/GroupQuizPlayerV1.vue
ver1/data/sampleQuiz.js
GROUP_QUIZ_REUSABLE_PLAYER_TASK_LIST.md
PROJECT_MAP.md
```

Optional later:

```txt
../components/GroupQuizGenerator.vue
../components/Toolbar.vue
```

## Verification

- Open reusable player route.
- Confirm multiple questions render.
- Confirm question navigation works.
- Confirm selected group answer is saved per question.
- Confirm grading updates score.
- Confirm reload restores selected version.
- Run `npm run build`.

## Risks

- Ver7 presentation state may not persist across routes, so localStorage bridge is safest.
- Current `InteractiveGroupMCQ.vue` includes QR/audio/scanner behavior that should not be copied all at once.
- Existing data uses `correctId`; current sample uses `correctOptionId`, so normalization is required.

## Recommended First Coding Scope

Start with:

1. Multi-question sample data.
2. Session composable.
3. Navigation components.
4. Player layout update.
5. Task list and project map update.
6. Build verification.

Then connect to generated Ver7 quiz data in a second step.

## Awaiting Confirmation

No coding should start until the user confirms which scope to implement:

- Option A: Multi-question player/navigation with sample data first.
- Option B: Multi-question player/navigation plus localStorage bridge.
- Option C: Also modify Ver7 generator to export/open generated quiz in reusable player.
