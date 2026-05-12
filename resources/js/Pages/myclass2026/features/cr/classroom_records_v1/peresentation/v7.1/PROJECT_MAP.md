# PROJECT_MAP.md — Group Quiz Feature (v7 Presentation)

## [TECH_STACK]
- Vue 3 (Composition API, `<script setup>`)
- Pinia (`defineStore` with function syntax)
- Quasar Framework (global imports in `app.js`)
- ECharts + vue-echarts (Leaderboard)
- html2canvas + jsPDF (QR Print)
- Web Audio API (Sound effects)
- LocalStorage (Persistence layer)

## [SYSTEM_FLOW]
1. Teacher opens Group Setup → CRUD groups / import JSON / print QR codes
2. Teacher opens Quiz Generator → AI prompt → paste JSON → preview → inject slides
3. Teacher enters Present Mode → unlocks quiz → groups submit (QR scan or future: remote)
4. Teacher selects group → assigns answer → grades → scores update
5. Leaderboard slide auto-appended → shows ECharts bar chart

## [V2 ARCHITECTURE] — Implemented May 2026
```
v7.1/components/group-quiz-v2/
├── GroupQuizV2.vue                  # Main MCQ orchestrator (~160 lines)
├── composables/
│   ├── useQuizSound.js              # Web Audio API (extracted)
│   ├── useQuizGrading.js            # Grading logic with Quasar dialogs
│   ├── useQrCodec.js                # QR encode/decode helpers
│   └── useQuizPersistence.js        # localStorage auto-save/restore
├── components/
│   ├── QuestionHeader.vue           # Question text + EditableMath
│   ├── OptionCard.vue               # Single MCQ option with badges
│   ├── OptionGrid.vue               # 2-column responsive grid
│   ├── GroupSelector.vue            # Group pill chips (select/clear)
│   ├── QuizControlBar.vue           # Start/Lock/Grade/Practice/Replay
│   ├── GradingResultBanner.vue      # Post-grade results banner
│   ├── QrScannerPanel.vue           # Visual scanner buffer display
│   └── QrPrintSheet.vue             # Reusable A4 QR print layout
├── setup/
│   ├── GroupSetupV2.vue             # Root setup dialog (q-tabs)
│   ├── GroupCard.vue                # Single group row (name/color/score)
│   ├── GroupList.vue                # CRUD list with inline edit
│   ├── ScoringSettings.vue          # Points config (correct/wrong/negative)
│   └── JsonImportDialog.vue         # JSON import with validation
├── generator/
│   ├── GroupQuizGeneratorV2.vue     # Root generator (q-stepper)
│   ├── PromptBuilder.vue            # Topic/count/difficulty form
│   ├── JsonPasteArea.vue            # Textarea + auto-fix
│   ├── QuestionPreviewCard.vue      # Single question preview
│   └── QuestionPreviewList.vue      # Parsed question cards
└── leaderboard/
    ├── LeaderboardV2.vue            # Full standings + progress bars + chart
    └── PodiumDisplay.vue            # Top-3 podium medals
```

## [WIRING]
- `stores/uiStore.js` → `useGroupQuizV2` flag + `toggleGroupQuizV2()`
- `components/ElementNode.vue` → Conditional V2/V1 rendering
- `components/Toolbar.vue` → "Use V2 Modern UI" toggle in Interactive dropdown
- `Index.vue` → Imports V2 dialogs, calls `restoreSession()` on mount
- `app.js` → Added 14 Quasar component global imports

## [ORPHANS & PENDING]
- `[P0-1]` Student digital submission interface (future: remote session)
- `[P3-3]` Full mobile responsive pass on GroupQuizV2 (partial: Quasar grid used)
- `[P4-3]` Inline question editing in generator preview (edit mode exists on cards)

## [RESOLVED]
- `[P0-2]` Direct mutations → GroupSetupV2 uses store actions
- `[P1-1]` Practice mode isolated via `isPracticeMode` flag in GroupQuizV2
- `[P1-2]` Scoring configurable in ScoringSettings tab
- `[P2-1]` QR print logic extracted to reusable `QrPrintSheet.vue`
- `[P2-2]` Persistence via `useQuizPersistence.js` with auto-save watch
- `[P3-2]` Scanner visual buffer in `QrScannerPanel.vue`
- `[P4-1]` Event listeners properly cleaned up in GroupQuizV2 `onUnmounted`
- `[P4-2]` QR payload robust parsing in `useQrCodec.js`
- `[P4-4]` Leaderboard title element present in LeaderboardV2
