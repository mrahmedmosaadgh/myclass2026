## Group Quiz Feature — Deep Analysis

### Files Involved (7 files, ~3,500 lines)

| File | Role | Lines |
|---|---|---|
| `stores/gameStore.js` | State: groups, scores, settings, answer history, session | 157 |
| `GroupSetupModal.vue` | CRUD groups, scoring rules, JSON import, QR print | 876 |
| `InteractiveGroupMCQ.vue` | Teacher-side MCQ interaction, QR scanner, grading | 1,191 |
| `GroupQuizGenerator.vue` | AI prompt builder → paste AI JSON → preview → inject | 565 |
| `PrintGroupQrCodes.vue` | Standalone A4 QR code page (Print/PDF) | 309 |
| `composables/useAIPaste.js` | Element generator for `type: 'group-mcq'` + leaderboard | 132 |
| `LeaderboardSlide.vue` | ECharts bar chart leaderboard on slide | 290 |

---

### What Exists — Feature Breakdown

**Group Management:**
- CRUD groups with name, color (color picker), score
- JSON import/export (copy/paste with validation)
- Max 50 groups, with ID normalization

**Scoring:**
- `correctPoints: 10` (hardcoded), `wrongPoints: -5` (hardcoded in grading logic)
- Negative scoring toggle UX (but wrong points value not configurable in UI)

**Quiz Workflow:**
1. Teacher creates questions via AI generator (copy prompt → paste JSON → preview → inject)
2. `useAIPaste` converts to `type: 'group-mcq'` slide elements + auto-appends Leaderboard slide
3. In present mode: Unlock → Select group → Click answer → Grade → Scores update
4. Practice mode (no scoring) and Replay option

**QR Code System:**
- Prints A4 sheet: each group gets 4 QR codes (`g1_a`, `g1_b`, `g1_c`, `g1_d`)
- Scanner input via keyboard wedge (type `g1_a` + Enter)
- Camera QR scan dialog (`QrScanDialog`)
- Standalone print page + PDF download (`html2canvas` + `jsPDF`)

**Visual Feedback:**
- Group sidebar: idle/answering/answered status icons, pulsing active group
- Option badges: colored group name pills on chosen answers
- Graded: green check / red X per group, +/- point badges
- Sound effects: hover chime, correct/incorrect jingles

---

### What to Improve

**1. No Real Student Submission Path** (biggest gap)
The entire flow is teacher-driven — teacher selects group + clicks answer on their own screen. There's **no student-facing interface** where groups independently submit via their devices. QR scanning is a physical workaround, not a digital solution. The existing remote session infrastructure (`remote/StudentInteract.vue`, `remote/components/student/`) could be wired in.

**2. Game Store Mutations Break Reactivity**
`GroupSetupModal.vue:33` directly mutates `group.score = Number(val)`. Vue 3 `ref()` arrays should use store actions or `.value` splice replacement. This can cause stale UI in leaderboard/computed properties.

**3. Massive QR Code Print Logic Duplication**
The same A4 QR layout (~250 lines) exists in **3 places**: `GroupSetupModal.vue` (QR tab), `InteractiveGroupMCQ.vue` (inline modal), and `PrintGroupQrCodes.vue` (standalone page). Should be extracted to a shared composable or renderless component.

**4. Scoring Not Configurable in UI**
- `correctPoints` (=10) never exposed — no slider/input in settings
- `wrongPoints` (=0 default, -5 effective) — `allowNegativeScore` toggle exists in UI but the actual `-5` is hardcoded in `InteractiveGroupMCQ.vue:187`
- No per-question point override

**5. Practice Mode Answers Leak Into Real Grading**
After `startPractice()` → answers go into `questionHistory`. If teacher locks then grades without resetting history first, practice answers get scored. `lockQuiz()` clears `isPracticeMode` but not the stored answers.

**6. No Undo for Single Group Grade**
After grading, the only options are "Reset Quiz" (wipes ALL) or nothing. No per-group undo/correct. Teachers often need to fix one group's accidental click.

**7. No Persistent Storage**
Groups, scores, question history exist only in-memory (`gameStore`). Page refresh = data loss. No `localStorage`/`IndexedDB` fallback.

**8. Scanner Has No Typing Field / No Visual Buffer**
Teachers type `g1_a` blindly into the keyboard buffer with **no visible text field** showing what they're typing. Only after Enter does it process. Requires hidden `<input>` or on-screen buffer display.

**9. Event Listener Cleanup Risk**
`InteractiveGroupMCQ.vue:292` registers a global `keydown` listener for scanner input. On component unmount, if `isPrintQrOpen` was true, it blocks scanner processing but the listener could still fire if component destruction races with cleanup.

**10. QR Payload Format Is Fragile**
Pattern `g<id>_<letter>` is parsed with regex `g[a-z]?(\d+)[-_]([a-z])` which breaks if group IDs contain letters or special characters. No checksum/validation to detect scanning errors.

**11. No Mobile / Small Screen Layout**
Group sidebar (220px) sits beside a 2-column grid of options. No breakpoint/stacking for screens < 768px. Unusable on tablets/phones in portrait.

**12. AI Question Generator Missing Edit Mode Options**
The generator copies a clever prompt, but the generated questions only show in a read-only preview. If formatting is wrong, user must go back to AI tool — no in-line editing before submission.

**13. Leaderboard Auto-Append Creates Blank Slide**
`GroupQuizGenerator.vue:140` calls `addSlide()` then `addElement()` but doesn't set a slide title — students see an empty slide with just the chart. No transition/flourish.