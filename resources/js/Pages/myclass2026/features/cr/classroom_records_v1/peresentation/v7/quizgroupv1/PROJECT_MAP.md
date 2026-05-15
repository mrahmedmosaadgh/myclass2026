# PROJECT_MAP.md — Group Quiz Reusable Player

## Tech Stack

- Vue 3 Composition API
- Quasar global components
- Inertia page routing
- LocalStorage for selected player version

## Route

- `/classroom-records/presentation/builder-v7/group-quiz-player`
- Route name: `classroom-records.presentation.builder-v7.group-quiz-player`
- Inertia page: `myclass2026/features/cr/classroom_records_v1/peresentation/v7/quizgroupv1/index`

## Current Architecture

```txt
quizgroupv1/
├── index.vue
├── GROUP_QUIZ_REUSABLE_PLAYER_PLAN.md
├── GROUP_QUIZ_REUSABLE_PLAYER_TASK_LIST.md
├── GROUP_QUIZ_ALL_QUESTIONS_NAVIGATION_PLAN.md
├── PROJECT_MAP.md
└── ver1/
    ├── adapters/
    │   └── groupQuizSlideAdapter.js
    ├── GroupQuizPlayerShell.vue
    ├── GroupQuizPlayerV1.vue
    ├── components/
    │   ├── AnswerOptions.vue
    │   ├── GroupQuizVersionSelector.vue
    │   ├── GroupSelector.vue
    │   ├── QuestionNavigator.vue
    │   ├── QuestionOverview.vue
    │   ├── QuestionProgressBar.vue
    │   ├── QuestionDisplay.vue
    │   ├── QuizControls.vue
    │   └── ScorePanel.vue
    ├── composables/
    │   ├── useGroupQuizPlayerSession.js
    │   └── useGroupQuizVersion.js
    └── data/
        └── sampleQuiz.js
```

## System Flow

1. Teacher opens the route.
2. `index.vue` renders `GroupQuizPlayerShell`.
3. `GroupQuizPlayerShell` renders the version selector and selected player.
4. `useGroupQuizVersion` restores the selected version from `localStorage`.
5. Version 1 renders a multi-question reusable Group Quiz player.
6. `useGroupQuizPlayerSession` loads a stored player session or falls back to sample questions.
7. `QuestionNavigator`, `QuestionOverview`, and `QuestionProgressBar` provide all-question navigation.
8. Ver7 `GroupQuizGenerator` can export quiz and groups to the reusable player session via `saveGroupQuizSession`.

## LocalStorage

- Key: `myclass2026.groupQuizPlayer.selectedVersion`
- Default value: `ver1`
- Session key: `myclass2026.groupQuizPlayer.session.v1`
- Selected question key: `myclass2026.groupQuizPlayer.selectedQuestionIndex.v1`

## Orphans and Pending

- `[P2]` Port full current `InteractiveGroupMCQ.vue` behavior into smaller Ver1 components.
- `[P3]` Add setup/generator wrappers when player shell is stable.
- `[P4]` Create Ver2 after Ver1 behavior parity is complete.
- `[P5]` Browser verification pending for the new route and Ver7 export flow.
