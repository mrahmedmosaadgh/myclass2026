# Simple Focus App Offline v1 — Task List

## Phase 1 — Foundation
- [ ] Create the `ver1` folder structure.
- [ ] Add a public no-auth route for the app.
- [ ] Add planning documents in `plan/`.
- [ ] Add a manifest and offline registration strategy.

## Phase 2 — Core state
- [ ] Build a reusable local persistence composable.
- [ ] Build a timer composable with start, pause, resume, reset, and finish states.
- [ ] Define task, session, and timeline models.
- [ ] Add JSON export and import helpers.

## Phase 3 — UI components
- [ ] Build a DOS-style shell layout.
- [ ] Build the task composer.
- [ ] Build the timer panel.
- [ ] Build the timeline log.
- [ ] Build the end-of-timer action chooser.
- [ ] Build the import/export controls.

## Phase 4 — Behavior
- [ ] Require confirmation before changing task state.
- [ ] Require confirmation before resuming from timeline.
- [ ] Save every task event into the timeline.
- [ ] Surface clear visual feedback for running, paused, and completed states.

## Phase 5 — Offline / install
- [ ] Register the service worker from the page.
- [ ] Provide a manifest for standalone install.
- [ ] Cache the route and static shell for offline reloads.
- [ ] Verify the page opens without auth.

## Phase 6 — Validation
- [ ] Check the page renders with a black DOS-style UI.
- [ ] Check the 10-minute timer workflow.
- [ ] Check export and import round-trip.
- [ ] Check timeline resume flow.
- [ ] Check future version path isolation under `ver1`.

## Notes for later versions
- [ ] Add more timer presets.
- [ ] Add richer analytics.
- [ ] Add cloud sync when ready.
