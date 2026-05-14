# Group Quiz Reusable Player Task List

## Phase 1: Planning and Mapping

- [x] Create `GROUP_QUIZ_REUSABLE_PLAYER_PLAN.md`
- [x] Create `GROUP_QUIZ_REUSABLE_PLAYER_TASK_LIST.md`
- [x] Map current Group Quiz source files
- [x] Confirm target folder structure
- [x] Confirm implementation should start

## Phase 2: Folder Structure

- [x] Create `ver1/components/`
- [x] Create `ver1/composables/`
- [x] Create `ver1/data/`
- [ ] Create `ver1/stores/` if needed after full behavior port

## Phase 3: Version 1 Copy

- [x] Create small reusable `ver1/GroupQuizPlayerV1.vue` foundation
- [x] Create `ver1/components/QuestionDisplay.vue`
- [x] Create `ver1/components/GroupSelector.vue`
- [x] Create `ver1/components/AnswerOptions.vue`
- [x] Create `ver1/components/QuizControls.vue`
- [x] Create `ver1/components/ScorePanel.vue`
- [x] Create `ver1/data/sampleQuiz.js`
- [ ] Port full current `../components/InteractiveGroupMCQ.vue` behavior into smaller Ver1 components
- [ ] Add setup/generator wrappers after player shell is stable

## Phase 4: Reusable Shell and Version Selector

- [x] Create `ver1/GroupQuizPlayerShell.vue`
- [x] Create `ver1/components/GroupQuizVersionSelector.vue`
- [x] Create `ver1/composables/useGroupQuizVersion.js`
- [ ] Create `ver1/composables/useGroupQuizLocalSettings.js` if settings grow beyond version selection
- [x] Add version registry with `ver1`
- [x] Render selected version dynamically
- [x] Save selected version to `localStorage`
- [x] Restore selected version on page reload
- [x] Fallback to `ver1` when saved version is missing or invalid

## Phase 5: Store and State Isolation

- [ ] Review current `../stores/gameStore.js` quiz-related state
- [ ] Decide whether Version 1 reuses `gameStore.js` or copies state into `stores/groupQuizPlayerStore.js`
- [ ] Create `stores/groupQuizPlayerStore.js` only if needed
- [ ] Avoid breaking current `../Index.vue` flow

## Phase 6: Test Page Integration

- [x] Update `index.vue` to import `GroupQuizPlayerShell.vue`
- [x] Render the version dropdown
- [x] Render Version 1 player
- [x] Add sample data path for initial test player
- [x] Verify dropdown version state is wired to localStorage
- [x] Verify reload restoration logic exists

## Phase 6.5: Route Integration

- [x] Add authenticated route `/classroom-records/presentation/builder-v7/group-quiz-player`
- [x] Render `v7/quizgroupv1/index`
- [x] Run production build and verify route/page imports compile
- [ ] Open route in browser and verify page loads

## Phase 7: Optional Main Presentation Integration

- [ ] Review `../Index.vue` integration points
- [ ] Review `../components/ElementNode.vue` group MCQ render path
- [ ] Decide if main presentation should use reusable shell now or later
- [ ] If approved, wire `element.type === 'group-mcq'` through reusable shell
- [ ] Verify old Group Quiz toolbar flow still opens correctly

## Phase 8: Verification

- [ ] Open Group Quiz test page
- [ ] Confirm `Version 1` is visible in dropdown
- [ ] Select `Version 1`
- [ ] Reload and confirm `Version 1` remains selected
- [ ] Open Groups flow
- [ ] Open Group Quiz generator flow
- [ ] Generate a group MCQ slide
- [x] Render sample group MCQ player
- [x] Select group answer in sample player
- [x] Grade answer in sample player
- [x] Confirm sample score update logic exists
- [ ] Confirm leaderboard still displays scores

## Pending Decisions

- [x] Should `version1` initially reuse the original `gameStore.js`? Decision: no for the first shell; sample-local state keeps the test page isolated.
- [x] Should copied Version 1 files be refactored immediately or kept identical first? Decision: start with small reusable foundation, then port behavior incrementally.
- [ ] Should main `v7/Index.vue` use the reusable shell in this first implementation?
- [ ] Should this reusable player later be moved/shared with `v7.1`?

## Future Updates and Ver2

- [ ] Complete Ver1 behavior parity with the current Group Quiz player
- [ ] Extract remaining monolithic behavior into reusable Ver1 components
- [ ] Create `ver2/` only after Ver1 is stable and verified
- [ ] Register Ver2 in the version dropdown
- [ ] Keep Ver1 available as a fallback after Ver2 is introduced

## Status

- **Current Status**: Ver1 reusable shell, sample player, localStorage version setting, project map, and route are created. Full current Group Quiz behavior port and Ver2 are pending.
- **Build Verification**: `npm run build` passed on 2026-05-14.
- **Last Updated**: 2026-05-14
