# Group Quiz V2 — Layout Improvement & Modern UI Plan

## Current State

**7 core files**, ~3,500 lines:

| File | Lines | Responsibility |
|------|-------|----------------|
| `stores/gameStore.js` | 158 | Group/score state, question history, session |
| `components/GroupSetupModal.vue` | 877 | Group CRUD, JSON import, QR print |
| `components/InteractiveGroupMCQ.vue` | 1,192 | Teacher MCQ interaction, grading, sound |
| `components/GroupQuizGenerator.vue` | 566 | AI prompt builder, JSON parse, preview |
| `components/LeaderboardSlide.vue` | 291 | ECharts bar chart visualization |
| `components/LeaderboardOverlay.vue` | ~300 | Floating overlay |
| `PrintGroupQrCodes.vue` | 309 | Standalone print page |

**Problems to solve:**
1. Monolithic components (InteractiveGroupMCQ = 1,192 lines)
2. UI feels dated — custom CSS over Quasar components
3. Layout not responsive (no mobile support)
4. No reusable sub-components
5. Mixed concerns (sound logic, QR logic, grading logic in one file)

---

## V2 Goals

1. **Same features** — zero feature regression
2. **Modern Quasar UI** — `q-card`, `q-btn`, `q-chip`, `q-badge`, `q-tabs`, `q-dialog`, `q-stepper`, `q-linear-progress`
3. **Reusable components** — small, focused, testable
4. **Clean layout** — responsive grid, proper spacing, visual hierarchy
5. **Composable extraction** — separate sound, QR, grading logic

---

## Architecture — V2 File Structure

```
v7.1/components/group-quiz-v2/
├── GroupQuizV2.vue                  # Root orchestrator (replaces InteractiveGroupMCQ)
├── components/
│   ├── QuizControlBar.vue           # Start/Lock/Grade/Practice/Replay buttons
│   ├── GroupSelector.vue            # Group pill chips for selecting active group
│   ├── OptionCard.vue               # Single MCQ option with badges + hover state
│   ├── OptionGrid.vue              # Grid of OptionCards (2-col layout)
│   ├── QuestionHeader.vue           # Question text + math rendering
│   ├── GradingResultBanner.vue      # Post-grade correct/incorrect feedback
│   ├── QrScannerPanel.vue           # QR scanning with visual buffer
│   └── ScoreBar.vue                 # Mini score display per group
├── generator/
│   ├── GroupQuizGeneratorV2.vue     # Root generator dialog
│   ├── PromptBuilder.vue            # Topic/count/difficulty form
│   ├── JsonPasteArea.vue            # Textarea + auto-fix + code fence strip
│   ├── QuestionPreviewList.vue      # Parsed question cards with edit
│   └── QuestionPreviewCard.vue      # Single question preview
├── setup/
│   ├── GroupSetupV2.vue             # Root setup dialog
│   ├── GroupList.vue                # CRUD list with inline edit
│   ├── GroupCard.vue                # Single group row (name, color, score, actions)
│   ├── ScoringSettings.vue          # Points config (correct/wrong/negative toggle)
│   ├── JsonImportDialog.vue         # JSON import with validation
│   └── QrPrintSheet.vue             # Reusable QR print layout
├── leaderboard/
│   ├── LeaderboardV2.vue            # Main leaderboard (ECharts + podium)
│   └── PodiumDisplay.vue            # Top-3 podium medals
└── composables/
    ├── useQuizSound.js              # Web Audio API sound effects (extracted)
    ├── useQuizGrading.js            # Grading logic (extracted from InteractiveGroupMCQ)
    ├── useQrCodec.js                # QR encode/decode helpers
    └── useQuizPersistence.js        # localStorage save/restore session
```

---

## Milestone Breakdown

### M1: Composable Extraction (Foundation)

**Goal:** Extract reusable logic from InteractiveGroupMCQ into composables.

| Composable | Extracted From | Responsibility |
|------------|----------------|----------------|
| `useQuizSound.js` | InteractiveGroupMCQ L34-71 | `playSound(type)`, mute toggle |
| `useQuizGrading.js` | InteractiveGroupMCQ L139-196 | `gradeGroups()`, `executeGrading()`, missing-group dialog |
| `useQrCodec.js` | InteractiveGroupMCQ L219+ & GroupSetupModal | Parse/validate QR payload |
| `useQuizPersistence.js` | New | Save/restore game state to localStorage |

**Verification:** Import composables in existing InteractiveGroupMCQ → behavior unchanged.

---

### M2: GroupQuizV2 — MCQ Interaction (Core UI Rewrite)

**Goal:** Replace `InteractiveGroupMCQ.vue` (1,192 lines) with `GroupQuizV2.vue` + sub-components.

**UI Design:**

```
┌─────────────────────────────────────────────────────┐
│  [QuestionHeader]                                    │
│  "What is 5 + 5?"                                   │
├─────────────────────────────────────────────────────┤
│  [GroupSelector]                                     │
│  ○ Group A  ● Group B (active)  ○ Group C           │
├─────────────────────────────────────────────────────┤
│  [OptionGrid - 2 columns]                            │
│  ┌──────────────┐  ┌──────────────┐                 │
│  │ A) 7         │  │ B) 10        │                 │
│  │   [GrpA chip]│  │              │                 │
│  └──────────────┘  └──────────────┘                 │
│  ┌──────────────┐  ┌──────────────┐                 │
│  │ C) 12        │  │ D) 15        │                 │
│  └──────────────┘  └──────────────┘                 │
├─────────────────────────────────────────────────────┤
│  [QuizControlBar]                                    │
│  [▶ Start] [🔒 Lock] [✓ Grade] [🔄 Replay] [🔊]   │
└─────────────────────────────────────────────────────┘
```

