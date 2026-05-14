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
├── PROJECT_MAP.md
└── ver1/
    ├── GroupQuizPlayerShell.vue
    ├── GroupQuizPlayerV1.vue
    ├── components/
    │   ├── AnswerOptions.vue
    │   ├── GroupQuizVersionSelector.vue
    │   ├── GroupSelector.vue
    │   ├── QuestionDisplay.vue
    │   ├── QuizControls.vue
    │   └── ScorePanel.vue
    ├── composables/
    │   └── useGroupQuizVersion.js
    └── data/
        └── sampleQuiz.js
```

## System Flow

1. Teacher opens the route.
2. `index.vue` renders `GroupQuizPlayerShell`.
3. `GroupQuizPlayerShell` renders the version selector and selected player.
4. `useGroupQuizVersion` restores the selected version from `localStorage`.
5. Version 1 currently renders a reusable sample Group Quiz player.

## LocalStorage

- Key: `myclass2026.groupQuizPlayer.selectedVersion`
- Default value: `ver1`

## Orphans and Pending

- `[P1]` Port full current `InteractiveGroupMCQ.vue` behavior into smaller Ver1 components.
- `[P2]` Add setup/generator wrappers when player shell is stable.
- `[P3]` Create Ver2 after Ver1 behavior parity is complete.
- `[P4]` Browser verification pending for the new route.
