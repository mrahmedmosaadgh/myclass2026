# Student Presentation View — Enhancement Plan

**Date:** 2026-05-15  
**Scope:** Fix student view + enhance quiz with sounds, effects, auto-submit

---

## Root Cause Analysis (From Screenshot)

| Bug | Root Cause |
|-----|-----------|
| Left slides panel showing | `SlideNavigationBar` is a sidebar component (with Add/Delete), wrong component used |
| "Slide NaN of NaN" | `PresentModeController` requires `currentIndex` + `totalSlides` props — none were passed |
| Slide canvas too small/cropped | `SlideCanvasReadonly` is fixed 800×600, no responsive scaling |
| Quiz rendered tiny | Canvas not scaling to viewport, quiz element too small |

---

## Fix 1: StudentPresentation.vue — Full Rewrite

**Problems to solve:**
- Remove `SlideNavigationBar` (it's the editor sidebar, not a slide counter)
- Remove `PresentModeController` (it's for the builder, needs props that aren't passed)
- Replace with clean bottom nav bar: ← Slide N of M →
- Fix "NaN" by reading directly from `presentation.currentSlideIndex` + `presentation.totalSlides`
- Give canvas full available viewport height

**New layout:**
```
[Header: Title | Slide N of M | Share | Fullscreen]
[Canvas Area: auto-scaled to fill container — NO sidebar]
[Bottom Nav: ← prev | Slide N / M | next → ]
```

---

## Fix 2: SlideCanvasReadonly.vue — Responsive Scaling

**Problem:** Fixed 800×600 with no scaling — appears tiny or cropped on any screen.

**Solution:** Apply CSS `transform: scale()` to fit the 800×600 canvas into its container, like a projector:
```js
// containerWidth / 800 gives scale ratio, same for height
// use min(scaleX, scaleY) to fit without cropping
```

**Approach:**
- Wrap canvas in a `div.canvas-scaler` that fills its parent
- Use `ResizeObserver` to watch container size
- Compute `scale = Math.min(containerW / 800, containerH / 600)`
- Apply `transform: scale(scale)` with `transform-origin: top center`
- The wrapper should have the scaled visual height to prevent collapse

---

## Fix 3: New Composable — `useQuizSounds.js`

**Location:** `/v8/composables/useQuizSounds.js`

**Built with Web Audio API (zero external dependencies)**

| Sound | Trigger | Description |
|-------|---------|-------------|
| `playClick()` | Any option tap | Soft click/tap sound |
| `playCorrect()` | Correct answer selected | Ascending chime (C-E-G) |
| `playWrong()` | Wrong answer selected | Low buzz/thud |
| `playTimerTick()` | Timer ≤ 10s countdown | Soft tick |
| `playComplete()` | Quiz results shown | Victory fanfare |

**Mute toggle:** stored in `localStorage` as `quiz-sounds-muted`

**Implementation:** Pure oscillator nodes via `AudioContext` — no audio files needed.

---

## Fix 4: QuizElementV2.vue — Enhanced Quiz Experience

### 4a. Sound Integration
- Import `useQuizSounds`
- Call `playClick()` on any option click
- Call `playCorrect()` / `playWrong()` after answer revealed
- Call `playTimerTick()` when `timerRemaining <= 10`
- Call `playComplete()` when `showResults` becomes true
- Mute button in quiz header (next to fullscreen btn)

### 4b. Visual Effects (CSS-only, no libraries)

**On correct answer:**
- Green flash animation on the selected option (`@keyframes correctFlash`)
- Floating "+10 pts" score pop-up that floats up and fades
- ✨ Confetti burst (5–8 divs with CSS animation, spawned via JS)

**On wrong answer:**
- Red shake animation on selected option (`@keyframes shake`)
- `-` penalty indicator if penalty > 0

**Answer options:**
- Staggered slide-in animation when question loads (`@keyframes slideInUp` with delay per option)
- Hover: scale(1.02) lift effect

**Progress bar:**
- Smooth animated width transition (CSS transition: width 0.5s ease)

**Timer:**
- Circular SVG ring timer that drains as time passes
- Green → Yellow (at 50%) → Orange (at 25%) → Red (at 10s)
- Pulse animation when `timerRemaining <= 5`

**Results screen:**
- Score number counts up from 0 to final value (JS counter animation)
- Per-question result items slide in with stagger delay

### 4c. Auto-Submit vs Manual Next

**New setting:** `autoSubmitOnAnswer` (Boolean, default `true`)

Behavior:
- **`true` (default):** Current behavior — quiz auto-advances after `autoAdvanceDelay`ms
- **`false`:** After selecting an answer, show feedback but display a "Next →" button — student must click to advance

**In QuizGeneratorDialog:** Add toggle for "Auto-advance after answer"

**In QuizElementV2:** Read `settings.autoSubmitOnAnswer`, conditionally show "Next →" button and skip auto-advance timer.

---

## Fix 5: QuizGeneratorDialog.vue — New Settings

Add to the settings section:
- **Auto-advance toggle:** "Auto-advance to next question after answering"  
  → `settings.autoSubmitOnAnswer` (Boolean, default `true`)

---

## Files to Modify

| File | Change |
|------|--------|
| `StudentPresentation.vue` | Full rewrite — clean layout, responsive, no sidebar |
| `SlideCanvasReadonly.vue` | Add responsive scaling via ResizeObserver + CSS transform |
| `composables/useQuizSounds.js` | **New** — Web Audio API sounds composable |
| `components/quiz-v2/QuizElementV2.vue` | Sounds, visual effects, auto-submit logic, enhanced timer |
| `components/quiz-v2/QuizGeneratorDialog.vue` | Add `autoSubmitOnAnswer` setting toggle |

---

## Implementation Order

1. **SlideCanvasReadonly.vue** — scaling fix (unblocks everything visible)
2. **StudentPresentation.vue** — rewrite (removes sidebar, fixes NaN)
3. **useQuizSounds.js** — new composable
4. **QuizElementV2.vue** — sounds + effects + auto-submit
5. **QuizGeneratorDialog.vue** — add autoSubmit setting

---

## Testing Checklist

- [ ] No left slides panel visible in student view
- [ ] "Slide N of M" shows correct numbers (no NaN)
- [ ] Slide canvas scales to fill viewport on desktop + mobile
- [ ] Quiz renders correctly at any screen size
- [ ] Click sound plays on option tap
- [ ] Correct sound + green flash on correct answer
- [ ] Wrong sound + shake on wrong answer
- [ ] Timer ring drains, color changes at 50%/25%/10s
- [ ] Timer tick plays at ≤10s
- [ ] Confetti on correct answer
- [ ] Score counts up on results screen
- [ ] Auto-advance works when `autoSubmitOnAnswer: true`
- [ ] "Next →" button shown when `autoSubmitOnAnswer: false`
- [ ] Mute button silences all sounds
- [ ] Attempt saved correctly in both modes