**Quasar Components Used:**
- `q-card` + `q-card-section` — option cards with elevation
- `q-chip` — group badges on options
- `q-btn` with icons — control bar
- `q-btn-toggle` — practice/real mode switch
- `q-badge` — group count indicators
- `q-tooltip` — action hints
- `q-separator` — visual sections
- `q-slide-transition` — reveal grading results

**Verification:** All existing interactions work: select group → click option → grade → score update → replay.

---

### M3: GroupSetupV2 — Setup Dialog

**Goal:** Replace `GroupSetupModal.vue` (877 lines) with modular setup.

**UI Design:**
- `q-dialog` with `q-tabs` (Setup | QR Codes | Scoring)
- Setup tab: `q-list` with `q-item` per group, inline `q-input` for name, `q-color` for color
- Scoring tab: `q-toggle` for negative scoring, `q-input` for point values
- QR tab: Reusable `QrPrintSheet.vue`

**Quasar Components Used:**
- `q-dialog` maximized on mobile
- `q-tabs` / `q-tab-panels` — section navigation
- `q-input` — group name, score
- `q-btn` with `q-color` popup — color picker
- `q-list` / `q-item` — group list
- `q-slide-item` — swipe to delete (mobile)
- `q-fab` — floating add group button

---

### M4: GroupQuizGeneratorV2 — AI Prompt Builder

**Goal:** Replace `GroupQuizGenerator.vue` (566 lines) with cleaner stepper flow.

**UI Design:**
- `q-stepper` (3 steps): Configure → Paste AI Response → Preview & Inject
- Step 1: `q-input` (topic), `q-slider` (count), `q-btn-toggle` (difficulty)
- Step 2: `q-input` type textarea with syntax highlighting hint
- Step 3: `QuestionPreviewList` with inline edit capability

**Quasar Components Used:**
- `q-stepper` / `q-step` — guided flow
- `q-slider` — question count
- `q-btn-toggle` — difficulty selector
- `q-editor` or `q-input` textarea — JSON paste
- `q-banner` — error/success messages
- `q-expansion-item` — per-question preview expand

---

### M5: LeaderboardV2

**Goal:** Polish leaderboard with podium display and title.

**UI additions:**
- `PodiumDisplay.vue` — top 3 with medal icons + animated score
- Title element on slide
- `q-linear-progress` — per-group score bar alternative view

---

### M6: Layout & Responsiveness

**Goal:** Responsive layout for all screen sizes.

**Approach:**
- Use Quasar's `$q.screen` breakpoints
- `q-responsive` for aspect ratio maintenance
- Mobile: stack options vertically, bottom sheet for controls
- Desktop: 2-column grid, side panel for groups
- `q-page` / `q-layout` patterns where applicable

---

## Key Design Decisions

| Decision | Rationale |
|----------|-----------|
| Sub-components in `group-quiz-v2/` folder | Colocation, easy import, no pollution of parent `components/` |
| Composables per concern | Testable, reusable across v7.1 and future versions |
| Keep `gameStore.js` mostly intact | Store API is clean; only add persistence layer |
| Quasar-first styling | No custom CSS for layout/spacing; use Quasar utility classes |
| `EditableMath` reused as-is | Already works, no need to rewrite |
| Progressive replacement | V2 components can coexist with V1 during migration |

---

## Dependencies (already available)

- Quasar Framework (global import in app.js)
- ECharts + vue-echarts
- html2canvas + jsPDF (QR print)
- Pinia

---

## Risk Analysis

| Risk | Mitigation |
|------|-----------|
| Feature regression during rewrite | Keep V1 files intact, V2 is parallel; switch via flag |
| Quasar component not globally imported | Check `app.js` before using; add if missing |
| Large diff hard to review | Milestone-based delivery, each independently testable |
| Sound API browser compat | Composable already handles AudioContext resume |

---

## Implementation Order

1. `useQuizSound.js` + `useQuizGrading.js` + `useQrCodec.js` (composables)
2. `OptionCard.vue` + `OptionGrid.vue` + `QuestionHeader.vue` (atoms)
3. `GroupSelector.vue` + `QuizControlBar.vue` (molecules)
4. `GroupQuizV2.vue` (orchestrator)
5. `GroupSetupV2.vue` + sub-components
6. `GroupQuizGeneratorV2.vue` + stepper sub-components
7. `LeaderboardV2.vue` + `PodiumDisplay.vue`
8. Layout responsiveness pass
9. Wire into `Index.vue` (replace V1 imports)
10. Remove V1 files after verification

---

## Success Criteria

- [ ] All V1 features work identically in V2
- [ ] No component exceeds 200 lines
- [ ] All composables are independently testable
- [ ] Responsive on 320px–1920px
- [ ] Quasar components used for all UI (no raw HTML buttons/inputs)
- [ ] No `window.alert` / `window.confirm` / `window.prompt`
- [ ] Score persistence survives page refresh
