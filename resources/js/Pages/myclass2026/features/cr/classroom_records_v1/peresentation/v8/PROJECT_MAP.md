# PROJECT_MAP.md — Group Quiz Feature (v8 Presentation Builder)

## [TECH_STACK]
- Vue 3 (Composition API, `<script setup>`)
- Pinia (`defineStore` function syntax)
- Quasar Framework (check app.js for global imports)
- ECharts + vue-echarts (Leaderboard)
- html2canvas + jsPDF (QR Print)
- Web Audio API (Sound effects)
- localStorage (Persistence — already in presentationStore)

## [SYSTEM_FLOW]
1. Teacher opens Group Setup dialog → CRUD groups / import JSON / print QR codes
2. Teacher opens Group Quiz Generator → AI prompt → paste JSON → preview → inject slides
3. Teacher enters Present Mode → unlocks group-mcq element → groups submit answers
4. Teacher grades → scores update → leaderboard auto-refreshes
5. Leaderboard slide shows ECharts bar chart of group standings

## [ARCHITECTURE]
```
v8/
├── stores/
│   ├── presentationStore.js  # Core presentation (existing)
│   ├── uiStore.js           # UI state (existing)
│   ├── clipboardStore.js    # Copy/paste (existing)
│   └── gameStore.js         # [PENDING] Group quiz state
├── composables/
│   ├── useDrag.js           # Existing
│   ├── useResize.js         # Existing
│   ├── useSnap.js           # Existing
│   ├── usePaste.js          # Existing
│   ├── useGamePersistence.js # [PENDING] Auto-save game state
│   ├── useQrCodec.js        # [PENDING] Robust QR encode/decode
│   └── useAIGroupQuiz.js    # [PENDING] AI → group-mcq element generator
├── components/
│   ├── EditorCanvas.vue      # Existing
│   ├── ElementNode.vue       # Existing — needs group-mcq rendering
│   ├── SlideCanvasReadonly.vue # Existing — needs group-mcq in present mode
│   ├── quiz-v1/             # Existing single-user quiz
│   └── group-quiz/          # [PENDING] Group quiz feature
│       ├── GroupSetupDialog.vue     # Group CRUD, scoring config, QR print
│       ├── GroupQuizGenerator.vue   # AI prompt builder + inline edit
│       ├── InteractiveGroupMCQ.vue  # Teacher present-mode interaction
│       ├── GroupLeaderboard.vue     # ECharts bar chart
│       ├── QrPrintSheet.vue         # Reusable A4 QR layout
│       └── QrScanInput.vue          # Visual scanner buffer + hardware wedge
└── Index.vue                # Main orchestrator — register group quiz tools
```

## [ORPHANS & PENDING]
- `[Step 1]` Create `stores/gameStore.js` — group/score/questionHistory state with persistence
- `[Step 2]` Create `composables/useQrCodec.js` — robust QR encode/decode with checksum
- `[Step 3]` Create `components/group-quiz/QrPrintSheet.vue` — reusable A4 QR print layout
- `[Step 4]` Create `components/group-quiz/QrScanInput.vue` — visual scanner buffer input
- `[Step 5]` Create `components/group-quiz/GroupSetupDialog.vue` — group CRUD + scoring config + QR print
- `[Step 6]` Create `components/group-quiz/GroupQuizGenerator.vue` — AI prompt → preview → inject
- `[Step 7]` Create `components/group-quiz/InteractiveGroupMCQ.vue` — teacher present-mode grading
- `[Step 8]` Create `components/group-quiz/GroupLeaderboard.vue` — ECharts visualization
- `[Step 9]` Wire group quiz into `ElementNode.vue` + `SlideCanvasReadonly.vue`
- `[Step 10]` Add group quiz toolbar buttons to `Index.vue`
