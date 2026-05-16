# Student Page UI Improvement Plan — Mobile First

**Date:** 2026-05-16  
**Status:** AWAITING APPROVAL

---

## Problem Analysis (from screenshot)

Looking at the current state:
1. **Quiz is tiny** — The slide canvas is 800×600px scaled down to fit mobile (~40% scale), making quiz text, options, and buttons barely readable
2. **Wasted space** — Huge empty dark area below the tiny quiz card
3. **Header is too tall** — 3 buttons (Submit Results / Share / Fullscreen) take up valuable screen real estate
4. **Quiz positioned for desktop** — Quiz element uses fixed `x, y, width, height` pixel values designed for the 800×600 canvas, not mobile
5. **White canvas on black page** — The white slide background against the dark page looks jarring
6. **No visual progress** — Just "Slide 2 of 3" text — no sense of momentum

---

## Root Cause

`SlideCanvasReadonly` renders an **800×600 canvas** and scales it down using `scale(n)` to fit the screen. On a 390px wide phone, this gives scale ≈ 0.43x. Every element on the canvas (including the quiz) inherits this tiny scale — making fonts ~10px and buttons barely tappable.

---

## Solution: Smart Mode Detection

When the current slide contains a `quiz-v2` element, **skip the canvas entirely** and render the quiz natively at full width. When the slide has no quiz, render the canvas with improved scaling.

```
Has quiz on slide?
  YES → Quiz Native Mode (full width, no canvas wrapper)
  NO  → Canvas Mode (improved scaling for mobile)
```

---

## Changes Required (5 files + 1 new component)

---

### Step 1 — New component: `StudentQuizView.vue`

**File:** `components/StudentQuizView.vue` (new)  
**Purpose:** Render a quiz-v2 element natively at full width without canvas scaling overhead.

- Accepts `element` prop (the quiz-v2 element from the slide)
- Uses `QuizElementV2` directly but wraps it with full-width mobile layout
- Removes the canvas-level `width: 680px` / `height: 520px` constraints
- The quiz fills 100% of the available width
- Auto-height (scrollable if needed)

---

### Step 2 — `StudentPresentation.vue` — Smart mode switching

**File:** `StudentPresentation.vue`  
**Changes:**

1. **Add `quizElement` computed** — finds first `quiz-v2` element on current slide
2. **Add `slideHasQuiz` computed** — `true` if `quizElement` is found  
3. **Main content area:**
   - If `slideHasQuiz` → render `<StudentQuizView :element="quizElement" />`
   - Else → render `<SlideCanvasReadonly>` (existing, with improved styling)
4. **Import `StudentQuizView`**

---

### Step 3 — `StudentPresentation.vue` — Full UI overhaul

#### Header (new design — 2 rows)
```
┌─────────────────────────────────────────────┐
│ [←] Slide 2 of 3              [⋮ Menu]      │  ← Row 1: slim
│ ████████████░░░░░░░░  2/3 done              │  ← Row 2: progress bar
└─────────────────────────────────────────────┘
```
- Collapse Submit/Share/Fullscreen into a single `⋮` overflow menu (q-menu)
- Progress bar showing percentage done (green fill)
- Clean white/light background for header instead of dark

#### Content area
- White/light card background for slides
- No overflow, no wasted black space
- For quiz mode: quiz fills 100% of viewport between header and nav bar

#### Bottom navigation (new design)
```
┌─────────────────────────────────────────────┐
│  ← Previous       2 / 3         Next →     │
└─────────────────────────────────────────────┘
```
- Larger tap targets: minimum 56px height
- Full-width layout
- Disable state shown clearly
- Swipe gesture support (touchstart/touchend) for mobile

#### Color scheme
- Background: `#f8fafc` (light gray, not dark)
- Header: white with bottom shadow
- Content: white card with shadow
- Accent: indigo (`#6366f1`)
- Nav: white bottom bar

---

### Step 4 — `SlideCanvasReadonly.vue` — Better mobile scaling

**File:** `components/SlideCanvasReadonly.vue`  
**Changes:**

1. **Scale calculation fix:** Currently uses `Math.min(scaleX, scaleY, 1.2)` which is limited by height. On mobile, the height limit causes unnecessary shrinkage.
   - New formula: use `scaleX` only (fit to width), capped at 1.0 max
   - `scale.value = Math.min(scaleX, 1.0)`
2. **Remove grid background** in student/present mode (adds noise, not needed for students)
3. **Wrapper styling:** Center content vertically but don't shrink it

---

### Step 5 — `StudentPresentation.vue` — Identifier dialog improvement

**Current state:** Quasar dialog, minimal.  
**Improvement:**
- Full-screen mobile-friendly dialog (not floating card)
- Welcome message + school icon
- Larger input field
- Friendly copy: "What's your name?" not "Enter Your Name"
- "Let's Start →" button (larger, full-width)

---

## Visual Layout — Quiz Mode (mobile, 390px)

```
┌──────────────────────────────────┐  ← Status bar
│  Student Presentation    2/3 [⋮] │  ← Header (56px)
│  ██████████░░░░░░░░░░░░░░░░░░░  │  ← Progress bar (4px)
├──────────────────────────────────┤
│                                  │
│  1. A ......... was a soldier in │  ← Question text (18px)
│                                  │
│  ┌──────────────────────────────┐│
│  │ A  champion                  ││  ← Option (56px height)
│  └──────────────────────────────┘│
│  ┌──────────────────────────────┐│
│  │ B  chariot                   ││
│  └──────────────────────────────┘│
│  ┌──────────────────────────────┐│
│  │ C  knight                    ││
│  └──────────────────────────────┘│
│  ┌──────────────────────────────┐│
│  │ D  firefighter               ││
│  └──────────────────────────────┘│
│                                  │
├──────────────────────────────────┤
│  ← Previous    2/3    Next →    │  ← Nav bar (64px)
└──────────────────────────────────┘
```

---

## Visual Layout — Non-Quiz Slide (mobile)

```
┌──────────────────────────────────┐
│  Student Presentation    1/3 [⋮] │
│  ██░░░░░░░░░░░░░░░░░░░░░░░░░░░  │
├──────────────────────────────────┤
│                                  │
│  ┌────────────────────────────┐  │
│  │                            │  │
│  │   [Slide content scaled    │  │
│  │    to fit width]           │  │
│  │                            │  │
│  └────────────────────────────┘  │
│                                  │
├──────────────────────────────────┤
│  ← Previous    1/3    Next →    │
└──────────────────────────────────┘
```

---

## Files to Create/Modify

| File | Action | Description |
|------|--------|-------------|
| `components/StudentQuizView.vue` | **CREATE** | Renders quiz-v2 at full native width |
| `StudentPresentation.vue` | **MODIFY** | Smart mode, full UI overhaul |
| `components/SlideCanvasReadonly.vue` | **MODIFY** | Better mobile scaling formula |

---

## What is NOT changing
- Quiz logic (QuizElementV2 is reused as-is)
- Store / API / backend
- Teacher side
- Share token flow

---

## Expected Result
- Quiz text readable at native font size (~16-18px)
- Option buttons large enough to tap easily (56px)
- No wasted black space
- Clean light UI that looks professional
- Progress bar gives students a sense of completion
- Single-tap ⋮ menu to submit/share instead of 3 separate buttons taking up header space

---

**Awaiting your approval to begin coding.**
