# Simple Focus App Offline v1 — Plan

## Vision
Build a standalone, no-auth, offline-first focus app with a terminal/DOS look and feel.

## Product goals
- Open from a public route with no authentication.
- Run fully offline after first visit.
- Be installable like a separate app.
- Keep the code split into reusable components and composables.
- Keep the structure versioned so future versions can live beside `ver1`.
- Persist all user data locally.
- Support JSON export/import.
- Keep task changes safe by requiring confirmation before destructive actions.

## UX summary
- Full-screen black background.
- Current active task centered at the top in very clear focus text.
- 10-minute default timer for the first version.
- Minimal command-panel style controls instead of normal forms.
- Timeline log below the timer with visual state markers.
- Clicking a timeline entry can resume the task after confirmation.
- When a timer ends, show action choices:
  - break 5 minutes then continue
  - continue same task
  - start new task and mark old task done
  - add notes and continue later

## Data model
Store everything client-side in localStorage for v1.

### Core entities
- `settings`
  - timer presets
  - install hints
  - display preferences
- `tasks`
  - id
  - title
  - status (`active`, `done`, `paused`, `needs_continue`)
  - notes
  - createdAt / updatedAt
- `sessions`
  - id
  - taskId
  - startedAt
  - endedAt
  - plannedMinutes
  - actualSeconds
  - outcome
- `timeline`
  - id
  - type
  - taskId
  - label
  - detail
  - timestamp

## Folder structure
All source files live inside:
- `resources/js/Pages/myclass2026/features/simple_focus_app_offline/ver1/`

Suggested structure:
- `Index.vue` — page entry
- `components/` — UI pieces
- `composables/` — state and timer logic
- `lib/` — storage helpers and JSON import/export helpers
- `plan/` — documentation

## Reusability strategy
- Keep state logic in composables, not in the page.
- Keep the timeline, timer, task composer, and action dialog isolated.
- Keep serialization helpers separate so future versions can reuse them.
- Keep button / terminal UI primitives generic.

## Offline / install strategy
- Register a service worker from the page so the app works independently of global app settings.
- Expose a manifest for installability.
- Cache the app route and static shell for offline reloads.
- Preserve app data in localStorage so the app still opens with current state offline.

## Safety rules
- Ask for confirmation before changing the active task, completing a task, or clearing data.
- Always preserve notes/history in the timeline.
- Export/import must never silently overwrite data.

## Milestones
1. Create planning docs and versioned folder structure.
2. Build the reusable offline state layer.
3. Implement the DOS-style page UI and timer workflow.
4. Add export/import and persistence.
5. Wire a public route, manifest, and offline caching.
6. Verify versioning and future extension points.

## v1 scope limit
- Single default timer preset: 10 minutes.
- One active focus task at a time.
- Local-only storage.
- Simple JSON import/export.
- No backend sync yet.

## Future versions
- Multiple timer presets.
- Better notes editor.
- Session analytics.
- Keyboard shortcuts.
- Cross-device sync.
- Enhanced install/onboarding flow.
