# Group Quiz Reusable Player Plan

## Scope

Build a reusable Group Quiz player area under `v7/quizgroupv1` by extracting the current v7 Group Quiz flow into a versioned structure. This plan covers documentation and future implementation only; no runtime behavior changes should happen until approved.

## Current Source Flow

| Current Area | File | Responsibility |
|---|---|---|
| Toolbar trigger | `../components/Toolbar.vue` | Opens Groups and Group Quiz from the Interactive dropdown |
| Group setup | `../components/GroupSetupModal.vue` | Creates/edits groups, scores, QR data, settings |
| Quiz generator | `../components/GroupQuizGenerator.vue` | Builds Group MCQ slide data and injects it into presentation slides |
| Quiz player | `../components/InteractiveGroupMCQ.vue` | Runs the teacher-facing group MCQ player, answer selection, grading, sound, scanner behavior |
| Element routing | `../components/ElementNode.vue` | Renders `InteractiveGroupMCQ` for `element.type === 'group-mcq'` |
| Leaderboard | `../components/LeaderboardSlide.vue`, `../components/LeaderboardOverlay.vue` | Shows group scores |
| State | `../stores/gameStore.js`, `../stores/uiStore.js` | Stores group data, quiz generator modal state, leaderboard state, scores |

## Assumptions

- Version 1 should preserve the current v7 Group Quiz behavior as closely as possible.
- Existing v7 presentation pages should remain stable while the reusable player is developed.
- `quizgroupv1/index.vue` will be the test/player page for the reusable Group Quiz player.
- A dropdown will choose the player version.
- The selected version will persist in `localStorage` and reload automatically.
- Initial dropdown option is `Version 1`.
- Future versions should be added without rewriting the test page.

## Proposed Structure

```txt
quizgroupv1/
├── index.vue
├── GROUP_QUIZ_REUSABLE_PLAYER_PLAN.md
├── GROUP_QUIZ_REUSABLE_PLAYER_TASK_LIST.md
├── components/
│   ├── GroupQuizPlayerShell.vue
│   ├── GroupQuizVersionSelector.vue
│   └── version1/
│       ├── GroupQuizPlayerV1.vue
│       ├── GroupQuizSetupV1.vue
│       ├── GroupQuizGeneratorV1.vue
│       ├── GroupQuizLeaderboardV1.vue
│       └── components/
│           ├── QuestionDisplay.vue
│           ├── GroupSelector.vue
│           ├── AnswerOptions.vue
│           ├── QuizControls.vue
│           └── ScorePanel.vue
├── composables/
│   ├── useGroupQuizVersion.js
│   └── useGroupQuizLocalSettings.js
└── stores/
    └── groupQuizPlayerStore.js
```

## Version Selection Design

- The shell owns the selected version.
- The selector shows all registered versions.
- The selected version is saved to:

```txt
myclass2026.groupQuizPlayer.selectedVersion
```

- On load:
  1. Read selected version from `localStorage`.
  2. If valid, load it.
  3. If missing or invalid, fallback to `version1`.

## Version Registry

The version registry should be simple and explicit:

| Key | Label | Component |
|---|---|---|
| `version1` | `Version 1` | `GroupQuizPlayerV1.vue` |

Future versions can be added by appending to the registry only.

## Implementation Strategy

### Phase 1: Documentation

Create this plan and the task list first. Stop before coding until the user approves.

### Phase 2: Safe Copy

Copy the current working Group Quiz implementation into `version1` files with minimum changes. Fix only imports and names required to run.

### Phase 3: Shell and Dropdown

Create a shell that renders the selected version and includes a version dropdown.

### Phase 4: Test Page

Use `quizgroupv1/index.vue` as the player test page. It imports only the shell and allows testing the selected version.

### Phase 5: Optional Main Integration

After the test page is stable, decide whether `../components/ElementNode.vue` should route `group-mcq` elements through the reusable shell.

## Success Criteria

- [ ] `quizgroupv1/index.vue` renders a reusable Group Quiz player shell.
- [ ] Version dropdown shows `Version 1`.
- [ ] Selected version persists in `localStorage`.
- [ ] Reload restores the saved selected version.
- [ ] Version 1 preserves current v7 Group Quiz behavior.
- [ ] Existing `../Index.vue` and toolbar flow remain working during migration.
- [ ] Future versions can be registered without rewriting the page.

## Risks

| Risk | Mitigation |
|---|---|
| Current player is large and tightly coupled | Start with copy-first approach, then extract gradually |
| Broken relative imports after copying | Verify imports file-by-file before running |
| Store coupling with `gameStore.js` | Keep existing store behavior until replacement is proven |
| Regressions in main presentation | Do not wire into main `ElementNode.vue` until test page works |

## Stop Condition

Do not begin implementation until the user confirms this plan.
