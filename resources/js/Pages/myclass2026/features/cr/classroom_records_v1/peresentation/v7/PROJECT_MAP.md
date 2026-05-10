# PROJECT_MAP.md — Group Quiz Feature (v7 Presentation)

## [TECH_STACK]
- Vue 3 (Composition API, `<script setup>`)
- Pinia (`defineStore` with function syntax)
- Quasar Framework (check `app.js` for global imports)
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

## [ARCHITECTURE]
```
v7/
├── stores/
│   └── gameStore.js          # Group/score state, question history, session
├── composables/
│   ├── useAIPaste.js          # Question → slide element generator
│   └── (pending) useQrPrint.js        # [ORPHAN] Extract QR print logic
│   └── (pending) useQrCodec.js        # [ORPHAN] Robust QR encode/decode
│   └── (pending) useGamePersistence.js # [ORPHAN] localStorage persistence
├── components/
│   ├── GroupSetupModal.vue    # Group CRUD, scoring rules, QR print
│   ├── InteractiveGroupMCQ.vue # Teacher MCQ interaction (1,191 lines)
│   ├── GroupQuizGenerator.vue # AI prompt builder, preview, inject
│   ├── LeaderboardSlide.vue   # ECharts visualization
│   └── (pending) QrPrintSheet.vue     # [ORPHAN] Reusable QR print layout
│   └── (pending) QrScanInput.vue        # [ORPHAN] Visual scanner buffer
│   └── (pending) GroupQuizCard.vue      # [ORPHAN] Student-facing quiz
└── remote/
    ├── StudentInteract.vue    # Student view router
    └── components/student/    # Student UI components
```

## [ORPHANS & PENDING]
- `[P0-1]` Student digital submission interface (no student-facing group quiz UI)
- `[P0-2]` Direct state mutations in `GroupSetupModal.vue:33` break Vue 3 reactivity
- `[P1-1]` Practice mode answers leak into real grading
- `[P1-2]` Scoring values not configurable in UI (hardcoded 10 / -5)
- `[P2-1]` QR print logic duplicated in 3 files (~250 lines each)
- `[P2-2]` No persistent storage (page refresh = data loss)
- `[P3-1]` No per-group undo after grading
- `[P3-2]` Scanner has no visual text field / buffer display
- `[P3-3]` No responsive layout for screens < 768px
- `[P4-1]` Event listener cleanup race condition on unmount
- `[P4-2]` QR payload regex fragile, no checksum
- `[P4-3]` No inline question editing in generator preview
- `[P4-4]` Leaderboard slide missing title element
